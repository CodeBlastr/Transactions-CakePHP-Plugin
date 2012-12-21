<div class="transactionTaxes index">
	<table cellpadding="0" cellspacing="0">
    <thead>
	<tr>
			<th><?php echo $this->Paginator->sort('name', __('Customer\'s Region'));?></th>
    		<th><?php echo $this->Paginator->sort('rate');?></th>
			<th><?php echo __('Subregions'); ?></th>
			<th class="actions"><?php echo __('Actions');?></th>
	</tr>
    </thead>
    <tbody>
	<?php
	$i = 0;
	foreach ($transactionTaxes as $tax) { ?>
	<tr>
		<td><?php echo $this->Html->link($tax['TransactionTax']['name'], array('action' => 'edit', $tax['TransactionTax']['id'])); ?>&nbsp;</td>
    	<td><?php echo __('%s&#37;', $tax['TransactionTax']['rate']); ?>&nbsp;</td>
        <td><?php echo !empty($tax['TransactionTax']['children']) ? $this->Html->link(__('%s subregions', $tax['TransactionTax']['children']), array($tax['TransactionTax']['id'])) : __('--'); ?>&nbsp;</td>
		<td class="actions">
			<?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $tax['TransactionTax']['id']), array('class' => 'btn btn-danger btn-mini'), __('Are you sure you want to delete %s?', $tax['TransactionTax']['name'])); ?>
		</td>
	</tr>
<?php } ?>
    </tbody>
	</table>
<?php echo $this->Element('paging'); ?>
</div>

<?php
// set the contextual menu items
$this->set('context_menu', array('menus' => array(
    array(
		'heading' => 'Products',
		'items' => array(
			$this->Html->link(__('Dashboard'), array('plugin' => 'products', 'controller' => 'products', 'action' => 'dashboard')),
			)
		),
    array(
		'heading' => 'Taxes',
		'items' => array(
			 $this->Html->link(__('Add'), array('action' => 'add')),
			 )
		),
	)));?>