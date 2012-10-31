<h2>We found a previous shopping cart from you!</h2>
<h3>What would you like to do?</h3>
<?php
foreach ($transactions as $transaction) {
  debug($transaction);
}
?>
<a>Use Cart 1</a>
<a>Merge Both Carts</a>
<a>Use Cart 2</a>