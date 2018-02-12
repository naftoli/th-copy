<?php

	class SchedulerController extends Zend_Controller_Action
	{
		private $_user_session_data;

		//

		function init()
		{}

		function preDispatch()
		{
			// Get the session object
			$this->_user_session_data = new Zend_Session_Namespace('user_session_data');

			//instantiate feeds
			$this->_feeds = new Feeds();
			/*
			if ($this->_user_session_data)
			{
				if (
					empty($this->_user_session_data->user_id)
					|| empty($this->_user_session_data->institution_id)
					|| empty($this->_user_session_data->permission)
					|| !$this->_user_session_data->is_user_active
				) {
					// Not allowed in
					$this->_redirect('logout');
				}
			}
			else
			{
				// Not allowed in
				$this->_redirect('logout');
			}
			*/
		}

		public function rulescheduleAction()
		{
			$objScheduler = new Scheduler();
			//load_mission_params

		}

		public function calendarAction()
		{
			$objTasks = new Tasks();
			$objMissions = new Missions();
			$intTask = $this->view->task_id = $this->_request->getParam("task_id");
			if (isset($intTask) && $intTask)
				$objTask = $this->view->objTask = $objTasks->_tasks_select(array(
					"task_id" => $intTask
				));
			$intMission = $this->view->mission_id = $this->_request->getParam("mission_id");
			if (isset($intMission) && $intMission)
				$objMission = $this->view->objMission = $objMissions->_missions_select(array(
					"mission_id" => $intMission
				));
			// For the purpose of pre populating the input box in the view
			$this->view->objValues = (isset($intTask) && $intTask) ? $objTask : $objMission;


			//
			// ajax start
			$objScheduler = new Scheduler();
			if ($this->_request->isPost())
			{
				if ($this->_request->getPost("preview") != "true")
				{
					$Result = $objScheduler->load_mission(array(
						"mission_id" => $intMission,
						"json_encode" => true,
						"calendar_format" => true
					));
					print $Result;
					exit;
				}
				else
				{ // Preview from parameters
					// Possible parameters for the scheduler
					$arrParams = array(
						"years" => $this->_request->getPost("years"),
						"weeks_in_year" => $this->_request->getPost("weeks_in_year"),
						"days_in_year" => $this->_request->getPost("days_in_year"),
						"months" => $this->_request->getPost("months"),
						"weeks_in_month" => $this->_request->getPost("weeks_in_month"),
						"days_in_month" => $this->_request->getPost("days_in_month"),
						"days_of_week" => $this->_request->getPost("days_of_week"),
						"hours_in_day" => $this->_request->getPost("hours_in_day"),
						"minutes_in_hour" => $this->_request->getPost("minutes_in_hour"),
						"frequency" => $this->_request->getPost("frequency"),
						"start_time" => $this->_request->getPost("start_time"),
						"expiration" => $this->_request->getPost("expiration"),
						"task_id" => $this->_request->getParam("task_id"),
						"mission_id" => $this->_request->getParam("mission_id")
					);

					$boolDebug = $this->_request->getParam("debug") == "1" ? 1 : 0;
					if ($boolDebug)
					{
						$objScheduler->_VERBOSE = 1;
					}
					else
					{
						$objScheduler->_VERBOSE = 0;
					}

					// Define the conditions of the incrament
					$boolParamSuccess = $objScheduler->params($arrParams);
					if (!$boolParamSuccess)
					{
						print "You must include at least one valid parameter.";
						exit;
					}
					$arrNewProcess = array(
						"proc_pointer" => date("U", mktime(0,0,0,1,1,date("Y", time())))
					);
					if (!isset($arrParams["task_id"]))
					{
						// Start a process from any time of any day desired
						$objScheduler->new_process($arrNewProcess);
						//$arrProcess =
						$objScheduler->process(array(
							/* set custom config settings */
							//"max_iterations" => 365
							//"max_years" => 10
						));
					}
					else
					{
						$arrWrapperParams = array(
							/* set custom config settings */
							//"max_iterations" => 365
							//"max_years" => 10
							"refactor_date" => "true"
						);
						if (!$boolDebug)
							$arrWrapperParams["encoding"] = "json";
						$Result = $objScheduler->calendar_wrapper($arrWrapperParams);
					}
					// Retrieve dates in json & ready for calendar import
					if ($boolDebug)
					{
						$intProcessingTime = $objScheduler->benchmarking(array(
							"calculate" => "sum"
						));
						print "\nBenchmarking: " . $intProcessingTime . " <br>\n";
						if (!isset($Result))
						{
							$Result = $objScheduler->export(array(
								//"encoding" => "json",
								"format" => "calendar"
							));
						}
						var_dump($Result);
					}
					else
					{
						if (!isset($Result))
						{
							$Result = $objScheduler->export(array(
								"encoding" => "json",
								"format" => "calendar"
							));
						}
						print $Result;
					}
					exit;
				}
			}
		}

		public function scheduleeditAction()
		{
			$objScheduler = new Scheduler();
			$objTasks = new Tasks();
			$objMissions = new Missions();
			$intTask = $this->view->task_id = $this->_request->getParam("task_id");
			$intMission = $this->view->mission_id = $this->_request->getParam("mission_id");
			if (!$intMission && !$intTask)
			{
				print text("Sorry, there was an error") . ": CS-SchE101-S7DFDS";
				exit;
			}
			if (isset($intTask) && $intTask)
				$objTask = $this->view->objTask = current($objTasks->_tasks_select(array(
					"task_id" => $intTask
				)));
			if (isset($intMission) && $intMission)
				$objMission = $this->view->objMission = current($objMissions->_missions_select(array(
					"mission_id" => (isset($intMission) && $intMission) ? $intMission : $objTask->mission_id
				)));
			// For the purpose of pre populating the input box in the view
			if (isset($intTask) && $intTask)
				// Load the task schedule
				$this->view->objParams = current($objScheduler->_scheduling_params_select(array(
					"task_id" => $intTask
				)));
			else
				// Load the mission schedule
				$this->view->objParams = current($objScheduler->_scheduling_params_select(array(
					"mission_id" => $intMission,
					"task_id" => 0
				)));

			// Ajax start
			if (
				$this->_request->isPost()
			) {
				$arrParams = array(
					"years" => $this->_request->getPost("years"),
					"weeks_in_year" => $this->_request->getPost("weeks_in_year"),
					"days_in_year" => $this->_request->getPost("days_in_year"),
					"months" => $this->_request->getPost("months"),
					"weeks_in_month" => $this->_request->getPost("weeks_in_month"),
					"days_in_month" => $this->_request->getPost("days_in_month"),
					"days_of_week" => $this->_request->getPost("days_of_week"),
					"hours_in_day" => $this->_request->getPost("hours_in_day"),
					"minutes_in_hour" => $this->_request->getPost("minutes_in_hour"),
					"frequency" => $this->_request->getPost("frequency"),
					"start_time" => $this->_request->getPost("start_time"),
					"expiration" => $this->_request->getPost("expiration"),
					"task_id" => $this->_request->getParam("task_id"),
					"mission_id" => $this->_request->getParam("mission_id")
				);
				if (
					isset($arrParams["task_id"])
					&& $arrParams["task_id"]
				) {
					$objTasks = new Tasks();
					$objTask = current($objTasks->_tasks_select(array(
						"task_id" => $arrParams["task_id"]
					)));
					if (!$objTask)
					{
						print text("Sorry, there was an error") . ": CSch-SE101-S6D5FD";
						exit;
					}
					$arrParams["mission_id"] = $objTask->mission_id;
				}
				$boolParamSuccess = $objScheduler->params($arrParams);
				if (!$boolParamSuccess)
				{
					print "You must include at least one valid parameter.";
					exit;
				}

				// Commit the params to the scheduling_params table
				$intAI = $objScheduler->insert_params();
				if ($intAI)
					print 1;
				exit;
			}
			// Ajax end
		}
	}
?>