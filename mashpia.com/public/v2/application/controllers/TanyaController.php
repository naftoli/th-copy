<?php
class TanyaController extends Zend_Controller_Action
{
	private $_user_session_data;

	function preDispatch()
	{
		// Get the session object
		$this->_user_session_data = new Zend_Session_Namespace('user_session_data');

		if ($this->_user_session_data)
		{
			if (!empty($this->_user_session_data->user_id) && !empty($this->_user_session_data->institution_id) && !empty($this->_user_session_data->permission) && $this->_user_session_data->is_user_active)
			{
				// Send the user's id, their permission, and listing permissions to the view files
				$this->view->user_id = $this->_user_session_data->user_id;
				$this->view->full_name = $this->_user_session_data->full_name;
				$this->view->company_id = $this->_user_session_data->company_id;
				$this->view->permission = $this->_user_session_data->permission;
				$this->view->institution_name = $this->_user_session_data->institution_name;
			}
			else
			{
				// Not allowed in
				$this->_redirect('logout');
			}
		}
		else
		{
			// Not allowed in
			$this->_redirect('logout');
		}
	}

	public function tanyaclassesAction()
	{
		$objClasses = new Classes();
		$arrParam = array(
			"institution_id" => $this->_user_session_data->institution_id,
			"active" => 1
		);
		$this->view->arrClass = $objClasses->classes_select($arrParam);
	}

	public function tanyalistAction()
	{
		if ($this->_request->getParam("approve") == "true")
		{
			$arrItems = array();
			foreach ($_POST as $strKey => $strValue)
			{
				$arrItemName = split("_", $strKey);
				if (
					$arrItemName[0] == "item"
					&& preg_match("/^[0-9]+$/", $arrItemName[1])
				) {
					$arrItems["item_" . $arrItemName[1]] = $strValue;
				}
			}
			$arrItems["arrItems"] = 1; // Flag
			if (!count($arrItems))
			{
				print text("Sorry, there was an error") . ": CTC-TLA101-FD4GF5";
				exit;
			}
			$objCurl = curl_init();
			curl_setopt($objCurl, CURLOPT_URL, 'http://www.mashpia.com/camps/content.php?output=approvals_pending_missions_icorpa');
			curl_setopt($objCurl, CURLOPT_POST, 1);
			curl_setopt($objCurl, CURLOPT_POSTFIELDS, $arrItems);
			curl_exec($objCurl);
			exit;
		} // End of POST
		$objRoles = new Roles();

		if ($objRoles->isRole('Parent'))
		{
			// Parent -> children
			$objRelationships = new Relationships();
			$arrChildren = $objRelationships->users_select_relationship(array("user_id"=>$this->_user_session_data->user_id,"forign_ref"=>"relation_id"));
			$arrUsers = array();
			foreach ($arrChildren as $objUser)
			{
				$arrUsers[] = $objUser->user_id;
			}
			$arrPost = array(
				"user_ids" => join(",", $arrUsers)
			);
		}
		else
		{
			$intClass = $this->_request->getParam("class_id");
			//$intClass = 999;
			if (!$intClass)
			{
				print text("Sorry, there was an error") . ": CTC-TLA101-DF156F";
				exit;
			}
			$objClasses = new Classes();
			$arrClasses = $objClasses->user_classes_select(array("class_id" => $intClass));
			$arrUsers = array();
			foreach ($arrClasses as $objClass)
			{
				$arrUsers[] = $objClass->user_id;
			}
			$arrPost = array(
				"user_ids" => join(",", $arrUsers)
			);
		}

		$objCurl = curl_init();
		curl_setopt($objCurl, CURLOPT_URL, 'http://www.mashpia.com/camps/content.php?output=approvals_pending_missions_icorpa');
		curl_setopt($objCurl, CURLOPT_POST, 1);
		curl_setopt($objCurl, CURLOPT_POSTFIELDS, $arrPost);
		curl_exec($objCurl);
	}

	public function tanyabackdatingclassesAction()
	{
		$objClasses = new Classes();
		$arrParam = array(
			"institution_id" => $this->_user_session_data->institution_id,
			"active" => 1
		);
		$this->view->arrClass = $objClasses->classes_select($arrParam);
	}

	public function tanyabackdatinglistAction()
	{
		if ($this->_request->getPost("is_post") == "true")
		{
			$arrPost = $this->_request->getPost();
			$objCurl = curl_init();
			curl_setopt($objCurl, CURLOPT_URL, 'http://www.mashpia.com/camps/content.php?output=tanya_backdate_temp');
			curl_setopt($objCurl, CURLOPT_POST, 1);
			curl_setopt($objCurl, CURLOPT_POSTFIELDS, $arrPost);
			curl_exec($objCurl);
			exit; // ajax
		}
		else
		{
			$intClass = $this->_request->getParam("class_id");
			if (!isset($intClass))
			{
				print text("Sorry, there was an error") . ": CTC-TBDL101-4DSFD5";
				exit;
			}
			$objUsers = new Users();
			$arrUsers = $this->view->arrUsers = $objUsers->users_student_select_class($intClass);
			$arrUserIds = array();
			foreach ($arrUsers as $objUser)
			{
				$arrUserIds["user_" . $objUser->user_id] = $objUser->user_id;
			}
			ob_start();
			$objCurl = curl_init();
			curl_setopt($objCurl, CURLOPT_URL, 'http://www.mashpia.com/camps/content.php?output=tanya_backdate_temp');
			curl_setopt($objCurl, CURLOPT_HEADER, 0);
			curl_exec($objCurl);
			curl_close($objCurl);
			$this->view->strTanyaJSON = ob_get_contents();
			ob_end_clean();
			ob_start();
			$objCurl = curl_init();
			curl_setopt($objCurl, CURLOPT_URL, 'http://www.mashpia.com/camps/content.php?output=tanya_backdate_temp');
			curl_setopt($objCurl, CURLOPT_POST, 1);
			$arrDOBUsers = $arrUserIds;
			$arrDOBUsers["dob"] = "get_user_dobs";
			curl_setopt($objCurl, CURLOPT_POSTFIELDS, $arrDOBUsers);
			curl_exec($objCurl);
			curl_close($objCurl);
			$strData = ob_get_contents();
			ob_end_clean();
			//print "DATA: " . $strData . " <br>\n";
			$this->view->arrDOBList = unserialize($strData);
			//var_dump($this->view->arrDOBList);
		}
	}

	public function tanyareportclassesAction()
	{
		$objClasses = new Classes();
		$arrParam = array(
			"institution_id" => $this->_user_session_data->institution_id,
			"active" => 1
		);
		$this->view->arrClass = $objClasses->classes_select($arrParam);
	}

	public function tanyareportlistAction()
	{
		$this->view->objRoles = $objRoles = new Roles();
		if ($objRoles->isRole('Parent'))
		{
			$objRelationships = new Relationships();
			$arrChildren = $objRelationships->users_select_relationship(array("user_id"=>$this->_user_session_data->user_id,"forign_ref"=>"relation_id"));
			$this->view->arrUsers = $arrChildren;
		}
		else
		{
			$intClass = $this->view->intClass = $this->_request->getParam("class_id");
			$intClass = $this->view->intClass = 999;
			if (!isset($intClass))
			{
				print text("Sorry, there was an error") . ": CTC-TRL101-SD6F54";
				exit;
			}
			$objUsers = new Users();
			$this->view->arrUsers = $objUsers->users_student_select_class($intClass);
		}
	}

	public function tanyareportAction()
	{
		$objUsers = new Users();
		$intUser = $this->view->intUser = $this->_request->getParam("user_id");
		$intClass = $this->view->intClass = $this->_request->getParam("class_id");
		if (!isset($intUser))
		{
			$arrUsers = array();
			$arrUsersGateway = $objUsers->users_student_select_class($intClass);
			foreach ($arrUsersGateway as $objUser)
			{
				$arrUsers[] = $objUser->user_id;
			}
		}
		else
		{
			$arrUsers = array($intUser);
		}
		$objCurl = curl_init();
		curl_setopt($objCurl, CURLOPT_URL, 'http://www.mashpia.com/camps/content.php?output=tanya_report_temp');
		curl_setopt($objCurl, CURLOPT_POST, 1);
		curl_setopt($objCurl, CURLOPT_POSTFIELDS, array("users" => serialize($arrUsers)));
		curl_exec($objCurl);
	}
}