<?php
class ClassesController extends Zend_Controller_Action
{
	private $_user_session_data;
	private $objPermission; // permission instance
	private $boolVerbose = 0;

	function preDispatch()
	{
		$this->_user_session_data = new Zend_Session_Namespace('user_session_data');
		//$arrParams = $this->_request->getParams();
		//$utilities = new Utilities();
		//$this->objPermission = $utilities->dispatch_helper($arrParams);
	}

	function classteachereditorAction()
	{
		$objClasses = new Classes();
		$objInstitutions = new Institutions();
		$objUsers = new Users();
		$objRoles = new Roles();
		$query = new QueryGen();

		$objInstitution = first($objInstitutions->_institutions_select(array(
			"institution_id" => $this->_user_session_data->institution_id
		)));
		if (!$objInstitution)
		{
			print text("Sorry, there was an error") . ": CC-CTE101-8DFDSS";
			exit;
		}

		$this->view->strPermission = $strUserType = "Student";
		if ($this->_request->getParam("permission") == "Teacher")
		{
			$this->view->strPermission = $strUserType = $this->_request->getParam("permission");
		}

		// Get the classes the admin is in
		if ($objRoles->isAllowed("Institution Administrator"))
		{
			$arrAdminClasses = array_hash("class_id", $objClasses->_classes_select(array(
				"institution_id" => $this->_user_session_data->institution_id,
				'_ORDER' => 'class_hierarchy+0'
			)));
		}
		else if ($objRoles->isAllowed("Teacher"))
		{
			$arrAdminClasses = array_hash("class_id", $objClasses->_user_classes_select(array(
				"user_id" => $this->_user_session_data->user_id,
				"user_role" => "Teacher"
			)));
		}
		else
		{
			print text("Sorry, there was an error") . ": CC-CTE102-9SS9SA";
			$this->_helper->viewRenderer->setNoRender();
			return;
		}

		// Get the students that are in those classes and create their encoded values
		$arrStudentClasses = $objClasses->_user_classes_select(array(
			"class_id" => array_keys($arrAdminClasses),
			"class_role" => $strUserType
		));
		// verify that the users exist
		$arrUsers = array_hash("user_id", $objUsers->_users_select_hierarchal(array(
			"institution_id" => $this->_user_session_data->institution_id,
			"permission" => $strUserType
		)));

		$arrClassStudents = array();
		foreach ($arrStudentClasses as $intKey => $objStudentClass)
		{
			if (!isset($arrUsers[$objStudentClass->user_id]))
			{
				unset($arrStudentClasses[$intKey]);
				continue;
			}
			$arrClassStudents[$objStudentClass->class_id][$objStudentClass->user_id] = 1; // selected
		}

		$arrEncodedClasses = array();
		foreach ($arrClassStudents as $intClass => $arrClassUsers)
		{
			$arrEncodedClasses[$intClass] = http_build_query($arrClassUsers);
		}

		$this->view->arrEncodedClasses = $arrEncodedClasses;

		$arrList = array();
		if (count($arrAdminClasses))
		{
			$arrSql = array();
			$arrSql["institution_id"] = $this->_user_session_data->institution_id;
			$arrSql["class_id"] = array_keys($arrAdminClasses);
			$arrClasses = $objClasses->_classes_select($arrSql);

			foreach ($arrClasses as $objClass)
			{
				$arrClassStudents = $objClasses->_user_classes_select(array(
					"class_id" => $objClass->class_id,
					"class_role" => $strUserType
				));
				$strKey = preg_replace("/^pre(?:\-?school)? *([0-9a-z]+)/i", "0\1", $objClass->grade)  ." ". $objClass->sub;
				$strName = $objClass->grade ." ". $objClass->sub;
				$arrList[$strKey]["count"] = count($arrClassStudents) == count($arrUsers) ? "All" : count($arrClassStudents);
				$arrList[$strKey]["link"] = "/checklist/users/title/" . urlencode($strName) . "/permission/" . strtolower($strUserType);
				$arrList[$strKey]["value"] = $objClass->class_id;
				$arrList[$strKey]["text"] = $strName;
			}
			//ksort($arrList);
		}
		$this->view->arrList = $arrList;

		if ($this->_request->isPost())
		{
			$arrParams = $this->_request->getPost();
			foreach ($arrAdminClasses as $objAdminClass)
			{
				if (!isset($arrParams["class_" . $objAdminClass->class_id]))
					continue;
				$objClasses->_user_classes_delete(array(
					"class_id" => $objAdminClass->class_id,
					"class_role" => $strUserType
				));
				parse_str($arrParams["class_" . $objAdminClass->class_id], $arrClassStudents);
				foreach ($arrClassStudents as $intUser => $boolValue)
				{
					if ($boolValue == "1")
					{
						$query->user_classes__insert(array(
							"user_id" => $intUser,
							"class_id" => $objAdminClass->class_id,
							"class_role" => $strUserType,
							"institution_id" => $this->_user_session_data->institution_id
						));
					}
				}
			}
			print 1;
			$this->_helper->viewRenderer->setNoRender();
			return;
		}
	}

	public function oneclassstudentsAction()
	{
		$query = new QueryGen();
		$objClasses = new Classes();
		$arrParams = $this->_request->getParams();
		$arrResult = array();
		$boolDoWhile = 1;
		$arrUsersParams = array();
		$arrClasses = array_hash("class_id", $objClasses->_classes_select(array(
			"institution_id" => $this->_user_session_data->institution_id
		)));
		while ($boolDoWhile--)
		{
			if (isset($arrParams["class_id"]))
			{
				if (!isset($arrClasses[$arrParams["class_id"]]))
				{
					$arrResult["error"] = text("Sorry, there was an error: ") . "CC-OCS101-FGGA24";
					break;
				}
				$arrUserClasses = array_hash("user_id", $query->user_classes__select(array(
					"class_id" => $arrParams["class_id"],
					"class_role" => "Student"
				)));
				$arrUsersParams["user_id"] = array_keys($arrUserClasses);

				$arrPermissionsParams = $arrUsersParams;
				$arrPermissionsParams["institution_id"] = $this->_user_session_data->institution_id;
				//$arrPermissionsParams["_GREATER"]["registration_expiration"] = time();
				$arrPermissions  = array_hash("user_id", $query->permissions__select($arrPermissionsParams));
				$arrUsersParams["user_id"] = array_keys($arrPermissions);
				$arrUsersParams["is_active"] = 1;

			} else {
				$arrPermissions = array_stack("user_id", $query->permissions__select(array(
					"permission" => "Student",
					"institution_id" => $this->_user_session_data->institution_id
					//"_GREATER" => array(
					//	"registration_expiration" => time()
					//)
				)));
				$arrUsersParams["user_id"] = array_keys($arrPermissions);
				$arrUserClasses = array_hash("user_id", $query->user_classes__select(array(
					"user_id" => $arrUsersParams["user_id"],
					"institution_id" => $this->_user_session_data->institution_id
				)));
			}

			$arrUsersParams["_ORDER"] = "first_name, last_name";
			$arrUsersParams['is_active'] = 1;
			$arrUsers = $query->users__select($arrUsersParams);
			//dumper($arrUserClasses, 1, 1);
			$arrResult["success"] = "true";
			$arrResult["arrUsers"] = $arrUsers;
			$arrResult["arrClasses"] = $arrClasses;
			$arrResult["arrUserClasses"] = $arrUserClasses;

			if ($this->_request->isPost())
			{
				$arrPost = $this->_request->getPost();
				$arrNewUserClasses = array();
				foreach ($arrPost as $strKey => $strValue)
				{
					if (!$strValue)
						continue;
					if (preg_match("/^(class)_([0-9]+)$/", $strKey, $arrMatched))
					{
						list($strKey, $strType, $intUser) = $arrMatched;
						$arrNewUserClasses[] = array(
							"class_id" => $strValue,
							"user_id" => $intUser
						);
					}
				}
				$arrInstructions = $query->_proc_query_instructions2($arrUserClasses, $arrNewUserClasses, "user_id", array("class_id"));
				foreach ($arrInstructions["_INSERT"] as $arrData)
				{
					$arrData["class_role"] = "Student";
					$arrData["institution_id"] = $this->_user_session_data->institution_id;
					$arrDeleteParams = $arrData;
					unset($arrDeleteParams["class_id"]);
					$query->user_classes__delete($arrDeleteParams);
					$query->user_classes__insert($arrData);

				}
				foreach ($arrInstructions["_UPDATE"] as $arrData)
				{
					$query->user_classes__update($arrData);
				}
				foreach ($arrInstructions["_DELETE"] as $arrData)
				{
					$query->user_classes__delete($arrData);
				}
				print json_encode(array(
					"success" => "true"
				));
				exit;
			}
		} // while
		$this->view->arrResult = $arrResult;
	}
}
?>