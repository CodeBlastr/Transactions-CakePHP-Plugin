<?php
///
//$options = array('Visa' => 'Visa', 'MasterCard' => 'MasterCard');
//echo 'Credit Card Type: <br />';
//echo $this->Form->select('credit_type', $options);
///

echo $this->Form->select('mode', $options['paymentOptions'], array('label' => 'Payment Method', 'default' => $options['paymentMode']));


if (isset($options['paymentOptions']['PAYPAL.ACCOUNT'])) {
	// do nothing.  Maybe change the checkout button to a PayPal image or something.
}


if (isset($options['paymentOptions']['PAYPAL.CC'])) {
	/** @todo **/
	// credit card inputs
	echo $this->Form->input('card_number', array('label' => 'Card Number', 'class' => 'required paypalCc creditcard', 'maxLength' => 16, 'pattern' => '...', 'inputmode' => 'numeric', 'autocomplete' => 'cc-number'));
	echo $this->Form->input('card_exp_month', 
			array('label' => 'Expiration Month', 'type' => 'select',
				'options' => array_combine(range(1, 12, 1), range(1, 12, 1)),
				'after' => $this->Form->input('card_exp_year', array('class' => 'required paypalCc', 'label' => 'Exp Year', 'type' => 'select', 'options' => array_combine(range(date('Y'), date('Y', strtotime('+ 10 years')), 1), range(date('Y'), date('Y', strtotime('+ 10 years')), 1)), 'dateFormat' => 'Y')),
				'class' => 'required paysimpleCc'
				)
			);
	echo $this->Form->input('card_sec', array('class' => 'required paypalCc', 'label' => 'CCV Code ' . $this->Html->link('?', '#ccvHelp', array('class' => 'helpBox paypalCc', 'title' => 'You can find this 3 or 4 digit code on the back of your card, typically in the signature area.')), 'maxLength' => 4));

}
