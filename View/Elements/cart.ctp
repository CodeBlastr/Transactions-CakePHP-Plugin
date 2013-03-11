<?php
if ($this->request->action !== 'merge') { // transactions/transactions/merge causes an infinite redirect
    $transaction = $this->requestAction(
        array('plugin' => 'transactions', 'controller' => 'transactions', 'action' => 'cart'),
        array()
    ); ?>
    
    <div id="transactionCartRight" class="transactionCart span4 pull-right">
        <?php echo $this->Element('Transactions.trust_logos'); ?>
        <fieldset id="transactionItems" class="transactionItems">
    	    <legend><?php echo __d('transactions', 'Shopping Cart') ?></legend>
    	    <?php 
            if (!empty($transaction['TransactionItem'])) { 
    	        foreach ($transaction['TransactionItem'] as $i => $transactionItem) {
    			    echo $this->Form->hidden("TransactionItem.{$i}.id", array('value' => $transactionItem['id']));
    	            echo __('<div class="transactionItemInCart" id="TransactionItem%s">%s</div>', $i, $this->Element('Transactions/cart_item', array('transactionItem' => $transactionItem, 'i' => $i), array('plugin' => ZuhaInflector::pluginize($transactionItem['model']))));
    	        }
            } else {
                echo __('<p>Cart Empty : %s</p>', $this->Html->link(__('Go Shopping'), array('plugin' => 'products', 'controller' => 'products', 'action' => 'index')));
            } ?>
        </fieldset>
        <?php if (!empty($transaction['TransactionItem'])) { ?>
        <fieldset>
    	    <legend><?php echo __d('transactions', 'Summary') ?></legend>
    	    <?php
        	echo $this->Form->hidden('Transaction.sub_total', array('label'=>'Sub-Total', 'readonly' => true, 'value' => ZuhaInflector::pricify($transaction['Transaction']['sub_total']))); 
        	// might not need this : echo $this->Form->hidden('Transaction.tax_charge', array('type' => 'hidden')); 
    	    $orderTotal = floatval($transaction['options']['defaultShippingCharge']) + floatval($transaction['Transaction']['tax_charge']) + floatval($transaction['Transaction']['sub_total']);
    	    $pricifiedOrderTotal = number_format($orderTotal, 2, null, ''); ?>
            <table class="table-hover">
                <tbody>
                    <tr>
                        <td class="subTotal"><?php echo __d('transactions', 'Subtotal') ?> <td id="TransactionSubtotal" class="total">$<span class="floatPrice"><?php echo ZuhaInflector::pricify($transaction['Transaction']['sub_total']) ?></span></td>
                    </tr>
                    <tr>
                        <td class="shippingTotal"><?php echo __('Shipping') ?>  </td><td id="TransactionShipping" class="total">+ $<span class="floatPrice"><?php echo ZuhaInflector::pricify($transaction['Transaction']['shipping_charge']) ?></span></td>
                    </tr>
                    <tr>
                        <td class="taxTotal"><?php echo __('Tax') ?>: </td><td id="TransactionTax" class="total">+ $<span class="floatPrice"><?php echo $transaction['Transaction']['tax_charge']; ?></span></td></div>
                    </tr>
                    <tr>
                        <td class="transactionTotal" style="margin: 10px 0; font-weight: bold;">Total </td><td id="TransactionTotal" class="total">$<span class="floatPrice"><?php echo $pricifiedOrderTotal ?></span></td>
                    </tr>
                </tbody>
            </table>
            <a href="/transactions/transactions/cart" class="btn">Checkout</a>
        </fieldset>
        <?php } ?>
    </div>
    
    <script type="text/javascript">
    	$(function() {
    		$('.productAddCart form, .transactionCart form').prepend('<input type="hidden" name="data[Override][redirect]" value="' + window.location.href + '">');
        })
    </script>
<?php 
} ?>
