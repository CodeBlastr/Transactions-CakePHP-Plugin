<div class="transactionCoupons form">
<?php echo $this->Form->create('TransactionCoupon');?>
	<fieldset>
		<legend><?php echo __('Edit Checkout Coupon'); ?></legend>
		<?php echo $this->Form->input('TransactionCoupon.id'); ?>
		<?php echo $this->Form->input('TransactionCoupon.name'); ?>
		<?php echo $this->Form->input('TransactionCoupon.description'); ?>
		<?php echo $this->Form->input('TransactionCoupon.discount'); ?>
		<?php echo $this->Form->input('TransactionCoupon.discount_type'); ?>
		<?php echo $this->Form->input('TransactionCoupon.code', array('after' => 'if blank, all matching transactions receive discount')); ?>
		<?php echo $this->Form->input('TransactionCoupon.start_date', array('type' => 'datepicker')); ?>
		<?php echo $this->Form->input('TransactionCoupon.end_date', array('type' => 'datepicker')); ?>
		<?php echo $this->Form->input('TransactionCoupon.is_active'); ?>
	</fieldset>
<?php echo $this->Form->end(__('Submit'));?>
</div>
<?php 
// set the contextual menu items
$this->set('context_menu', array('menus' => array(
	array(
		'heading' => 'Coupons',
		'items' => array(
			$this->Form->postLink(__('Delete'), array('action' => 'delete', $this->Form->value('Condition.id')), null, __('Are you sure you want to delete # %s?', $this->Form->value('Condition.id'))),
			$this->Html->link(__('List Order Coupons'), array('action' => 'index')),
			)
		),
	)));