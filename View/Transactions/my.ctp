<div class="list-group">
	<?php $expand = 'in'; ?>
	<?php foreach ($transactions as $transaction) : ?>
    	<div class="list-group-item clearfix <?php echo $expand; ?>">
    		<span class="badge badge-info"><?php echo count($transaction['TransactionItem']); ?></span>
			<h5 class="title pull-left" data-toggle="collapse" data-target="#demo<?php echo $transaction['Transaction']['id']; ?>"> Transactions on <?php echo ZuhaInflector::datify($transaction['Transaction']['modified']); ?> </h5>
			<div id="demo<?php echo $transaction['Transaction']['id']; ?>" class="collapse <?php echo $expand; ?>">
				<table>
					
				<?php foreach ($transaction['TransactionItem'] as $item) : ?>
					<tr>
						<td>
							<?php echo $item['name']; ?>
						</td>
						<td>
							<?php echo $item['status']; ?>
						</td>
					</tr>
				<?php endforeach; ?>
				</table>
			</div>
		</div>
		<?php $expand = 'collapsed'; ?>
		<?php unset($items); ?>
	<?php endforeach; ?>
</div>
<?php if (empty($transactions)) : ?>
	<div class="well">No previous orders found.</div>
<?php endif; ?>

<?php
// set the contextual menu items
$this->set('context_menu', array('menus' => array(
    array(
		'heading' => 'Products',
		'items' => array(
			$this->Html->link(__('Dashboard'), array('plugin' => 'products', 'controller' => 'products', 'action' => 'dashboard')),
			)
		),
	)));
