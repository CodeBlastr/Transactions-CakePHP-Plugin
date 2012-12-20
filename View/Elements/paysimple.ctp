<!-- PaySimple -->
<?php

if(isset($this->request->data['Customer']['Connection'][0])) {
	//debug($this->request->data);  
	$connectionData = unserialize($this->request->data['Customer']['Connection'][0]['value']);
	 
	if(isset($connectionData['Account']['CreditCard'])) {
		echo '<h5>Use a saved Credit Card</h5>';
		foreach($connectionData['Account']['CreditCard'] as $savedCC) {
			$ccAccounts[$savedCC['Id']] = $savedCC['Issuer'] . ' ' . $savedCC['CreditCardNumber'] . ' exp. ' . $savedCC['ExpirationDate'];
			if($savedCC['IsDefault'] == true) $defaultAccount = $savedCC['Id'];
			// echo out a hidden div for easy copying of data from the saved account list to the payment fields
//			echo $this->Html->div('hidden', $savedCC['Issuer'], array('id' => $savedCC['Id'].'_issuer'));
//			echo $this->Html->div('hidden', $savedCC['CreditCardNumber'], array('id' => $savedCC['Id'].'_card'));
//			echo $this->Html->div('hidden', $savedCC['ExpirationDate'], array('id' => $savedCC['Id'].'_exp'));
		}  
		echo $this->Form->radio('paysimple_account', $ccAccounts, array('style' => 'float: left;', 'hiddenField'=>false, 'class' => 'savedCredit'));
	}
	if(isset($connectionData['Account']['Ach'])) {
		echo '<h5>Use a saved ACH Account</h5>';
		foreach($connectionData['Account']['Ach'] as $savedAch) {
			$achAccounts[$savedAch['Id']] = $savedAch['BankName'] . $savedAch['AccountNumber'];
			if($savedAch['IsDefault'] == true) $defaultAccount = $savedAch['Id'];
			// echo out a hidden div for easy copying of data from the saved account list to the payment fields
//			echo $this->Html->div('hidden', $savedAch['BankName'], array('id' => $savedAch['Id'].'_bank'));
//			echo $this->Html->div('hidden', $savedAch['AccountNumber'], array('id' => $savedAch['Id'].'_acct'));
		} 
		echo $this->Form->radio('paysimple_account', $achAccounts, array('style' => 'float: left;', 'hiddenField'=>false, 'class' => 'savedAch'));
	}
	
	echo '<h5>Use a new Payment Method</h5>';
}


echo $this->Form->input('mode', array('label' => 'Payment Method', 'options' => $options['paymentOptions'], 'default' => $options['paymentMode'])); ?>

<fieldset id="creditCardInfo">  
<?php
echo $this->Form->input('card_number', array('label' => 'Card Number', 'class' => 'required paysimpleCc'));
echo $this->Form->input('card_exp_month', 
		array('label' => 'Expiration Month', 'type' => 'select',
			'options' => array_combine(range(1, 12, 1), range(1, 12, 1)),
			'after' => $this->Form->input('card_exp_year', array('class' => 'paysimpleCc', 'label' => 'Exp Year', 'type' => 'select', 'options' => array_combine(range(date('Y'), date('Y', strtotime('+ 10 years')), 1), range(date('Y'), date('Y', strtotime('+ 10 years')), 1)), 'dateFormat' => 'Y')),
			'class' => 'paysimpleCc'
			)
);
echo $this->Form->input('card_sec', array('class' => 'paysimpleCc', 'label' => 'CCV Code ' . $this->Html->link('?', '#ccvHelp', array('class' => 'helpBox paysimpleCc', 'title' => 'You can find this 3 or 4 digit code on the back of your card, typically in the signature area.')), 'maxLength' => 4));   ?>
</fieldset><!-- #creditCardInfo -->
<fieldset id="echeckInfo">
<?php
echo $this->Form->input('ach_routing_number', array('label' => 'Routing Number', 'class' => 'required paysimpleCheck'));
echo $this->Form->input('ach_account_number', array('label' => 'Account Number', 'class' => 'required paysimpleCheck'));
echo $this->Form->input('ach_bank_name', array('label' => 'Bank Name', 'class' => 'required paysimpleCheck'));
echo $this->Form->input('ach_is_checking_account', array('type' => 'checkbox', 'label' => 'Is this a checking account?', 'class' => 'paysimpleCheck')); ?>
   </fieldset><!-- #echeckInfo --> 
<script type="text/javascript">
$(function() {
	$(".savedCredit, .savedAch").click(function(){
		clearPaymentInputs();
		// copy data to fields?
		
	});
	
	function clearPaymentInputs() {
		$('input.paysimpleCc, input.paysimpleCheck').each(function(){
			$(this).val('');
			$(this).removeClass('required');
			$(this).removeAttr('required');
		});
	}
	
    function changePaymentInputs() {
		if ($('#TransactionMode').val() == 'PAYSIMPLE.CHECK') {
			$('input.paysimpleCheck').each(function(){
				$(this).addClass('required');
				$(this).attr('required', 'required');
			});
			$('input.paysimpleCc').each(function(){
				$(this).val('');
				$(this).removeClass('required');
				$(this).removeAttr('required');
			});
			$('.paysimpleCc').parent().parent().hide('fast');
			$('.paysimpleCheck').parent().parent().show('slow');
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
			$('.paysimpleCheck').parent().parent().hide('fast');
			$('.paysimpleCc').parent().parent().show('slow');
		}
    }
    $('#TransactionMode').change(function(e){
		changePaymentInputs();
    });
    changePaymentInputs();
});
</script>