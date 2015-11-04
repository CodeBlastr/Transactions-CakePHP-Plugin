<div class="jumbotron">
	<h1>Thank you for your purchase !</h1>
	<hr>
	<p>You can expect an email with more information. </p>
</div>
<?php if(!$userId) : ?>
	<p>Would you like to register an account with us? <br><a href="/users/users/register" class="btn btn-primary">Register Account</a></p>
<?php else : ?>
   <p><?php echo $this->Html->link('View Your Order History', array('plugin' => 'transactions', 'controller' => 'transactions', 'action' => 'my'), array('class' => 'btn btn-block btn-lg btn-success')); ?></p> 
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
	))); ?>