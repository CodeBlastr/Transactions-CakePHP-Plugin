<?php
$TransactionItemHelper = $this->loadHelper('Transactions.TransactionItem');
$count = $TransactionItemHelper->find('count', array('conditions' => array('TransactionItem.status' => 'incart', 'TransactionItem.customer_id' => $this->Session->read('Auth.User.id')))); 
echo $count ? $count : 0; ?>