<?php
class IncrementalsController extends Zend_Controller_Action
{
    private $_user_session_data;
	private $_tools;

    function init()
    {}

    function preDispatch()
    {
		$this->_tools = new ToolsModels();
		$this->_user_session_data = new Zend_Session_Namespace('user_session_data');
		if (!$this->_user_session_data->institution_id) {
			$this->_redirect('logout');
		}
		//$arrParams = $this->_request->getParams();
		//$utilities = new Utilities();
		//$this->objPermission = $utilities->dispatch_helper($arrParams);
	}

	public function taskinstallAction()
	{
		$query = new QueryGen();
		$this->view->campaign_id = $intCampaign = intval($this->_request->getParam("campaign_id"));
		$this->view->objCampaign = $objCampaign = first($query->campaigns__select(array(
			'campaign_id' => $intCampaign,
			'is_active' => 1
		)));
		$this->view->arrHostTasks = $arrHostTasks = array_hash('task_id', $query->tasks__select(array(
			'campaign_id' => $objCampaign->installed_campaign_id,
			'is_active' => 1
		)));
		$this->view->arrInstalledTasks = array_hash('installed_task_id', $query->tasks__select(array(
			'installed_task_id' => array_keys($arrHostTasks),
			'campaign_id' => $intCampaign
		)));
		if ($this->_request->isPost())
		{
			$arrPost = $this->_request->getPost();
			if ($arrPost['action'] == "uninstall")
			{
				$objTask = first($query->tasks__select(array(
					'task_id' => $arrPost['task_id']
				)));
				$query->tasks__delete(array(
					'task_id' => $arrPost['task_id'],
					'institution_id' => $this->_user_session_data->institution_id
				));
				print $objTask->installed_task_id;
				exit;
			} else {
				$objTask = first($query->tasks__select(array(
					'task_id' => $arrPost['template_task_id']
				)));
				$arrTask = (array) $objTask;
				$arrTask['installed_task_id'] = $arrTask['task_id'];
				$arrTask['campaign_id'] = $intCampaign;
				$arrTask['institution_id'] = $this->_user_session_data->institution_id;
				unset($arrTask['task_id']);
				unset($arrTask['created']);
				unset($arrTask['modified']);
				print $query->tasks__insert($arrTask);
				exit;
			}
		}
	}

	public function taskeditAction()
	{
		$query = new QueryGen();
		$this->view->task_id = $intTask = intval($this->_request->getParam("task_id"));
		$this->view->campaign_id = $intCampaign = intval($this->_request->getParam("campaign_id"));
		$this->view->objTask = $objTask = NULL;
		if (!empty($intTask))
		{
			$this->view->objTask = $objTask = first($query->tasks__select(array(
				'task_id' => $intTask
			)));
			$this->view->objCampaign = $objCampaign = first($query->campaigns__select(array(
				'campaign_id' => $objTask->campaign_id
			)));
			$intCampaign = $objCampaign->campaign_id;
		}
		else
		{
			$this->view->objCampaign = $objCampaign = first($query->campaigns__select(array(
				'campaign_id' => $intCampaign
			)));
		}
		if (!$objCampaign)
		{
			print json_encode(array(
				'error' => 'This campaign seems to no longer exist.'
			));
			exit;
		}
		$arrGet = $this->_request->getParams();
		$arrPost = $this->_request->getPost();

		if (
			!empty($arrPost['start_date'])
		) {
			if (preg_match('/([0-9]+)[^0-9]([0-9]+)[^0-9]([0-9]+)$/', $arrPost['start_date'], $arrMatched))
			{
				list($strMatch, $intMonth, $intDay, $inYear) = $arrMatched;
				$arrPost['start_date'] = $inYear . '-' . $intMonth . '-' . $intDay;
			}
		}
		if (
			!empty($arrPost['end_date'])
		) {
			if (preg_match('/([0-9]+)[^0-9]([0-9]+)[^0-9]([0-9]+)$/', $arrPost['end_date'], $arrMatched))
			{
				list($strMatch, $intMonth, $intDay, $inYear) = $arrMatched;
				$arrPost['end_date'] = $inYear . '-' . $intMonth . '-' . $intDay;
			}
		}
		if (isset($arrGet['task_save']))
		{
			if (empty($arrPost["task_name"]))
			{
				print json_encode(array(
					'error' => 'The task name is required.'
				));
				exit;
			}
			if ($objTask)
			{
				$objTaskName = first($query->tasks__select(array(
					'campaign_id' => $objCampaign->campaign_id,
					'task_name' => $arrPost['task_name'],
					'institution_id' => $this->_user_session_data->institution_id,
					'_LIMIT' => 1,
					'_NOT' => array(
						'task_id' => $intTask
					)
				)));
				if ($objTaskName)
				{
					print json_encode(array(
						'error' => 'There is already a task in this campaign with the same name.'
					));
					exit;
				}
				$query->tasks__update(array(
					'where' => array(
						'task_id' => $intTask
					),
					'values' => array(
						'task_name' => $arrPost['task_name'],
						'points' => $arrPost['points'],
						'start_date' => $arrPost['start_date'],
						'end_date' => $arrPost['end_date'],
						'min_points' => $arrPost['min_points'],
						'max_points' => $arrPost['max_points'],
						'is_active' => @$arrPost['is_active'] ? 1 : 0,
						'is_grid' => @$arrPost['is_grid'] ? 1 : 0,
						'is_checkbox' => @$arrPost['is_checkbox'] ? 1 : 0,
						'is_card' => @$arrPost['is_card'] ? 1 : 0,
						'is_locked' => @$arrPost['is_locked'] ? 1 : 0
					)
				));
				print json_encode(array(
					'success' => 'true'
				));
				exit;
			}
			else
			{
				$objTaskName = first($query->tasks__select(array(
					'campaign_id' => $objCampaign->campaign_id,
					'task_name' => $arrPost['task_name'],
					'institution_id' => $this->_user_session_data->institution_id,
					'_LIMIT' => 1
				)));
				if ($objTaskName)
				{
					print json_encode(array(
						'error' => 'There is already a task in this campaign with the same name.'
					));
					exit;
				}
				$intTask = $query->tasks__insert(array(
					'institution_id' => $this->_user_session_data->institution_id,
					'task_name' => $arrPost['task_name'],
					'points' => $arrPost['points'],
					'campaign_id' => $intCampaign,
					'start_date' => $arrPost['start_date'],
					'end_date' => $arrPost['end_date'],
					'min_points' => $arrPost['min_points'],
					'max_points' => $arrPost['max_points'],

					'is_active' => @$arrPost['is_active'] ? 1 : 0,
					'is_grid' => @$arrPost['is_grid'] ? 1 : 0,
					'is_checkbox' => @$arrPost['is_checkbox'] ? 1 : 0,
					'is_card' => @$arrPost['is_card'] ? 1 : 0,
					'is_locked' => @$arrPost['is_locked'] ? 1 : 0
				));
				print json_encode(array(
					'success' => 'true',
					'intTask' => $intTask
				));
				exit;
			}

		}
	}

	public function taskmanager3Action()
	{
		$this->view->campaign_id = $intCampaign = intval($this->_request->getParam("campaign_id"));
		$boolInactive = $this->view->boolInactive = $this->_request->getParam('inactive') ? 1 : 0;
		if (!$intCampaign)
		{
			print text("Sorry, there was an error") . ": IC-TM101-DSF89D";
			exit;
		}
		$intTask = intval(preg_replace("/^cti_/", "", $this->_request->getParam("task")));

		$objCampaigns = new Campaigns();
		$objMissions = new Missions();
		$objTasks = new Tasks();
		$objRoles = new Roles();
		$objClasses = new Classes();
		$query = new QueryGen();

		$objCampaign = $this->view->objCampaign = first($objCampaigns->_campaigns_select(array(
			"campaign_id" => $intCampaign
		)));
		if (!$objCampaign)
		{
			print text("Sorry, there was an error") . ": IC-TM102-7DFG7F";
			exit;
		}

		if ($this->_request->isPost())
		{
			// Find the template campaign object
			$objTemplateCampaign = first($objCampaigns->_campaigns_select(array(
				"installed_campaign_id" => $intCampaign,
				"institution_id" => $this->_user_session_data->institution_id
			)));

			$objMission = first($objMissions->_missions_select(array(
				"campaign_id" => $intCampaign
			)));

			if ($intTask)
			{
				$objTask = first($objTasks->_tasks_select(array(
					"task_id" => $intTask
				)));
				if (!$objTask)
				{
					print text("Sorry, there was an error") . ": CI-TM101-43R34D";
					exit;
				}
			}
			//dumper($this->_request->getPost(),0,1);
			if ($this->_request->getPost("job") == "update_task")
			{
				// First check if the task is installed
				if ($objTask->institution_id != $this->_user_session_data->institution_id) {
					// not installed, must do insert instead
					$arrNewParams = (array) $objTask;
					$arrNewParams["institution_id"] = $this->_user_session_data->institution_id;
					$arrNewParams["task_name"] = $this->_request->getPost("task_name");
					$arrNewParams["points"] = $this->_request->getPost("points") ? 1 : 0;
					$arrNewParams["is_checkbox"] = $this->_request->getPost("is_checkbox") == 'checked' ? 1 : 0;
					$arrNewParams["is_locked"] = $this->_request->getPost("is_locked") == 'checked' ? 1 : 0;
					$arrNewParams["installed_task_id"] = $arrNewParams["task_id"];
					$arrNewParams["campaign_id"] = $intCampaign;
					unset($arrNewParams["task_id"]);
					unset($arrNewParams["modifed"]);
					unset($arrNewParams["created"]);
					unset($arrNewParams["created_by"]);
					$intNewID = $query->tasks__insert($arrNewParams);
					print $intNewID;
					exit;
				}
				else
				{
					$query->tasks__update(array(
						"where" => array(
							"task_id" => $intTask
						),
						"values" => array(
							"task_name" => $this->_request->getPost("task_name"),
							"points" => $this->_request->getPost("points"),
							"is_checkbox" => $this->_request->getPost("is_checkbox") == 'checked' ? 1 : 0,
							"is_locked" => $this->_request->getPost("is_locked") == 'checked' ? 1 : 0
						)
					));
					print "saved";
					exit;
				}
			}
			else if ($this->_request->getPost("job") == "add_camp_task")
			{
				// First find the latest sequence
				$objLatestTask = first($objTasks->_tasks_select(array(
					"institution_id" => $this->_user_session_data->institution_id,
					"campaign_id" => $intCampaign,
					"_ORDER" => "sequence + 0 DESC",
					"_LIMIT" => 1
				)));
				$intLatestSequence = $objLatestTask ? $objLatestTask->sequence : 0;
				// Do the insert
				$arrSql = array(
					"task_name" => $this->_request->getPost("task_name"),
					"sequence" => $intLatestSequence + 1,
					"campaign_id" => $objCampaign->campaign_id,
					"mission_id" => 0,//$objMission->mission_id,
					"institution_id" => $this->_user_session_data->institution_id,
					"points" => $this->_request->getPost("points"),
					"is_checkbox" => $this->_request->getPost("is_checkbox") ? 1 : 0,
					"is_locked" => $this->_request->getPost("is_locked") ? 1 : 0
				);
				$intClass = intval($this->_request->getParam("class_id"));
				if ($objRoles->isRole("Teacher"))
				{
					if ($intClass < 1)
					{
						$objTeacherClass = first($objClasses->_user_classes_select(array(
							"user_id" => $this->_user_session_data->user_id,
							"class_role" => "Teacher"
						)));
						if (!$objTeacherClass)
						{
							print text("Sorry, there was an error") . ": CC-CCE101-DSF7DS";
							exit;
						}
						$intClass = $objTeacherClass->class_id;
					}
				}
				if ($intClass > 0)
					$arrSql["class_id"] = $intClass;

				$intAutoId = $query->tasks__insert($arrSql);
				print $intAutoId;
				exit;
			}
			else if ($this->_request->getPost("job") == "install_host_task")
			{
				$arrNewParams = (array) $objTask;
				$arrNewParams["institution_id"] = $this->_user_session_data->institution_id;
				$arrNewParams["task_name"] = $this->_request->getPost("task_name");
				$arrNewParams["points"] = $this->_request->getPost("points");
				$arrNewParams["installed_task_id"] = $arrNewParams["task_id"];
				$arrNewParams["campaign_id"] = $intCampaign;
				unset($arrNewParams["task_id"]);
				unset($arrNewParams["modifed"]);
				unset($arrNewParams["created"]);
				unset($arrNewParams["created_by"]);
				$intNewID = $objTasks->_tasks_insert($arrNewParams);
				print $intNewID;
			}
			else if ($this->_request->getPost("job") == "delete_task")
			{
				if (isset($objTask) && $objTask)
					$query->tasks__update(array(
						"where" => array(
							"task_id" => $objTask->task_id
						),
						"values" => array(
							"is_active" => 0
						)
					));
				print 1;
			}
			else if ($this->_request->getPost("job") == "reactivate")
			{
				if (isset($objTask) && $objTask)
					$query->tasks__update(array(
						"where" => array(
							"task_id" => $objTask->task_id
						),
						"values" => array(
							"is_active" => 1
						)
					));
				print 1;
			}
			exit;
		}

		$this->view->arrCampTasksInactive = $this->_tools->cleanSlashes($query->tasks__select(array(
			"campaign_id" => $intCampaign,
			'is_active' => $boolInactive
		)));
		$arrCampTasks = $this->_tools->cleanSlashes($query->tasks__select(array(
			"campaign_id" => $intCampaign,
			'is_active' => !$boolInactive
		)));
		// Create a hash of what host campaigns are already installed into this campaign
		$arrInstalledTasks = array();
		foreach ($arrCampTasks as $objCampTask)
		{
			if ($objCampTask->installed_task_id)
			{
				$arrInstalledTasks[$objCampTask->installed_task_id] = 1;
			}
		}
		$arrResults = array();
		// Add host tasks that have no yet been installed
		// to result set
		$arrHostTasks = array();
		$intOffsetSequence = 0;
		$arrHostTasksHash = array();
		foreach ($arrHostTasks as $objHostTask)
		{
			if (!isset($arrInstalledTasks[$objHostTask->task_id]))
			{
				$arrResults[$intOffsetSequence] = $objHostTask;
			}
			else
			{
				$arrHostTasksHash[$objHostTask->task_id] = $objHostTask;
			}
			$intOffsetSequence++;
		}
		foreach ($arrCampTasks as $objCampTask)
		{
			if ($objCampTask->installed_task_id)
			{
				$arrResults[$intOffsetSequence] = $objCampTask;
			}

			$intOffsetSequence++;
		}
		foreach ($arrCampTasks as $objCampTask)
		{
			if (!$objCampTask->installed_task_id)
			{
				$arrResults[$intOffsetSequence] = $objCampTask;
			}

			$intOffsetSequence++;
		}
		ksort($arrResults, SORT_NUMERIC);
		//dumper($arrResults,1,1);
		$this->view->arrTasks = $arrResults;
	}

	public function taskmanagerhostAction()
	{
		$this->view->campaign_id = $intCampaign = intval($this->_request->getParam("campaign_id"));
		if (!$intCampaign)
		{
			print text("Sorry, there was an error") . ": IC-TMH101-DSF89D";
			exit;
		}
		$intTask = intval(preg_replace("/^cti_/", "", $this->_request->getParam("task")));

		$objCampaigns = new Campaigns();
		$objMissions = new Missions();
		$objTasks = new Tasks();
		$objRoles = new Roles();
		$objClasses = new Classes();

		$objCampaign = $this->view->objCampaign = first($objCampaigns->_campaigns_select(array(
			"campaign_id" => $intCampaign
		)));
		if (!$objCampaign)
		{
			print text("Sorry, there was an error") . ": IC-TMH102-7DFG7F";
			exit;
		}

		if ($this->_request->isPost())
		{
			// Find the template campaign object
			$objTemplateCampaign = first($objCampaigns->_campaigns_select(array(
				"installed_campaign_id" => $intCampaign,
				"institution_id" => $this->_user_session_data->institution_id
			)));

			$objMission = first($objMissions->_missions_select(array(
				"campaign_id" => $intCampaign
			)));

			if ($intTask)
			{
				$objTask = first($objTasks->_tasks_select(array(
					"task_id" => $intTask
				)));
				if (!$objTask)
				{
					print text("Sorry, there was an error") . ": CI-TMH101-43R34D";
					exit;
				}
			}
			if ($this->_request->getPost("job") == "update_task")
			{
				$objTasks->_tasks_update(array(
					"where" => array(
						"task_id" => $intTask
					),
					"values" => array(
						"task_name" => $this->_request->getPost("task_name"),
						"points" => $this->_request->getPost("points"),
						"school_type" => $this->_request->getPost("school_type")
					)
				));
				print "saved";
				exit;
			}
			else if ($this->_request->getPost("job") == "add_camp_task")
			{
				// First find the latest sequence
				$objLatestTask = first($objTasks->_tasks_select(array(
					"institution_id" => $this->_user_session_data->institution_id,
					"campaign_id" => $intCampaign,
					"_ORDER" => "sequence + 0 DESC",
					"_LIMIT" => 1
				)));
				$intLatestSequence = $objLatestTask ? $objLatestTask->sequence : 0;
				// Do the insert
				$arrSql = array(
					"task_name" => $this->_request->getPost("task_name"),
					"sequence" => $intLatestSequence + 1,
					"campaign_id" => $objCampaign->campaign_id,
					"mission_id" => $objMission->mission_id,
					"institution_id" => $this->_user_session_data->institution_id,
					"points" => $this->_request->getPost("points"),
					"school_type" => $this->_request->getPost("school_type")
				);
				$intAutoId = $objTasks->_tasks_insert($arrSql);
				print $intAutoId;
				exit;
			}
			else if ($this->_request->getPost("job") == "delete_task")
			{
				if (isset($objTask) && $objTask)
					$objTasks->_tasks_delete(array(
						"task_id" => $objTask->task_id
					));
				print 1;
			}
			exit;
		}

		$arrHostTasks = $this->_tools->cleanSlashes($objTasks->_tasks_select(array(
			"campaign_id" => $intCampaign
		)));

		$arrResults = array();

		$intOffsetSequence = 0;
		foreach ($arrHostTasks as $objHostTask)
		{
			$arrResults[$intOffsetSequence] = $objHostTask;

			$intOffsetSequence++;
		}

		ksort($arrResults, SORT_NUMERIC);
		$this->view->arrTasks = $arrResults;
	}

	public function taskmanager2Action()
	{
		$this->view->campaign_id = $intCampaign = intval($this->_request->getParam("campaign_id"));
	}

	public function taskmanagerAction()
	{
		$this->view->campaign_id = $intCampaign = intval($this->_request->getParam("campaign_id"));
		$boolInactive = $this->view->boolInactive = $this->_request->getParam('inactive') ? 1 : 0;
		if (!$intCampaign)
		{
			print text("Sorry, there was an error") . ": IC-TM101-DSF89D";
			exit;
		}
		$intTask = intval(preg_replace("/^cti_/", "", $this->_request->getParam("task")));

		$objCampaigns = new Campaigns();
		$objMissions = new Missions();
		$objTasks = new Tasks();
		$objRoles = new Roles();
		$objClasses = new Classes();
		$query = new QueryGen();

		$objCampaign = $this->view->objCampaign = first($objCampaigns->_campaigns_select(array(
			"campaign_id" => $intCampaign
		)));
		if (!$objCampaign)
		{
			print text("Sorry, there was an error") . ": IC-TM102-7DFG7F";
			exit;
		}

		if ($this->_request->isPost())
		{
			// Find the template campaign object
			$objTemplateCampaign = first($objCampaigns->_campaigns_select(array(
				"installed_campaign_id" => $intCampaign,
				"institution_id" => $this->_user_session_data->institution_id
			)));

			$objMission = first($objMissions->_missions_select(array(
				"campaign_id" => $intCampaign
			)));

			if ($intTask)
			{
				$objTask = first($objTasks->_tasks_select(array(
					"task_id" => $intTask
				)));
				if (!$objTask)
				{
					print text("Sorry, there was an error") . ": CI-TM101-43R34D";
					exit;
				}
			}
			//dumper($this->_request->getPost(),0,1);
			if ($this->_request->getPost("job") == "update_task")
			{
				// First check if the task is installed
				if ($objTask->institution_id != $this->_user_session_data->institution_id) {
					// not installed, must do insert instead
					$arrNewParams = (array) $objTask;
					$arrNewParams["institution_id"] = $this->_user_session_data->institution_id;
					$arrNewParams["task_name"] = $this->_request->getPost("task_name");
					$arrNewParams["points"] = $this->_request->getPost("points") ? 1 : 0;
					$arrNewParams["is_checkbox"] = $this->_request->getPost("is_checkbox") == 'checked' ? 1 : 0;
					$arrNewParams["is_locked"] = $this->_request->getPost("is_locked") == 'checked' ? 1 : 0;
					$arrNewParams["installed_task_id"] = $arrNewParams["task_id"];
					$arrNewParams["campaign_id"] = $intCampaign;
					unset($arrNewParams["task_id"]);
					unset($arrNewParams["modifed"]);
					unset($arrNewParams["created"]);
					unset($arrNewParams["created_by"]);
					$intNewID = $query->tasks__insert($arrNewParams);
					print $intNewID;
					exit;
				}
				else
				{
					$query->tasks__update(array(
						"where" => array(
							"task_id" => $intTask
						),
						"values" => array(
							"task_name" => $this->_request->getPost("task_name"),
							"points" => $this->_request->getPost("points"),
							"is_checkbox" => $this->_request->getPost("is_checkbox") == 'checked' ? 1 : 0,
							"is_locked" => $this->_request->getPost("is_locked") == 'checked' ? 1 : 0
						)
					));
					print "saved";
					exit;
				}
			}
			else if ($this->_request->getPost("job") == "add_camp_task")
			{
				// First find the latest sequence
				$objLatestTask = first($objTasks->_tasks_select(array(
					"institution_id" => $this->_user_session_data->institution_id,
					"campaign_id" => $intCampaign,
					"_ORDER" => "sequence + 0 DESC",
					"_LIMIT" => 1
				)));
				$intLatestSequence = $objLatestTask ? $objLatestTask->sequence : 0;
				// Do the insert
				$arrSql = array(
					"task_name" => $this->_request->getPost("task_name"),
					"sequence" => $intLatestSequence + 1,
					"campaign_id" => $objCampaign->campaign_id,
					"mission_id" => 0,//$objMission->mission_id,
					"institution_id" => $this->_user_session_data->institution_id,
					"points" => $this->_request->getPost("points"),
					"is_checkbox" => $this->_request->getPost("is_checkbox") ? 1 : 0,
					"is_locked" => $this->_request->getPost("is_locked") ? 1 : 0
				);
				$intClass = intval($this->_request->getParam("class_id"));
				if ($objRoles->isRole("Teacher"))
				{
					if ($intClass < 1)
					{
						$objTeacherClass = first($objClasses->_user_classes_select(array(
							"user_id" => $this->_user_session_data->user_id,
							"class_role" => "Teacher"
						)));
						if (!$objTeacherClass)
						{
							print text("Sorry, there was an error") . ": CC-CCE101-DSF7DS";
							exit;
						}
						$intClass = $objTeacherClass->class_id;
					}
				}
				if ($intClass > 0)
					$arrSql["class_id"] = $intClass;

				$intAutoId = $query->tasks__insert($arrSql);
				print $intAutoId;
				exit;
			}
			else if ($this->_request->getPost("job") == "install_host_task")
			{
				$arrNewParams = (array) $objTask;
				$arrNewParams["institution_id"] = $this->_user_session_data->institution_id;
				$arrNewParams["task_name"] = $this->_request->getPost("task_name");
				$arrNewParams["points"] = $this->_request->getPost("points");
				$arrNewParams["installed_task_id"] = $arrNewParams["task_id"];
				$arrNewParams["campaign_id"] = $intCampaign;
				unset($arrNewParams["task_id"]);
				unset($arrNewParams["modifed"]);
				unset($arrNewParams["created"]);
				unset($arrNewParams["created_by"]);
				$intNewID = $objTasks->_tasks_insert($arrNewParams);
				print $intNewID;
			}
			else if ($this->_request->getPost("job") == "delete_task")
			{
				if (isset($objTask) && $objTask)
					$query->tasks__update(array(
						"where" => array(
							"task_id" => $objTask->task_id
						),
						"values" => array(
							"is_active" => 0
						)
					));
				print 1;
			}
			else if ($this->_request->getPost("job") == "reactivate")
			{
				if (isset($objTask) && $objTask)
					$query->tasks__update(array(
						"where" => array(
							"task_id" => $objTask->task_id
						),
						"values" => array(
							"is_active" => 1
						)
					));
				print 1;
			}
			exit;
		}

		$this->view->arrCampTasksInactive = $this->_tools->cleanSlashes($query->tasks__select(array(
			"campaign_id" => $intCampaign,
			'is_active' => $boolInactive
		)));
		$arrCampTasks = $this->_tools->cleanSlashes($query->tasks__select(array(
			"campaign_id" => $intCampaign,
			'is_active' => !$boolInactive
		)));
		// Create a hash of what host campaigns are already installed into this campaign
		$arrInstalledTasks = array();
		foreach ($arrCampTasks as $objCampTask)
		{
			if ($objCampTask->installed_task_id)
			{
				$arrInstalledTasks[$objCampTask->installed_task_id] = 1;
			}
		}
		$arrResults = array();
		// Add host tasks that have no yet been installed
		// to result set
		$arrHostTasks = array();
		$intOffsetSequence = 0;
		$arrHostTasksHash = array();
		foreach ($arrHostTasks as $objHostTask)
		{
			if (!isset($arrInstalledTasks[$objHostTask->task_id]))
			{
				$arrResults[$intOffsetSequence] = $objHostTask;
			}
			else
			{
				$arrHostTasksHash[$objHostTask->task_id] = $objHostTask;
			}
			$intOffsetSequence++;
		}
		foreach ($arrCampTasks as $objCampTask)
		{
			if ($objCampTask->installed_task_id)
			{
				$arrResults[$intOffsetSequence] = $objCampTask;
			}

			$intOffsetSequence++;
		}
		foreach ($arrCampTasks as $objCampTask)
		{
			if (!$objCampTask->installed_task_id)
			{
				$arrResults[$intOffsetSequence] = $objCampTask;
			}

			$intOffsetSequence++;
		}
		ksort($arrResults, SORT_NUMERIC);
		$this->view->arrTasks = $arrResults;
	}

	public function taskeditorAction()
	{
		$_VERBOSE = 0;

		$intItemsPerPage = $this->view->intItemsPerPage = 10;
		$this->view->intTotalLines = 0;
		$objTasks = new Tasks();
		$objMissions = new Missions();
		$objCampaigns = new Campaigns();

		$this->view->mission_id = $intMission = intval($this->_request->getParam("mission_id"));
		if (!$intMission)
		{
			print text("Sorry, there was an error") . ": CI-TE101-DD7F6S";
			exit;
		}

		$objMission = first($objMissions->_missions_select(array(
			"mission_id" => $intMission
		)));
		if (!$objMission || $objMission->mission_type != "Incremental")
		{
			print text("Sorry, there was an error") . ": CI-TE101-SD7F7D";
			exit;
		}

		$objCampaign = $this->view->objCampaign = first($objCampaigns->_campaigns_select(array(
			"campaign_id" => $objMission->campaign_id
		)));

		$intTotalLines = $this->view->intTotalLines = first(first($objTasks->_tasks_select(array(
			"campaign_id" => $objMission->campaign_id,
			"_COUNT" => true
		))));

		if ($this->_request->getParam("load_ajax") == "true")
		{
			$intPage = $this->_request->getParam("page");
			if ($intPage < 0)
			{
				print text("Sorry, there was an error") . ": BC-BE101-F6G8F6";
				exit;
			}

			$arrTasks = $objTasks->_tasks_select(array(
				"campaign_id" => $objMission->campaign_id,
				"mission_id" => $intMission,
				"_LIMIT" => ($intPage * $intItemsPerPage) . "," . $intItemsPerPage
			));

			foreach ($arrTasks as $intKey => $objTask)
			{
				$arrTasks[$intKey]->task_name = preg_replace("/\\\\+/", "", $objTask->task_name);
			}
			print json_encode(array(
				"book_lines" => $arrTasks,
				"lines_count" => $intTotalLines
			));
			exit;
		}

		if($this->_request->isPost()) // Save / update book data w/ ajax
		{
			//var_dump($this->_request->getPost());exit;
			$arrLines = array();
			// Parse the lines
			$arrItems = $this->_request->getPost();
			foreach ($arrItems as $strKey => $strValue)
			{
				if (preg_match("/row_([0-9]+)(_+)col_(.+)/", $strKey, $arrMatched))
				{
					$arrLines[$arrMatched[1]][$arrMatched[2]][$arrMatched[3]] = $strValue;
				}
			}
			// Loop through the lines and update / insert the new data
			foreach ($arrLines as $intKey => $arrItem)
			{
				foreach ($arrItem as $strFlag => $arrLine)
				{
					if (!isset($arrLine["line_data"]) || $arrLine["line_data"] == "")
					{
						continue;
					}
					// First check if the line already exists
					if ($strFlag == "__") // If the line was just created from the ui an added underscore is available
					{
						$intTaskId = $objTasks->task_insert(array(
							"task_name" => $arrLine["line_data"],
							"sequence" => $intKey,
							"campaign_id" => $objMission->campaign_id,
							"mission_id" => $intMission,
							"institution_id" => $this->_user_session_data->institution_id,
							"points" => $arrLine["points"]
						));
						if ($_VERBOSE)
							print "insert_line:$intTaskId,";
					}
					else
					{
						$boolSuccess = $objTasks->_tasks_update(array(
							"where" => array(
								"sequence" => $intKey,
								"mission_id" => $intMission
							),
							"values" => array(
								"task_name" => $arrLine["line_data"],
								"points" => $arrLine["points"]
							)
						));
						if ($_VERBOSE)
							print "update_line:$boolSuccess,";
					}
				}
			}

			print 1;
			exit; // ajax end
		}

		if ($this->_request->getParam("del_item") == "true")
		{
			$intItemId = intval($this->_request->getParam("item_id"));
			if (!$intItemId)
			{
				print text("Sorry, there was an error") . ": CB-BE101-S8D9S9";
				exit;
			}
			$boolResult = $objTasks->_tasks_delete(array(
				"sequence" => $intItemId,
				"mission_id" => $intMission
			));
			print $boolResult;
			exit;
		}
	}
}
?>