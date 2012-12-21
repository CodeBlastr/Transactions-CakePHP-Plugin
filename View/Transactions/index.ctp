<?php
echo $this->Element('scaffolds/index', array(
	'data' => $transactions, 
	'actions' => false/*array(
		$this->Html->link('View', array('action' => 'view', '{model}', '{foreign_key}')), 
		$this->Html->link('Edit', array('action' => 'edit', '{model}', '{foreign_key}')), 
		$this->Html->link('Delete', array('action' => 'delete', '{id}'), array(), 'Are you sure you want to permanently delete?'),
		),*/
	)); ?>

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