<h1>Thank you for your purchase !</h1>
<p>Expect an email with further information. </p>

<?php if(!$userId) { ?>
<p>Would you like to register an account with us? <a href="#" class="btn">Register Account</a></p>
<?php } else {
   echo __('<p>%s</p>', $this->Html->link('View Your Order History', array('plugin' => 'transactions', 'controller' => 'transactions', 'action' => 'my'))); 
}
?>

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