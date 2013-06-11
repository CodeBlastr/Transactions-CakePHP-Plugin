<!-- PaySimple -->
<?php

if ( isset($this->request->data['Customer']['Connection'][0]) ) {
	$connectionData = unserialize($this->request->data['Customer']['Connection'][0]['value']);
	if ( isset($connectionData['Account']['CreditCard']) ) {
		echo '<legend><h5>Use a saved Credit Card</h5></legend>';
		foreach ( $connectionData['Account']['CreditCard'] as $savedCC ) {
			$ccAccounts[$savedCC['Id']] = $savedCC['Issuer'] . ' ' . $savedCC['CreditCardNumber'] . ' exp. ' . $savedCC['ExpirationDate'];
			if ( $savedCC['IsDefault'] == true ) $defaultAccount = $savedCC['Id'];
			echo $this->Form->input('paysimple_account', array(
				'value' => $savedCC['Id'],
				'label' => $savedCC['Issuer'] . ' ' . $savedCC['CreditCardNumber'] . ' exp. ' . $savedCC['ExpirationDate'],
				'type' => 'checkbox',
				'hiddenField' => false,
				'class' => 'savedCredit'
			));
		}  
	}
	if ( isset($connectionData['Account']['Ach']) ) {
		echo '<h5>Use a saved ACH Account</h5>';
		foreach ( $connectionData['Account']['Ach'] as $savedAch ) {
			$achAccounts[$savedAch['Id']] = $savedAch['BankName'] . $savedAch['AccountNumber'];
			if ( $savedAch['IsDefault'] == true ) $defaultAccount = $savedAch['Id'];
			echo $this->Form->input('paysimple_account', array(
				'value' => $savedAch['Id'],
				'label' => $savedAch['BankName'] . ": " . $savedAch['AccountNumber'],
				'type' => 'checkbox',
				'hiddenField' => false,
				'class' => 'savedAch',
				'id' => 'savedAch_'.$savedAch['Id']
			));
		} 
	}	
	echo '<legend><h5 id="useNewPayment">Use a new Payment Method</h5></legend>';
}


echo $this->Form->input('mode', array('label' => 'Payment Method', 'options' => $options['paymentOptions'], 'default' => $options['paymentMode']));
// credit card inputs
echo $this->Html->tag('div',
		$this->Form->input('card_number', array('label' => 'Card Number', 'class' => 'required paysimpleCc creditcard', 'maxLength' => 16, 'pattern' => '...', 'inputmode' => 'numeric', 'autocomplete' => 'cc-number'))
		. $this->Form->input('card_exp_month', 
		array('label' => 'Expiration Month', 'type' => 'select',
			'options' => array_combine(range(1, 12, 1), range(1, 12, 1)),
			'after' => $this->Form->input('card_exp_year', array('class' => 'required paysimpleCc', 'label' => 'Exp Year', 'type' => 'select', 'options' => array_combine(range(date('Y'), date('Y', strtotime('+ 10 years')), 1), range(date('Y'), date('Y', strtotime('+ 10 years')), 1)), 'dateFormat' => 'Y')),
			'class' => 'required paysimpleCc'
			)
		)
		, array('id' => 'creditCardInfo'));
echo $this->Form->input('card_sec', array('class' => 'required paysimpleCc', 'label' => 'CCV Code ' . $this->Html->link('?', '#ccvHelp', array('class' => 'helpBox paysimpleCc', 'title' => 'You can find this 3 or 4 digit code on the back of your card, typically in the signature area.')), 'maxLength' => 4));
//echeck info
echo $this->Html->tag('div',
		$this->Form->input('ach_routing_number', array('label' => 'Routing Number', 'class' => 'required paysimpleCheck'))
		. $this->Form->input('ach_account_number', array('label' => 'Account Number', 'class' => 'required paysimpleCheck'))
		. $this->Form->input('ach_bank_name', array('label' => 'Bank Name', 'class' => 'required paysimpleCheck'))
		. $this->Form->input('ach_is_checking_account', array('type' => 'checkbox', 'label' => 'Is this a checking account?', 'class' => 'paysimpleCheck'))
		, array('id' => 'echeckInfo'));
?>

<script type="text/javascript">
$(function() {
	// clear the new payment method inputs when they choose a previous payment method
	$(".savedCredit, .savedAch").click(function(){

		var clickedCheckboxId = $(this).attr('id');
				
		if ( $('#'+clickedCheckboxId).prop('checked') === false ) {
			// they are deselecting a saved payment method
			$('#useNewPayment').show('slow');
			$('#TransactionMode').parent().parent().show('slow');
			changePaymentInputs();
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
		$('#useNewPayment').hide('slow');
		$('#TransactionMode').parent().parent().hide('slow');
		$('.paysimpleCc').parent().parent().hide('slow');
		$('.paysimpleCheck').parent().parent().hide('slow');
	});
	
	// delect saved payment account when they type in a new account
	$('input.paysimpleCc, input.paysimpleCheck').keypress(function(){
		$(".savedCredit, .savedAch").prop('checked', false);
		changePaymentInputs();
	});
	
    function changePaymentInputs() {
		$(".savedCredit, .savedAch").prop('checked', false);
		if ( $('#TransactionMode').val() === 'PAYSIMPLE.CHECK' ) {
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
			$('.paysimpleCc').parent().parent().hide();
			$('.paysimpleCheck').parent().parent().show('slow');
			/* ^ was this, but removed the parent() and is working on discoverywoods.buildrr.com/transactions/transactions/cart
			$('.paysimpleCc').parent().parent().hide();
			$('.paysimpleCheck').parent().parent().show('slow'); */
		} else {
			$('input.paysimpleCc').each(function(){
				$(this).addClass('required');
				$(this).attr('required', 'required');
			});
			$('input.paysimpleCheck').each(function(){
				$(this).val('');
				$(this).removeClass('required');
				$(this).removeAttr('required');
			});
			$('.paysimpleCheck').parent().parent().hide();
			$('.paysimpleCc').parent().parent().show('slow');
			/* ^ was this, but removed the parent() and is working on discoverywoods.buildrr.com/transactions/transactions/cart
			$('.paysimpleCheck').parent().parent().hide();
			$('.paysimpleCc').parent().parent().show('slow'); */
		}
    }
    $('#TransactionMode').change(function(e){
		changePaymentInputs();
    });
    changePaymentInputs();
});
</script>