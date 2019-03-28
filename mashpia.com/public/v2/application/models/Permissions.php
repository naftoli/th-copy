<?php
class Permissions 
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
	
	public function _permissions_select($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);
		
		// Possible column selections
		$arrColumns = array (
			"permission_id"			=> @$arrParams["permission_id"],
			"template_style"		=> @$arrParams["template_style"],
			"user_id"				=> @$arrParams["user_id"],
			"institution_id"		=> @$arrParams["institution_id"],
			"permission"			=> @$arrParams["permission"],
			"default_permission"	=> @$arrParams["default_permission"],
			"created"				=> @$arrParams["created"],
			"modified"				=> @$arrParams["modified"],
			"created_by"			=> @$arrParams["created_by"]
		);

		$strSql = "
			SELECT
				*
			FROM
				permissions
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
		
		$strSql .= "
			ORDER BY
				default_permission+0 DESC, modified DESC, created DESC";
		
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
	}
	
	public function _permissions_insert($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);
		
		if (!isset($arrParams["created_by"]))
		{
			$arrParams["created_by"] = $this->_user_session_data->user_id;
		}
		
		$arrFeilds = array (
			"template_style"		=> @$arrParams["template_style"],
			"user_id"				=> @$arrParams["user_id"],
			"institution_id"		=> @$arrParams["institution_id"],
			"permission"			=> @$arrParams["permission"],
			"default_permission"	=> @$arrParams["default_permission"],
			"created"				=> date("Y-m-d H:i:S"),
			"created_by"			=> @$arrParams["created_by"]
		);

		// Execute
		$boolResult = $this->_db->insert("permissions", $arrFeilds);
		if ($boolResult)
		{
			return $this->_db->lastInsertId();
		}
	}
	
	public function _permissions_update($arrParams)
	{
		$arrValuesParams = array("template_style","user_id","institution_id","permission","default_permission","created","created_by");
		$arrWhereParams = array("permission_id","template_style","user_id","institution_id","permission","default_permission","created","modified","created_by");
		
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
		$arrWhere = array();
		foreach ($arrWhereParams as $strKey)
		{
			if (isset($arrParams["where"][$strKey]))
				$arrWhere[] = $this->_db->quoteInto($strKey . ' = ?', $arrParams["where"][$strKey]);
		}
		
		if (!count($arrWhere))
		{
			print "Sorry, there was an error: MB-BU101-TRTHTT";
			exit;
		}
		
		// Execute
		$boolResult = $this->_db->update("permissions", $arrValues, $arrWhere);
		return $boolResult;
	}
	
	public function _permissions_delete($arrParams)
	{
		$arrWhereParams = array("permission_id","template_style","user_id","institution_id","permission","default_permission","created","modified","created_by");
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
		$boolResult = $this->_db->delete("permissions", $arrFeilds);
		return $boolResult;
	}
	
	/*
	 * Delete permissions of a specific type for a user
	 * Required: institution_type, user_id
	 */
	function permissions_delete_type($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);
		if (!isset($arrParams["institution_type"]))
		{
			print "Sorry, there was an error: MP-PDT101-SDF9SD";
			exit;
		}
		if (!isset($arrParams["user_id"]))
		{
			print "Sorry, there was an error: MP-PDT102-SD89FD";
			exit;
		}
		
		$strSql = "
			SELECT
				permissions.permission_id
			FROM
				permissions,
				institutions
			WHERE
				permissions.user_id = " . $arrParams["user_id"] . "
				AND " . $this->_db->quoteInto('institutions.institution_type = ?', $arrParams["institution_type"]);
		$arrResult = $this->_db->fetchAll($strSql);
		$arrFeilds = array();
		foreach ($arrResult as $objPermission)
		{
			$arrFeilds[] = $this->_db->quoteInto('permission_id = ?', $objPermission->permission_id);
		}
		$boolResult = 0;
		if (count($arrFeilds))
		{
			$boolResult = $this->_db->delete("permissions", $arrFeilds);
		}
		return $boolResult;
	}
	
	/*
	 * Delete permissions that don't currently have an institution associated to them
	 */
	public function permissions_clean_up($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);
		if (!isset($arrParams["user_id"]))
		{
			print "Sorry, there was an error: MP-PCU101-8DF8DD";
			exit;
		}
		$strSql = "
			DELETE FROM
				permissions
			WHERE
			    permissions.user_id = " . $arrParams["user_id"] . "
			    AND permissions.institution_id NOT IN (select institution_id from institutions);
		";
		$this->_db->query($strSql);
	}
}
?>