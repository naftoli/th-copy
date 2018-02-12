<?php

class LaddersController extends Zend_Controller_Action
{
    private $_user_session_data;
	private $_tools;

    function init()
    {}

    function preDispatch()
    {
		$this->_tools = new ToolsControllers();
		$this->_model_tools = new ToolsModels();
		// Get the session object
		$this->_user_session_data = new Zend_Session_Namespace('user_session_data');

		if ($this->_user_session_data)
		{
			if (!empty($this->_user_session_data->user_id) && !empty($this->_user_session_data->institution_id) && !empty($this->_user_session_data->permission) && $this->_user_session_data->is_user_active)
			{
				// Send the user's id, their permission, and listing permissions to the view files
				$this->view->objSession = $this->_user_session_data;
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

	public function editorAction()
	{
		$objCampaigns = new Campaigns();
		$objLadders = new Ladders();
		$intTask = $this->view->intTask = $this->_request->getParam("task_id");
		if (isset($intTask))
		{
			$objTask = $objCampaigns->task_select_id($intTask);
			$intMission = $objTask->mission_id;
		}
		if (!isset($intMission))
		{
			$intMission = $this->view->intMission = $this->_request->getParam("mission_id");
		}
		if (!$intMission)
		{
			print text("Sorry, there was an error") . ": CLC-E102-S456DF";
			exit;
		}
		$objMission = $this->view->objMission = $objCampaigns->mission_select_id($intMission);
		if (!$objMission)
		{
			print text("Sorry, there was an error") . ": CLC-E103-456SFD";
			exit;
		}
		$objCampaign = $this->view->objCampaign = current($objCampaigns->_campaigns_select(array("campaign_id" => $objMission->campaign_id)));

		// Ajax start
		if ($this->_request->isPost())
		{
			$arrPost = $this->_request->getPost();
			$arrInsert = array();
			foreach ($arrPost as $strKey => $strValue)
			{
				if (preg_match("/^s_grade_([0-9]+)_ladder_([0-9]+)/", $strKey, $arrMatched))
				{
					list($strKey, $intGrade, $intLadder) = $arrMatched;
					$arrInsert[] = array(
						"grade" => $intGrade,
						"ladder" => $intLadder,
						"comment" => isset($arrPost["comment_grade_{$intGrade}_ladder_$intLadder"]) ? $arrPost["comment_grade_{$intGrade}_ladder_$intLadder"] : ""
					);
				}
			}
			$arrParams = array(
				"grades_ladders" => $arrInsert,
				"mission_id" => $intMission,
				"task_id" => $intTask,
				"campaign_id" => $objMission->campaign_id,
				"institution_id" => $objMission->institution_id,
				"is_required" => 1,
				"velocity" => $objMission->default_velocity
			);

			$objLadders->tasks_scale_delete(
				array(
					"mission_id" => $intMission,
					"task_id" => $intTask
				)
			);
			$arrAIIds = $objLadders->tasks_scale_insert($arrParams);
			print 1; // Always sucessful
			exit;
		}
		// Ajax end

		$arrMissionLadder = $objLadders->tasks_scale_select(
			array(
				"mission_id" => $intMission,
				"task_id" => 0
			)
		);
		$objGrades = new Grades();
		$this->view->arrGrades = $objGrades->_grades_select_hierarchal(
			array(
				"institution_id" => $objMission->institution_id
			)
		);

		$this->view->arrLadders = $arrMissionLadder;
		if (isset($intTask))
		{
			$this->view->arrLadders = $objLadders->tasks_scale_select(
				array(
					"task_id" => $intTask
				)
			);
			$arrDisabled = array();
			foreach ($arrMissionLadder as $objLadder)
			{
				$arrDisabled[$objLadder->grade . "_" . $objLadder->ladder] = 1;
			}
			$this->view->arrDisabled = $arrDisabled;
		}
	}

	public function quotasAction()
	{
		$query = new QueryGen();
		$objUsers = new Users();
		$objCampaigns = new Campaigns();
		$objRoles = new Roles();
		$objLadders = new Ladders();
		$objClasses = new Classes();
		$objAutomations = new Automation();

		$arrParams = $this->_request->getParams();

		if (!isset($arrParams["campaign_id"]))
		{
			print text("Sorry, there was an error") . ": CL-Q101-9A8SDS";
			exit;
		}

		if ($this->_request->isPost())
		{
			$arrPost = $this->_request->getPost();
			$arrUsers = array();
			foreach ($arrPost as $strKeyName => $boolChecked)
			{
				if (
					preg_match("/user_([0-9]+)/", $strKeyName, $arrMatched)
					&& (
						isset($arrParams["group_commit"])
						|| $boolChecked > 0
					)
				)
					$arrUsers[$arrMatched[1]] = $arrMatched[1];
			}
			foreach ($arrUsers as $intUser)
			{
				if (isset($arrPost["quota_" . $intUser]))
				{
					$objCampaigns->_user_campaigns_update(array(
						"where" => array(
							"user_id" => $intUser,
							"status" => "Enrollment",
							"campaign_id" => $arrParams["campaign_id"],
							"institution_id" => $this->_user_session_data->institution_id
						),
						"values" => array(
							"ladder" => isset($arrParams["group_commit"]) ? $arrPost["group_value"] : $arrPost["quota_" . $intUser]
						)
					));
					$arrResult = $objAutomations->user_goal(array(
						"user_id" => $intUser,
						"campaign_id" => $arrParams["campaign_id"]
					));
				}
			}
			print 1;
			exit;
		}

		$this->view->objCampaign = $objCampaign = first($objCampaigns->_campaigns_select(array(
			"campaign_id" => $arrParams["campaign_id"]
		)));
		if (!$objCampaign)
		{
			print text("Sorry, there was an error") . ": CL-Q102-2F3233";
			exit;
		}
		$this->view->campaign_id = $arrParams["campaign_id"];
		$this->view->tstyle = $arrParams["tstyle"];

		// Create array of users to display
		if (isset($arrParams["user_id"]))
		{
			$arrUsers = $objUsers->_users_select(array(
				"user_id" => $arrParams["user_id"]
			));
		}
		else if ($objRoles->isRole("Parent"))
		{
			$arrRelationships = $objUsers->_relationships_select(array(
				"user_id" => $this->_user_session_data->user_id
			));
			$arrUserIds = array();
			foreach ($arrRelationships as $objRelationship)
			{
				$arrUserIds[] = $objRelationship->relation_id;
			}
			$arrUserIds = $this->_tools->array_var_extract($arrRelationships, "relation_id");
			if (count($arrUserIds))
			{
				$arrUsers = $objUsers->_users_select_hierarchal(array(
					"user_id" => $arrUserIds,
					"institution_id" => $this->_user_session_data->institution_id
				));
			}
			else
				$arrUsers = array();

		}
		else if (
			isset($arrParams["class_id"])
			&& $objRoles->isAllowed("Teacher")
		) {
			$arrUsers = $objUsers->_users_select_hierarchal(array(
				"class_id" => $arrParams["class_id"],
				"permission" => "Student"
			));
		}
		else if (
			$objRoles->isAllowed("Institution Administrator")
		) {
			$arrUsers = $objUsers->_users_select_hierarchal(array(
				"institution_id" => $this->_user_session_data->institution_id,
				"permission" => "Student"
			));
		}
		else
			$arrUsers = array();
		if (isset($arrParams["class_id"]))
		{
			$this->view->objClass = first($objClasses->_classes_select(array(
				'class_id' => $arrParams["class_id"]
			)));
		}
		$arrUsers = $this->_model_tools->cleanSlashes($arrUsers);
		$arrUsers = $this->_tools->array_hash($arrUsers, "user_id");
		$arrUserIds = array_keys($arrUsers);
		$arrUserClasses = $objClasses->_user_classes_select(array(
			"user_id" => $arrUserIds
		));
		$arrUserClassesHash = $this->_tools->array_hash($arrUserClasses, "user_id");
		if (!isset($arrParams["class_id"]))
		{
			$arrClasses = $objClasses->_classes_select(array(
				"class_id" => array_keys($this->_tools->array_hash($arrUserClasses, "class_id"))
			));
		}
		else
		{
			$arrClasses = $objClasses->_classes_select(array(
				"class_id" => $arrParams["class_id"]
			));

		}
		$arrClassesHash = $this->view->arrClassesHash = $this->_tools->array_hash($arrClasses, "class_id");
		$arrResult = array();
		if (count($arrUserIds))
		{
			$arrUserCampaigns = $objCampaigns->_user_campaigns_select(array(
				"status" => "Enrollment",
				"user_id" => $arrUserIds,
				"campaign_id" => $arrParams["campaign_id"]
			));
			foreach ($arrUserCampaigns as $objUserCampaign)
			{
				if ($arrParams["tstyle"] == "tanyatemplate1")
				{
					$strResultKey = "Tanya";
				}
				else
					$strResultKey = isset($arrParams["class_id"]) ? $arrParams["class_id"] : $arrUserClassesHash[$objUserCampaign->user_id]->class_id;
				$objUser = $arrUsers[$objUserCampaign->user_id];
				$strUserKey = $objUser->last_name . " : " . $objUser->first_name . " : " . $objUser->user_id;
				$arrResult[$strResultKey][$strUserKey] = array(
					"objUserCampaign" => $objUserCampaign,
					"objUser" => $objUser
				);
				if (isset($arrParams["class_id"]))
				{
					if (!isset($arrLadders[$arrParams["class_id"]]))
						$arrLadders[$arrParams["class_id"]] = $objLadders->class_campaign_ladders(array(
							"class_id" => $arrParams["class_id"],
							"campaign_id" => $arrParams["campaign_id"]
						));
					$arrResult[$strResultKey][$strUserKey]["arrLadders"] = $arrLadders[$arrParams["class_id"]];
				}
				else
				{
					if (!isset($arrUserClassesHash[$objUserCampaign->user_id]))
						continue;
					if (!isset($arrClassesHash[$arrUserClassesHash[$objUserCampaign->user_id]->class_id]))
						continue;
					if (!isset($arrLadders[$arrUserClassesHash[$objUserCampaign->user_id]->class_id]))
						$arrLadders[$arrUserClassesHash[$objUserCampaign->user_id]->class_id] = $objLadders->class_campaign_ladders(array(
							"class_id" => $arrUserClassesHash[$objUserCampaign->user_id]->class_id,
							"campaign_id" => $arrParams["campaign_id"]
						));
					if (!count($arrLadders[$arrUserClassesHash[$objUserCampaign->user_id]->class_id]))
							continue;
					$arrResult[$strResultKey][$strUserKey]["arrLadders"] = $arrLadders[$arrUserClassesHash[$objUserCampaign->user_id]->class_id];
				}
				ksort($arrResult[$strResultKey]);

			}
		}
		$this->view->arrResult = $arrResult;
	}

	public function suggestedtanyaAction()
	{

	}
}
?>