<div class="transactionTax form">
    <?php echo $this->Form->create('TransactionTax');?>
    <fieldset>
    <?php
        echo $this->Form->input('TransactionTax.id');
    	echo $this->Form->input('TransactionTax.name');
		echo $this->Form->input('TransactionTax.rate', array('label' => 'Tax Rate %'));
        echo $this->Form->input('TransactionTax.label');
	?>
	</fieldset>
    <?php echo $this->Form->end(__('Save Region'));?>
</div>

<?php 
// set the contextual menu items
$this->set('context_menu', array('menus' => array(
	array(
		'heading' => 'Taxes',
		'items' => array(
			$this->Html->link(__('List'), array('action' => 'index')),
			)
		),
	))); ?>