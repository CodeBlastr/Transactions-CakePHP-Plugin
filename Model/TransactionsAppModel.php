<?php
class TransactionsAppModel extends AppModel {


	/**
	 * This function is meant to transfer a cart when a guest shopper logs in.
	 * After doing so, it deletes their Transaction._guestId session.
	 *
	 * @param mixed $fromId
	 * @param mixed $toId
	 * @return boolean
	 * @throws Exception
	 */
	public function reassignGuestCart($fromId, $toId) {
	  if($fromId && $toId) {
		if ($this->updateAll(array('customer_id' => $toId), array('customer_id' => $fromId))) {
		  CakeSession::write('Transaction._guestId', $fromId);
		  return true;
		} else {
		  throw new Exception(__d('transactions', 'Guest cart merge failed'));
		}
	  }
	}


}