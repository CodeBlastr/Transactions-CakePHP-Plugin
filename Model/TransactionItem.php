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
 * Creates a new cart or returns the id of the existing cart for a user, based on their user id
 * 
 * @param integer $userId The UUID of a current or future User, who is currently using a Transaction cart.
 * @return string Id of the cart in question
 */
    public function setCartId($userId) {
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
				array('TransactionItem' => array('transaction_id' => $this->Transaction->id))
				);

		return $itemData;
	}
    
    
/**
 * @todo check stock and cart max and ARB
 * @param array $data
 */
    public function verifyItemRequest($data) {
		
		$isArb = false;
		$transaction = $this->Transaction->findById($this->Transaction->id);
       // debug($transaction);
       // break;
		foreach ($transaction['TransactionItem'] as $transactionItem) {
			App::uses($data['TransactionItem']['model'], ZuhaInflector::pluginize($data['TransactionItem']['model']) . '.Model');
			$Model = new $data['TransactionItem']['model'];
			$product = $Model->findById($transactionItem['foreign_key']);
			if(!empty($product['arb_settings'])) {
				$isArb = true;
			}
		}
		
		if($isArb && count($transaction['TransactionItem']) > 1) {
			// you can only have one item in your cart if one of the items is using ARB
			return false;
		} else {
			return true;
		} 

    }

	
    public function statuses() {
        $statuses = array();
        foreach (Zuha::enum('ORDER_ITEM_STATUS') as $status) {
            $statuses[Inflector::underscore($status)] = $status;
        }
        return Set::merge(array('incart' => 'In Cart', 'paid' => 'Paid', 'shipped' => 'Shipped'), $statuses);
    }
    
}
