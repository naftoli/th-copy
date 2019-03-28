<?php
/*
	// Table of contents
*/

class Cart 
{
	 private $_db;
	 private $_cart_session_data;
   
	 public $items = array();

	 public function __construct()
	 {
		// Start the DB objects
		$this->_db = Zend_Registry::get('db');
		$this->_db->setFetchMode(Zend_Db::FETCH_OBJ);

		// Start the session object
		$this->_cart_session_data = new Zend_Session_Namespace('cart');
	 }
	 
	 public function addItem($prize_id, $prize_quantity)
	 {
		  $cartItem = new CartItem($prize_id, $prize_quantity);
		  $this->items[$cartItem->prize_id] = $cartItem;
	 }
	 
	 public function deleteFromCart()
	 {
		  //for later development
	 }
	 
	 public function submitCart($user_id, $prize_id, $prize_quantity, $points)
	 {
			
	 }
	 
	 public function checkOut()
	 {
		  //checks out items but orders are not actually submitted for printing
		  //however the stock value should be reduced and points for the kid should be reduced
			
	 }
	 
}

?>