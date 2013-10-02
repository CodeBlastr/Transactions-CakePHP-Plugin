<?php
/**
 * CakePHP PurchaseOrderComponent
 * @author Joel Byrnes <joel@razorit.com>
 */
class PurchaseOrderComponent extends Component {

	public $name = 'PurchaseOrder';

	public $components = array();

	public function pay($data) {
		$data['Transaction']['status'] = 'pending';
		return $data;
	}

}
