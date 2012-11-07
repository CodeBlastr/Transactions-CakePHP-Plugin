<?php
App::uses('TransactionsAppModel', 'Transactions.Model');
/**
 * TransactionAddress Model
 *
 * @property Transaction $Transaction
 * @property User $User
 * @property TransactionItem $TransactionItem
 */
class TransactionAddress extends TransactionsAppModel {
	
	public $name = 'TransactionAddress';
	
/**
 * Display field
 *
 * @var string
 */
	//public $displayField = 'name';


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
		'User' => array(
			'className' => 'Users.User',
			'foreignKey' => 'user_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
	);

}
