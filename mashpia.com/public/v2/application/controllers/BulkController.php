<?php
class BulkController extends Zend_Controller_Action
{
	private $_user_session_data;
	private $objPermission;

	function init()
	{}

	function preDispatch()
	{
		$this->_user_session_data = new Zend_Session_Namespace('user_session_data');
		//$arrParams = $this->_request->getParams();
		//$utilities = new Utilities();
		//$this->objPermission = $utilities->dispatch_helper($arrParams);
	}

	function studentsEditorAction()
	{
		$objUsers = new Users();
		$objClasses = new Classes();

		$arrClasses = $this->view->arrClasses = $objClasses->_classes_select_hierarchal(array(
			"institution_id" => $this->_user_session_data->institution_id
		));
		$arrClassesHash = array();
		foreach ($arrClasses as $objClass)
		{
			$arrClassesHash[$objClass->class_id] = $objClass;
		}
		$this->view->arrClassesHash = $arrClassesHash;

		if ($this->_request->isPost())
		{
			$arrPostParams = $this->_request->getPost();
			if ($this->_request->getParam("load_data"))
			{
				$arrClassParams = array();
				foreach ($arrPostParams as $intKey => $strON)
				{
					if (preg_match("/^bulk_class_([0-9]+)/", $intKey, $arrMatched))
					{
						$arrClassParams[$arrMatched[1]] = $arrMatched[1];
					}
				}

				$arrNewParams = array();
				$arrNewParams["institution_id"] = $this->_user_session_data->institution_id;
				$arrNewParams["permission"] = "Student";
				$arrNewParams["class_id"] = $arrClassParams;
				$arrUsers = $objUsers->_users_select_hierarchal($arrNewParams);

				$arrRowData = array();
				$arrResults = array();
				$arrResults["arrPostParams"] = $arrPostParams;
				foreach ($arrUsers as $objUser)
				{
					if (isset($arrPostParams["user_bunk"]))
					{
						$objClass = first($objClasses->_user_classes_select(array(
							"user_id" => $objUser->user_id
						)));
						$arrRowData[$objUser->user_id]["bunk"] = $objClass->class_id;
					}
					if (isset($arrPostParams["user_first_last"]))
					{
						$arrRowData[$objUser->user_id]["first_name"] = $objUser->first_name;
						$arrRowData[$objUser->user_id]["last_name"] = $objUser->last_name;
					}
					if (isset($arrPostParams["user_first_last_hebrew"]))
					{
						$arrRowData[$objUser->user_id]["hebrew_first_name"] = $objUser->hebrew_first_name;
						$arrRowData[$objUser->user_id]["hebrew_last_name"] = $objUser->hebrew_last_name;
					}
					if (isset($arrPostParams["user_gender"]))
					{
						$arrRowData[$objUser->user_id]["gender"] = $objUser->gender;
					}
					if (isset($arrPostParams["user_dob"]))
					{
						$arrRowData[$objUser->user_id]["dob"] = $objUser->dob;
					}
					if (isset($arrPostParams["user_address"]))
					{
						$arrRowData[$objUser->user_id]["address"] = $objUser->address;
					}
					if (isset($arrPostParams["user_city"]))
					{
						$arrRowData[$objUser->user_id]["city"] = $objUser->city;
					}
					if (isset($arrPostParams["user_state"]))
					{
						$arrRowData[$objUser->user_id]["state"] = $objUser->state;
					}
					if (isset($arrPostParams["user_postal_zip"]))
					{
						$arrRowData[$objUser->user_id]["postal"] = $objUser->postal;
					}
					if (isset($arrPostParams["user_country"]))
					{
						$arrRowData[$objUser->user_id]["country"] = $objUser->country;
					}
					if (isset($arrPostParams["user_phone"]))
					{
						$arrRowData[$objUser->user_id]["phone"] = $objUser->phone;
					}
					if (isset($arrPostParams["user_active"]))
					{
						$arrRowData[$objUser->user_id]["is_active"] = $objUser->is_active;
					}
					if (isset($arrPostParams["user_image_id"]))
					{
						$arrRowData[$objUser->user_id]["image_id"] = $objUser->image_id;
					}
				}
				$arrResults["arrRowData"] = $arrRowData;
				print json_encode($arrResults);
				exit;
			}
			else if ($this->_request->getParam("save_data"))
			{
				// Collect the data to be saved
				$arrSaveData = array();
				foreach ($arrPostParams as $strKey => $Value)
				{
					if (preg_match("/^(.+?)_([0-9]+)$/", $strKey, $arrMatched))
					{
						$strColumn = $arrMatched[1];
						$intUser = $arrMatched[2];
						$arrSaveData[$intUser][$strColumn] = $Value;
					}
				}
				//var_dump($arrSaveData);exit;

				// Loop thourgh the data and update the required tables
				foreach ($arrSaveData as $intUser => $arrData)
				{
					// Update the appropriate tables
					// Table: user_classes
					if (isset($arrData["bunk"]))
					{
						$objClass = first($objClasses->_user_classes_select(array(
							"user_id" => $intUser,
							"class_id" => $arrData["bunk"]
						)));
						if (!$objClass)
						{
							$objClasses->_user_classes_update(array(
								"where" => array(
									"user_id" => $intUser,
								),
								"values" => array(
									"class_id" => $arrData["bunk"]
								)
							));
						}
					}
					// Table: users
					if (
						isset($arrData["first_name"])
						|| isset($arrData["last_name"])
						|| isset($arrData["hebrew_first_name"])
						|| isset($arrData["hebrew_last_name"])
						|| isset($arrData["gender"])
						|| isset($arrData["dob"])
						|| isset($arrData["address"])
						|| isset($arrData["city"])
						|| isset($arrData["state"])
						|| isset($arrData["postal"])
						|| isset($arrData["country"])
						|| isset($arrData["phone"])
						|| isset($arrData["is_active"])
						|| isset($arrData["image_id"])
					) {
						$objUsers->_users_update(array(
							"where" => array(
								"user_id" => $intUser
							),
							"values" => array(
								"first_name" => @$arrData["first_name"],
								"last_name" => @$arrData["last_name"],
								"hebrew_first_name" => @$arrData["hebrew_first_name"],
								"hebrew_last_name" => @$arrData["hebrew_last_name"],
								"gender" => @$arrData["gender"],
								"dob" => @$arrData["dob"],
								"address" => @$arrData["address"],
								"city" => @$arrData["city"],
								"state" => @$arrData["state"],
								"postal" => @$arrData["postal"],
								"country" => @$arrData["country"],
								"phone" => @$arrData["phone"],
								"is_active" => @$arrData["is_active"],
								"image_id" => @$arrData["image_id"]
							)
						));
					}
				}
				print 1;
				exit;
			}
		}
	}

	function studentClassesAction()
	{
		$objClasses = new Classes();
		$this->view->arrClasses = $objClasses->_classes_select(array(
			"institution_id" => $this->_user_session_data->institution_id
		));
	}
}
?>