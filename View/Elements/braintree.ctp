<?php
/**
 * @var $this View
 * @todo These should not be prefixed with brainTree like they are, but I'm not sure what to change them to quite yet.
**/
?>


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