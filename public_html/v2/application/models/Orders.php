<?php
class Orders
{
	private $_db;
	private $_user_session_data;
	private $_tools;

	public function __construct()
	{
		// Start the DB objects
		$this->_db = Zend_Registry::get('db');
		$this->_db->setFetchMode(Zend_Db::FETCH_OBJ);

		// Start the session object
		$this->_user_session_data = new Zend_Session_Namespace('user_session_data');

		$this->_tools = new ToolsModels();
   	}
	
	public function _orders_select($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);
		
		// Possible column selections
		$arrColumns = array (
			"order_id"			=> @$arrParams["order_id"],
			"user_id"			=> @$arrParams["user_id"],
			"institution_id"	=> @$arrParams["institution_id"],
			"prize_id"			=> @$arrParams["prize_id"],
			"item_id"			=> @$arrParams["item_id"],
			"item_id_ref"		=> @$arrParams["item_id_ref"],
			"description"		=> @$arrParams["description"],
			"currency"			=> @$arrParams["currency"],
			"total_price"		=> @$arrParams["total_price"],
			"serial"			=> @$arrParams["serial"],
			"status"			=> @$arrParams["status"],
			"created"			=> @$arrParams["created"],
			"modified"			=> @$arrParams["modified"],
			"created_by"		=> @$arrParams["created_by"]
		);

		$strSql = "
			SELECT
				*
			FROM
				orders
			WHERE
				1
		";
		
		foreach ($arrColumns as $strColumn => $Value)
		{
			if (
				isset($Value)
				&& (
					$Value === 0
					|| $Value
				)
			) {
				if (!is_int($Value))
				{
					$Value = '"' . $Value . '"';
				}
				$strSql .= "
					AND `" . $strColumn . "` = " . $Value . "
				";
			}
		}
		
		if (isset($arrParams["LIMIT"]))
		{
			$strSql .= "
				LIMIT " . $arrParams["LIMIT"];
		}
		
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
	}
	
	public function _orders_insert($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);
		
		if (!isset($arrParams["created_by"]))
		{
			$arrParams["created_by"] = $this->_user_session_data->user_id;
		}
		
		$arrFeilds = array (
			"order_id"			=> @$arrParams["order_id"],
			"user_id"			=> @$arrParams["user_id"],
			"institution_id"	=> @$arrParams["institution_id"],
			"prize_id"			=> @$arrParams["prize_id"],
			"item_id"			=> @$arrParams["item_id"],
			"item_id_ref"		=> @$arrParams["item_id_ref"],
			"description"		=> @$arrParams["description"],
			"currency"			=> @$arrParams["currency"],
			"total_price"		=> @$arrParams["total_price"],
			"serial"			=> @$arrParams["serial"],
			"status"			=> @$arrParams["status"],
			"created"			=> date("Y-m-d H:i:S"),
			"modified"			=> @$arrParams["modified"],
			"created_by"		=> @$arrParams["created_by"]
		);

		// Execute
		$boolResult = $this->_db->insert("orders", $arrFeilds);
		if ($boolResult)
		{
			return $this->_db->lastInsertId();
		}
	}
	
	public function _orders_update($arrParams)
	{
		$arrValuesParams = array("user_id","institution_id","prize_id","item_id","item_id_ref","description","currency","total_price","serial","status","modified","created_by");
		$arrWhereParams = array("order_id","user_id","institution_id","prize_id","item_id","item_id_ref","description","currency","total_price","serial","status","created","modified","created_by");
		
		$arrParams = $this->_tools->rsqlclean($arrParams);
		
		$arrValues = array();
		
		// Values
		foreach ($arrValuesParams as $strKey)
		{
			if (isset($arrParams["values"][$strKey]))
				$arrValues[$strKey] = $arrParams["values"][$strKey];
		}
		$arrValues["modified"] = date("Y-m-d H:i:S");
		
		// Where
		
		foreach ($arrWhereParams as $strKey)
		{
			if (isset($arrParams["where"][$strKey]))
				$arrWhere[] = $this->_db->quoteInto($strKey . ' = ?', $arrParams["where"][$strKey]);
		}
		
		if (!count($arrWhere))
		{
			print "Sorry, there was an error: MB-BLU101-ASDASD";
			exit;
		}	
		
		// Execute
		$boolResult = $this->_db->update("orders", $arrValues, $arrWhere);
		return $boolResult;
	}
	
	public function _orders_delete($arrParams)
	{
		$arrWhereParams = array("order_id","user_id","institution_id","prize_id","item_id","item_id_ref","description","currency","total_price","serial","status","created","modified","created_by");
		$arrParams = $this->_tools->rsqlclean($arrParams);
		$arrFeilds = array();
		foreach ($arrWhereParams as $strKey)
		{
			if (isset($arrParams[$strKey]))
				$arrFeilds[] = $this->_db->quoteInto($strKey . ' = ?', $arrParams[$strKey]);
		}
		if (!count($arrFeilds))
		{
			print "Sorry, there was an error: MB-BLD101-SD7SD7";
			exit;
		}
		$boolResult = $this->_db->delete("orders", $arrFeilds);
		return $boolResult;
	}
}
?>