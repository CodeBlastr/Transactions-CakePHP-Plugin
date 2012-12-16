<div class="transactionTaxes children index">
    <?php echo $this->Form->create('TransactionTax', array('url' => array('action' => 'edit', $parent['TransactionTax']['id'])));?>
    <table cellpadding="0" cellspacing="0">
        <thead>
	    <tr>
			<th><?php echo $this->Paginator->sort('name', Inflector::pluralize($transactionTaxes[0]['TransactionTax']['label']));?></th>
    		<th><?php echo $this->Paginator->sort('rate');?></th>
			<th><?php echo $this->Paginator->sort('label');?></th>
            <?php if(!empty($parent['TransactionTax']['rate'])) { ?>
        	<th><?php echo $this->Paginator->sort('type');?></th>
            <?php } ?>
			<th class="actions"><?php echo __('Actions');?></th>
	    </tr>
        </thead>
        <tbody>
    	<?php
    	$i = 0;
    	foreach ($transactionTaxes as $tax) { ?>
    	    <tr>
        		<td><?php echo $this->Form->input($i.'.TransactionTax.name', array('value' => $tax['TransactionTax']['name'], 'label' => false)); ?></td>
            	<td><?php echo $this->Form->input($i.'.TransactionTax.rate', array('value' => $tax['TransactionTax']['rate'], 'label' => false, 'class' => 'span1', 'after' => __(' &#37;'))); ?></td>
                <td><?php echo $this->Form->input($i.'.TransactionTax.label', array('value' => $tax['TransactionTax']['label'], 'label' => false, 'class' => 'span2')); ?></td>
                <?php if(!empty($parent['TransactionTax']['rate'])) { ?>
            	<td><?php echo $this->Form->input($i.'.TransactionTax.type', array('value' => $tax['TransactionTax']['type'], 'type' => 'select', 'options' => $types, 'empty' => '-- Select --', 'label' => false)); ?></td>
                <?php } ?>
                <td class="actions">
        			<?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $tax['TransactionTax']['id']), array('class' => 'btn btn-danger btn-mini'), __('Are you sure you want to delete %s?', $tax['TransactionTax']['name'])); ?>
        		</td>
    	    </tr>
        <?php
            echo $this->Form->input($i.'.TransactionTax.id', array('value' => $tax['TransactionTax']['id'], 'type' => 'hidden'));
            $i++;
        } ?>
        </tbody>
	</table>
    <?php echo $this->Form->end(__('Save Regions'));?>
<?php echo $this->Element('paging'); ?>
</div>

<?php
// set the contextual menu items
$this->set('context_menu', array('menus' => array(
    array(
		'heading' => 'Taxes',
		'items' => array(
    		 $this->Html->link(__('Regions'), array('action' => 'index')),
			 $this->Html->link(__('Add'), array('action' => 'add')),
			 )
		),
	)));?>