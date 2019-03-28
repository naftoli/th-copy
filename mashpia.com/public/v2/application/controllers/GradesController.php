<?php
class GradesController extends Zend_Controller_Action
{
	private $_user_session_data;
	private $_tools;
	private $objPermissions; // permission class
	private $objPermission; // permission instance
	private $boolVerbose = 0;

	function preDispatch()
	{
		$this->objPermissions = new Permissions();
		$this->_user_session_data = new Zend_Session_Namespace('user_session_data');
		//$arrParams = $this->_request->getParams();
		//$utilities = new Utilities();
		//$this->objPermission = $utilities->dispatch_helper($arrParams);
	}

	public function indexAction()
	{

	}

	public function gradesinstitutionsAction()
	{
		$objListInstitutions = new Institutions();

		if(isset($this->_request->host_id)) $this->view->host_id = $this->_request->getParam('host_id');
		if(isset($this->_request->network_id)) $this->view->network_id = $this->_request->getParam('network_id');
		if(isset($this->_request->institution_id)) $this->view->institution_id = $this->_request->getParam('institution_id');
		if(isset($this->_request->grade_id)) $this->view->grade_id = $this->_request->getParam('grade_id');

		// Check if the form has been posted
		if ($this->_request->isGet())
		{
			$this->view->is_active = $this->_request->getParam("status");
			$this->view->intHost = $this->_request->getParam("host_id");
			$this->view->intNetwork = $this->_request->getParam("network_id");
			$this->view->arrInstitutions = $objListInstitutions->institutions_select($this->view->intHost,$this->view->intNetwork,$this->view->is_active);
		}
	}

	public function gradeaddhostsAction()
	{
		if(isset($this->_request->host_id)) $this->view->host_id = $this->_request->getParam('host_id');
		if(isset($this->_request->network_id)) $this->view->network_id = $this->_request->getParam('network_id');
		if(isset($this->_request->institution_id)) $this->view->institution_id = $this->_request->getParam('institution_id');

		$objInstitutionAddHosts = new Institutions();
		$this->view->all_hosts = $objInstitutionAddHosts->get_all_hosts();
	}

	public function gradeshostlistAction()
	{
		$objListAllInstitutionsHosts = new Institutions();
		$this->view->intHost = $objListAllInstitutionsHosts->get_all_hosts();
	}

	public function gradenetworksAction()
	{
		$objInstitutionNetworks = new Institutions();
		$this->view->intHost = $this->_request->getParam("host_id");
		$this->view->arrNetworks = $objInstitutionNetworks->network_list($this->view->intHost);
	}

	public function gradeaddnetworksAction()
	{
		if(isset($this->_request->host_id)) $this->view->host_id = $this->_request->getParam('host_id');
		if(isset($this->_request->network_id)) $this->view->network_id = $this->_request->getParam('network_id');
		if(isset($this->_request->institution_id)) $this->view->institution_id = $this->_request->getParam('institution_id');

		$objInstitutionAddNetworks = new Institutions();
		$intHostId = $this->_request->getParam("host_id");
		if(!isset($this->_request->host_id)){
			$this->view->all_networks = $objInstitutionAddNetworks->get_all_of_the_networks();
		} else {
			$this->view->all_networks = $objInstitutionAddNetworks->get_all_networks($intHostId);
		}

	}

	public function gradesaddAction()
	{
		if(isset($this->_request->host_id)) $this->view->host_id = $this->_request->getParam('host_id');
		if(isset($this->_request->network_id)) $this->view->network_id = $this->_request->getParam('network_id');
		if(isset($this->_request->institution_id)) $this->view->institution_id = $this->_request->getParam('institution_id');


		if ($this->_request->isPost()){

			$objGrade = new Grades();

			if(isset($this->_request->institution_id)){
				$institution_id = $this->_request->getParam('institution_id');
			} elseif(isset($this->_request->network_id)){
				$institution_id = $this->_request->getParam('network_id');
			} elseif(isset($this->_request->host_id)){
				$institution_id = $this->_request->getParam('host_id');
			} else {
				echo 'Error: No institution ID specified.';
				exit;
			}
			$date = date('Y-m-d H:i:s', time());
			$grade_name = $this->_request->getParam('name');
			$grade_hierarchy = $this->_request->getParam('hierarchy');

			//check for duplicates
			if($objGrade->grade_is_duplicate($grade_hierarchy, $institution_id, 'hierarchy')){
				echo 'A grade with the same hierachy already exists';
				exit;
			}elseif($objGrade->grade_is_duplicate($grade_name, $institution_id, 'name')){
				echo 'A grade with the same name already exists';
				exit;
			}

			$arrInsert = array('institution_id' 	=> $institution_id,
							   'grade_name'			=> $grade_name,
							   'grade_hierarchy'	=> $grade_hierarchy,
							   'created'			=> $date,
							   'created_by'			=> $this->_user_session_data->user_id);
			$result = $objGrade->insert_grade($arrInsert);
			echo $result;
			exit;
		}
	}

	public function gradeinstitutionaddAction()
	{
		$objInstitution = new Institutions();

		if(isset($this->_request->host_id)) $this->view->host_id = $this->_request->getParam('host_id');
		if(isset($this->_request->network_id)) $this->view->network_id = $this->_request->getParam('network_id');
		if(isset($this->_request->institution_id)) $this->view->institution_id = $this->_request->getParam('institution_id');

		if(isset($this->_request->institution_id)){
			$this->view->arrInstitutions = $objInstitution->get_institution($this->_request->getParam('institution_id'));
		} elseif(isset($this->_request->network_id)){
			$this->view->arrInstitutions = $objInstitution->get_institutions_by_network_id($this->_request->getParam('network_id'));
		} elseif(isset($this->_request->host_id)){
			$this->view->arrInstitutions = $objInstitution->get_institutions_by_host_id($this->_request->getParam('host_id'));
		} else {
			$this->view->arrInstitutions = $objInstitution->get_all_of_the_institutions();
		}
	}

	public function gradeslistAction()
	{
		// Automatically inherit

		if (isset($this->_request->host_id))
		{
			$this->view->host_id = $intInstitution = $host_id = $this->_request->getParam("host_id");
		}
		else if (isset($this->_request->network_id))
		{
			$this->view->network_id = $intInstitution = $network_id = $this->_request->getParam("network_id");
		}
		else if (isset($this->_request->institution_id))
		{
			$this->view->institution_id = $intInstitution = $this->_request->getParam("institution_id");
		}
		else
		{
			$this->view->institution_id = $intInstitution = $this->_user_session_data->institution_id;
		}
		$objGrades = new Grades();
		$objInstitutions = new Institutions();
		$arrGrades = $this->view->arrGrades = $objGrades->_grades_select(array(
			"institution_id" => $intInstitution
		));
		if (!count($arrGrades))
		{
			$objInstitution = current($objInstitutions->_institutions_select(array(
				"institution_id" => $intInstitution
			)));
			if ($objInstitution->network_id)
			{
				$arrGrades = $this->view->arrGrades = $objGrades->_grades_select(array(
					"institution_id" => $objInstitution->network_id
				));
			}
			if (!count($arrGrades) && $objInstitution->host_id)
			{
				$arrGrades = $this->view->arrGrades = $objGrades->_grades_select(array(
					"institution_id" => $objInstitution->host_id
				));
			}
		}
	}

	public function gradeseditAction()
	{
		$objGrade = new Grades();
		if(isset($this->_request->host_id)) $this->view->host_id = $this->_request->getParam('host_id');
		if(isset($this->_request->network_id)) $this->view->network_id = $this->_request->getParam('network_id');
		if(isset($this->_request->institution_id)) $this->view->institution_id = $this->_request->getParam('institution_id');
		if(isset($this->_request->grade_id)) $this->view->grade_id = $this->_request->getParam('grade_id');

		$this->view->arrGrade = $objGrade->get_grade_by_id($this->_request->getParam('grade_id'));

		if($this->_request->isPost()){
			$name = trim($this->_request->getParam('name'));
			$institution_id = $this->_request->getParam('institution_id');
			$grade_id = $this->_request->getParam('grade_id');

			$arrUpdate = array('grade_name' 		=> $name,
							   'institution_id'		=> $institution_id,
							   'grade_id'			=> $grade_id);
			$result = $objGrade->grade_update($arrUpdate);
			exit;
		}
	}
	public function gradeseditorAction()
    {
		$query = new QueryGen();
		$objGrades = new Grades();
		$objClasses = new Classes();

		$this->view->institution_id = $intInstitution = $this->_user_session_data->institution_id;

		//select grades that belong to an institution
		$arrGrades = $objGrades->_grades_select(array(
			"institution_id" => $intInstitution
		));
		$arrGradeIds = array_hash("grade_id", $arrGrades);
		// Note: for now grades and classes are being considered 1:1
		// because of this we will now load the classes that are associated
		// to them. And treat all grades as if they are grades and classes
		// together.
		$arrClasses = $objClasses->_classes_select(array(
			"grade_id" => array_keys($arrGradeIds)
		));
		$arrClasses = array_hash("grade_id", $arrClasses);
		$arrResult = array();
		foreach ($arrGrades as $objGrade)
		{
			if (isset($arrClasses[$objGrade->grade_id]))
				$arrResult[] = array(
					"objGrade" => $objGrade,
					"objClass" => $arrClasses[$objGrade->grade_id]
				);
		}

		$boolAjax = $this->_request->getParam("ajax");
		$intGrade = intval($this->_request->getParam("grade_id"));
		if ($boolAjax) // Display grades
		{
			print json_encode($arrResult);
			exit;
		}
		elseif ($intGrade) // Move up, down, or delete
		{
			if ($this->_request->getParam("up"))
			{
				$boolResult = $objGrades->move_hierarchy(array(
					"move" => "up",
					"grade_id" => $intGrade
				));
				print $boolResult;
				exit;
			}
			else if ($this->_request->getParam("down"))
			{
				$boolResult = $objGrades->move_hierarchy(array(
					"move" => "down",
					"grade_id" => $intGrade
				));
				print $boolResult;
				exit;
			}
			else if ($this->_request->getParam("delete"))
			{
				$boolResult = $objGrades->_grades_delete(array(
					"grade_id" => $intGrade
				));
				$arrClassIds = object_extract("class_id", $query->classes__select(array(
					"grade_id" => $intGrade
				)));
				$query->user_classes__delete(array(
					"class_id" => $arrClassIds
				));
				$objClasses->_classes_delete(array(
					"grade_id"	=> $intGrade
				));
				$query->prize_classes__delete(array(
					"class_id" => $arrClassIds
				));
				print $boolResult;
				exit;
			}
		}
		if($this->_request->isPost()) // Save / update grades
		{
			if ($this->_request->getParam("update")) // Update multipule grades
			{
				// Loop through the fields and complete the updates
				$intUpdatedCount = 0;
				$intItr=-1;
				$arrResult = array();
				while ($this->_request->getPost((++$intItr) . "_name"))
				{
					$intGradeId = intval($this->_request->getPost($intItr . "_grade_id"));
					if (!isset($arrClasses[$intGradeId]))
					{
						$arrResult["error"] = text("Sorry, there was an error") . ": CM-ME101-9SDFD9";
						break;
					}
					$strName = preg_replace("/[^a-z0-9 ]/i", "", $this->_request->getPost($intItr . "_name"));
					$strSub = preg_replace("/[^a-z0-9 ]/i", "", $this->_request->getPost($intItr . "_sub"));
					if (
						$arrClasses[$intGradeId]->sub == $strSub
						&& $arrClasses[$intGradeId]->grade == $strName
					) {
						continue;
					}
					if (!$intGradeId)
					{
						$arrResult["error"] = text("Sorry, there was an error") . ": CM-ME102-SD089D";
						break;
					}
					if (!strlen($strName))
					{
						$arrResult["error"][$intItr . "_name"] = text("You must include a name for the class you are attempting to insert.");
						continue;
					}
					$objClass = first($objClasses->_classes_select(array(
						"grade"				=> $strName,
						"sub"				=> $strSub,
						"institution_id"	=> $intInstitution
					)));
					if ($objClass && $objClass->grade == $strName)
					{
						$arrResult["error"][$intItr . "_sub"] = "This class already exists. You cannot add a class that already exists.";
						continue;
					}

					$objGrades->_grades_update(array(
						"where" => array(
							"grade_id"	    	 => $intGradeId
						),
						"values" => array(
							"grade_name"	     => $strName
						)
					));
					$intUpdatedCount += $objClasses->_classes_update(array(
						"where" => array(
							"grade_id"	    	 => $intGradeId
						),
						"values" => array(
							"grade"				=> $strName,
							"sub"				=> $strSub
						)
					));
				}
				if (!isset($arrResult["error"]))
				{
					$arrResult["success"] = "true";
					$arrResult["count"] = $intUpdatedCount;
				}
				print json_encode($arrResult);
				$this->_helper->viewRenderer->setNoRender();
				return;
			}
			else // Save new
			{
				// A bit of validation
				$strName = preg_replace("/[^a-z0-9 ]/i", "", $this->_request->getPost("grade_name"));
				$strSub = (string) preg_replace("/[^a-z0-9 ]/i", "", $this->_request->getPost("grade_sub"));
				if (!strlen($strName))
				{
					print text("You must include a name for the class you are attempting to insert.");
					$this->_helper->viewRenderer->setNoRender();
					return;
				}

				$objClass = first($objClasses->_classes_select(array(
					"grade" => $strName,
					"sub" => $strSub,
					"institution_id"	=> $intInstitution
				)));
				if ($objClass)
				{
					print text("This class already exists. You cannot add a class that already exists.");
					$this->_helper->viewRenderer->setNoRender();
					return;
				}

				$intGradeId = $objGrades->_grades_insert(array(
					"institution_id"	=> $intInstitution,
					"grade_hierarchy"	=> count($arrGrades),
					"is_active"			=> 1,
					"grade_name"	    => $strName
				));

				$query->classes__insert(array(
					"institution_id"	=> $intInstitution,
					"class_hierarchy"	=> count($arrGrades),
					"grade"				=> $strName,
					"sub"				=> $strSub,
					"grade_id"			=> $intGradeId
				));

				print $intGradeId;
				$this->_helper->viewRenderer->setNoRender();
				return;
			}
		}
	}
}

?>