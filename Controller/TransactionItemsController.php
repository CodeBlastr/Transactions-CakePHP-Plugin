<?php
App::uses('TransactionsAppController', 'Transactions.Controller');
/**
 * TransactionItems Controller
 *
 * @property TransactionItem $TransactionItem
 */
class AppTransactionItemsController extends TransactionsAppController {

	public	$name = 'TransactionItems';
	
	public	$uses = array('Transactions.TransactionItem');

	/**
	 *
	 */
	public function beforeFilter() {
		parent::beforeFilter();
	}


/**
 * index method
 *
 * @return void
 */
	public function index() {
		//$this->TransactionItem->recursive = 0;
		$this->paginate['fields'] = array('TransactionItem.id', 'TransactionItem.name', 'TransactionItem.price', 'TransactionItem.created');
		$this->set('transactionItems', $this->paginate());
		$this->set('page_title_for_layout', 'Assigned Transaction Items');
	}


	/**
	 *
	 * @param string $id
	 * @throws NotFoundException
	 */
	public function view($id = null) {
		$this->TransactionItem->id = $id;
		if (!$this->TransactionItem->exists()) {
			throw new NotFoundException(__d('transactions', 'Invalid transaction item'));
		}
		$this->set('transactionItem', $transactionItem = $this->TransactionItem->read(null, $id));
		$this->redirect(array('controller' => 'transactions', 'action' => 'view', $transactionItem['TransactionItem']['transaction_id']));
	}


/**
 *
 * @throws Exception
 * @throws NotFoundException
 */
	public function add() {
		if ($this->request->is('post')) {
			try {
				if ($this->TransactionItem->addItemToCart($this->request->data)) {
					$this->Session->setFlash(__d('transactions', 'Added to cart'), 'flash_success');
					$this->redirect(array('controller'=>'transactions', 'action'=>'cart'));
				} else {
				  $this->Session->setFlash(__d('transactions', 'Error adding to cart. Please, try again.'), 'flash_warning');
				  $this->redirect($this->referer());
				}
			} catch (Exception $e) {
				$this->Session->setFlash($e->getMessage());
				$this->redirect($this->referer());
				// if you change this back, leave a comment for why we're throwing an exception here instead of redirecting
				// throw new Exception(__d('transactions', $e->getMessage()));
			}
		} else {
			$this->Session->setFlash(__('Invalid transaction request'));
			$this->redirect($this->referer());
			// if you change this back, leave a comment for why we're throwing an exception here instead of redirecting
		    // throw new Exception(__('Invalid transaction request'));
		}
	}


/**
 *
 * @param string $id
 * @throws NotFoundException
 */
	public function edit($id = null) {
		$this->TransactionItem->id = $id;
		if (!$this->TransactionItem->exists()) {
			throw new NotFoundException(__d('transactions', 'Invalid transaction item'));
		}
		if ($this->request->is('post') || $this->request->is('put')) {
			if ($this->TransactionItem->save($this->request->data)) {
				$this->Session->setFlash(__d('transactions', 'The transaction item has been saved'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__d('transactions', 'The transaction item could not be saved. Please, try again.'));
			}
		} else {
			$this->request->data = $this->TransactionItem->read(null, $id);
		}
		$products = $this->TransactionItem->Product->find('list');
		$transactionPayments = $this->TransactionItem->TransactionPayment->find('list');
		$transactionShipments = $this->TransactionItem->TransactionShipment->find('list');
		$transactions = $this->TransactionItem->Transaction->find('list');
		$customers = $this->TransactionItem->Customer->find('list');
		$contacts = $this->TransactionItem->Contact->find('list');
		$assignees = $this->TransactionItem->Assignee->find('list');

		$this->set(compact('products', 'transactionPayments', 'transactionShipments', 'transactions', 'customers', 'contacts', 'assignees'));
	}


/**
 *
 * @param string $id
 * @throws MethodNotAllowedException
 * @throws NotFoundException
 */
	public function delete($id = null) {
        if ( $this->request->is('post') || $this->request->is('get') ) {
    		$this->TransactionItem->id = $id;
    		if (!$this->TransactionItem->exists()) {
    			throw new NotFoundException(__d('transactions', 'Invalid transaction item'));
    		}
    		if ($this->TransactionItem->delete()) {
    			$this->Session->setFlash(__d('transactions', 'Removed'));
    			$this->redirect(array('controller' => 'transactions', 'action' => 'cart'));
    		}
        }
		$this->Session->setFlash(__d('transactions', 'Item could not be removed'));
		$this->redirect(array('controller' => 'transactions', 'action' => 'cart'));
	}
}

if (!isset($refuseInit)) {
	class TransactionItemsController extends AppTransactionItemsController {
	}
}