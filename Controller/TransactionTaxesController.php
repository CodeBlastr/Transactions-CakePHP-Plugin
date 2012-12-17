<?php
App::uses('TransactionsAppController', 'Transactions.Controller');
/**
 * TransactionTaxes Controller
 *
 * @property TransactionTax $TransactionTax
 */
class TransactionTaxesController extends TransactionsAppController {

/**
 * Name
 * 
 * @var string $name
 */
    public $name = 'TransactionTaxes';
    
/**
 * Uses
 * 
 * @var string $uses
 */
	public $uses = 'Transactions.TransactionTax';
    
/**
 * Allowed actions
 * 
 * @var string $uses
 */
	public $allowedActions = array('calculate');
	
/**
 * index method
 *
 * @return void
 */
	public function index($parentId = null) {
        if (!empty($parentId)) {
            return $this->_childIndex($parentId);
        }
        $this->paginate['conditions']['TransactionTax.parent_id'] = null;
		$this->set('transactionTaxes', $this->paginate());
        $this->set('title_for_layout', __('Taxes and Regions'));
        $this->set('page_title_for_layout', __('Taxes and Regions'));
	}
    
/**
 * Child Index method
 * 
 * @return void
 */
    protected function _childIndex($parentId) {
        $this->paginate['limit'] = 100;
        $this->paginate['conditions']['TransactionTax.parent_id'] = $parentId;
        $this->set('parent', $parent = $this->TransactionTax->findById($parentId));
        $this->set('types', $this->TransactionTax->types($parent)); // move to children only
    	$this->set('transactionTaxes', $transactionTaxes = $this->paginate());
        $this->set('title_for_layout', __('Adjust Taxes for %s', $parent['TransactionTax']['name']));
        $this->set('page_title_for_layout', __('Adjust Taxes for %s', $parent['TransactionTax']['name']));
        $this->view = 'index_children';
    }

/**
 * add method
 *
 * @return void
 */
	public function add() {
		if ($this->request->is('post')) {
			$this->TransactionTax->create();
			if ($this->TransactionTax->save($this->request->data)) {
				$this->Session->setFlash(__('The Tax has been saved'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The Tax could not be saved. Please, try again.'));
			}
		}
    	$this->set('codes', $this->TransactionTax->countries(array('type' => 'filtered')));
        $this->set('title_for_layout', __('Add Region & Tax'));
        $this->set('page_title_for_layout', __('Add Region & Tax'));
	}

/**
 * edit method
 *
 * @param string $id
 * @return void
 */
	public function edit($id = null) {
		$this->TransactionTax->id = $id;
		if (!$this->TransactionTax->exists()) {
			throw new NotFoundException(__('Invalid Tax'));
		}
		if ($this->request->is('post') || $this->request->is('put')) {
			if ($this->TransactionTax->saveAll($this->request->data)) {
				$this->Session->setFlash(__('The Tax has been saved'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The Tax could not be saved. Please, try again.'));
			}
		} else {
            $this->TransactionTax->contain('Parent');
			$this->request->data = $this->TransactionTax->read(null, $id);
            if (!empty($this->request->data['Parent']['id'])) {
                return $this->_childEdit();
            }
		}
        $this->set('codes', $this->TransactionTax->countries());
	}
    
/**
 * Child Edit method
 * 
 */
    protected function _childEdit() {
        $this->view = 'edit_child';
    }

/**
 * delete method
 *
 * @param string $id
 * @return void
 */
	public function delete($id = null) {
		if (!$this->request->is('post')) {
			throw new MethodNotAllowedException();
		}
		$this->TransactionTax->id = $id;
		if (!$this->TransactionTax->exists()) {
			throw new NotFoundException(__('Invalid Tax'));
		}
		if ($this->TransactionTax->delete()) {
			$this->Session->setFlash(__('Tax deleted'));
			$this->redirect(array('action' => 'index'));
		}
		$this->Session->setFlash(__('Tax was not deleted'));
		$this->redirect(array('action' => 'index'));
	}
    
/**
 * Calculate method
 * 
 */
    public function rate() {
        //if ($this->request->is('post') || $this->request->is('put')) {
            $this->set('transactionTax', $this->TransactionTax->applyTax($this->request->data));
        //}
    }
	
}
