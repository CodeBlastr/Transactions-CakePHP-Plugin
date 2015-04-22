<?php
/**
 * @var $this View
 * @todo These should not be prefixed with brainTree like they are, but I'm not sure what to change them to quite yet.
**/
?>

<?php if (isset($this->request->data['Customer']['Connection'][0])) : ?>
	<?php $connectionData = unserialize($this->request->data['Customer']['Connection'][0]['value']); ?>
	<?php if (isset($connectionData['Account']['CreditCard'])) : ?>
		<h5>Use a Saved Credit Card</h5>
		<?php foreach ($connectionData['Account']['CreditCard'] as $savedCC) : ?>
			<?php $ccAccounts[$savedCC['Id']] = $savedCC['Issuer'] . ' ' . $savedCC['CreditCardNumber'] . ' exp. ' . $savedCC['ExpirationDate']; ?>
			<?php // not even used // if ($savedCC['IsDefault'] == true) $defaultAccount = $savedCC['Id']; ?>
			<?php echo $this->Form->input('braintree_account', array(
					'value' => $savedCC['Id'],
					'label' => $savedCC['Issuer'] . ' ' . $savedCC['CreditCardNumber'] . ' exp. ' . $savedCC['ExpirationDate'],
					'type' => 'checkbox',
					'hiddenField' => false,
					'class' => 'savedCredit'
					)); ?>
		<?php endforeach; ?>
	<?php endif; ?>
	<h5 class="new-payment-title">Use a new Payment Method</h5>
<?php endif; ?>

<div class="new-payment-fields">
    <?php echo $this->Form->input('mode', array('label' => 'Payment Method', 'options' => $options['paymentOptions'], 'default' => $options['paymentMode'])); ?>
    
    <div class="row">
    	<div class="col-sm-12">
    		<?php echo $this->Form->input('Transaction.card_number', array('label' => 'Card Number', 'class' => 'required creditcard', 'maxLength' => 16, 'pattern' => '...', 'inputmode' => 'numeric', 'autocomplete' => 'cc-number')); // credit card inputs ?>
    	</div>
    </div>
    <div class="row">
    	<div class="col-sm-4">
    		<?php echo $this->Form->input('Transaction.card_expire.month', array('label' => 'Expiration Month', 'type' => 'select', 'options' => array_combine(range(1, 12, 1), range(1, 12, 1)))); ?>
    	</div>
    	<div class="col-sm-4">
    		<?php echo $this->Form->input('Transaction.card_expire.year', array('class' => 'required', 'label' => 'Exp Year', 'type' => 'select', 'options' => array_combine(range(date('Y'), date('Y', strtotime('+ 10 years')), 1), range(date('Y'), date('Y', strtotime('+ 10 years')), 1)), 'dateFormat' => 'Y')); ?>
    	</div>
    	<div class="col-sm-4">
    		<?php echo $this->Form->input('Transaction.card_sec', array('class' => 'required', 'label' => 'CCV Code ' . $this->Html->link('?', '#ccvHelp', array('class' => 'helpBox paysimpleCc', 'title' => 'You can find this 3 or 4 digit code on the back of your card, typically in the signature area.')), 'maxLength' => 4)); ?>
    	</div>
    </div>
</div>
	
	<script type="text/javascript">
$(function() {
	// clear the new payment method inputs when they choose a previous payment method
	$(".savedCredit, .savedAch").click(function(){
		var clickedCheckboxId = $(this).attr('id');
		if ( $('#'+clickedCheckboxId).prop('checked') === false ) {
			// they are deselecting a saved payment method
			$('#useNewPayment').show('slow');
			$('.new-payment-fields').show('slow');
			document.changePaymentInputs();
			return ;
		}

		// uncheck other saved methods
		$(".savedCredit, .savedAch").each(function() {
			if($(this).attr('id') !== clickedCheckboxId) $(this).prop('checked', false);
		});

		// remove required from cc and check inputs
		$('.new-payment-fields input').each(function(){
			$(this).val('');
			$(this).removeClass('required');
			$(this).removeAttr('required');
		});
		// hide cc and check inputs
		$('.new-payment-title').hide('slow');
		$('.new-payment-fields').hide('slow');
		$('.purchaseOrder').parent().parent().hide();
		$('.pdfInvoice').hide();
	});

	// delect saved payment account when they type in a new account
	$('.new-payment-fields input').keypress(function(){
		$(".savedCredit, .savedAch").prop('checked', false);
		document.changePaymentInputs();
	});
});
</script>