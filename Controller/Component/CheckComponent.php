<?php
/**
 * CakePHP CheckComponent
 * @author Joel Byrnes <joel@razorit.com>
 */
class CheckComponent extends Component {

	public $name = 'Check';

	public $components = array();

	public function Pay($data) {
		$data['Transaction']['status'] = 'pending';
		return $data;
	}

}
