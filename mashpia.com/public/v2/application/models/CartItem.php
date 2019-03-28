<?php
class cartItem
{
    private $_db;
    
    public  $prize_id;
    public  $prize_name;
    public  $points;
    public  $quantity;
    
    public function __construct($prize_id, $prize_quantity)
    {
	// Start the DB objects
	$this->_db = Zend_Registry::get('db');
	$this->_db->setFetchMode(Zend_Db::FETCH_OBJ);
        
	$strSql = "SELECT * FROM prizes WHERE prize_id=" . $prize_id;
	$objResult = $this->_db->fetchRow($strSql);		
        
        $this->prize_id = $prize_id;
        $this->prize_name = $objResult->prize_name;
		$this->points = $objResult->points;
        $this->image_id = $objResult->image_id;
        $this->quantity = $prize_quantity;
    }

}
?>