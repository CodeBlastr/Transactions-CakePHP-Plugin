<?php
/**
 * TransactionFixture
 *
 */
class TransactionFixture extends CakeTestFixture {

/**
 * Import
 *
 * @var array
 */
	public $import = array('config' => 'Transactions.Transaction');
	
	
/**
 * Records
 *
 * @var array
 */
	public $records = array(
		// transaction for User.id = 1
		array(
			'id' => '5077241d-9040-43c9-85b1-22d40000001',
			'transaction_coupon_id' => '',
			'processor_response' => '',
			'introduction' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
			'conclusion' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
			'status' => 'open',
			'mode' => '',
			'total' => 1,
			'is_virtual' => 0,
			'is_arb' => 0,
			'customer_id' => '1',
			'contact_id' => '1',
			'assignee_id' => '1',
			'creator_id' => '1',
			'modifier_id' => '1',
			'created' => '2012-10-11 19:55:09',
			'modified' => '2012-10-11 19:55:09'
		),
		// transaction for guest Customer.id = 5738299d-9040-43c9-85b1-22d400000001
		array(
			'id' => '5043572d-9040-43c9-85b1-22d400000002',
			'transaction_coupon_id' => '',
			'processor_response' => '',
			'introduction' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
			'conclusion' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
			'status' => 'open',
			'mode' => '',
			'total' => 2,
			'is_virtual' => 0,
			'is_arb' => 0,
			'customer_id' => '5738299d-9040-43c9-85b1-22d400000001',
			'contact_id' => '1',
			'assignee_id' => '1',
			'creator_id' => '1',
			'modifier_id' => '1',
			'created' => '2012-10-11 19:55:09',
			'modified' => '2012-10-11 19:55:09'
		),
		// ARB transaction for guest Customer.id = 5738299d-9040-43c9-85b1-22d400000002
		array(
			'id' => '5043572d-9040-43c9-85b1-22d400000003',
			'transaction_coupon_id' => '',
			'processor_response' => '',
			'introduction' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
			'conclusion' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
			'status' => 'open',
			'mode' => '',
			'total' => 2,
			'is_virtual' => 0,
			'is_arb' => 1,
			'customer_id' => '5738299d-9040-43c9-85b1-22d400000002',
			'contact_id' => '',
			'assignee_id' => '',
			'creator_id' => '',
			'modifier_id' => '',
			'created' => '2012-10-11 19:55:09',
			'modified' => '2012-10-11 19:55:09'
		),
		// ARB transaction for logged in Customer.id = 2
		array(
			'id' => '5043572d-9040-43c9-85b1-22d400000004',
			'transaction_coupon_id' => '',
			'processor_response' => '',
			'introduction' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
			'conclusion' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
			'status' => 'open',
			'mode' => '',
			'total' => 6,
			'is_virtual' => 0,
			'is_arb' => 1,
			'customer_id' => '2',
			'contact_id' => '',
			'assignee_id' => '',
			'creator_id' => '',
			'modifier_id' => '',
			'created' => '2012-10-11 19:55:09',
			'modified' => '2012-10-11 19:55:09'
		),
	);
}
