<?php
// Buyable Test
App::uses('BuyableBehavior', 'Transactions.Model/Behavior');


if (!class_exists('TransactionArticle')) {
	class TransactionArticle extends CakeTestModel {
	/**
	 *
	 */
		public $callbackData = array();

	/**
	 *
	 */
		public $actsAs = array(
			'Transactions.Buyable'
			);
	/**
	 *
	 */
		public $useTable = 'transaction_articles';

	/**
	 *
	 */
		public $name = 'Article';
	/**
	 *
	 */
		public $alias = 'Article';
	}
}


if (!class_exists('MockSession')) {
	class MockSession {
	/**
	 * read
	 */
		public function read() {
			return array('Auth' => array(
				'User', array(
					'id' => 2,
					'username' => 'admin',
					)
				));
		}
	}
}


/**
 * BuyableBehavior Test Case
 *
 */
class BuyableBehaviorTestCase extends CakeTestCase {
/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'plugin.Products.Product',
		
		'plugin.Transactions.TransactionArticle',
		'plugin.Transactions.Transaction',
		'plugin.Transactions.TransactionTax',
		'plugin.Transactions.TransactionItem',
		'plugin.Transactions.TransactionAddress',
		
		
		'plugin.Users.User',
		'plugin.Contacts.Contact',
		'plugin.Connections.Connection',
		);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();

		$this->Buyable = new BuyableBehavior();
		$this->Article = Classregistry::init('TransactionArticle');
		$this->Product = Classregistry::init('Products.Product');
		$this->Transaction = Classregistry::init('Transactions.Transaction');
		
		if (!class_exists('CakeSession')) {
			App::uses('CakeSession', 'Model/Datasource'); 
		} 
	}

/**
 * tearDown method
 *
 * @return void
 */
    public function tearDown() {
		unset($this->Draftable);
		unset($this->Article);
		unset($this->Product);
		unset($this->Transaction);
		ClassRegistry::flush();

		parent::tearDown();
	}
	

/**
 * Test behavior instance
 *
 * @return void
 */
	public function testBehaviorInstance() {
		$this->assertTrue(is_a($this->Article->Behaviors->Buyable, 'BuyableBehavior'));
	}


/**
 * Test finding 
 */ 
	public function testFinding() {
	}

}
