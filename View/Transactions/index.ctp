<div class="transactions index">
	<table cellpadding="0" cellspacing="0">
        <thead>
	    <tr>
			<th><?php echo $this->Paginator->sort('Customer.last_name', 'Customer');?></th>
    		<th><?php echo $this->Paginator->sort('Transaction.created', 'Date');?></th>
			<th><?php echo $this->Paginator->sort('Transaction.status', 'Status');?></th>
			<th><?php echo $this->Paginator->sort('Transaction.total', 'Total');?></th>
			<th class="actions"><?php echo __('Actions');?></th>
    	</tr>
        </thead>
        <tbody>
    	<?php foreach ($transactions as $transaction) { ?>
    	<tr>
    		<td><?php echo $this->Html->link($transaction['Customer']['full_name'], array('plugin' => 'users', 'controller' => 'users', 'action' => 'view', $transaction['Customer']['id'])); ?>&nbsp;</td>
    		<td><?php echo ZuhaInflector::datify($transaction['Transaction']['created']); ?>&nbsp;</td>
    		<td><?php echo $transaction['Transaction']['status']; ?>&nbsp;</td>
    		<td><?php echo __('$%s', ZuhaInflector::pricify($transaction['Transaction']['total'])); ?>&nbsp;</td>
    		<td class="actions">
    			<?php echo $this->Html->link(__('View'), array('action' => 'view', $transaction['Transaction']['id'])); ?>
    			<?php echo $this->Html->link(__('Edit'), array('action' => 'edit', $transaction['Transaction']['id'])); ?>
    			<?php echo $this->Html->link(__('Delete'), array('action' => 'delete', $transaction['Transaction']['id']), null, sprintf(__('Are you sure you want to delete # %s?', true), $transaction['Transaction']['id'])); ?>
    		</td>
	    </tr>
    <?php } 
    echo $this->Element('paging'); ?>
        </tbody>
    </table>
</div>

<?php
// set the contextual menu items
$this->set('context_menu', array('menus' => array(
    array(
		'heading' => 'Products',
		'items' => array(
			$this->Html->link(__('Dashboard'), array('plugin' => 'products', 'controller' => 'products', 'action' => 'dashboard')),
			)
		),
	))); ?>