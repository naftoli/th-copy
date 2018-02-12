<?php
class Roles
{
	private $_db;
	private $_user_session_data;
	private $arrPermissions = array();
	private $strUserRole;
	private $_tools;

	public function __construct()
	{
		// Start the DB objects
		$this->_db = Zend_Registry::get('db');
		$this->_db->setFetchMode(Zend_Db::FETCH_OBJ);

		// Start the session object
		$this->_user_session_data = new Zend_Session_Namespace('user_session_data');

		$this->setRoles();
		$this->_tools = new ToolsModels();
   	}

	// Set you role privileges based on your permission hierarchy
	public function setRoles($strPermission=false)
	{
		$permission = $this->_user_session_data->permission;
		$this->arrPermissions = array();
		$this->arrPermissions[] = $permission;
		$this->strUserRole = $permission;
		/*
		$strSql = "
			SELECT
				permission
			FROM
				permissions
			WHERE
				user_id=" . $this->_user_session_data->user_id . "
				AND default_permission = 1";
		$objPermission = $this->_db->fetchRow($strSql);
		*/
		/*
		$strUserRole = $this->strUserRole = !$strPermission ? $this->_user_session_data->permission : $strPermission;
		$strSql = "
			SELECT
				*
			FROM
				permission_types";
		$arrResult = $this->_db->fetchAll($strSql);
		$arrResult = array_reverse($arrResult);
		$this->arrPermissions = array();
		foreach ($arrResult as $objPermissionType)
		{
			array_push($this->arrPermissions, $objPermissionType->permission_type);
			
			if ($strUserRole == $objPermissionType->permission_type){
			  break;
			}
		}
		*/
	}

	public function isAllowed($strResource)
	{
		if (in_array($strResource, $this->arrPermissions))
			return true;
	}

	public function isRole($strRole)
	{
		if (strtolower($this->strUserRole) == strtolower($strRole))
			return true;
	}

}
?>