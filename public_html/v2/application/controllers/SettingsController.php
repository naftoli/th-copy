<?php
class SettingsController extends Zend_Controller_Action
{
	private $_user_session_data;
	private $objPermission; // permission instance

	function preDispatch()
	{
		$query = new QueryGen();
		$arrParams = $this->_request->getParams();
		unset($arrParams["controller"], $arrParams["action"], $arrParams["module"]);
		$strParam = preg_replace("/[&=]+/", "/", http_build_query($arrParams));

		// Load thie session array
		$this->_user_session_data = new Zend_Session_Namespace('user_session_data');
		/*
		if (
			!$this->_user_session_data->user_id
			|| !$this->_user_session_data->permission_id
			|| !$this->_user_session_data->permission
			|| !$this->_user_session_data->institution_id
		)
			$this->_redirect('logout/index/' . $strParam);
		$this->objPermission = first($query->permissions__select(array(
			"user_id" => $this->_user_session_data->user_id,
			"permission_id" => $this->_user_session_data->permission_id,
			"permission" => $this->_user_session_data->permission,
			"institution_id" => $this->_user_session_data->institution_id
		)));
		if (!$this->objPermission)
			$this->_redirect('logout/index/' . $strParam);
		*/
	}

	public function networkalertsAction()
	{
		$query = new QueryGen();
		$arrParams = $this->_request->getParams();
		$arrNetworkAlerts = array_bubble_hash('alert_location', $query->network_alerts__select(array(
			'network_id' => $this->_user_session_data->network_id
		)));
		$arrAlertLocation = array('ID Cards', 'User Registration', 'Institution Registration');
		foreach ($arrAlertLocation as $strLocation)
		{
			if (!isset($arrNetworkAlerts[$strLocation]))
				$arrNetworkAlerts[$strLocation] = array();
		}
		$this->view->arrNetworkAlerts = $arrNetworkAlerts;
		if ($this->_request->isPost())
		{
			$arrPost = $this->_request->getPost();
			$arrData = json_decode($arrPost['value']);
			if (count($arrData))
			{
				$objFirst = reset($arrData);
				$query->network_alerts__delete(array(
					'network_id' => $this->_user_session_data->network_id,
					'alert_location' => urldecode($objFirst->alert_location)
				));
				foreach ($arrData as $objData)
				{
					$strAlertLocation = urldecode($objData->alert_location);
					$strAlertEmail = urldecode($objData->alert_email);
					// check if item needs to be inserted or updated
					$query->network_alerts__insert(array(
						'network_id' => $this->_user_session_data->network_id,
						'alert_location' => $strAlertLocation,
						'alert_email' => $strAlertEmail
					));
				}
			}
			//$strAlertLocation = $objData->alert_location;
			//dumper($objData,1,1);
			print json_encode(array(
				'success' => 'true'
			));
			exit;
		}
	}

	public function sponsorlogosAction()
	{
		$objConfig = new Config();
		$query = new QueryGen();
		$roles = new Roles();
		$intInstitution = $this->_user_session_data->institution_id;
		if ($roles->isAllowed('Network')) {
			if ($this->_request->getParam('institution_id'))
				$intInstitution = $this->_request->getParam('institution_id');
		}

		$this->view->tstyle = $this->_request->getParam('tstyle');
		$arrKioskLogos = $this->view->arrKioskLogos = $objConfig->load(array(
			"set" => array("kiosk_logos", "kiosk_logos_on"),
			"institution_id" => $intInstitution
		));
		$arrGet = $this->_request->getParams();
		if ($this->_request->isPost())
		{
			$arrPost = $this->_request->getPost();
			if (isset($arrGet['kiosk_logos']))
			{
				$arrConfigResult = array();
				foreach ($arrPost as $strKeyData => $strValue)
				{
					list($strSet, $strKey) = explode("__", $strKeyData);
					$arrConfigResult[$strSet][$strKey] = $strValue;
				}
				$objConfig->save($arrConfigResult, array(
					"set" => "kiosk_logos_on",
					"institution_id" => $intInstitution
				));
				print json_encode(array(
					'success' => 'true'
				));
				exit;
			}
			else if (!empty($arrPost['sponsorlogos__logoimage']))
			{
				$arrConfigResult = array();
				$arrConfigResult['kiosk_logos']['logo_' . $arrPost['logo_key']] = $arrPost['sponsorlogos__logoimage'];
				$objConfig->save($arrConfigResult, array(
					"set" => "kiosk_logos",
					'key' => 'logo_' . $arrPost['logo_key'],
					"institution_id" => $intInstitution
				));
				print 1;
				exit;
			}
		}
	}

	public function notificationsAction()
	{
		$objConfig = new Config();
		$query = new QueryGen();
		$roles = new Roles();
		$intInstitution = $this->_user_session_data->institution_id;
		if ($roles->isAllowed('Network')) {
			if ($this->_request->getParam('institution_id'))
				$intInstitution = $this->_request->getParam('institution_id');
		}

		$this->view->tstyle = $this->_request->getParam('tstyle');
		$arrUserOptions = $this->view->arrUserOptions = $objConfig->load(array(
			"set" => "kiosk",
			"key" => "store_admin_notifications",
			"institution_id" => $intInstitution
		));
		if ($this->_request->isPost())
		{
			$arrPost = $this->_request->getPost();
			$arrConfigResult = array();
			foreach ($arrPost as $strKeyData => $strValue)
			{
				list($strSet, $strKey) = explode("__", $strKeyData);
				$arrConfigResult[$strSet][$strKey] = $strValue;
			}
			$objConfig->save($arrConfigResult, array(
				"set" => "kiosk",
				"key" => "store_admin_notifications",
				"institution_id" => $intInstitution
			));
			print json_encode(array(
				'success' => 'true'
			));
			exit;
		}
	}

	public function studentfieldsAction()
	{
		$query = new QueryGen();
		$roles = new Roles();
		$arrGet = $this->_request->getParams();
		$arrPost = $this->_request->getPost();

		$intInstitution = $this->_user_session_data->institution_id;
		if ($roles->isAllowed('Network')) {
			if ($this->_request->getParam('institution_id'))
				$intInstitution = $this->_request->getParam('institution_id');
		}

		$objInstitution = first($query->institutions__select(array(
			'institution_id' => $intInstitution
		)));
		$strCustomFields = $objInstitution->custom_fields;
		$arrCustomFields = array();
		if (empty($strCustomFields))
			$arrCustomFields = array();
		else
			$arrCustomFields = unserialize($strCustomFields);

		if (!empty($arrGet['ajax']))
		{
			$arrResults = array();
			$arrResults['success'] = 'false';
			print json_encode($arrCustomFields);
			exit;
		}
		else if (isset($arrGet["up"]))
		{
			$intFieldId = $arrGet["field_id"];
			if ($intFieldId < 1)
			{
				print json_encode(array(
					'error' => itext("Sorry, there was an error") . ": CS-SF102-7987AS"
				));
				exit;
			}
			$arrFieldKeys = array_keys($arrCustomFields);
			$intItr=0;
			$arrNewFieldsOrder = array();
			foreach ($arrCustomFields as $strKey => $arrRowData)
			{
				if ($intFieldId-1 == $intItr)
					$arrNewFieldsOrder['field_name_' . $arrCustomFields[$arrFieldKeys[$intFieldId]]['field_name']] = $arrCustomFields[$arrFieldKeys[$intFieldId]];
				else if ($intFieldId == $intItr)
					$arrNewFieldsOrder['field_name_' . $arrCustomFields[$arrFieldKeys[$intFieldId-1]]['field_name']] = $arrCustomFields[$arrFieldKeys[$intFieldId-1]];
				else
					$arrNewFieldsOrder[$strKey] = $arrRowData;
				$intItr++;
			}
			$strCustomFields = serialize($arrNewFieldsOrder);
			$query->institutions__update(array(
				'where' => array(
					'institution_id' => $intInstitution
				),
				'values' => array(
					'custom_fields' => $strCustomFields
				)
			));
			print 1;
			exit;
		}
		else if (isset($arrGet["down"]))
		{
			$intFieldId = $arrGet["field_id"];
			if ($intFieldId > count($arrCustomFields))
			{
				print json_encode(array(
					'error' => itext("Sorry, there was an error") . ": CS-SF102-7987AS"
				));
				exit;
			}
			$arrFieldKeys = array_keys($arrCustomFields);
			$intItr=0;
			$arrNewFieldsOrder = array();
			foreach ($arrCustomFields as $strKey => $arrRowData)
			{
				if ($intFieldId+1 == $intItr)
					$arrNewFieldsOrder['field_name_' . $arrCustomFields[$arrFieldKeys[$intFieldId]]['field_name']] = $arrCustomFields[$arrFieldKeys[$intFieldId]];
				else if ($intFieldId == $intItr)
					$arrNewFieldsOrder['field_name_' . $arrCustomFields[$arrFieldKeys[$intFieldId+1]]['field_name']] = $arrCustomFields[$arrFieldKeys[$intFieldId+1]];
				else
					$arrNewFieldsOrder[$strKey] = $arrRowData;
				$intItr++;
			}
			$strCustomFields = serialize($arrNewFieldsOrder);
			$query->institutions__update(array(
				'where' => array(
					'institution_id' => $intInstitution
				),
				'values' => array(
					'custom_fields' => $strCustomFields
				)
			));
			print 1;
			exit;
		}
		else if (isset($arrGet["delete"]))
		{
			$intFieldId = $arrGet["field_id"];
			$intItr = 0;
			foreach ($arrCustomFields as $strKey => $strVal)
			{
				if ($intFieldId == $intItr)
				{
					unset($arrCustomFields[$strKey]);
					break;
				}
				$intItr++;
			}
			$strCustomFields = serialize($arrCustomFields);
			$query->institutions__update(array(
				'where' => array(
					'institution_id' => $intInstitution
				),
				'values' => array(
					'custom_fields' => $strCustomFields
				)
			));
			print 1;
			exit;
		}
		if ($this->_request->isPost())
		{
			if ($this->_request->getParam("update")) // Update multipule grades
			{
				// Loop through the fields and complete the updates
				$intUpdatedCount = 0;
				$intItr=-1;
				$arrResult = array();
				while ($this->_request->getPost((++$intItr) . "_name"))
				{
					$strFieldKey = $this->_request->getPost($intItr . "_field_id");
					if (!isset($arrCustomFields[$strFieldKey]))
					{
						$arrResult["error"] = itext("Sorry, there was an error") . ": CS-SF101-9SDFD9";
						break;
					}
					$strName = preg_replace("/[^a-z0-9 ]/i", "", $this->_request->getPost($intItr . "_name"));
					$strType = preg_replace("/[^a-z0-9 ]/i", "", $this->_request->getPost($intItr . "_type"));
					if (!strlen($strName))
					{
						$arrResult["error"][$intItr . "_name"] = itext("You must include a name for the field you are attempting to insert.");
						continue;
					}
					$arrFieldRow = $arrCustomFields[$strFieldKey];
					if (
						$arrFieldRow['field_name'] == $strName
						&& $arrFieldRow['field_type'] == $strType
					)
						continue;
					$arrCustomFields[$strFieldKey]['field_name'] = $strName;
					$arrCustomFields[$strFieldKey]['field_type'] = $strType;
					$intUpdatedCount++;
				}
				if (!isset($arrResult["error"]))
				{
					if ($intUpdatedCount)
					{
						$arrFieldsOutput = array();
						foreach ($arrCustomFields as $arrRow)
						{
							$arrFieldsOutput['field_name_' . $arrRow['field_name']] = $arrRow;
						}
						$strCustomFields = serialize($arrFieldsOutput);
						$query->institutions__update(array(
							'where' => array(
								'institution_id' => $intInstitution
							),
							'values' => array(
								'custom_fields' => $strCustomFields
							)
						));
					}
					$arrResult["success"] = "true";
					$arrResult["count"] = $intUpdatedCount;
				}
				print json_encode($arrResult);
				exit;
			}
			else
			{
				$objUser = first($query->institutions__select(array(
					'institution_id' => $intInstitution
				)));
				$strCustomFields = $objUser->custom_fields;
				$arrCustomFields = array();
				if (empty($strCustomFields))
					$arrCustomFields = array();
				else
					$arrCustomFields = unserialize($strCustomFields);
				$arrCustomFields['field_name_' . $arrPost['field_name']] = array(
					'field_type' => $arrPost['field_type'],
					'field_name' => $arrPost['field_name']
				);
				$strCustomFields = serialize($arrCustomFields);
				$query->institutions__update(array(
					'where' => array(
						'institution_id' => $intInstitution
					),
					'values' => array(
						'custom_fields' => $strCustomFields
					)
				));
				print 1;
				exit;
			}
		}
	}
}
?>