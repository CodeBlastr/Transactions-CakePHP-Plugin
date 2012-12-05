<?php
App::uses('AppModel', 'Model');
class TransactionsAppModel extends AppModel {


	/**
	 * This function is meant to transfer a cart when a guest shopper logs in.
	 * After doing so, it deletes their Transaction._guestId session.
	 *
	 * @param mixed $fromId ID to search for
	 * @param mixed $toId ID to replace with
	 * @param string $field The field to update.
	 * @return boolean
	 * @throws Exception
	 */
	public function reassignGuestCart($fromId, $toId, $field = 'customer_id') {
	  if($fromId && $toId) {
		if ($this->updateAll(array($field => $toId), array($field => $fromId))) {
		  CakeSession::write('Transaction._guestId', $fromId);
		  return true;
		} else {
		  throw new Exception(__d('transactions', 'Guest cart merge failed'));
		}
	  }
	}


}