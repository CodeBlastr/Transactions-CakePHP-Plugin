<?php
/**
 * TransactionItemFixture
 *
 */
class TransactionItemFixture extends CakeTestFixture {
    
    public $name = 'TransactionItem';    


/**
 * Import
 *
 * @var array
 */
    public $import = array('config' => 'Transactions.TransactionItem');
    
    

/**
 * Records
 *
 * @var array
 */
	public $records = array(
		// two items for the Customer.id 5738299d-9040-43c9-85b1-22d400000001
		array(
			'id' => '50773d75-cab4-40dd-b34c-187800000001',
			'name' => 'Test Item #1',
			'transaction_id' => '5043572d-9040-43c9-85b1-22d400000002',
			'quantity' => 1,
			'price' => 2,
			'weight' => 1,
			'height' => 1,
			'width' => 1,
			'length' => 1,
			'status' => '',
			'tracking_no' => '',
			'location' => '',
			'deadline' => '2012-10-11',
			'arb_settings' => '',
			'payment_type' => '',
			'featured' => 1,
			'foreign_key' => '',
			'model' => '',
			'is_virtual' => 1,
			'hours_expire' => 1,
			'customer_id' => '5738299d-9040-43c9-85b1-22d400000001',
			'assignee_id' => '',
			'creator_id' => '',
			'modifier_id' => '',
			'created' => '2012-10-11 21:43:17',
			'modified' => '2012-10-11 21:43:17'
		),
		array(
			'id' => '50773d75-cab4-40dd-b34c-187800000002',
			'name' => 'Test Item #2',
			'transaction_id' => '5043572d-9040-43c9-85b1-22d400000002',
			'quantity' => 2,
			'price' => 2,
			'weight' => 1,
			'height' => 1,
			'width' => 1,
			'length' => 1,
			'status' => '',
			'tracking_no' => '',
			'location' => '',
			'deadline' => '2012-10-11',
			'arb_settings' => '',
			'payment_type' => 'Lorem ipsum dolor sit amet',
			'featured' => 1,
			'foreign_key' => '',
			'model' => '',
			'is_virtual' => 1,
			'hours_expire' => 1,
			'customer_id' => '5738299d-9040-43c9-85b1-22d400000001',
			'assignee_id' => '',
			'creator_id' => '',
			'modifier_id' => '',
			'created' => '2012-10-11 21:45:55',
			'modified' => '2012-10-11 21:45:55'
		),
		// a single item for the logged in User.id 1
		array(
			'id' => '50773d75-cab4-40dd-b34c-187800000003',
			'name' => 'Test Item #3',
			'transaction_id' => '5077241d-9040-43c9-85b1-22d40000001',
			'quantity' => 1,
			'price' => 1,
			'weight' => 1,
			'height' => 1,
			'width' => 1,
			'length' => 1,
			'status' => '',
			'tracking_no' => '',
			'location' => '',
			'deadline' => '2012-10-11',
			'arb_settings' => '',
			'payment_type' => '',
			'featured' => 1,
			'foreign_key' => '',
			'model' => 'Product',
			'is_virtual' => 1,
			'hours_expire' => 1,
			'customer_id' => '1',
			'assignee_id' => '',
			'creator_id' => '',
			'modifier_id' => '',
			'created' => '2012-10-11 21:43:17',
			'modified' => '2012-10-11 21:43:17'
		),
		// a single ARB item for the guest User.id 5738299d-9040-43c9-85b1-22d400000002
		array(
			'id' => '50773d75-cab4-40dd-b34c-187800000004',
			'name' => 'ARB Test Item #3',
			'transaction_id' => '5043572d-9040-43c9-85b1-22d400000003',
			'quantity' => 1,
			'price' => 4,
			'weight' => 1,
			'height' => 1,
			'width' => 1,
			'length' => 1,
			'status' => '',
			'tracking_no' => '',
			'location' => '',
			'deadline' => '2012-10-11',
			'arb_settings' => 'a:7:{s:13:"PaymentAmount";s:1:"4";s:18:"FirstPaymentAmount";s:0:"";s:16:"FirstPaymentDate";s:0:"";s:9:"StartDate";s:0:"";s:7:"EndDate";s:0:"";s:22:"ExecutionFrequencyType";s:8:"Annually";s:27:"ExecutionFrequencyParameter";s:0:"";}',
			'payment_type' => '',
			'featured' => 1,
			'foreign_key' => '',
			'model' => 'Product',
			'is_virtual' => 1,
			'hours_expire' => 1,
			'customer_id' => '5738299d-9040-43c9-85b1-22d400000002',
			'assignee_id' => '',
			'creator_id' => '',
			'modifier_id' => '',
			'created' => '2012-10-11 21:43:17',
			'modified' => '2012-10-11 21:43:17'
		),
		// a single ARB item for the logged in User.id 2
		array(
			'id' => '50773d75-cab4-40dd-b34c-187800000005',
			'name' => 'ARB Test Item #4',
			'transaction_id' => '5043572d-9040-43c9-85b1-22d400000004',
			'quantity' => 1,
			'price' => 2.10,
			'weight' => 1,
			'height' => 1,
			'width' => 1,
			'length' => 1,
			'status' => '',
			'tracking_no' => '',
			'location' => '',
			'deadline' => '2012-10-11',
			'arb_settings' => 'a:7:{s:13:"PaymentAmount";s:1:"6";s:18:"FirstPaymentAmount";s:0:"";s:16:"FirstPaymentDate";s:0:"";s:9:"StartDate";s:0:"";s:7:"EndDate";s:0:"";s:22:"ExecutionFrequencyType";s:8:"Annually";s:27:"ExecutionFrequencyParameter";s:0:"";}',
			'payment_type' => '',
			'featured' => 1,
			'foreign_key' => '',
			'model' => 'Product',
			'is_virtual' => 1,
			'hours_expire' => 1,
			'customer_id' => '1',
			'assignee_id' => '',
			'creator_id' => '',
			'modifier_id' => '',
			'created' => '2012-10-11 21:43:17',
			'modified' => '2012-10-11 21:43:17'
		),
		// a single item for the logged in User.id 1
		array(
			'id' => '50773d75-cab4-40dd-b34c-187800000006',
			'name' => 'Test Item #5',
			'transaction_id' => '5043572d-9040-43c9-85b1-22d400000005',
			'quantity' => 1,
			'price' => 1,
			'weight' => 1,
			'height' => 1,
			'width' => 1,
			'length' => 1,
			'status' => '',
			'tracking_no' => '',
			'location' => '',
			'deadline' => '2012-10-11',
			'arb_settings' => '',
			'payment_type' => '',
			'featured' => 1,
			'foreign_key' => '',
			'model' => '',
			'is_virtual' => 1,
			'hours_expire' => 1,
			'customer_id' => '8723994',
			'assignee_id' => '',
			'creator_id' => '',
			'modifier_id' => '',
			'created' => '2012-10-11 21:43:17',
			'modified' => '2012-10-11 21:43:17'
		),
	);
}
