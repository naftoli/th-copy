<?php
class Content
{
	public function process($strText, $intLocationId)
	{
		$this->_db = Zend_Registry::get('db');
		$this->_db->setFetchMode(Zend_Db::FETCH_OBJ);
		$this->_user_session_data = new Zend_Session_Namespace('user_session_data');
	}


}
?>