<?php
/**
 * Transactions Checkout View
 *
 * Displays the checkout form for conducting transactions.
 *
 * PHP versions 5
 *
 * Zuha(tm) : Business Management Applications (http://zuha.com)
 * Copyright 2009-2012, Zuha Foundation Inc. (http://zuhafoundation.org)
 *
 * Licensed under GPL v3 License
 * Must retain the above copyright notice and release modifications publicly.
 *
 * @copyright     Copyright 2009-2012, Zuha Foundation Inc. (http://zuha.com)
 * @link          http://zuha.com Zuha� Project
 * @package       zuha
 * @subpackage    zuha.app.plugins.transactions.views
 * @since         Zuha(tm) v 0.0.1
 * @license       GPL v3 License (http://www.gnu.org/licenses/gpl.html) and Future Versions
 */
?>

<div id="transactionsCheckout" class="transactions checkout form">
    <?php 
    echo $this->Html->script('plugins/jquery.validate.min', array('inline' => false));
    echo $this->Html->css('/transactions/css/transactions', null, array('inline' => false));
    echo $this->Form->create('Transaction', array('class' => 'form-responsive validate'));     
    
	if($this->request->data['Customer']['id'] == NULL) {
	    // show a login button
	    echo __d('transactions', 'Returning Customer?') . '&nbsp;';
	    echo $this->Html->link(__d('transactions', 'Please Login'), array('plugin' => 'users', 'controller' => 'users', 'action' => 'login'), array('class' => 'btn'));
	} ?>
    
    <div id="transactionForm" class="transactionForm text-inputs row-fluid">
	    <div id="transactionCartLeft" class="span8 pull-left">
		    <div id="transactionAddress">
			    <fieldset id="billingAddress" class="control-group">
    				<legend><?php echo __d('transactions', 'Billing Details'); ?></legend>
    				<?php
        			echo $this->Form->input('TransactionAddress.0.first_name', array('class' => 'required', 'after' => $this->Form->input('TransactionAddress.0.last_name', array('class' => 'required'))));
    				echo $this->Form->input('TransactionAddress.0.email', array('class' => 'required email'));
                    echo $this->Form->input('TransactionAddress.0.country', array('label' => 'Country', 'class' => 'required', 'type' => 'select', 'options' => $options['countries']));
    				echo $this->Form->input('TransactionAddress.0.street_address_1', array('label' => 'Street', 'class' => 'required'));
    				echo $this->Form->input('TransactionAddress.0.street_address_2', array('label' => 'Street 2'));
    				echo $this->Form->input('TransactionAddress.0.city', array('label' => 'City ', 'class' => 'required', 'after' => $this->Form->input('TransactionAddress.0.state', array('label' => 'State ', 'class' => 'required', 'type' => 'select', 'empty' => '-- Select --', 'options' => $options['states'])) . $this->Form->input('TransactionAddress.0.zip', array('label' => 'Zip ', 'class' => 'required', 'data-mask' => '99999')) ));
    				echo $this->Form->input('TransactionAddress.0.phone', array('label' => 'Phone', 'class' => 'required', 'data-mask' => '(999) 999-9999'));
                    echo $this->Form->hidden('TransactionAddress.0.type', array('value' => 'billing'));
    				echo $options['displayShipping'] ? $this->Form->input('TransactionAddress.0.shipping', array('type' => 'checkbox', 'label' => 'Click here if your shipping address is different than your billing address.')) : null; ?>
			    </fieldset>
          
			    <fieldset id="shippingAddress" class="control-group">
    				<legend><?php echo __d('transactions', 'Shipping Address'); ?></legend>
    				<div id="shipping_error"></div>
    				<?php
    				echo $this->Form->input('TransactionAddress.1.country', array('label' => 'Country ', 'type' => 'select', 'empty' => '-- Select --', 'options' => $options['countries']));
    				echo $this->Form->input('TransactionAddress.1.street_address_1', array('label' => 'Street', 'size' => '49'));
    				echo $this->Form->input('TransactionAddress.1.street_address_2', array('label' => 'Street 2', 'size' => '49'));
    				echo $this->Form->input('TransactionAddress.1.city', array('label' => 'City ', 'after' => $this->Form->input('TransactionAddress.1.state', array('label' => 'State ', 'type' => 'select', 'empty' => '-- Select --', 'options' => $options['states'])) . $this->Form->input('TransactionAddress.1.zip', array('label' => 'Zip ', 'maxlength' => '10')) ));
    				echo $this->Form->hidden('TransactionAddress.1.type', array('value' => 'shipping')); ?>
			    </fieldset>
		    </div>

		    <fieldset id="paymentInformation" class="control-group">
			    <legend><?php echo __d('transactions', 'Payment Information'); ?></legend>
			    <?php
				// unFlatten the paymentOptions
				$paymentOptions = array();
				foreach ($options['paymentOptions'] as $k => $v) {
					$paymentOptions = Set::insert($paymentOptions, $k, $v);
				}
				// display each payment option's element
				foreach ( $paymentOptions as $k => $v ) {
					echo $this->Element(strtolower($k));
				}
				?>
		    </fieldset>
	    </div>


	    <div id="transactionCartRight" class="span4 pull-right">
		    <?php echo $this->Element('Transactions.trust_logos'); ?>
		    <fieldset id="transactionItems" class="transactionItems">
			    <legend><?php echo __d('transactions', 'Shopping Cart') ?></legend>
			    <?php 
			    foreach ($this->request->data['TransactionItem'] as $i => $transactionItem) {
				    echo $this->Form->hidden("TransactionItem.{$i}.id", array('value' => $transactionItem['id']));
					$plugin = ZuhaInflector::pluginize($transactionItem['model']).'.';
					$item = $this->_getElementFilename($plugin.'Transactions/cart_item') ? $this->Element($plugin.'Transactions/cart_item', array('transactionItem' => $transactionItem, 'i' => $i)) : $this->Element('Transactions.cart_item', array('transactionItem' => $transactionItem, 'i' => $i));
                    echo __('<div class="transactionItemInCart" id="TransactionItem%s">%s</div>', $i, $item);
			    } ?>
		    </fieldset>

		    <fieldset>
			    <legend><?php echo __d('transactions', 'Summary') ?></legend>
			    <?php
            	echo $this->Form->hidden('Transaction.sub_total', array('label'=>'Sub-Total', 'readonly' => true, 'value' => ZuhaInflector::pricify($this->request->data['Transaction']['sub_total']))); 
            	// might not need this : echo $this->Form->hidden('Transaction.tax_charge', array('type' => 'hidden')); 
			    $orderTotal = floatval($options['defaultShippingCharge']) + floatval($this->request->data['Transaction']['tax_charge']) + floatval($this->request->data['Transaction']['sub_total']);
			    $pricifiedOrderTotal = number_format($orderTotal, 2, null, '');
			    echo $this->Html->link(__('<small>Have a Promo Code?</small>'), '#', array('id' => 'promoCode', 'escape' => false));
                echo $this->Form->input('TransactionCoupon.code', array('label' => false, 'placeholder' => 'enter code', 'after' => __(' %s', $this->Html->link('Apply', '#', array('id' => 'applyCode', 'class' => 'btn'))))); ?>
                <table class="table-hover">
                    <tbody>
                        <tr>
                            <td class="subTotal"><?php echo __d('transactions', 'Subtotal') ?> <td id="TransactionSubtotal" class="total">$<span class="floatPrice"><?php echo ZuhaInflector::pricify($this->request->data['Transaction']['sub_total']) ?></span></td>
                        </tr>
                        <tr>
                            <td class="shippingTotal"><?php echo __('Shipping') ?>  </td><td id="TransactionShipping" class="total">+ $<span class="floatPrice"><?php echo ZuhaInflector::pricify($this->request->data['Transaction']['shipping_charge']) ?></span></td>
                        </tr>
                        <tr>
                            <td class="discountTotal"><?php echo __('Discount') ?>: </td><td id="TransactionDiscount" class="total"><span class="floatPrice"></span></td></div>
                        </tr>
                        <tr>
                            <td class="taxTotal"><?php echo __('Tax') ?>: </td><td id="TransactionTax" class="total">+ $<span class="floatPrice"><?php echo $this->request->data['Transaction']['tax_charge']; ?></span></td></div>
                        </tr>
                        <tr>
                            <td class="transactionTotal" style="margin: 10px 0; font-weight: bold;">Total </td><td id="TransactionTotal" class="total">$<span class="floatPrice"><?php echo $pricifiedOrderTotal ?></span></td>
                        </tr>
                    </tbody>
                </table>
                <?php
                echo $this->Form->hidden('Transaction.hiddentotal', array('label'=>'Total', 'readonly' => true, 'value' => $pricifiedOrderTotal));
			    echo $this->Form->end(__('Checkout'));	?>
		    </fieldset>
	    </div>
    </div>
</div>


<script type="text/javascript">
$(function() {
    // hide / show the coupon code input dependent on value
    if (!$("#TransactionCouponCode").val()) {
	  $("#TransactionCouponCode").parent().hide();
	  $("#promoCode").click(function(e){
		  e.preventDefault();
		  $("#TransactionCouponCode").parent().toggle('slow');
	  });
    }
     
    // hide the discount input if empty
    if (!$("#TransactionDiscount").val()) {
	  $("#TransactionDiscount").parent().hide();
    }

	// toggle Shipping Address fields
    $('#TransactionAddress0Shipping').change(function(e){
	  if ( $('#TransactionAddress0Shipping').prop("checked") ) {
		  $('#shippingAddress').show('slow');
	  } else {
		  $('#shippingAddress').hide('slow');
	  }
    });

    // handle a submitted code for verification (update total)
    $("#applyCode").click(function(e){
   	    e.preventDefault(); 
      
        if($("#TransactionHiddentotal").val() > 0) {
	        $.ajax({
		        type: "POST",
		        data: $('#TransactionCartForm').serialize(),
		        url: "/transactions/transaction_coupons/verify.json" ,  
		        dataType: "json",       
                success:function(data) {  
            
                    var discount = $("#TransactionOrderCharge").text() - data['data']['Transaction']['sub_total'];
			        $('#TransactionTotal').text('$'+data['data']['Transaction']['sub_total']);
                    $('#TransactionTotal').val(data['data']['Transaction']['sub_total']);
                    $("#TransactionDiscount").val(discount.toFixed(2));
                    if ( data['data']['TransactionCoupon']['discount_type'] === 'fixed' ) {
                        symbol='$';  
                        $("#TransactionDiscount").text('- '+ symbol + data['data']['TransactionCoupon']['discount']);
                    } else { 
                        var discount_amount=(data['data']['TransactionCoupon']['discount']/100)*$("#TransactionHiddentotal").val(); 
                        symbol='%';  
                        $("#TransactionDiscount").text('- '+ symbol+ data['data']['TransactionCoupon']['discount']+' ( $ '+discount_amount+')');
                    }
                    $("#TransactionDiscount").parent().show();
                },
		        error:function(data){  
     		        $("#TransactionDiscount").val('');
			        $("#TransactionDiscount").parent().hide();
			        $('#TransactionTotal').val($("#TransactionOrderCharge").val());
			        alert('Code out of date or does not apply.');
		        }
	        });
        } else {
             alert("Total amount is zero.");
        } 
    });
    
	// dynamic totals
	$('.TransactionItemCartQty').bind("change keyup", function(e){
		updateItemTotals($(this));
		updateSubtotal();
		updateTaxTotal();
		updateOrderTotal();
	});
    $('#TransactionAddress0Country, #TransactionAddress0State').change(function() {
		updateSubtotal();
		updateTaxRate();
		updateTaxTotal();
		updateOrderTotal();
    });
	
	function updateItemTotals(itemQty) {
		var transactionItemX = itemQty.prop('id').replace('Quantity', '');
		if($.isNumeric(itemQty.val()) === false) itemQty.val('0');
		// calculate the item total
		var itemTotal = $('#' + transactionItemX + ' .priceOfOne').text() * itemQty.val();
		// update the item total
		$('#' + transactionItemX + ' .floatPrice').text(itemTotal.toFixed(2));	
	}
	
	function updateSubtotal() {
		var subtotal = 0;
		$('#transactionItems .floatPrice').each(function(e) {
			subtotal += parseFloat($(this).text());
		});
		$('#TransactionSubtotal .floatPrice').text(subtotal.toFixed(2));		
	}

    function updateTaxRate() {
        if ($('#TransactionAddress0Country').val() && $('#TransactionAddress0State').val()) {
            $.ajax({
                type: "POST",
                data: $('#TransactionCartForm').serialize(),
                url: "/transactions/transaction_taxes/rate.json",
                dataType: "text",
                success:function(data){
                    var response = JSON.parse(data);
                    var rate = response['transactionTax']['Transaction']['tax_rate'];
                    $('#jsTaxRate').remove();
                    if(typeof rate !== 'undefined') {
                        // nothing it's already set
                    } else {
                        rate = 0;
                    }
                    
                    $('body').append('<span id="jsTaxRate" style="visibility: hidden;">' + rate + '</span>');
                    updateTaxTotal();
                    updateOrderTotal();
                }
            });
        }
    }
    
    function updateTaxTotal() {
        var rate = $('#jsTaxRate').text();
        var tax = parseFloat(rate * parseFloat($('#TransactionSubtotal .floatPrice').text())).toFixed(2);
        $('#TransactionTax .floatPrice').text(tax);
    }

	function updateShippingTotal() {
		
	}

	function updateOrderTotal() {
		var subtotal = parseFloat($('#TransactionSubtotal .floatPrice').text());
		var taxTotal = parseFloat($('#TransactionTax .floatPrice').text());
		var shippingTotal = parseFloat($('#TransactionShipping .floatPrice').text());
		var discountTotal = parseFloat($('#TransactionDiscount .floatPrice').text());
		if($.isNumeric(discountTotal) === false) discountTotal = 0;
		
		var orderTotal = subtotal + taxTotal + shippingTotal - discountTotal;
		
		$('#TransactionTotal .floatPrice').text(orderTotal.toFixed(2));
		
	}

    document.changePaymentInputs = function () {

		if ( $('#TransactionMode').val() === 'PAYSIMPLE.CC' ) {
			document.changeToPaysimpleCC();
		}
		if ( $('#TransactionMode').val() === 'PAYSIMPLE.CHECK' ) {
			document.changeToPaysimpleCheck();
		}
		if ( $('#TransactionMode').val() === 'PURCHASEORDER' ) {
			document.changeToPurchaseOrder();
		}
    };
    $('#TransactionMode').change(function(e){
		document.changePaymentInputs();
    });
    document.changePaymentInputs();

});
</script>

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
