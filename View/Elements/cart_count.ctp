<?php
$TransactionItemHelper = $this->loadHelper('Transactions.TransactionItem');
$userId = $this->Session->read('Auth.User.id') ? $this->Session->read('Auth.User.id') : $this->Session->read('Transaction._guestId');
$count = $TransactionItemHelper->find('count', array('conditions' => array('TransactionItem.status' => 'incart', 'TransactionItem.customer_id' => $userId))); 
echo $count ? $count : 0; ?>