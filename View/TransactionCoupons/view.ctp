<div class="transactionCoupon view">
    <h2><?php  echo __($orderCoupon['TransactionCoupon']['name']); ?></h2>
    <div id="n1" class="info-block">
      <div class="viewRow">
        <ul class="metaData">
          <li><span class="metaDataLabel"> <?php echo __('Start Date: '); ?> </span><span class="metaDataDetail"><?php echo $orderCoupon['TransactionCoupon']['start_date']; ?></span></li>
          <li><span class="metaDataLabel"> <?php echo __('End Date: '); ?> </span><span class="metaDataDetail"><?php echo $orderCoupon['TransactionCoupon']['end_date']; ?></span></li>
          <li><span class="metaDataLabel"> <?php echo __('Discount: '); ?> </span><span class="metaDataDetail"><?php echo $orderCoupon['TransactionCoupon']['discount'] . ' ' .$orderCoupon['TransactionCoupon']['discount_type'] ; ?></span></li>
        </ul>
        <div class="recordData">
          <?php echo $orderCoupon['TransactionCoupon']['description']; ?> </div>
      </div>
    </div>
</div>
<?php
// set the contextual menu items
$this->set('context_menu', array('menus' => array(
	array(
		'heading' => 'Checkout Coupons',
		'items' => array(
			$this->Html->link(__('Edit', true), array('controller' => 'transaction_coupons', 'action' => 'edit', $orderCoupon['TransactionCoupon']['id']), array('class' => 'edit')),
			$this->Html->link(__('Delete'), array('action' => 'delete', $this->Form->value('Condition.id')), null, __('Are you sure you want to delete # %s?', $this->Form->value('Condition.id')), array('class' => 'delete')),
			)
		),
	))); 
?>
