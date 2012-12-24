<!-- Authorize -->
<?php

echo $this->Form->input('mode', array('label' => 'Payment Method', 'options' => $options['paymentOptions'], 'default' => $options['paymentMode']));
echo $this->Form->input('card_number', array('label' => 'Card Number', 'class' => 'required'));
echo $this->Form->input('card_exp_month', 
		array('label' => 'Expiration Month', 'type' => 'select',
			'options' => array_combine(range(1, 12, 1), range(1, 12, 1)),
			'after' => $this->Form->input('card_exp_year', array('class' => 'authorizeCc', 'label' => 'Exp Year', 'type' => 'select', 'options' => array_combine(range(date('Y'), date('Y', strtotime('+ 10 years')), 1), range(date('Y'), date('Y', strtotime('+ 10 years')), 1)), 'dateFormat' => 'Y')),
			'class' => 'authorizeCC'
			)
);
echo $this->Form->input('card_sec', array('class' => 'authorizeCc', 'label' => 'CCV Code ' . $this->Html->link('?', '#ccvHelp', array('class' => 'helpBox authorizeCc', 'title' => 'You can find this 3 or 4 digit code on the back of your card, typically in the signature area.')), 'maxLength' => 4)); ?>

<script type="text/javascript">
$(function() {
	// clear the new payment method inputs when they choose a previous payment method
	
	// delect saved payment account when they type in a new account
	$('input.authorizeCc').keypress(function(){
		$(".savedCredit, .savedAch").prop('checked', false);
		changePaymentInputs();
	});
	
    function changePaymentInputs() {
		$(".savedCredit, .savedAch").prop('checked', false);
		$('input.authorizeCc').each(function(){
			$(this).addClass('required');
			$(this).attr('required', 'required');
		});
		$('input.authorizeCheck').each(function(){
			$(this).val('');
			$(this).removeClass('required');
			$(this).removeAttr('required');
		});
		$('.authorizeCheck').parent().parent().hide('fast');
		$('.authorizeCc').parent().parent().show('slow');
    }
    $('#TransactionMode').change(function(e){
		changePaymentInputs();
    });
    changePaymentInputs();
});
</script>
