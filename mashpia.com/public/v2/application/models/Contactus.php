<?php

class Contactus
{
	private $_db;
	private $_user_session_data;

	public function __construct() 
	{
		// Start the DB objects
		$this->_db = Zend_Registry::get('db');
		$this->_db->setFetchMode(Zend_Db::FETCH_OBJ);
   	}

	function insert_new_contact_us($array)
	{
		if($array){
			$this->_db->insert('contactus', $array);
		}
		return 0;
	}
	
	function get_all_contact_us()
	{
		$strSql = "select * from contactus";		
		$arrResult = $this->_db->fetchAll($strSql);
		//var_dump ($arrResult);
		if ($arrResult)
		{
			return $arrResult;
		}
		return 0;		
	}	
	
}

?>