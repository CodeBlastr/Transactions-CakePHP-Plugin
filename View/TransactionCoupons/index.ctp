<div class="orderCoupons index">
	<h2><?php echo __('Checkout Coupons');?></h2>
	<table cellpadding="0" cellspacing="0">
	<tr>
			<th><?php echo $this->Paginator->sort('name');?></th>
			<th><?php echo $this->Paginator->sort('discount');?></th>
			<th><?php echo $this->Paginator->sort('discount_type');?></th>
			<th><?php echo $this->Paginator->sort('code');?></th>
			<th><?php echo $this->Paginator->sort('start_date');?></th>
			<th><?php echo $this->Paginator->sort('end_date');?></th>
			<th><?php echo $this->Paginator->sort('is_active');?></th>
			<th class="actions"><?php echo __('Actions');?></th>
	</tr>
	<?php
	$i = 0;
	foreach ($transactionCoupons as $coupon): ?>
	<tr>
		<td><?php echo $this->Html->link(h($coupon['TransactionCoupon']['name']), array('action' => 'view', $coupon['TransactionCoupon']['id'])); ?>&nbsp;</td>
		<td><?php echo h($coupon['TransactionCoupon']['discount']); ?>&nbsp;</td>
		<td><?php echo h($coupon['TransactionCoupon']['discount_type']); ?>&nbsp;</td>
		<td><?php echo h($coupon['TransactionCoupon']['code']); ?>&nbsp;</td>
		<td><?php echo h($coupon['TransactionCoupon']['start_date']); ?>&nbsp;</td>
		<td><?php echo h($coupon['TransactionCoupon']['end_date']); ?>&nbsp;</td>
		<td><?php echo h($coupon['TransactionCoupon']['is_active']); ?>&nbsp;</td>
		<td class="actions">
			<?php echo $this->Html->link(__('View'), array('action' => 'view', $coupon['TransactionCoupon']['id'])); ?>
			<?php echo $this->Html->link(__('Edit'), array('action' => 'edit', $coupon['TransactionCoupon']['id'])); ?>
			<?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $coupon['TransactionCoupon']['id']), null, __('Are you sure you want to delete # %s?', $coupon['TransactionCoupon']['name'])); ?>
		</td>
	</tr>
<?php endforeach; ?>
	</table>
<?php $this->Element('paging'); ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('New Checkout Coupon'), array('action' => 'add')); ?></li>
	</ul>
</div>
