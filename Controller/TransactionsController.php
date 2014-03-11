<?php
App::uses('TransactionsAppController', 'Transactions.Controller');

/**
 * Transactions Controller
 *
 * @property Transaction $Transaction
 */
class AppTransactionsController extends TransactionsAppController {

/**
 * Name
 *
 * @var string
 */
	public $name = 'Transactions';

/**
 * Uses
 *
 * @var array
 */
	public $uses = array('Transactions.Transaction');

/**
 * Components
 *
 * @var array
 */
	//public $components = array('Ssl', 'Transactions.Payments');
	public $components = array('Ssl');


	public $allowedActions = array(
		'add',
		'cart',
		'merge',
		'success',
		'my'
	);

/**
 * Dashboard method
 *
 * @return void
 */
 	public function dashboard() {
 		$this->redirect('admin');
		$this->set('counts', $counts = array_count_values(array_filter(Set::extract('/Transaction/status', $this->Transaction->find('all')))));
		$this->set('statsSalesToday', $this->Transaction->salesStats('today'));
		$this->set('statsSalesThisWeek', $this->Transaction->salesStats('thisWeek'));
		$this->set('statsSalesThisMonth', $this->Transaction->salesStats('thisMonth'));
		$this->set('statsSalesThisYear', $this->Transaction->salesStats('thisYear'));
		$this->set('statsSalesAllTime', $this->Transaction->salesStats('allTime'));
		$this->set('transactionStatuses', $this->Transaction->statuses());
		$this->set('itemStatuses', $this->Transaction->TransactionItem->statuses());
		$this->set('title_for_layout', __('Ecommerce Dashboard'));
		$this->set('page_title_for_layout', __('Ecommerce Dashboard'));
 	}

/**
 * Index method
 *
 * @return void
 */
	public function index() {
        $this->Transaction->contain(array('TransactionAddress', 'TransactionItem', 'Customer')); // contained items for the csv output
		$this->paginate['order'] = array('Transaction.modified' => 'DESC');
		$this->set('transactions', $this->paginate());
		$type = !empty($this->request->named['filter']) ? str_replace('status:', '', $this->request->named['filter']) : 'All';
		$this->set('title_for_layout', __('%s Transactions', Inflector::humanize($type)));
		$this->set('page_title_for_layout', __('%s Transactions', Inflector::humanize($type)));
        $this->set('displayName', 'created');
	}

/**
 * View method
 *
 * @param string $id
 * @throws NotFoundException
 * @todo Add LoggableBehavior and track who the referrer was from the stats in the session $this->triggerLog() in the model, if done right.
 */
	public function view($id = null) {
		$this->Transaction->id = $id;
		if (!$this->Transaction->exists()) {
			throw new NotFoundException(__d('transactions', 'Invalid transaction'));
		}
        $this->paginate['conditions']['TransactionItem.transaction_id'] = $id;
        $transactionItems = Set::extract('{n}.TransactionItem', $this->paginate('TransactionItem'));
        $this->Transaction->contain(array('Customer' => array('Contact'), 'Assignee'));
        $this->set('transaction', $transaction = Set::merge($this->Transaction->read(null, $id), array('TransactionItem' => $transactionItems)));
        $this->set('statuses', $this->Transaction->TransactionItem->statuses());
		$this->set('assignees', $assignees = $this->Transaction->Assignee->find('list'));
		$this->set('shippingAddress', $this->Transaction->TransactionAddress->find('first', array('conditions' => array('TransactionAddress.transaction_id' => $id, 'TransactionAddress.type' => 'shipping'))));
		$this->set('billingAddress', $this->Transaction->TransactionAddress->find('first', array('conditions' => array('TransactionAddress.transaction_id' => $id, 'TransactionAddress.type' => 'billing'))));
		$this->set('orderCount', $this->Transaction->find('count', array('conditions' => array('Transaction.customer_id' => $transaction['Transaction']['customer_id']))));
        $this->set('page_title_for_layout', 'Transaction');
	}

/**
 * Add method
 *
 * @return void
 */
	public function add() {
		if ($this->request->is('post')) {
			$this->Transaction->create();
			if ($this->Transaction->save($this->request->data)) {
				$this->Session->setFlash(__d('transactions', 'The transaction has been saved'), 'flash_success');
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__d('transactions', 'The transaction could not be saved. Please, try again.'), 'flash_warning');
			}
		}
		// $transactionCoupons = $this->Transaction->TransactionCoupon->find('list');
		$customers = $this->Transaction->Customer->find('list');
		$contacts = $this->Transaction->Contact->find('list');
		$assignees = $this->Transaction->Assignee->find('list');
		$this->set(compact('customers', 'contacts', 'assignees'));
	}


/**
 * Edit method
 *
 * @param string $id
 * @throws NotFoundException
 */
	public function edit($id = null) {
		$this->Transaction->id = $id;
		if (!$this->Transaction->exists()) {
			throw new NotFoundException(__d('transactions', 'Invalid transaction'));
		}
		if ( ($this->request->is('post') || $this->request->is('put')) && !empty($this->request->data)) {
			if ($this->Transaction->saveAll($this->request->data)) {
				$this->Session->setFlash(__d('transactions', 'The transaction has been saved'), 'flash_success');
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__d('transactions', 'The transaction could not be saved. Please, try again.'), 'flash_warning');
			}
		} else {
			$this->request->data = $this->Transaction->read(null, $id);
		}
		$transactionAddresses = $this->Transaction->TransactionAddress->find('list');
		$transactionCoupons = $this->Transaction->TransactionCoupon->find('list');
		$customers = $this->Transaction->Customer->find('list');
		$contacts = $this->Transaction->Contact->find('list');
		$assignees = $this->Transaction->Assignee->find('list');
		$this->set(compact('transactionAddresses', 'transactionCoupons', 'customers', 'contacts', 'assignees'));
	}

/**
 * Delete method
 *
 * @param string $id
 * @throws MethodNotAllowedException
 * @throws NotFoundException
 */
	public function delete($id = null) {
		if (!$this->request->is('post')) {
			throw new MethodNotAllowedException();
		}
		$this->Transaction->id = $id;
		if (!$this->Transaction->exists()) {
			throw new NotFoundException(__d('transactions', 'Invalid transaction'));
		}
		if ($this->Transaction->delete()) {
			$this->Session->setFlash(__d('transactions', 'Transaction deleted'), 'flash_success');
			return $this->redirect(array('action' => 'index'));
		}
		$this->Session->setFlash(__d('transactions', 'Transaction was not deleted'), 'flash_danger');
		return $this->redirect(array('action' => 'index'));
	}


/**
 * Cart method
 *
 * @throws NotFoundException
 * @todo Convert to Transaction->buy()
 */
    public function cart() {
        if (
				($this->request->is('post') || $this->request->is('put'))
				&& !empty($this->request->data)
			) {
        	try {
            	// remove these three lines soon (10-1-2013 RK)
				//$data = $this->Transaction->beforePayment($this->request->data);
                //$data = $this->Payments->pay($data);
                //$this->Transaction->afterSuccessfulPayment($this->Auth->loggedIn(), $data);
                $this->Transaction->buy($this->request->data);
				return $this->redirect($this->_redirect());
    		} catch (Exception $e) {
    		    $this->Session->setFlash($e->getMessage(), 'flash_warning');
    		}
	    }

	  	// gather checkout options like shipping, payments, ssl, etc
		$options = $this->Transaction->gatherCheckoutOptions();

	    // ensure that SSL is on if it's supposed to be
		$options['ssl'] !== null ? $this->Ssl->force() : null;

		// If they have two carts, we are going to ask the customer what to do with them
		$userId = $this->Transaction->getCustomersId();
		$numberOfCarts = $this->Transaction->find('count', array('conditions' => array('Transaction.customer_id' => $userId, 'Transaction.status' => 'open')));

		if ($numberOfCarts > 1) {
            return $this->redirect(array('plugin' => 'transactions', 'controller' => 'transactions', 'action' => 'merge'));
		} else {
            // get their cart and process it
            $this->request->data = $this->Transaction->processCart($userId, $this->request->data);
            // save the updated cart
            $this->Transaction->save($this->request->data);
            // show the empty cart view
            empty($this->request->data['TransactionItem']) ? $this->view = 'empty' : null;
            // set the variables to display in the cart
            $options['displayShipping'] = !empty($this->request->data['TransactionItem']) ? count($this->request->data['TransactionItem']) != array_sum(Set::extract('/is_virtual', $this->request->data['TransactionItem'])) : true;
            $this->set(compact('options'));
		}
        $this->set('title_for_layout', __('Checkout'));
        $this->set('page_title_for_layout', __('Checkout <small>Please fill in your billing details.</small>'));
		return array_merge($this->request->data, array('options' => $options)); // for the ajax cart element
	}


/**
 * Success method
 * A simple "thank you" page with some post-order actions.
 * Also used as the return_url for PayPal transactions.
 */
	public function success() {
		if (isset($this->request->query['token']) && isset($this->request->query['PayerID'])) {
			// This user probably coming back from hitting OK at PayPal.
			// Execute the payment
			App::uses('Paypal', 'Transactions.Model/Processor');
			$this->Processor = new Paypal;
			$this->Processor->executePayment($this->request->query['PayerID']);
			// Run the afterPayment callbacks.
			$data = CakeSession::read('Transaction.data');
			if (!empty($data)) {
				$this->Transaction->afterSuccessfulPayment(CakeSession::read('Auth.User.id'), $data);
			}
			$boughtModel = CakeSession::read('Transaction.modelName');
			if (!empty($boughtModel)) {
				App::uses($boughtModel, ZuhaInflector::pluginize($boughtModel).'.Model');
				$Model = new $boughtModel;
				if (method_exists($Model, 'afterSuccessfulPayment') && is_callable('afterSuccessfulPayment')) {
					$Model->afterSuccessfulPayment(CakeSession::read('Auth.User.id'), $data);
				}
			}
		}
		//// Interswitch
		if (isset($this->request->data['Interswitch']['order_number']) && isset($this->request->data['Interswitch']['cart_order_id'])) {
            // This user probably coming back from hitting OK at Interswitch.
            // Verify payment
            App::uses('Interswitch', 'Transactions.Model/Processor');
            $this->Processor = new Interswitch;
            $processorResponse = $this->Processor->executePayment($this->request->data['Interswitch']);
			if ($processorResponse) {
				$this->Session->setFlash($processorResponse);
			}
            // Run the afterPayment callbacks.
            $data = CakeSession::read('Transaction.data');
            if (!empty($data)) {
				$this->Transaction->afterSuccessfulPayment(CakeSession::read('Auth.User.id'), $data);
            }

            $boughtModel = CakeSession::read('Transaction.modelName');
            if (!empty($boughtModel)) {
                App::uses($boughtModel, ZuhaInflector::pluginize($boughtModel).'.Model');
                $Model = new $boughtModel;
                if (method_exists($Model, 'afterSuccessfulPayment') && is_callable('afterSuccessfulPayment')) {
                    $Model->afterSuccessfulPayment(CakeSession::read('Auth.User.id'), $data);
                }
            }
        } //end Interswitch

		$this->set('userId', $this->Session->read('Auth.User.id'));
	}


/**
 * My method
 *
 * Show transaction history
 * @return void
 */
    public function my() {
		if ($this->Session->read('Auth.User.id')) {
			$this->set('title_for_layout', __('Order History | ' . __SYSTEM_SITE_NAME));
			$this->paginate['conditions']['Transaction.customer_id'] = $this->Session->read('Auth.User.id');
			$this->paginate['contain'] = 'TransactionItem';
			//$this->Transaction->recursive = 2;
			$this->set('transactions', $this->paginate());
		} else {
			$this->redirect('/');
		}
    }

/**
 * Merge Carts method
 */
	public function merge() {
	    $transactions = $this->Transaction->find('all',array(
		    'conditions' => array(
			    'customer_id' => $this->Session->read('Auth.User.id'),
			    'status' => array('open', 'failed')
			    ),
		    'contain' => array('TransactionItem'),
		    'order' => array('Transaction.modified' => 'desc')
	    ));

	    $this->set('transactions', $transactions);

	    // they have made a choice.  process it; choices are: '1', 'merge', or '2'
	    if(isset($this->request->params['named']['choice'])) {
		    if(in_array($this->request->params['named']['choice'], array('1', 'merge', '2'))) {
		        switch ($this->request->params['named']['choice']) {
			        case '1':
			            $this->Transaction->delete($transactions[1]['Transaction']['id']);
			            break;
			        case '2':
			            $this->Transaction->delete($transactions[0]['Transaction']['id']);
			            break;
			        case 'merge':
			            $mergedTransaction = $this->Transaction->combineTransactions($transactions);
			            $this->Transaction->saveAll($mergedTransaction);
			            $this->Transaction->delete($transactions[0]['Transaction']['id']);
			            $this->Transaction->delete($transactions[1]['Transaction']['id']);
			            break;
		        }
                $this->redirect(array('plugin'=>'transactions', 'controller'=>'transactions', 'action' => 'cart'));
            }
	    }
	}

/**
 * Settings method
 *
 */
 	public function settings() {
 		// data gets submitted to /settings/add
 		if (defined('__TRANSACTIONS_RECEIPT_EMAIL')) {
 			$email = unserialize(__TRANSACTIONS_RECEIPT_EMAIL);
 			$this->request->data['Setting']['value']['subject'] = stripcslashes($email['subject']);
 			$this->request->data['Setting']['value']['body'] = stripcslashes($email['body']);
 		}
 	}

/**
 * Redirect method
 *
 * @return array $url
 */
    protected function _redirect() {
        if (defined('__TRANSACTIONS_CHECKOUT_REDIRECT')) {
			extract(unserialize(__TRANSACTIONS_CHECKOUT_REDIRECT));
			if(empty($url)) {
				$plugin = strtolower(ZuhaInflector::pluginize($model));
				$controller = Inflector::tableize($model);
				if(!empty($pass)) {
					// get foreign key of TransactionItem using given setings
					$foreign_key = $this->Transaction->TransactionItem->find('first', array('fields' => $pass,
						'conditions' => array(
							'TransactionItem.transaction_id' => $this->Transaction->id,
						)
					));
				} else {
					$foreign_key = NULL;
				}
				$url = array('plugin' => $plugin, 'controller' => $controller, 'action' => $action, !empty($foreign_key['TransactionItem']['foreign_key']) ? $foreign_key['TransactionItem']['foreign_key'] : '');
			}
		} else {
			$url = array('plugin' => 'transactions', 'controller' => 'transactions', 'action' => 'success');
		}
        return $url;
    }

}

if (!isset($refuseInit)) {
    class TransactionsController extends AppTransactionsController {}
}
