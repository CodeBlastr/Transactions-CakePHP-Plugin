<?php
echo __('<h2>We found a previous shopping cart from you!</h2>');
foreach ($transactions as $txnNum => $transaction) {
    echo '<h4>Cart #'. ($txnNum +1) . '</h4>';
    echo __d('transactions', 'Created') . ': ' . CakeTime::niceShort($transaction['Transaction']['created']);
    foreach ($transaction['TransactionItem'] as $i => $transactionItem) {
	    echo __('<div class="transactionItemInCart">%s</div>', 
            $this->element('Transactions/cart_item', array(
                'transactionItem' => $transactionItem,
    		    'i' => $i), array(
                    'plugin' => ZuhaInflector::pluginize($transactionItem['model'])
                    )
	            ));
    }
} 

echo '<h3>' . __d('transactions', 'What would you like to do?') . '</h3>';
echo $this->Html->link(__d('transactions', 'Use Cart #1'), array('action' => 'merge', 'choice'=>'1'), array('class' => 'btn'));
echo $this->Html->link(__d('transactions', 'Combine Both Carts'), array('action' => 'merge', 'choice'=>'merge'), array('class' => 'btn'));
echo $this->Html->link(__d('transactions', 'Use Cart #2'), array('action' => 'merge', 'choice'=>'2'), array('class' => 'btn'));

// set the contextual menu items
$this->set('context_menu', array('menus' => array(
    array(
		'heading' => 'Products',
		'items' => array(
			$this->Html->link(__('Dashboard'), array('plugin' => 'products', 'controller' => 'products', 'action' => 'dashboard')),
			)
		),
	))); ?>