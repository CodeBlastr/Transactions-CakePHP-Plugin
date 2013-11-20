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
    	<?php foreach ($transactions as $transaction) : ?>
    	<tr>
    		<td>
    			<?php echo !empty($transaction['Customer']['full_name']) ? $this->Html->link($transaction['Customer']['full_name'], array('plugin' => 'users', 'controller' => 'users', 'action' => 'view', $transaction['Customer']['id'])) : __('%s %s', $transaction['TransactionAddress'][0]['first_name'], $transaction['TransactionAddress'][0]['last_name']); ?>
    			<div><?php echo $transaction['Customer']['email'] ?></div>
    		</td>
    		<td><?php echo ZuhaInflector::datify($transaction['Transaction']['created']); ?>&nbsp;</td>
    		<td><?php echo $transaction['Transaction']['status']; ?>&nbsp;</td>
    		<td><?php echo __('$%s', ZuhaInflector::pricify($transaction['Transaction']['total'])); ?>&nbsp;</td>
    		<td class="actions">
    			<?php echo $this->Html->link(__('View'), array('action' => 'view', $transaction['Transaction']['id']), array('class' => 'btn btn-default')); ?>
    			<?php echo $this->Html->link(__('Edit'), array('action' => 'edit', $transaction['Transaction']['id']), array('class' => 'btn btn-warning')); ?>
    			<?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $transaction['Transaction']['id']), array('class' => 'btn btn-danger'), __('Are you sure you want to delete # %s?', $transaction['Transaction']['id'])); ?>
    		</td>
	    </tr>
	    <?php endforeach; ?>
        </tbody>
    </table>
    <?php echo $this->Element('paging'); ?>
</div>

<?php
// set the contextual breadcrumb items
$this->set('context_crumbs', array('crumbs' => array(
	$this->Html->link(__('Ecommerce Dashboard'), array('plugin' => 'products', 'controller' => 'products', 'action' => 'dashboard')),
	$page_title_for_layout,
)));

// set the contextual menu items
$named =  array('limit' => $this->Paginator->counter('{:count}')) + array_reverse($this->request->named);
$this->set('context_menu', array('menus' => array(
    array(
		'heading' => 'Products',
		'items' => array(
			$this->Html->link(__('Dashboard'), array('plugin' => 'products', 'controller' => 'products', 'action' => 'dashboard')),
			//http://discoverywoods.buildrr.com/admin/transactions/transactions/index/sort:Transaction.created/direction:desc/limit:1000/filter:status%3Apaid.csv
			$this->Html->link(__('Download %s Transactions', $this->Paginator->counter('{:count}')), array('plugin' => 'transactions', 'controller' => 'transactions', 'action' => 'index', 'ext' =>'csv') + $named),
			)
		),
	)));
