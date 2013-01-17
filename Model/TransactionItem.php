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
class TransactionItem extends TransactionsAppModel {

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
                App::uses($model, ZuhaInflector::pluginize($model).'.Model');
                $Model = new $model;
                if (method_exists($Model, 'origin_afterFind') && is_callable(array($Model, 'origin_afterFind'))) {
                    $results = $Model->origin_afterFind($this, $results, $primary);
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
        $m = ZuhaInflector::pluginize($model) . '.Model' ;
		App::uses($model, ZuhaInflector::pluginize($model) . '.Model');
		$Model = new $model;
		$itemData = $Model->mapTransactionItem($data['TransactionItem']['foreign_key']);

		$itemData = Set::merge(
				$itemData,
				$data,
				array('TransactionItem' => array(
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

	
	public function beforeSave($options) {
		// serialize ARB settings that were passed with the TransactionItem
		if(!empty($this->data['TransactionItem']['arb_settings'])) {
			$this->data['TransactionItem']['arb_settings'] = serialize($this->data['TransactionItem']['arb_settings']);
		}
		return parent::beforeSave($options);
	}
	
	
    public function statuses() {
        $statuses = array();
        foreach (Zuha::enum('ORDER_ITEM_STATUS') as $status) {
            $statuses[Inflector::underscore($status)] = $status;
        }
        return Set::merge(array('incart' => 'In Cart', 'paid' => 'Paid', 'shipped' => 'Shipped'), $statuses);
    }
    
}
