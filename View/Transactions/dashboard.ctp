<?php echo $this->Html->script('http://code.highcharts.com/highcharts.js', array('inline' => false)); ?>
<?php echo $this->Html->script('http://code.highcharts.com/modules/exporting.js', array('inline' => false)); ?>

<div class="products row">
    <div class="span8 col-md-8">
        <ul class="nav nav-tabs" id="myTab">
            <li><a href="#today" data-toggle="tab">Today</a></li>
            <li><a href="#thisWeek" data-toggle="tab">This Week</a></li>
            <li><a href="#thisMonth" data-toggle="tab">This Month</a></li>
            <li><a href="#thisYear" data-toggle="tab">This Year</a></li>
            <li><a href="#allTime" data-toggle="tab">All Time</a></li>
        </ul>
        <div id="myTabContent" class="tab-content">
            <div class="tab-pane fade" id="today">
                <div class="row-fluid">
                    <div class="clearfix">
                        <h3 class="col-md-6 pull-left"><?php echo $statsSalesToday['count']; ?> Orders Today</h3>
                        <h3 class="col-md-6 pull-left"><?php echo ZuhaInflector::pricify($statsSalesToday['value'], array('currency' => 'USD')); ?> Total Value </h3>
                    </div>

                    <?php
                    $hour = array_fill(0, 24, 0);
                    foreach ($statsSalesToday as $order) {
                        if ($order['Transaction']) {
                            $hourKey = (int) date('H', strtotime($order['Transaction']['created']));
                            $hour[$hourKey]++;
                        }
                    } ?>
                    <script type="text/javascript">
                    $(function () {
                        $('#myTab a:first').tab('show');
                    });
                    var chart;
                    $(document).ready(function() {
                        chart = new Highcharts.Chart({
                            chart: {
                                renderTo: 'ordersToday',
                                type: 'spline'
                            },
                            credits: false,
                            title: {
                                text: false
                            },
                            subtitle: {
                                text: false
                            },
                            xAxis: {
                                type: 'datetime',
                                dateTimeLabelFormats: { // don't display the dummy year
                                    month: '%e. %b',
                                    year: '%b'
                                }
                            },
                            yAxis: {
                                title: {
                                    text: false
                                },
                                min: 0
                            },
                            tooltip: {
                                formatter: function() {
                                        return '<b>'+ this.series.name +'</b><br/>'+
                                        Highcharts.dateFormat('%e. %b', this.x) +': '+ this.y +' m';
                                }
                            },
        
                            series: [{
                                name: 'Orders',
                                // Define the data points. All series have a dummy year
                                // of 1970/71 in order to be compared on the same x axis. Note
                                // that in JavaScript, months start at 0 for January, 1 for February etc.
                                data: [
                                <?php
                                $i = 0;
                                while ($i < 24) { ?>
                                    [<?php echo $i ?>,   <?php echo $hour[$i] ? $hour[$i] : 0; ?>],
                                <?php ++$i; } ?>
                                ]
                            }]
                        });
                    });
                    </script>
                    <div id="ordersToday" style="min-width: 300px; height: 300px;"></div>
                </div>
            </div>
            <div class="tab-pane fade" id="thisWeek">
                <h1><?php echo $statsSalesThisWeek['count']; ?></h1><b>Orders This Week</b>
                <h1><?php echo ZuhaInflector::pricify($statsSalesThisWeek['value'], array('currency' => 'USD')); ?></h1><b>Total Value</b>
            </div>
            <div class="tab-pane fade" id="thisMonth">
                <h1><?php echo $statsSalesThisMonth['count']; ?></h1><b>Orders This Month</b>
                <h1><?php echo ZuhaInflector::pricify($statsSalesThisMonth['value'], array('currency' => 'USD')); ?></h1><b>Total Value</b>
            </div>
            <div class="tab-pane fade" id="thisYear">
                <h1><?php echo $statsSalesThisYear['count']; ?></h1><b>Orders This Year</b>
                <h1><?php echo ZuhaInflector::pricify($statsSalesThisYear['value'], array('currency' => 'USD')); ?></h1><b>Total Value</b>
            </div>
            <div class="tab-pane fade" id="allTime">
                <h1><?php echo $statsSalesAllTime['count']; ?></h1><b>Orders All Time</b>
                <h1><?php echo ZuhaInflector::pricify($statsSalesAllTime['value'], array('currency' => 'USD')); ?></h1><b>Total Value</b>
            </div>
        </div>
    </div>
    
    

    <div class="tagProducts span3 col-md-4 last">
        <ul class="list-group">
            <?php
            $counts['open'] = __('<span class="badge alert-warning">%s</span>', $counts['open']);
            $counts['shipped'] = __('<span class="badge alert-info">%s</span>', $counts['shipped']);
            $counts['paid'] = __('<span class="badge alert-success">%s</span>', $counts['paid']);
            $counts['failed'] = __('<span class="badge alert-danger">%s</span>', $counts['failed']);
            foreach (array_reverse($transactionStatuses) as $key => $status) {
				if (is_numeric($counts[strtolower($status)])) {
					$counts[strtolower($status)] = __('<span class="badge">%s</span>', $counts[strtolower($status)]);
				}
                echo $this->Html->link(__('%s %s Transactions', $counts[strtolower($status)], $status), array(
					'admin' => true, 'plugin' => 'transactions', 'controller' => 'transactions', 'action' => 'index',
					'filter' => 'status:' . $key, 'sort' => 'Transaction.created', 'direction' => 'desc'
					), array('escape' => false, 'class' => 'list-group-item'));
            }
            echo $this->Html->link(__('%s In Cart Transactions', $counts['open']), array(
				'admin' => true, 'plugin' => 'transactions', 'controller' => 'transactions', 'action' => 'index',
				'filter' => 'status:open', 'sort' => 'Transaction.created', 'direction' => 'desc'
				), array('escape' => false, 'class' => 'list-group-item'));
            echo $this->Html->link(__('My Assigned Transactions'), array(
				'admin' => true, 'plugin' => 'transactions', 'controller' => 'transaction_items', 'action' => 'index',
				'filter' => 'assignee_id:'.$this->Session->read('Auth.User.id')
				), array('class' => 'list-group-item'));
			?>
        </ul>
    </div>

</div>

<hr />

<div class="products clear first row">
    <div class="span3 col-sm-3">
        <ul class="nav nav-list">
        	<li class="dropdown-header">Store</li>
            <li>
            	<div class="btn-group">
            		<?php echo $this->Html->link('Create a Product', array('plugin' => 'products', 'controller' => 'products', 'action' => 'add'), array('class' => 'btn btn-primary btn-small', 'escape' => false)); ?>
            		<a class="btn btn-primary btn-small dropdown-toggle" data-toggle="dropdown">
					    <span class="caret"></span>
					</a>
            		<ul class="dropdown-menu">
            			<li><?php echo $this->Html->link('Create an Membership Product', array('plugin' => 'products', 'controller' => 'products', 'action' => 'add', 'membership')); ?></li>
            			<li><?php echo $this->Html->link('Create an ARB Product', array('plugin' => 'products', 'controller' => 'products', 'action' => 'add', 'arb')); ?></li>
            			<?php echo CakePlugin::loaded('Auctions') ? __('<li>%s</li>', $this->Html->link('Create an Auction', array('plugin' => 'auctions', 'controller' => 'auctions', 'action' => 'add'))) : null; ?>
            			<!-- <li><?php echo $this->Html->link('Create a Virtual Product', array('plugin' => 'products', 'controller' => 'products', 'action' => 'add', 'virtual')); ?></li> -->
            		</ul>
            	</div>
            </li>
            <li><?php echo $this->Html->link('All Products', array('plugin' => 'products', 'controller' => 'products', 'action' => 'index')); ?></li>
            <li><?php echo $this->Html->link('Out Of Stock Products', array('plugin' => 'products', 'controller' => 'products', 'action' => 'index', 'filter' => 'stock:0')); ?></li>
        </ul>
    </div>
    <div class="span3 col-sm-3">
        
        <ul class="nav nav-list">
        	<li class="dropdown-header">Brands</li>
            <li><?php echo $this->Html->link('List All Brands', array('plugin' => 'products', 'controller' => 'product_brands', 'action' => 'index')); ?></li>
            <li><?php echo $this->Html->link('Add a Brand', array('plugin' => 'products', 'controller' => 'product_brands', 'action' => 'add')); ?></li>
        </ul>
    </div>
    <div class="span3 col-sm-3">
        <ul class="nav nav-list">
        	<li class="dropdown-header">Attributes</li>
            <li><?php echo $this->Html->link('Product Variations', array('plugin' => 'products', 'controller' => 'products', 'action' => 'categories')); ?></li>
        </ul>
        <ul class="nav nav-list">
        	<li class="dropdown-header">Categories</li>
            <li><?php echo $this->Html->link('Product Categories', array('plugin' => 'products', 'controller' => 'products', 'action' => 'categories')); ?></li>
        </ul>
    </div>
    <div class="span2 col-sm-2">
        <ul class="nav nav-list">
        	<li class="dropdown-header">Settings</li>
            <li><?php echo $this->Html->link('List All', array('admin' => true, 'plugin' => null, 'controller' => 'settings', 'action' => 'index', 'start' => 'type:Transactions')); ?></li>
            <li><?php echo $this->Html->link('Emails', array('admin' => true, 'plugin' => 'transactions', 'controller' => 'transactions', 'action' => 'settings')); ?></li>
            <li><?php echo $this->Html->link('Tax Rates', array('admin' => true, 'plugin' => 'transactions', 'controller' => 'transaction_taxes', 'action' => 'index')); ?></li>
            <li><?php echo $this->Html->link('Status Types', array('admin' => true, 'plugin' => null, 'controller' => 'enumerations', 'action' => 'index', 'filter' => 'type:TRANSACTIONS_ITEM_STATUS')); ?></li>
            <li><?php echo $this->Html->link('Item Status Types', array('admin' => true, 'plugin' => null, 'controller' => 'enumerations', 'action' => 'index', 'start' => 'type:TRANSACTIONS_STATUS')); ?></li>
            <li><?php echo $this->Html->link('Coupon Codes', array('admin' => true, 'controller' => 'transaction_coupons', 'action' => 'index')); ?></li>
        </ul>
    </div>
</div>

<?php
// set contextual search options
$this->set('forms_search', array(
    'url' => '/products/products/index/', 
	'inputs' => array(
		array(
			'name' => 'contains:name', 
			'options' => array(
				'label' => '', 
				'placeholder' => 'Product Search',
				'value' => !empty($this->request->params['named']['contains']) ? substr($this->request->params['named']['contains'], strpos($this->request->params['named']['contains'], ':') + 1) : null,
				)
			),
		)
	));
	
// set the contextual breadcrumb items
$this->set('context_crumbs', array('crumbs' => array(
	$this->Html->link(__('Admin Dashboard'), '/admin'),
	$page_title_for_layout,
)));

// set the contextual menu items
$this->set('context_menu', array('menus' => array(
    array(
		'heading' => 'Products',
		'items' => array(
			$this->Html->link(__('Dashboard'), array('admin' => true, 'controller' => 'transactions', 'action' => 'dashboard'), array('class' => 'active')),
			)
		),
        array(
            'heading' => 'Products',
            'items' => array(
                $this->Html->link(__('Products'), array('plugin' => 'products', 'controller' => 'products', 'action' => 'index')),
                $this->Html->link(__('Transactions'), array('plugin' => 'transactions', 'controller' => 'transactions', 'action' => 'index')),
            )
        ),
        ))); ?>