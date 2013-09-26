<?php

App::uses('TransactionsAppModel', 'Transactions.Model');

/**
 * TransactionItem Model
 *
 * @property Product $Product
 * @property Transaction $Transaction
 * @property Customer $Customer
 * @property Contact $Contact
 * @property Assignee $Assignee
 * @property Creator $Creator
 * @property Modifier $Modifier
 */
class _TransactionItem extends TransactionsAppModel {

    public $name = 'TransactionItem';

    public $displayField = 'name';

    public $validate = array(
	   'price' => array(
            'notempty' => array(
                'rule' => array('notempty'),
                'message' => '*required',
            	),
        	),
       );



/**
 * belongsTo associations
 *
 * @var array
 */
    public $belongsTo = array(
		'Transaction' => array(
			'className' => 'Transactions.Transaction',
			'foreignKey' => 'transaction_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'Assignee' => array(
			//'className' => 'Users.Assignee',
			'className' => 'Users.User',
			'foreignKey' => 'assignee_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'Customer' => array(
			//'className' => 'Users.Assignee',
			'className' => 'Users.User',
			'foreignKey' => 'customer_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
    );

/**
 * Constructor method
 *
	public function __construct($id = false, $table = null, $ds = null) {
    	parent::__construct($id, $table, $ds);
    }
 */

/**
 * After Find Callback
 *
 */
	public function afterFind($results, $primary = false) {
		if (!empty($results[0]['TransactionItem']['model'])) {
            // let the model say how the associated record should look
			$models = Set::extract('/TransactionItem/model', $results);
            foreach ($models as $model) {
				$model = Inflector::classify($model);
				try {
	                App::uses($model, ZuhaInflector::pluginize($model).'.Model');
	                $Model = new $model;
	                if (method_exists($Model, 'origin_afterFind') && is_callable(array($Model, 'origin_afterFind'))) {
	                    $results = $Model->origin_afterFind($this, $results, $primary);
	                }
				} catch (Exception $e) {
					// we get here sometimes if the plugin doesn't exist (virtual / test plugins)
					// do nothing, we just don't fire the "origin_afterFind" method
				}
            }
		}
	    return $results;
	}

/**
 * Creates a new cart or returns the id of the existing cart for a user, based on their user id
 *
 * @param integer $userId The UUID of a current or future User, who is currently using a Transaction cart.
 * @return string Id of the cart in question
 */
    public function setCartId($userId = null) {

		if(empty($userId)) {
			$userId = $this->Transaction->getCustomersId();
		}

	  // an item was added, check for an open shopping cart.
	  $myCart = $this->Transaction->find('first', array(
		  'conditions' => array('customer_id' => $userId, 'status' => 'open')
		  ));
	  if (!$myCart) {
		  // no cart found. give them a new shopping cart.
		  $this->Transaction->create(array(
			'customer_id' => $userId,
			'status' => 'open'
		  ));
		  $this->Transaction->save();
	  } else {
		  // existing shopping cart found..  use it.
		  $this->Transaction->id = $myCart['Transaction']['id'];
	  }

	  return $this->Transaction->id;
    }


/**
 * This function ensures that a TransactionItem has it's fields filled out correctly
 * by calling upon the Model that the Item belongs to.
 * @param array $data
 * @return array
 */

    public function mapItemData($data) {
		if (empty($data['TransactionItem']['model'])) {
			throw new InternalErrorException(__('Invalid transaction item'));
		}
		$model = Inflector::classify($data['TransactionItem']['model']);
		
		try {
			App::uses($model, ZuhaInflector::pluginize($model) . '.Model');
			$Model = new $model;
			if (method_exists($Model, 'mapTransactionItem') && is_callable(array($Model, 'mapTransactionItem'))) {
				$itemData = $Model->mapTransactionItem($data['TransactionItem']['foreign_key']);
			}
		} catch (Exception $e) {
			// we get here sometimes if the plugin doesn't exist (virtual / test plugins)
			// do nothing, we just don't fire the "origin_afterFind" method
		}
		
		if (empty($data['TransactionItem']['price'])) {
			$data['TransactionItem']['price'] = $itemData['TransactionItem']['price'];
		}

		$itemData = Set::merge(
			$itemData,
			$data,
			array(
				'TransactionItem' => array(
				'transaction_id' => $this->Transaction->id,
				'customer_id' => $this->Transaction->getCustomersId()
				))
			);
			
		return $itemData;
		
	}


/**
 * Verify Item Request
 *
 * Used to check whether the item being added to the cart
 * is incompatible with other items in the cart.
 *
 * ARB:
 * - Check to see if transaction already has an ARB item, if so, disregard their current request
 * - If they are adding their first ARB item, we need to make sure it's serialized.
 * -- TransactionItems can have ARB settings in them, or it can come from the Product itself.
 *
 * @todo ($transaction =) doesn't seem to be right.  Kind of like it wouldn't contain the TransactionItem, and that $this->Transaction->id isn't the best variable name to use.
 * @todo check stock and cart max and ARB
 * @param array $data
 */
    public function verifyItemRequest($data) {

		// check for a model
		if(empty($data['TransactionItem']['model'])) {
			throw new Exception(__d('transactions', 'Invalid transaction request [M]'));
		}

		$isArb = false;

		// check to see if this Transaction already contains an ARB item
		$transaction = $this->Transaction->find('first', array(
			'conditions' => array('id' => $this->Transaction->id),
			'contain' => 'TransactionItem'
			));
        if (!empty($transaction['TransactionItem'])) {
            foreach ($transaction['TransactionItem'] as $transactionItem) {

				// check to see if this TransactionItem's Model record has arb_settings
                App::uses($data['TransactionItem']['model'], ZuhaInflector::pluginize($data['TransactionItem']['model']) . '.Model');
                $Model = new $data['TransactionItem']['model'];

                $product = $Model->findById($transactionItem['foreign_key']);

                if( !empty($product['arb_settings']) || !empty($transactionItem['arb_settings']) || !empty($data['TransactionItem']['arb_settings'])) {
                    $isArb = true;
					break;//foreach()
                }
            }
        }

		if($isArb && count($transaction['TransactionItem']) > 1) {
			// you can only have one item in your cart if one of the items is using ARB
			throw new NotFoundException(__d('transactions', 'Item payment plans not compatible.  Please checkout or remove an item.'));;
		} else {
			return;
		}

    }


/**
 * Given the correct data, this method will add an item to the user's cart.
 *
 * @param array $data
 * @return boolean
 * @todo check stock and cart max 
 */
	public function addItemToCart($data) {
		try {
			// determine and set the transaction id (cart id) for this user
			$this->Transaction->id = $this->setCartId();
			$this->verifyItemRequest($data);
			$itemData = $this->mapItemData($data);
			// Check if the TransactionItem already exists in this Transaction
			$conditions = array(
				'TransactionItem.transaction_id' => $this->Transaction->id,
				'TransactionItem.model' => $data['TransactionItem']['model'],
				'TransactionItem.foreign_key' => $data['TransactionItem']['foreign_key']
			);
			$chkdata = $this->find('all', array('conditions' => $conditions));
			if (empty($chkdata)) {
				// create the item internally
				$this->create($itemData);
			} else {
				$data['TransactionItem']['quantity'] = $chkdata[0]['TransactionItem']['quantity'] + $data['TransactionItem']['quantity'];
				$data['TransactionItem']['quantity'] = $data['TransactionItem']['quantity'] > $data['TransactionItem']['cart_max'] ? $data['TransactionItem']['cart_max'] : $data['TransactionItem']['quantity'];
				$data['TransactionItem']['quantity'] = $data['TransactionItem']['quantity'] < $data['TransactionItem']['cart_min'] ? $data['TransactionItem']['cart_min'] : $data['TransactionItem']['quantity']; 
				$this->id = $chkdata[0]['TransactionItem']['id'];
			}
			return $this->save($data);
		} catch (Exception $e) {
			throw new Exception(__d('transactions', $e->getMessage()));
		}
	}

/**
 * Before save callback
 * 
 */
	public function beforeSave($options = array()) {
		// make sure we have an arb payment amount if arb is set
		if (!empty($this->data['TransactionItem']['arb_settings']) && unserialize($this->data['TransactionItem']['arb_settings'])) {
			$this->data['TransactionItem']['arb_settings'] = unserialize($this->data['TransactionItem']['arb_settings']);
		}
		if(!empty($this->data['TransactionItem']['arb_settings']) && is_array($this->data['TransactionItem']['arb_settings'])) {
			if (empty($this->data['TransactionItem']['arb_settings']['PaymentAmount'])) {
				$this->data['TransactionItem']['arb_settings']['PaymentAmount'] = $this->data['TransactionItem']['price'];
			}
			$this->data['TransactionItem']['arb_settings'] = serialize($this->data['TransactionItem']['arb_settings']);
		}

		return parent::beforeSave($options);
	}

/**
 * Statuses method
 * A list of pre-defined, and user created transaction item statuses
 * 
 * @return array
 */
    public function statuses() {
        $statuses = array();
        foreach (Zuha::enum('ORDER_ITEM_STATUS') as $status) {
            $statuses[Inflector::underscore($status)] = $status;
        }
        return Set::merge(array('incart' => 'In Cart', 'paid' => 'Paid', 'shipped' => 'Shipped', 'used' => 'Used'), $statuses);
    }

}

if ( !isset($refuseInit) ) {
	class TransactionItem extends _TransactionItem {}
}
