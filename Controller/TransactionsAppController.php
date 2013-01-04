<?php

class TransactionsAppController extends AppController {

	public function beforeFilter() {
		parent::beforeFilter();
		if ($this->name == 'Transactions' && in_array('Tasks', CakePlugin::loaded())) {
			$this->Transaction->TransactionItem->Behaviors->attach('Tasks.Assignable', array('notifyAssignee' => true, 'notifySubject' => 'New Order Assigned', 'notifyMessage' => 'Please login and view your assigned tasks, or orders to get the order details.'));
		}
	}
    
}