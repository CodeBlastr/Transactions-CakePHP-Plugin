<?php
/**
*@var $this View
**/
?>


<div class="new-payment-fields">
    <?php echo $this->Form->input('mode', array('label' => 'Payment Method', 'options' => $options['paymentOptions'], 'default' => $options['paymentMode'])); ?>
    <?php echo $this->Form->input('brainTree.creditCard.number', array('label' => 'Card Number', 'class' => 'required creditcard', 'maxLength' => 16, 'pattern' => '...', 'inputmode' => 'numeric', 'autocomplete' => 'cc-number')); // credit card inputs ?>
    <?php echo $this->Form->input('brainTree.creditCard.month', array(
        'label' => 'Expiration Month', 'type' => 'select',
        'options' => array_combine(range(1, 12, 1), range(1, 12, 1)),
        'after' => $this->Form->input('brainTree.creditCard.year', array('class' => 'required', 'label' => 'Exp Year', 'type' => 'select', 'options' => array_combine(range(date('Y'), date('Y', strtotime('+ 10 years')), 1), range(date('Y'), date('Y', strtotime('+ 10 years')), 1)), 'dateFormat' => 'Y')),
        'class' => 'required'
    )); ?>
    <?php echo $this->Form->input('brainTree.creditCard.cvv', array('class' => 'required', 'label' => 'CCV Code ' . $this->Html->link('?', '#ccvHelp', array('class' => 'helpBox paysimpleCc', 'title' => 'You can find this 3 or 4 digit code on the back of your card, typically in the signature area.')), 'maxLength' => 4)); ?>


</div>


