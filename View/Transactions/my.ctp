<?php debug($transactions); ?>
<?php
$expand = 'in';
foreach ($transactions as $transaction) {
    $i = 0;
	foreach ($transaction['TransactionItem'] as $item) {
		$items[$i]['TransactionItem']['id'] = $item['id'];
		$items[$i]['TransactionItem']['name'] = $item['name'];
		$items[$i]['TransactionItem']['status'] = $item['status'];
		$items[$i]['TransactionItem']['foreign_key'] = $item['foreign_key'];
        $i++;
	} ?>
	<div class="dashboardBox <?php echo $expand; ?>">
		<h3 class="title" data-toggle="collapse" data-target="#demo<?php echo $transaction['Transaction']['id']; ?>"> Transaction from <?php echo ZuhaInflector::datify($transaction['Transaction']['modified']); ?> </h3>
		<p>
			<small><?php echo __('<span class="badge badge-info">%s</span> %s %s.', count($items), Inflector::humanize(Inflector::pluralize($item['model'])), $item['status']); ?></small>
		</p>
		<div id="demo<?php echo $transaction['Transaction']['id']; ?>" class="collapse <?php echo $expand; ?>">
			<?php echo !empty($items) ? $this->Element('scaffolds/index', array('data' => $items, 'modelName' => 'TransactionItem', 'actions' => false, 'link' => array('pluginName' => 'products', 'controllerName' => 'products', 'actionName' => 'view', 'pass' => 'foreign_key'))) : null; ?>
		</div>
	</div>
	<?php
	$expand = 'collapsed';
	unset($items);
}

if ( empty($transactions) ) {
?>

<div class="well">No previous orders found.</div>

<?php
}

// set the contextual menu items
$this->set('context_menu', array('menus' => array(
    array(
		'heading' => 'Products',
		'items' => array(
			$this->Html->link(__('Dashboard'), array('plugin' => 'products', 'controller' => 'products', 'action' => 'dashboard')),
			)
		),
	)));
