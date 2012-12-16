<?php
/**
 * TransactionCouponFixture
 *
 */
class TransactionCouponFixture extends CakeTestFixture {
    
    /**
 * name TransactionCoupon
 *
 * @var string
 */
    public $name = 'TransactionCoupon';



/**
 * Import
 *
 * @var array
 */
    public $import = array('config' => 'Transactions.TransactionCoupon');
    

/**
 * Records
 *
 * @var array
 */
	public $records = array(
		array(
			'id' => '1',
			'name' => 'Lorem ipsum dolor sit amet',
			'description' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
			'conditions' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
			'discount' => 5,
			'discount_type' => 'percent',
			'discount_max' => 1,
			'discount_qty_x' => 1,
			'discount_shipping' => 1,
			'code' => 'zuha',
			'uses_allowed' => 1,
			'user_uses_allowed' => 1,
			'uses' => 1,
			'start_date' => '2012-10-11 19:39:12',
			'end_date' => '2012-10-11 19:39:12',
			'is_active' => 1,
			'creator_id' => 'Lorem ipsum dolor sit amet',
			'modifier_id' => 'Lorem ipsum dolor sit amet',
			'created' => '2012-10-11 19:39:12',
			'modified' => '2012-10-11 19:39:12'
		),
        array(
            'id' => '2',
            'name' => 'Winter Offer',
            'description' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
            'conditions' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
            'discount' => 12,
            'discount_type' => 'fixed',
            'discount_max' => 1,
            'discount_qty_x' => 1,
            'discount_shipping' => 1,
            'code' => 'vini',
            'uses_allowed' => 1,
            'user_uses_allowed' => 1,
            'uses' => 1,
            'start_date' => '2012-11-01 19:39:12',
            'end_date' => '2012-12-30 19:39:12',
            'is_active' => 1,
            'creator_id' => 'Lorem ipsum dolor sit amet',
            'modifier_id' => 'Lorem ipsum dolor sit amet',
            'created' => '2012-11-27 01:39:12',
            'modified' => '2012-10-27 13:39:12'
        ),
        array(
            'id' => '3',
            'name' => 'Summer Offer',
            'description' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
            'conditions' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
            'discount' => 5,
            'discount_type' => 'fixed',
            'discount_max' => 1,
            'discount_qty_x' => 1,
            'discount_shipping' => 1,
            'code' => 'vini',
            'uses_allowed' => 1,
            'user_uses_allowed' => 1,
            'uses' => 1,
            'start_date' => '2012-11-01 19:39:12',
            'end_date' => '2012-12-30 19:39:12',
            'is_active' => 1,
            'creator_id' => 'Lorem ipsum dolor sit amet',
            'modifier_id' => 'Lorem ipsum dolor sit amet',
            'created' => '2012-11-27 01:39:12',
            'modified' => '2012-10-27 13:39:12'
        )
	);
}
