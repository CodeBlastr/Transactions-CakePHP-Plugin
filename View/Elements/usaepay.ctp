	<?php echo $this->Form->input('mode', array('label' => 'Payment Method', 'options' => $options['paymentOptions'], 'default' => $options['paymentMode'])); ?>
	<?php echo $this->Form->input('card_number', array('label' => 'Card Number', 'class' => 'required usaepayCc creditcard', 'maxLength' => 16, 'pattern' => '...', 'inputmode' => 'numeric', 'autocomplete' => 'cc-number')); // credit card inputs ?>
	<div class="row">
		<div class="col-sm-4">
			<?php echo $this->Form->input('card_exp_month', array('label' => 'Expiration Month', 'type' => 'select', 'options' => array_combine(range(1, 12, 1), range(1, 12, 1)),'class' => 'required usaepayCc')); ?>
		</div>
		<div class="col-sm-4">
			<?php echo $this->Form->input('card_exp_year', array('class' => 'required usaepayCc', 'label' => 'Exp Year', 'type' => 'select', 'options' => array_combine(range(date('Y'), date('Y', strtotime('+ 10 years')), 1), range(date('Y'), date('Y', strtotime('+ 10 years')), 1)), 'dateFormat' => 'Y')); ?>
		</div>
		<div class="col-sm-4">
			<?php echo $this->Form->input('card_sec', array('class' => 'required usaepayCc', 'label' => 'CCV Code ' . $this->Html->link('?', '#ccvHelp', array('class' => 'helpBox paysimpleCc', 'title' => 'You can find this 3 or 4 digit code on the back of your card, typically in the signature area.')), 'maxLength' => 4)); ?>
		</div>
	</div>