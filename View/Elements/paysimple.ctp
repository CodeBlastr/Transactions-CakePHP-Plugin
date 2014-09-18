<!-- PaySimple -->
<?php if (isset($this->request->data['Customer']['Connection'][0])) : ?>
	<?php $connectionData = unserialize($this->request->data['Customer']['Connection'][0]['value']); ?>
	<?php if (isset($connectionData['Account']['CreditCard'])) : ?>
		<h5>Use a Saved Credit Card</h5>
		<?php foreach ($connectionData['Account']['CreditCard'] as $savedCC) : ?>
			<?php $ccAccounts[$savedCC['Id']] = $savedCC['Issuer'] . ' ' . $savedCC['CreditCardNumber'] . ' exp. ' . $savedCC['ExpirationDate']; ?>
			<?php // not even used // if ($savedCC['IsDefault'] == true) $defaultAccount = $savedCC['Id']; ?>
			<?php echo $this->Form->input('paysimple_account', array(
					'value' => $savedCC['Id'],
					'label' => $savedCC['Issuer'] . ' ' . $savedCC['CreditCardNumber'] . ' exp. ' . $savedCC['ExpirationDate'],
					'type' => 'checkbox',
					'hiddenField' => false,
					'class' => 'savedCredit'
					)); ?>
		<?php endforeach; ?>
	<?php endif; ?>
	<?php if (isset($connectionData['Account']['Ach'])) : ?>
		<h5>Use a Saved ACH Account</h5>
		<?php foreach ($connectionData['Account']['Ach'] as $savedAch) : ?>
			<?php $achAccounts[$savedAch['Id']] = $savedAch['BankName'] . $savedAch['AccountNumber']; ?>
			<?php // not even used // if ( $savedAch['IsDefault'] == true ) $defaultAccount = $savedAch['Id']; ?>
			<?php echo $this->Form->input('paysimple_account', array(
					'value' => $savedAch['Id'],
					'label' => $savedAch['BankName'] . ": " . $savedAch['AccountNumber'],
					'type' => 'checkbox',
					'hiddenField' => false,
					'class' => 'savedAch',
					'id' => 'savedAch_'.$savedAch['Id']
					)); ?>
		<?php endforeach; ?>
	<?php endif; ?>
	<h5 class="new-payment-title">Use a new Payment Method</h5>
<?php endif; ?>

<div class="new-payment-fields">
	<?php echo $this->Form->input('mode', array('label' => 'Payment Method', 'options' => $options['paymentOptions'], 'default' => $options['paymentMode'])); ?>
	<?php echo $this->Form->input('card_number', array('label' => 'Card Number', 'class' => 'required paysimpleCc creditcard', 'maxLength' => 16, 'pattern' => '...', 'inputmode' => 'numeric', 'autocomplete' => 'cc-number')); // credit card inputs ?>
	<div class="row">
		<div class="col-sm-4">
			<?php echo $this->Form->input('card_exp_month', array('label' => 'Expiration Month', 'type' => 'select', 'options' => array_combine(range(1, 12, 1), range(1, 12, 1)),'class' => 'required paysimpleCc')); ?>
		</div>
		<div class="col-sm-4">
			<?php echo $this->Form->input('card_exp_year', array('class' => 'required paysimpleCc', 'label' => 'Exp Year', 'type' => 'select', 'options' => array_combine(range(date('Y'), date('Y', strtotime('+ 10 years')), 1), range(date('Y'), date('Y', strtotime('+ 10 years')), 1)), 'dateFormat' => 'Y')); ?>
		</div>
		<div class="col-sm-4">
			<?php echo $this->Form->input('card_sec', array('class' => 'required paysimpleCc', 'label' => 'CCV Code ' . $this->Html->link('?', '#ccvHelp', array('class' => 'helpBox paysimpleCc', 'title' => 'You can find this 3 or 4 digit code on the back of your card, typically in the signature area.')), 'maxLength' => 4)); ?>
		</div>
	</div>
	
	
	<div class="row">
		<div class="col-sm-6">
			<?php echo $this->Form->input('ach_routing_number', array('label' => 'Routing Number', 'class' => 'required paysimpleCheck')); //echeck info ?>
		</div>
		<div class="col-sm-6">
			<?php echo $this->Form->input('ach_account_number', array('label' => 'Account Number', 'class' => 'required paysimpleCheck')); ?>
		</div>
	</div>
	<div class="row">
		<div class="col-sm-6">
			<?php echo $this->Form->input('ach_bank_name', array('label' => 'Bank Name', 'class' => 'required paysimpleCheck')); ?>
		</div>
		<div class="col-sm-6">
			<?php echo $this->Form->input('ach_is_checking_account', array('type' => 'checkbox', 'label' => 'Is this a checking account?', 'class' => 'paysimpleCheck')); ?>
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
		$('input.paysimpleCc, input.paysimpleCheck').each(function(){
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
	$('input.paysimpleCc, input.paysimpleCheck').keypress(function(){
		$(".savedCredit, .savedAch").prop('checked', false);
		document.changePaymentInputs();
	});

	document.changeToPaysimpleCC = function () {
		$('input.paysimpleCc').each(function(){
			$(this).addClass('required');
			$(this).attr('required', 'required');
		});
		$('input.paysimpleCheck').each(function(){
			$(this).val('');
			$(this).removeClass('required');
			$(this).removeAttr('required');
		});
		$('.paysimpleCheck').closest('div.input').hide();
		$('.paysimpleCc').closest('div.input').show('slow');
		$('.purchaseOrder').closest('div.input').hide();
		$('.pdfInvoice').hide();
	};

	document.changeToPaysimpleCheck = function () {
		$(".savedCredit, .savedAch").prop('checked', false);
		$('input.paysimpleCheck').each(function(){
			if($(this).attr('id') !== 'TransactionAchIsCheckingAccount') {
				$(this).addClass('required');
				$(this).attr('required', 'required');
			}
		});
		$('input.paysimpleCc').each(function(){
			$(this).val('');
			$(this).removeClass('required');
			$(this).removeAttr('required');
		});
		$('.paysimpleCc').closest('div.input').hide();
		$('.paysimpleCheck').closest('div.input').show('slow');
		$('.purchaseOrder').closest('div.input').hide();
		$('.pdfInvoice').hide();
	};

});
</script>
