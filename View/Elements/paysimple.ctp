<!-- PaySimple -->
<?php

if(isset($this->request->data['Customer']['Connection'][0])) {
	//debug($this->request->data);  
	$connectionData = unserialize($this->request->data['Customer']['Connection'][0]['value']);
	 
	if(isset($connectionData['Account']['CreditCard'])) {
		echo '<h5>Use a saved Credit Card</h5>';
		foreach($connectionData['Account']['CreditCard'] as $savedCC) {
			$ccAccounts[$savedCC['Id']] = $savedCC['Issuer'] . $savedCC['CreditCardNumber'] . ' exp. ' . $savedCC['ExpirationDate'];
			if($savedCC['IsDefault'] == true) $defaultAccount = $savedCC['Id'];
		}  
		echo $this->Form->radio('paysimple_account', $ccAccounts, array('style' => 'float: left;','hiddenField'=>false));
	}
	if(isset($connectionData['Account']['Ach'])) {
		echo '<h5>Use a saved ACH Account</h5>';
		foreach($connectionData['Account']['Ach'] as $savedAch) {
			$achAccounts[$savedAch['Id']] = $savedAch['BankName'] . $savedAch['AccountNumber'];
			if($savedAch['IsDefault'] == true) $defaultAccount = $savedAch['Id'];
		} 
		echo $this->Form->radio('paysimple_account', $achAccounts, array('style' => 'float: left;','hiddenField'=>false));
	}
	
	echo '<h5>Use a new Payment Method</h5>';
}

echo $this->Form->input('mode', array('label' => 'Payment Method', 'options' => $options['paymentOptions'], 'default' => $options['paymentMode']));
echo $this->Form->input('card_number', array('label' => 'Card Number', 'class' => 'required'));
echo $this->Form->input('card_exp_month', array('label' => 'Expiration Month', 'type' => 'select', 'options' => array_combine(range(1, 12, 1), range(1, 12, 1)), 'after' => $this->Form->input('card_exp_year', array('label' => 'Exp Year', 'type' => 'select', 'options' => array_combine(range(date('Y'), date('Y', strtotime('+ 10 years')), 1), range(date('Y'), date('Y', strtotime('+ 10 years')), 1)), 'dateFormat' => 'Y'))));
echo $this->Form->input('card_sec', array('label' => 'CCV Code ' . $this->Html->link('?', '#ccvHelp', array('class' => 'helpBox', 'title' => 'You can find this 3 or 4 digit code on the back of your card, typically in the signature area.')), 'maxLength' => 4));
echo $this->Form->input('ach_routing_number', array('label' => 'Routing Number', 'class' => 'required'));
echo $this->Form->input('ach_account_number', array('label' => 'Account Number', 'class' => 'required'));
echo $this->Form->input('ach_bank_name', array('label' => 'Bank Name', 'class' => 'required'));
echo $this->Form->input('ach_is_checking_account', array('type' => 'checkbox', 'label' => 'Is this a checking account?')); ?>

<script type="text/javascript">
    function changePaymentInputs() {
		if ($('#TransactionMode').val() == 'PAYSIMPLE.CHECK') {
			$('#TransactionCardNumber').removeClass('required');
			$('#creditCardInfo input').each(function(){	$(this).val(''); });
			$('#creditCardInfo').hide('fast');
			$('#echeckInfo').show('slow');
		} else {
			$('#TransactionCardNumber').addClass('required');
			$('#echeckInfo input').each(function(){ $(this).val(''); });
			$('#echeckInfo').hide('fast');
			$('#creditCardInfo').show('slow');
		}
    }
    $('#TransactionMode').change(function(e){
		changePaymentInputs();
    });
    changePaymentInputs();
</script>