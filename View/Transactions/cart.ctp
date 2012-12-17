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
    echo $this->Form->create('Transaction', array('action' => 'checkout', 'class' => 'form-responsive validate'));     
    
	if($this->request->data['Customer']['id'] == NULL) {
	    // show a login button
	    echo __d('transactions', 'Returning Customer?') . '&nbsp;';
	    echo $this->Html->link(__d('transactions', 'Please Login'), array('plugin' => 'users', 'controller' => 'users', 'action' => 'login'), array('class' => 'btn'));
	} ?>
    
    <div id="transactionForm" class="transactionForm text-inputs row">
	    <div id="transactionCartLeft" class="span8 pull-left">
		    <div id="transactionAddress">
			    <fieldset id="billingAddress" class="control-group">
    				<legend><?php echo __d('transactions', 'Billing Details'); ?></legend>
    				<?php
        			echo $this->Form->input('TransactionAddress.0.first_name', array('class' => 'required', 'after' => $this->Form->input('TransactionAddress.0.last_name', array('class' => 'required'))));
    				echo $this->Form->input('TransactionAddress.0.email', array('class' => 'required email'));
                    echo $this->Form->input('TransactionAddress.0.country', array('label' => 'Country', 'class' => 'required'));
    				echo $this->Form->input('TransactionAddress.0.street_address_1', array('label' => 'Street', 'class' => 'required'));
    				echo $this->Form->input('TransactionAddress.0.street_address_2', array('label' => 'Street 2', 'class' => 'required'));
    				echo $this->Form->input('TransactionAddress.0.city', array('label' => 'City ', 'class' => 'required', 'after' => $this->Form->input('TransactionAddress.0.state', array('label' => 'State ', 'class' => 'required', 'type' => 'select', 'empty' => '-- Select --', 'options' => states())) . $this->Form->input('TransactionAddress.0.zip', array('label' => 'Zip ', 'class' => 'required', 'maxlength' => '10')) ));
    				echo $this->Form->input('TransactionAddress.0.phone', array('label' => 'Phone', 'class' => 'required', 'maxlength'=>'10'));
    				echo $options['displayShipping'] ? $this->Form->input('TransactionAddress.0.shipping', array('type' => 'checkbox', 'label' => 'Click here if your shipping address is different than your contact information.')) : null;
    				echo $options['displayShipping'] ? $this->Form->hidden('TransactionAddress.0.type', array('value' => 'billing')) : null; ?>
			    </fieldset>
          
			    <fieldset id="shippingAddress" class="control-group">
    				<legend><?php echo __d('transactions', 'Shipping Address'); ?></legend>
    				<div id="shipping_error"></div>
    				<?php
    				echo $this->Form->input('TransactionAddress.1.street_address_1', array('label' => 'Street', 'size' => '49'));
    				echo $this->Form->input('TransactionAddress.1.street_address_2', array('label' => 'Street 2', 'size' => '49'));
    				echo $this->Form->input('TransactionAddress.1.city', array('label' => 'City'));
    				echo $this->Form->input('TransactionAddress.1.state', array('label' => 'State ', 'empty' => '-- Select --', 'options' => states()));
    				echo $this->Form->input('TransactionAddress.1.zip', array('label' => 'Zip', 'maxlength'=>'10'));
    				echo $this->Form->input('TransactionAddress.1.country', array('label' => 'Country '));
    				echo $this->Form->hidden('TransactionAddress.1.type', array('value' => 'shipping')); ?>
			    </fieldset>
		    </div>

		    <fieldset id="paymentInformation" class="control-group">
			    <legend><?php echo __d('transactions', 'Payment Information'); ?></legend>
			    <?php echo $this->Element(strtolower($options['paymentMode'])); ?>
		    </fieldset>
	    </div>


	    <div id="transactionCartRight" class="span4 pull-right">
		    <?php echo $this->Element('trust_logos', array('plugin' => 'transactions')); ?>
		    <fieldset id="transactionItems" class="transactionItems">
			    <legend><?php echo __d('transactions', 'Shopping Cart') ?></legend>
			    <?php
			    foreach ($this->request->data['TransactionItem'] as $i => $transactionItem) {
				    echo $this->Form->hidden("TransactionItem.{$i}.id", array('value' => $transactionItem['id']));
				    echo __('<div class="transactionItemInCart" id="TransactionItem%s">%s</div>', $i, $this->element('Transactions/cart_item', array('transactionItem' => $transactionItem, 'i' => $i), array('plugin' => ZuhaInflector::pluginize($transactionItem['model']))));
			    } ?>
		    </fieldset>

		    <fieldset>
			    <legend><?php echo __d('transactions', 'Summary') ?></legend>
			    <?php
            	echo $this->Form->hidden('Transaction.order_charge', array('label'=>'Sub-Total', 'readonly' => true, 'value' => ZuhaInflector::pricify($this->request->data['Transaction']['order_charge']))); 
			    $orderTotal = floatval($options['defaultShippingCharge']) + floatval($this->request->data['Transaction']['order_charge']);
			    $pricifiedOrderTotal = number_format($orderTotal, 2, null, ''); ?>
			    <div class="promoCode"><small><a id="enterPromo" href="#"><?php echo __d('transactions', 'Have a Promo Code?') ?></a></small></div>
    		    <?php echo $this->Form->input('TransactionCoupon.code', array('label' => false, 'placeholder' => 'enter code', 'after' => '<a id="applyCode" href="#" class="btn">Apply Code</a>')); ?>
                <table class="table-hover"><tbody><tr>
                <td class="subTotal"><?php echo __d('transactions', 'Subtotal') ?> <td id="TransactionSubtotal" class="total">$<span class="floatPrice"><?php echo ZuhaInflector::pricify($this->request->data['Transaction']['order_charge']) ?></span></td>
			    </tr><tr>
                <td class="shippingTotal"><?php echo __('Shipping') ?>  </td><td id="TransactionShipping" class="total">+ $<span class="floatPrice"><?php echo ZuhaInflector::pricify($this->request->data['Transaction']['shipping_charge']) ?></span></td>
			    </tr><tr>
                <td class="discountTotal"><?php echo __('Discount') ?>: <span id="TransactionDiscount" class="total"><span class="floatPrice"></span></span></div>
			    </tr><tr>
			    <td class="transactionTotal" style="margin: 10px 0; font-weight: bold;">Total </td><td id="TransactionTotal" class="total">$<span class="floatPrice"><?php echo $pricifiedOrderTotal ?></span></td>
			    </tbody></tr></table>
                <?php
                echo $this->Form->hidden('Transaction.hiddentotal', array('label'=>'Total', 'readonly' => true, 'value' => $pricifiedOrderTotal));
			    echo $this->Form->end(__('Checkout'));	?>
		    </fieldset>
	    </div><!-- #transactionCartRight -->
    </div>
</div>


<script type="text/javascript">  
   
    // hide / show the coupon code input dependent on value
    if (!$("#TransactionCouponCode").val()) {
	  $("#TransactionCouponCode").parent().hide();
	  $("#enterPromo").click(function(e){
		  e.preventDefault();
		  $("#TransactionCouponCode").parent().toggle('slow');
	  });
    }
     
    // hide the discount input if empty
    if (!$("#TransactionDiscount").val()) {
	  $("#TransactionDiscount").parent().hide();
    }

   
    // handle a submitted code for verification (update total)
    $("#applyCode").click(function(e){
   	    e.preventDefault(); 
      
        if($("#TransactionHiddentotal").val() > 0) {
	        $.ajax({
		        type: "POST",
		        data: $('#TransactionCheckoutForm').serialize(),
		        url: "/transactions/transaction_coupons/verify.json" ,  
		        dataType: "json",       
                success:function(data) {  
            
                    var discount = $("#TransactionOrderCharge").text() - data['data']['Transaction']['order_charge'];
			        $('#TransactionTotal').text('$'+data['data']['Transaction']['order_charge']);
                    $('#TransactionTotal').val(data['data']['Transaction']['order_charge']);
                    $("#TransactionDiscount").val(discount.toFixed(2));
                    if(data['data']['TransactionCoupon']['discount_type']=='fixed') {
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
    
    var shipTypeValue = $('#TransactionShippingType').val();

	// shipping same as billing toggle 
    $('#TransactionAddress0Shipping').change(function(e){
	  if ( $('#TransactionAddress0Shipping').attr("checked") == undefined) {
		  $('#TransactionAddress1FirstName').val($('#TransactionAddress0FirstName').val());
		  $('#TransactionAddress1LastName').val($('#TransactionAddress0LastName').val());
		  $('#TransactionAddress1StreetAddress1').val($('#TransactionAddress0StreetAddress1').val());
		  $('#TransactionAddress1StreetAddress2').val($('#TransactionAddress0StreetAddress2').val());
		  $('#TransactionAddress1City').val($('#TransactionAddress0City').val());
		  $('#TransactionAddress1State').val($('#TransactionAddress0State').val());
		  $('#TransactionAddress1Zip').val($('#TransactionAddress0Zip').val());
		  $('#TransactionAddress1Country').val($('#TransactionAddress0Country').val());
		  $('#shippingAddress').hide('slow');
	  }
	  if ( $('#TransactionAddress0Shipping').attr("checked") == 'checked') {
		  $('#shippingAddress').show('slow');
	  }
    });

    $('.shipping_type').change(function(e){
	    shipTypeValue = $(this).val();
	    var dimmensions = new Array();
	    $(this).parent().siblings().children().each(function() {
		    dimmensions[$(this).attr("id")] = $(this).val();
	    });
	    getShipRate(shipTypeValue, dimmensions);
    });


    function getShipRate(shipTypeValue, dimmensions) {
	    if (shipTypeValue == ' ') {
		    $('#TransactionShippingCharge').val(0);
		    $('#TransactionTotal').val(parseFloat(<?php echo $this->request->data['Transaction']['order_charge']; ?>));
		    return;
	    }

	    $.ajax({
		    type: "POST",
		    data: $('#TransactionCheckoutForm').serialize(),
		    url: "/shipping/shippings/getShippingCharge/",
		    dataType: "text",
		    success:function(data){
			    response(data, dimmensions['OrderTransactionShippingAmmount'])
		    }
	    });
    }

    function response(data, prevShippingAmmount) {
	    if (data.length > 0) {
		    var response = JSON.parse(data);
		    if(response['Message']) {
			    $('#shipping_error').html(response['Message']);
			    //$('#step3').hide();
		    } else if(response['amount']) {
			    $('#shipping_error').html('');
			    var transactionShipCharge = parseFloat($('#TransactionShippingCharge').val());
			    if(isNaN(transactionShipCharge)) {
				    transactionShipCharge = 0;
                    transactionShipCharge -= parseFloat(prevShippingAmmount) ;
                    transactionShipCharge += parseFloat(response['amount']) ;
                    $('#TransactionShippingCharge').val(transactionShipCharge);
                }
			    $('#TransactionTotal').val(parseFloat(<?php echo $this->request->data['Transaction']['order_charge']; ?>) + parseFloat(response['amount']) );
		    }
	    }
    }

    function shipping_response(data, option_value, option_key) {
	    if (data.length > 0) {
		    var response = JSON.parse(data);
		    if(response['amount']) {
			    $('#TransactionShippingType').append('<option value="' + option_value + '">'+ option_key +'</option>');
			    $('#TransactionShippingCharge').val(response['amount']);
			    $('#TransactionTotal').val(parseFloat(<?php echo $this->request->data['Transaction']['order_charge']; ?>) + parseFloat(response['amount']) );
		    }
	    }
    }
    
	// dynamic totals
	$('.TransactionItemCartQty').bind("change keyup", function(e){
		updateItemTotals($(this));
		updateSubtotal();
		updateOrderTotal();
	});
	
	function updateItemTotals(itemQty) {
		var transactionItemX = itemQty.attr('id').replace('Quantity', '');
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

	function updateShippingTotal() {
		
	}

	function updateOrderTotal() {
		var subtotal = parseFloat($('#TransactionSubtotal .floatPrice').text());
		var shippingTotal = parseFloat($('#TransactionShipping .floatPrice').text());
		var discountTotal = parseFloat($('#TransactionDiscount .floatPrice').text());
		if($.isNumeric(discountTotal) === false) discountTotal = 0;
		
		var orderTotal = subtotal + shippingTotal - discountTotal;
		
		$('#TransactionTotal .floatPrice').text(orderTotal.toFixed(2));
		
	}
</script>
