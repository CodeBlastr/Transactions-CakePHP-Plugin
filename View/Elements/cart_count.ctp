<?php
$cart_count = 0;
{$cart_count = $this->Session->read('TransactionsCartCount');} 

echo  $cart_count ; ?>