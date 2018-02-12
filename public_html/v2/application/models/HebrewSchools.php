<?php
class HebrewSchools
{
	private $_db;
	private $_sesh;

	public function __construct()
	{
		// Start the DB objects
		$this->_db = Zend_Registry::get('db');
		$this->_db->setFetchMode(Zend_Db::FETCH_OBJ);

		// Start the session object
		$this->_sesh = new Zend_Session_Namespace('hebrewschools');
   	}

	function KioskAuthenticate($arrParams)
	{
		$strTStyle = $arrParams['template_style'];
		$arrParams = array_clean_sql($arrParams);

		$objUsers = new Users();
		$objPermissions = new Permissions();
		$objInstitutions = new Institutions();

		if (
			!isset($arrParams["bar_code"])
			|| !preg_match("/^[0-9]{20}|[0-9]{16}$/", $arrParams["bar_code"])
		)
			return array(
				"error" => "Invalid bar code."
			);

		$objUser = first($objUsers->_users_select(array(
			"bar_code" => (string) $arrParams["bar_code"]
		)));

		if (!$objUser)
			return array(
				"error" => "Bar code not found."
			);

		$objPermission = first($objPermissions->_permissions_select(array(
			"user_id" => $objUser->user_id,
			"permission" => "Student",
			"template_style" => $strTStyle
		)));
		if (!$objPermission)
			return array(
				"error" => "Your access was deactivated."
			);
		if ($objPermission->registration_expiration < time())
		{
			return array(
				"error" => "You are not registered."
			);
		}

		$objInstitution = first($objInstitutions->_institutions_select(array(
			"institution_id" => $objPermission->institution_id
		)));
		if (!$objInstitution)
			return array(
				"error" => "Institution not found."
			);

		$this->_sesh->setExpirationSeconds(9999999999);
		$this->_sesh->bar_code = $arrParams["bar_code"];
		$this->_sesh->user_id = $objUser->user_id;
		$this->_sesh->first_name = $objUser->first_name;
		$this->_sesh->last_name = $objUser->last_name;
		$this->_sesh->image_id = $objUser->image_id;
		$this->_sesh->permission_id = $objPermission->permission_id;
		$this->_sesh->permission = $objPermission->permission;
		$this->_sesh->institution_id = $objPermission->institution_id;
		$this->_sesh->template_style = $strTStyle;
		$this->_sesh->language_id = $objUsers->language_id($objUser->user_id, $objPermission->institution_id,1);
		return array(
			"success" => "true"
		);
	}
}
?>