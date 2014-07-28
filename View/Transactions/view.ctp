<div class="transaction view row-fluid">
    <div class="well well-large clearfix">
    	<div class="span3 col-md-3 pull-left">
	        <h3><?php echo __('%s<small> Customer %s</small>', $this->Html->link($transaction['Customer']['full_name'], array('plugin' => 'users', 'controller' => 'users', 'action' => 'view', $transaction['Customer']['id'])), $this->Html->link(__('<i class="icon-info-sign">info</i>'), array('admin' => true, 'plugin' => 'contacts', 'controller' => 'contacts', 'action' => 'view', $transaction['Customer']['Contact']['id']), array('escape' => false))); ?></h3>
	        <div class="alert alert-success pull-left">
	        	<p><span class="label label-success">Order Total</span> <?php echo __('$%s', ZuhaInflector::pricify($transaction['Transaction']['total'])); ?> <span class="label label-success">Status</span> <?php echo $transaction['Transaction']['status']; ?></p>
	        	<?php echo __('<div class="row-fluid row"><h1 class="pull-left span3 col-md-3">%s </h1><h5 class="pull-right span9 col-md-9">Number of times %s has ordered from you.</h5></div>', $orderCount, $transaction['Customer']['full_name']); ?>
	        </div>
	    </div>
    	<div class="span4 col-md-4 pull-right">
    		<?php if (!empty($billingAddress)) : ?>
	        <h3><?php echo __('%s %s <small>Billing Address</small>', $billingAddress['TransactionAddress']['first_name'], $billingAddress['TransactionAddress']['last_name']); ?></h3>
	        <table class="table table-hover">
	        	<tbody>
	        		<tr>
	        			<td class="span2 col-md-2">Street</td>
	        			<td><?php echo $billingAddress['TransactionAddress']['street_address_1']; ?></td>
	        		</tr>
	        		<tr>
	        			<td class="span2 col-md-2">Street</td>
	        			<td><?php echo $billingAddress['TransactionAddress']['street_address_2']; ?></td>
	        		</tr>
	        		<tr>
	        			<td class="span2 col-md-2">City</td>
	        			<td><?php echo $billingAddress['TransactionAddress']['city']; ?></td>
	        		</tr>
	        		<tr>
	        			<td class="span2 col-md-2">State</td>
	        			<td><?php echo $billingAddress['TransactionAddress']['state']; ?></td>
	        		</tr>
	        		<tr>
	        			<td class="span2 col-md-2">Zip</td>
	        			<td><?php echo $billingAddress['TransactionAddress']['zip']; ?></td>
	        		</tr>
	        		<tr>
	        			<td class="span2 col-md-2">Country</td>
	        			<td><?php echo $billingAddress['TransactionAddress']['country']; ?></td>
	        		</tr>
	        	</tbody>
	        </table>
	        <?php endif; ?>
	    </div>
    	<div class="span4 col-md-4 pull-right">
    		<?php if (!empty($shippingAddress)) : ?>
	        <h3><?php echo __('%s %s <small>Shipping Address</small>', $shippingAddress['TransactionAddress']['first_name'], $shippingAddress['TransactionAddress']['last_name']); ?></h3>
	        <table class="table table-hover">
	        	<tbody>
	        		<tr>
	        			<td class="span2 col-md-2">Street</td>
	        			<td><?php echo $shippingAddress['TransactionAddress']['street_address_1']; ?></td>
	        		</tr>
	        		<tr>
	        			<td class="span2 col-md-2">Street</td>
	        			<td><?php echo $shippingAddress['TransactionAddress']['street_address_2']; ?></td>
	        		</tr>
	        		<tr>
	        			<td class="span2 col-md-2">City</td>
	        			<td><?php echo $shippingAddress['TransactionAddress']['city']; ?></td>
	        		</tr>
	        		<tr>
	        			<td class="span2 col-md-2">State</td>
	        			<td><?php echo $shippingAddress['TransactionAddress']['state']; ?></td>
	        		</tr>
	        		<tr>
	        			<td class="span2 col-md-2">Zip</td>
	        			<td><?php echo $shippingAddress['TransactionAddress']['zip']; ?></td>
	        		</tr>
	        		<tr>
	        			<td class="span2 col-md-2">Country</td>
	        			<td><?php echo $shippingAddress['TransactionAddress']['country']; ?></td>
	        		</tr>
	        	</tbody>
	        </table>
	        <?php endif; ?>
	    </div>
    </div>
    <div class="content">
        <?php
        echo $this->Form->create('Transaction', array('url' => array('action' => 'edit', $transaction['Transaction']['id'])));
        echo $this->Form->input('Transaction.id', array('value' => $transaction['Transaction']['id'], 'type' => 'hidden'));
		?>
        <table cellpadding="0" cellspacing="0">
            <thead>
            <tr>
    			<th><?php echo $this->Paginator->sort('TransactionItem.name', 'Item');?></th>
        		<th><?php echo $this->Paginator->sort('TransactionItem.price', 'Price');?></th>
        		<th><?php echo $this->Paginator->sort('TransactionItem.comments', 'Comments');?></th>
        		<th><?php echo $this->Paginator->sort('TransactionItem.quantity', 'Qty');?></th>
    			<th><?php echo $this->Paginator->sort('TransactionItem.tracking_no', 'Tracking #');?></th>
        		<th><?php echo $this->Paginator->sort('TransactionItem.status', 'Status');?></th>
        		<?php if (CakePlugin::loaded('Tasks')) : ?>
        		<th><?php echo $this->Paginator->sort('Assignee.full_name', 'Assigned To');?></th>
        		<?php endif; ?>
    	    </tr>
            </thead>
            <tbody>
        	<?php
        	$i = 0;
        	foreach ($transaction['TransactionItem'] as $item) : ?>
        	    <tr>
            		<td>
            			<?php echo $this->Html->link($item['name'], array('plugin' => Inflector::tableize(ZuhaInflector::pluginize($item['model'])), 'controller' => Inflector::tableize($item['model']), 'action' => 'view', $item['foreign_key'])); ?>
            			<?php echo !empty($item['_associated']['seller']) ? __('(Seller : %s)', $this->Html->link($item['_associated']['seller']['username'], array('admin' => false, 'plugin' => 'users', 'controller' => 'users', 'action' => 'view', $item['_associated']['seller']['id']))) : null; ?>
            		</td>
                	<td><?php echo __('$%s', ZuhaInflector::pricify($item['price'])); ?></td>
                	<td><?php echo $item['comments']; ?></td>
                	<td><?php echo $item['quantity']; ?></td>
                	<td><?php echo $this->Form->input('TransactionItem.'.$i.'.tracking_no', array('value' => $item['tracking_no'], 'label' => false, 'class' => 'span')); ?></td>
                    <td><?php echo $this->Form->input('TransactionItem.'.$i.'.status', array('value' => $item['status'], 'label' => false, 'class' => 'span')); ?></td>
                    <?php if (CakePlugin::loaded('Tasks')) : ?>
                    <td><?php echo $this->Form->input('TransactionItem.'.$i.'.assignee_id', array('value' => $item['assignee_id'], 'empty' => ' -- Select --', 'label' => false, 'class' => 'span')); ?></td>
                    <?php endif; ?>
        	    </tr>
            <?php
                echo $this->Form->input('TransactionItem.'.$i.'.id', array('value' => $item['id'], 'type' => 'hidden'));
                $i++;
            endforeach; ?>
            </tbody>
    	</table>
        <?php echo $this->Form->end(__('Update Items')); ?>
        <?php echo $this->Element('paging'); ?>
    </div>
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
    		 $this->Html->link(__('Regions'), array('action' => 'index')),
			 $this->Html->link(__('Add'), array('action' => 'add')),
			 )
		)
	)));
