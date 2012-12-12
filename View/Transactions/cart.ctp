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
//debug($this->request->data);
?>

<div id="transactionsCheckout" class="transactions checkout form">
    <?php
    echo $this->Html->script('/transactions/js/jquery-1.8.2.min', array('inline' => false));  
    echo $this->Html->script('plugins/jquery.validate.min', array('inline' => false));
    echo $this->Html->css('/transactions/css/transactions', null, array('inline' => false));
    echo $this->Form->create('Transaction', array('action' => 'checkout'));

    ?>
    <script>
     /*   jQuery(document).ready(function(){
            // binds form submission and fields to the validation engine
            jQuery("#TransactionCheckoutForm").validationEngine();
        }); */
    </script>     
  	<?php // is_virtual is true , then don't show shipping information form.  
    $display_shipping=true;
    $transaction_item_count=count($this->request->data['TransactionItem']);
    for($i=0;$i<$transaction_item_count;$i++) {
          if($this->request->data['TransactionItem'][$i]['is_virtual']==true) {
              $display_shipping=false;
          }
    }
    
    
	if($this->request->data['Customer']['id'] == NULL) {
	  // show a login button
	  echo __d('transactions', 'Returning Customer?') . '&nbsp;';
	  echo $this->Html->link(__d('transactions', 'Please Login'), array('plugin' => 'users', 'controller' => 'users', 'action' => 'login'), array('class' => 'btn'));
	}
	?>
  
    <h2>
	<?php echo __d('transactions', 'You are 30 seconds away from ordering...'); ?>
    </h2>
 
    <div id="orderTransactionForm" class="orderTransactionForm text-inputs">
	  <h3><?php echo __d('transactions', 'Please fill in your billing details'); ?></h3>
	  
	  <div id="transactionCartLeft">

		  <div id="orderTransactionAddress">
			<fieldset id="billingAddress">
				<legend><?php echo __d('transactions', 'Billing Address'); ?></legend>
				<?php
				echo $this->Form->input('TransactionAddress.0.email', array('class' => 'required'));
				echo $this->Form->input('TransactionAddress.0.first_name', array('class' => 'validate[required,custom[onlyLetterNumber],maxSize[20]', 'div' => array('style' => 'display:inline-block')));
				echo $this->Form->input('TransactionAddress.0.last_name', array('class' => 'validate[required,custom[onlyLetterNumber],maxSize[20]', 'div' => array('style' => 'display:inline-block; margin-left: 5px;')));
				echo $this->Form->input('TransactionAddress.0.street_address_1', array('label' => 'Street', 'class' => 'validate[required] text-input', 'size' => '49'));
				echo $this->Form->input('TransactionAddress.0.street_address_2', array('label' => 'Street 2', 'class' => 'validate[required] text-input','size' => '49'));
				echo $this->Form->input('TransactionAddress.0.city', array('label' => 'City ', 'class' => 'validate[required,custom[onlyLetterSp]] text-input', 'size' => '29', 'div' => array('style' => 'display:inline-block')));
				echo $this->Form->input('TransactionAddress.0.state', array('label' => 'State ', 'class' => 'validate[required] text-input', 'type' => 'select', 'options' => array_merge(array('' => '--Select--'), states()), 'div' => array('style' => 'display:inline-block')));
				echo $this->Form->input('TransactionAddress.0.zip', array('label' => 'Zip ', 'class' => 'validate[required,custom[onlyLetterNumber],maxSize[10]', 'maxlength'=>'10', 'size' => '10'));
				echo $this->Form->hidden('TransactionAddress.0.country', array('label' => 'Country', 'class' => 'validate[required,custom[onlyLetterNumber]','value' => 'US'));
				echo $this->Form->input('TransactionAddress.0.phone', array('label' => 'Phone', 'class' => 'validatevalidate[required,custom[phone]] text-input','maxlength'=>'10'));
				
                if($display_shipping==true) {
                    //$display_shipping is false, shipping address not display 
                echo $this->Form->input('TransactionAddress.0.shipping', array('type' => 'checkbox', 'label' => 'Click here if your shipping address is different than your contact information.'));
				echo $this->Form->hidden('TransactionAddress.0.type', array('value' => 'billing'));
                }
                
				?>
			</fieldset>
          
			<fieldset id="shippingAddress">
				<legend><?php echo __d('transactions', 'Shipping Address'); ?></legend>
				<div id="shipping_error"></div>
				<?php
				echo $this->Form->input('TransactionAddress.1.street_address_1', array('label' => 'Street', 'size' => '49'));
				echo $this->Form->input('TransactionAddress.1.street_address_2', array('label' => 'Street 2', 'size' => '49'));
				echo $this->Form->input('TransactionAddress.1.city', array('label' => 'City', 'size' => '29', 'div' => array('style' => 'display:inline-block')));
				echo $this->Form->input('TransactionAddress.1.state', array('label' => 'State ', 'options' => array_merge(array('' => '--Select--'), states()), 'div' => array('style' => 'display:inline-block')));
				echo $this->Form->input('TransactionAddress.1.zip', array('label' => 'Zip', 'maxlength'=>'10', 'size' => '10'));
				echo $this->Form->hidden('TransactionAddress.1.country', array('label' => 'Country ', 'value' => 'US'));
				echo $this->Form->hidden('TransactionAddress.1.type', array('value' => 'shipping'));
				?>
			</fieldset>
           
		  </div><!-- #orderTransactionAddress -->


		  <fieldset id="paymentInformation">
			<legend><?php echo __d('transactions', 'Payment Information'); ?></legend>
			<?php
            //debug($options['paymentMode']);
            
            
				echo $this->Element(strtolower($options['paymentMode']));
			?>
		  </fieldset><!-- #PaymentInformation -->

	  </div><!-- #transactionCartLeft -->


	  <div id="transactionCartRight">
		  <?php
		  echo $this->Element('trust_logos', array('plugin' => 'transactions'));
		  ?>
		  <fieldset id="orderTransactionItems" class="orderTransactionItems">
			<legend><?php echo __d('transactions', 'Shopping Cart') ?></legend>

			<?php

			//debug($this->request->data['TransactionItem']);
			foreach ($this->request->data['TransactionItem'] as $i => $transactionItem) {
				echo $this->Form->hidden("TransactionItem.{$i}.id", array('value' => $transactionItem['id'])); ?>
				<div class="transactionItemInCart" id="TransactionItem<?php echo $i ?>">
					<?php      
					echo $this->element('Transactions/cart_item', array(
						'transactionItem' => $transactionItem,
						'i' => $i
						),
						array('plugin' => ZuhaInflector::pluginize($transactionItem['model']))
					);
					?>
				</div>

			<?php
			} // foreach($transactionItem)
			?>
		  </fieldset><!-- end orderTransactionItems -->

		  <fieldset>
			<legend><?php echo __d('transactions', 'Order Summary') ?></legend>
			<?php
            			//echo !empty($enableShipping) ? $this->Form->input('Transaction.shipping_charge', array('readonly' => true, 'value' => ZuhaInflector::pricify($options['defaultShippingCharge']))) : $this->Form->hidden('OrderTransaction.shipping_charge', array('readonly' => true, 'value' => ''));
			//echo $this->Form->input('Transaction.order_charge', array('label'=>'Sub-Total', 'readonly' => true, 'value' => ZuhaInflector::pricify($myCart['Transaction']['order_charge'])));
            echo $this->Form->hidden('Transaction.order_charge', array('label'=>'Sub-Total', 'readonly' => true, 'value' => ZuhaInflector::pricify($this->request->data['Transaction']['order_charge']))); 
			$orderTotal = floatval($options['defaultShippingCharge']) + floatval($this->request->data['Transaction']['order_charge']);
			$pricifiedOrderTotal = number_format($orderTotal, 2, null, ''); // field is FLOAT, no commas allowed
			//echo $this->Form->input('Transaction.discount', array('label' => 'Discount', 'readonly' => true));
			?>
			<div><?php echo __d('transactions', 'Subtotal') ?>: <span id="TransactionSubtotal" class="total" style="float:right; font-weight: bold; font-size: 110%">$<span class="floatPrice"><?php echo ZuhaInflector::pricify($this->request->data['Transaction']['order_charge']) ?></span></span></div>
			<div><?php echo __d('transactions', 'Shipping') ?>: <span id="TransactionShipping" class="total" style="float:right; font-weight: bold; font-size: 110%">+ $<span class="floatPrice"><?php echo ZuhaInflector::pricify($this->request->data['Transaction']['shipping_charge']) ?></span></span></div>
			<div><?php echo __d('transactions', 'Discount') ?>: <span id="TransactionDiscount" class="total" style="float:right; font-weight: bold; font-size: 110%"><span class="floatPrice"><?php //echo ZuhaInflector::pricify($this->request->data['Transaction']['discount']) ?></span></span></div>
			<hr/>
			<div style="margin: 10px 0; font-weight: bold;">Total: <span id="TransactionTotal" class="total" style="float:right; font-weight: bold; font-size: 120%">$<span class="floatPrice"><?php echo $pricifiedOrderTotal ?></span></span></div>
			<div><small><a id="enterPromo" href="#"><?php echo __d('transactions', 'Have a Promo Code?') ?></a></small></div>
			<?php
            echo $this->Form->hidden('Transaction.hiddentotal', array('label'=>'Total', 'readonly' => true, 'value' => $pricifiedOrderTotal));
			//echo $this->Form->input('Transaction.total', array('label' => 'Total <small><a id="enterPromo" href="#">Enter Promo Code</a></small>', 'readonly' => true, 'value' => $pricifiedOrderTotal, 'class' =>'uneditable-input',/* 'after' => defined('__USERS_CREDITS_PER_PRICE_UNIT') ? " Or Credits : " . __USERS_CREDITS_PER_PRICE_UNIT * $orderTotal : "Or Credits : " .  $orderTotal */));
			echo $this->Form->input('TransactionCoupon.code', array('label' => false, 'placeholder' => 'enter code', 'after' => '<a id="applyCode" href="#" class="btn">Apply Code</a>'));
           
			echo $this->Form->end(__d('transactions', 'Checkout'));
			?>
		  </fieldset>
	  </div><!-- #transactionCartRight -->

    </div><!--  id="orderTransactionForm" class="orderTransactionForm text-inputs" -->
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
          success:function(data){  
            
        var discount = $("#TransactionOrderCharge").text() - data['data']['Transaction']['order_charge'];
			$('#TransactionTotal').text('$'+data['data']['Transaction']['order_charge']);
            $('#TransactionTotal').val(data['data']['Transaction']['order_charge']);
            
           	$("#TransactionDiscount").val(discount.toFixed(2));
            if(data['data']['TransactionCoupon']['discount_type']=='fixed') {
              symbol='$';  
              $("#TransactionDiscount").text('- '+ symbol + data['data']['TransactionCoupon']['discount']);
            }else{ 
              var discount_amount=(data['data']['TransactionCoupon']['discount']/100)*$("#TransactionHiddentotal").val(); 
              symbol='%';  
            $("#TransactionDiscount").text('- '+ symbol+ data['data']['TransactionCoupon']['discount']+' ( $ '+discount_amount+')');
            }
             
          //document.getElementById("TransactionOrderCharge").value=data['data']['Transaction']['order_charge'];
			$("#TransactionDiscount").parent().show();
			//total();
		  },
		  error:function(data){  
     		$("#TransactionDiscount").val('');
			$("#TransactionDiscount").parent().hide();
			$('#TransactionTotal').val($("#TransactionOrderCharge").val());
			alert('Code out of date or does not apply.');
		  }
	  });
      
               }
            else {
              alert("Total amount should not zero");
            } 
    });
    
     
 </script>               

<script type="text/javascript">      
    var shipTypeValue = $('#TransactionShippingType').val();

        <?php if (!empty($allVirtual)) { ?>
            $("#TransactionShipping").parent().hide();
        <?php } ?>


	/**
	 * shipping same as billing toggle
	 */
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
			if(isNaN(transactionShipCharge))
				transactionShipCharge = 0;
                transactionShipCharge -= parseFloat(prevShippingAmmount) ;
                transactionShipCharge += parseFloat(response['amount']) ;
                $('#TransactionShippingCharge').val(transactionShipCharge);

			$('#TransactionTotal').val(parseFloat(<?php echo $this->request->data['Transaction']['order_charge']; ?>) + parseFloat(response['amount']) );
			//$('#step3').show();
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
    $().ready(function() {
	  //$("#TransactionCheckoutForm").validate();
    });
    
	/**
	 * experimental fancy scrolling Shopping Cart
	 */
//    var $scrollingDiv = $("#orderTransactionItems");
//
//    $(window).scroll(function(){
//	  if($(window).scrollTop() + $("#orderTransactionItems").innerHeight() >= $("#transactionCartLeft")[0].scrollHeight) {
//		  $scrollingDiv.stop();
//	  } else {
//	  $scrollingDiv
//		  .stop()
//		  .animate({"marginTop": ($(window).scrollTop() + 30) + "px"}, "slow" );
//	  }
//    });
	
	
	
	/**
	 * dynamic totals
	 */
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
		$('#orderTransactionItems .floatPrice').each(function(e) {
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
