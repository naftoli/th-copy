<?php
class StoreController extends Zend_Controller_Action
{
	private $_user_session_data;
	private $objPermission; // permission instance
	private $boolVerbose = 0;

	function init()
	{
		// This is required for the flash uploader as it cannot handle php sessions properly
		if ($this->_request->getPost('sid'))
		{ Zend_Session::setId($this->_request->getPost('sid')); }
		Zend_Session::start();
	}

	function preDispatch()
	{
		$query = new QueryGen();
		$arrParams = $this->_request->getParams();
		unset($arrParams["controller"], $arrParams["action"], $arrParams["module"]);
		$strParam = preg_replace("/[&=]+/", "/", http_build_query($arrParams));

		// Load thie session array
		$this->_user_session_data = new Zend_Session_Namespace('user_session_data');
		if (!$this->_user_session_data->institution_id) {
			$this->_redirect('logout');
		}
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

	function barcodeprintAction()
	{
		$query = new QueryGen();
		$this->view->strBarCode = $this->_request->getParam("barcode");
		$this->view->strPrice = $this->_request->getParam("price");
		$intPrize = $this->_request->getParam("prize_id");
		$this->view->objPrize = first($query->prize__select(array(
			"prize_id" => $intPrize
		)));
	}

	// PRIZES /////////////////////////////////////////////////////////////////////////////////////////////////////////

	function prizeinstallAction()
	{
		$query = new QueryGen();
		$objInstitutions = new Institutions();
		$objStore = new Store();

		$objInstitution = first($objInstitutions->_institutions_select(array(
			"institution_id" => $this->_user_session_data->institution_id
		)));
		if (!$objInstitution)
		{
			print text("Sorry, there was an error") . ": CS-PI101-S9DF8D";
			exit;
		}
		// Ajax
		if ($this->_request->isPost())
		{
			$intPrize = intval($this->_request->getPost("prize_id"));
			$intTemplatePrizeId = intval($this->_request->getPost("template_prize_id"));
			if (!$intPrize && !$intTemplatePrizeId)
			{
				print text("Sorry, there was an error") . ": CS-PI102-9DS8FD";
				exit;
			}
			$strAction = $this->_request->getPost("action");
			if ($strAction == "install") {

				$intSuccess = $objStore->prizes_install($intTemplatePrizeId);
			} else {
				$intSuccess = $objStore->prizes_uninstall($intPrize);
			}

			print $intSuccess;
			exit;
		}

		$arrHostPrizes = $query->prize__select(array(
			"institution_id" => $objInstitution->host_id,
			"parent_prize_id" => 0,
			"prize_type" => "Installable"
		));
		$arrInstitutionPrizes = array_hash("template_prize_id", $objStore->_prizes_select(array(
			"institution_id" => $objInstitution->institution_id
		)));
		$arrPrizes = array();
		foreach ($arrHostPrizes as $objHostPrize)
		{
			if (isset($arrInstitutionPrizes[$objHostPrize->prize_id]))
			{
				$arrPrizes[] = $arrInstitutionPrizes[$objHostPrize->prize_id];
			}
			else
			{
				$arrPrizes[] = $objHostPrize;
			}
		}


		foreach ($arrPrizes as $intKey => $objPrize)
		{
			if ($objPrize->prize_name == "Tanya")
				unset($arrPrizes[$intKey]);
		}

		$this->view->arrPrizes = $arrPrizes;
	}

	function copyprizetocampAction()
	{
		$query = new QueryGen();
		$auto = new Automation();
		$intPrize = $this->_request->getParam("prize_id");
		$objPrize = first($query->prize__select(array(
			'prize_id' => $intPrize,
			'institution_id' => $this->_user_session_data->institution_id
		)));
		if (!$objPrize) {
			print "Sorry, there was an error: CS-CPTC-kfjn2f";
			exit;
		}
		print json_encode(
			$auto->copy_prize_to_camp($intPrize)
		);
		exit;
	}

 	function prizeAction()
	{
		$query = new QueryGen();

		global $arrTemplateTypes;

		$objRoles = $this->view->objRoles = new Roles();
		//dumper($objRoles, 1);
		$objStore = new Store();
		$objInstitutions = new Institutions();
		$objRules = new Rules();
		$objClasses = new Classes();
		$intInstitution = $this->_user_session_data->institution_id;
		if ($objRoles->isAllowed('Network')) {
			if ($this->_request->getParam('institution_id'))
				$intInstitution = $this->_request->getParam('institution_id');
		}
		$this->view->tstyle = $this->_request->getParam("tstyle");

		if ($this->_request->getParam("gen_bar_code") == "true")
		{
			// Generate a unique order code
			while (1)
			{
				$arrDataSource = explode(",", "1,2,3,4,5,6,7,8,9,0");
				$strBarCode =
					$arrDataSource[rand(0, count($arrDataSource)-1)]
					. $arrDataSource[rand(0, count($arrDataSource)-1)]
					. $arrDataSource[rand(0, count($arrDataSource)-1)]
					. $arrDataSource[rand(0, count($arrDataSource)-1)]
					. $arrDataSource[rand(0, count($arrDataSource)-1)]
					. $arrDataSource[rand(0, count($arrDataSource)-1)]
					. $arrDataSource[rand(0, count($arrDataSource)-1)]
					. $arrDataSource[rand(0, count($arrDataSource)-1)]
					. $arrDataSource[rand(0, count($arrDataSource)-1)]
					. $arrDataSource[rand(0, count($arrDataSource)-1)]
					. $arrDataSource[rand(0, count($arrDataSource)-1)]
					. $arrDataSource[rand(0, count($arrDataSource)-1)]
					. $arrDataSource[rand(0, count($arrDataSource)-1)]
					. $arrDataSource[rand(0, count($arrDataSource)-1)]
					. $arrDataSource[rand(0, count($arrDataSource)-1)]
					. $arrDataSource[rand(0, count($arrDataSource)-1)]
					. $arrDataSource[rand(0, count($arrDataSource)-1)]
					. $arrDataSource[rand(0, count($arrDataSource)-1)]
					. $arrDataSource[rand(0, count($arrDataSource)-1)]
					. $arrDataSource[rand(0, count($arrDataSource)-1)];
				$objPrizeFound = first($query->prize__select(array(
					"bar_code" => $strBarCode
				)));
				if (!$objPrizeFound)
					break;
			}
			print json_encode(array(
				"success" => "true",
				"bar_code" => $strBarCode
			));
			exit;
		}

		$this->view->intPrize = $intPrize = intval($this->_request->getParam("prize_id"));
		$this->view->intParentPrize = $intParentPrize = $this->_request->getParam('parent_prize_id');
		$this->view->intTemplate = $intTemplate = intval($this->_request->getParam("template_id"));
		$this->view->arrPrizeClasses = $arrPrizeClasses = array();
		$this->view->objInstitution = first($query->institutions__select(array(
			"institution_id" => $intInstitution
		)));
		
		if ($objRoles->isRole("Teacher"))
		{
			$arrClasses = array_hash("class_id", $objClasses->_classes_select(array(
				"class_id" => $this->_user_session_data->class_id
			)));
			$this->view->strPrizeClasses = $this->_user_session_data->class_id . '=1';
			
		}
		else
		{
			$arrClasses = array_hash("class_id", $objClasses->_classes_select(array(
				"institution_id" => $intInstitution
			)));
		}

		$strPrizeClasses = "";
		$arrPrizeSizes = $this->view->arrPrizeSizes = array();

		if ($intTemplate > 0) // Load template
		{
			$objPrize = first($query->prize__select(array(
				"prize_id" => $intTemplate
			)));
			$objPrize->prize_id = 0;
			$this->view->objPrize = $objPrize;
			if (!$objPrize)
			{
				$this->view->boolPrizeNotFound = true;
				return;
			}
			$this->view->boolTemplate = true;
		}
		else if ($intPrize) // Load prize
		{
			$arrPrizeSizes = $this->view->arrPrizeSizes = $query->prize_sizes__select(array(
				"prize_id" => $intPrize,
				"_ORDER" => "prize_size_hierarchy + 0 ASC"
			));
			$this->view->objPrize = $objPrize = first($query->prize__select(array(
				"prize_id" => $intPrize
			)));
			if (!$objPrize)
			{
				$this->view->boolPrizeNotFound = true;
				return;
			}
			
			//print_r($this->view->objPrize->image_id);
			//print_r(get_class_methods ($query));
			// useless code 
			//$imgs = new Imgs();
			//$this->view->objImage = $imgs->_imgs_select(["img_id" => $this->view->objPrize->image_id]);

			$this->view->boolEditable = $objPrize->prize_type == "School Installed" ? false : true;

			$arrRules = $objRules->_rules_select(array(
				"prize_id" => $intPrize
			));
			$this->view->rule_exists = count($arrRules);

			$this->view->arrPrizeClasses = $arrPrizeClasses = first($objStore->_prize_classes_select(array(
				"prize_id" => $intPrize
			)));

			if($objRoles->isAllowed('Super Administrator'))
			{
				// Serialized school types prepared for the checklist module
				$this->view->arrPrizeSchoolTypes = $arrPrizeSchoolTypes = $objStore->_prize_school_types_select(array(
					"prize_id" => $intPrize
				));
				$strPrizeSchoolTypes = "";
				foreach ($arrPrizeSchoolTypes as $objPrizeSchoolType)
				{
					$strPrizeSchoolTypes .= $objPrizeSchoolType->school_type . "=1&";
				}
				$strPrizeSchoolTypes = rtrim($strPrizeSchoolTypes, "&");
				$this->view->strPrizeSchoolTypes = $strPrizeSchoolTypes;

			} else if (
				$objRoles->isRole('Teacher')
				|| $objRoles->isRole('Institution Administrator')
			) {
				// Serialized classes prepared for the checklist module
				$this->view->arrPrizeClasses = $arrPrizeClasses = $query->prize_classes__select(array(
					"prize_id" => $intPrize
				));
				foreach ($arrPrizeClasses as $objPrizeClass)
				{
					$strPrizeClasses .= $objPrizeClass->class_id . "=1&";
				}
				$strPrizeClasses = rtrim($strPrizeClasses, "&");
				$this->view->strPrizeClasses = $strPrizeClasses;
			}

		}

		// Handle a post request
		if ($this->_request->isPost()) // Ajax
		{
			$arrParams = $this->_request->getPost();

			$arrResult = array();

			if (!isset($arrParams["prize_name"]) || !strlen($arrParams["prize_name"]))
			{
				$arrResult["error"]["prize_name"] = "Prize name is a required field.";
			}
			else if (strlen(utf8_decode($arrParams["prize_name"])) > 40)
			{
				$arrResult["error"]["prize_name"] = "The prize name must not be longer than 40 characters.";
			}
			else if (
				(
					$intPrize // editing a prize and the name has changed
					&& $objPrize->prize_name != $arrParams["prize_name"]
				)
				|| !$intPrize // creating a new prize
			) {
				// Check if a prize by this name already exists in this institution
				$arrPrizeTempParams = array(
					"prize_name" => $arrParams["prize_name"]
				);
				if ($objRoles->isRole('Network') && !$this->_request->getParam('institution_id'))
					$arrPrizeTempParams["network_id"] = $this->_user_session_data->network_id;
				else
					$arrPrizeTempParams["institution_id"] = $intInstitution;
				$objPrizeTemp = first($query->prize__select($arrPrizeTempParams));
				if ($objPrizeTemp)
				{
					$arrResult["error"]["prize_name"] = "A prize by this name already exists within this institution.";
				}
			}
			$arrParams["bar_code"] = trim(@$arrParams["bar_code"]);
			if (
				(
					$intPrize // editing a prize and the name has changed
					&& $objPrize->bar_code != $arrParams["bar_code"]
				)
				|| !$intPrize // creating a new prize
			) {
				if (strlen($arrParams["bar_code"]))
				{
					// Check if a prize by this name already exists in this institution
					$objPrizeTemp = first($query->prize__select(array(
						"bar_code" => $arrParams["bar_code"]
					)));
					if ($objPrizeTemp)
					{
						$arrResult["error"]["bar_code"] = "This barcode already exist on a prize in our system.";
					}
				}
			}
			if (isset($arrParams["prize_description"]) && strlen($arrParams["prize_description"]) > 2000)
			{
				$arrResult["error"]["prize_description"] = "The prize name must not be longer than 2000 characters.";
			}
			$arrParams["prize_description"] = urlencode(br2nl(html_entity_decode(trim($arrParams["prize_description"]))));
			if (isset($arrResult["error"]))
			{
				print json_encode($arrResult);
				exit;
			}
			else
			{
				// Post processing
				if (
					isset($arrParams["prize_sizes"])
					&& isset($arrPrizeSizes)
					&& is_array($arrPrizeSizes)
				) {
					$arrPrizeSizesTemp = json_decode(stripslashes($arrParams["prize_sizes"]));
					if (!is_null($arrPrizeSizesTemp) && is_array($arrPrizeSizesTemp))
					{
						$strPrimaryKey = "prize_size_id";
						$arrPertinentOtherParams = array("prize_size_hierarchy", "prize_size");

						$arrInstructions = $query->_proc_query_instructions($arrPrizeSizes, $arrPrizeSizesTemp, $strPrimaryKey, $arrPertinentOtherParams);
						//var_dump($arrInstructions);exit;
						foreach ($arrInstructions["_INSERT"] as $objPrize)
						{
							$query->prize_sizes__insert($objPrize);
						}
						foreach ($arrInstructions["_UPDATE"] as $arrUpdatePrize)
						{
							$query->prize_sizes__update($arrUpdatePrize);
						}
						foreach ($arrInstructions["_DELETE"] as $objPrize)
						{
							$query->prize_sizes__delete($objPrize);
						}
					}
				}

				$arrParams["prize_type"] = trim($arrParams["prize_type"]);
				$arrParams["installable_default_on"] = 0;
				if ($arrParams["prize_type"] == "Installable ON")
				{
					$arrParams["installable_default_on"] = 1;
					$arrParams["prize_type"] = "Installable";
				}
				$arrParams["prize_type"] = str_replace("Installable OFF", "Installable", $arrParams["prize_type"]);
				$arrParams["add_on_restricted"] = (isset($arrParams["add_on_restricted"]) && $arrParams["add_on_restricted"]) ? 1 : 0;
				$arrParams["one_per_user"] = (isset($arrParams["one_per_user"]) && $arrParams["one_per_user"]) ? 1 : 0;
				$arrParams["use_sub_prizes"] = (isset($arrParams["use_sub_prizes"]) && $arrParams["use_sub_prizes"]) ? 1 : 0;

				if ($intPrize) // update
				{
					// verify permission
					if ($objRoles->isRole('Teacher'))
						$objTemp = first($query->prize__select (array(
							"prize_id" => $intPrize,
							"teacher_id" => $this->_user_session_data->user_id
						)));
					else if ($objRoles->isRole('Parent'))
						$objTemp = first($query->prize__select (array(
							"prize_id" => $intPrize,
							"guardian_id" => $this->_user_session_data->user_id
						)));
					if (
						!$objRoles->isAllowed("Institution Administrator")
						&& !$objTemp
					) {
						print text("Sorry, there was an error") . ": CS-P103-S90DF8";
						exit;
					}

					// update prize
					$query->prize__update(array(
						"where" => array(
							"prize_id" => $intPrize
						),
						"values" => $arrParams
					));
					if ($objRoles->isAllowed('Super Administrator'))
					{
						// Install "Default ON" prizes
						if (
							@$arrParams["installable_default_on"] == 1
							&& $objPrize->installable_default_on == 0
						) {
							parse_str($arrParams["school_types"], $arrSchoolTypes);
							$arrSchoolTypes = array_keys($arrSchoolTypes);
							$arrTempParams = array();
							$arrTempParams["institution_type"] = "School";
							$arrInstitutions = $objInstitutions->_institutions_select($arrTempParams);
							$arrPrize = $arrParams;
							$arrPrize["template_prize_id"] = $intPrize;
							foreach ($arrInstitutions as $objInstitution)
							{
								$arrPrize["institution_id"] = $objInstitution->institution_id;
								$query->prize__insert($arrPrize);
							}
						}
					}
				}
				else // insert
				{
					$arrInsertParams = array(
						"template_prize_id"			=> @$arrParams["template_prize_id"],
						"parent_prize_id"			=> @$arrParams["parent_prize_id"],
						"teacher_id"				=> @$arrParams["teacher_id"],
						"guardian_id"				=> @$arrParams["guardian_id"],
						"prize_name"				=> @$arrParams["prize_name"],
						"bar_code"					=> @$arrParams["bar_code"],
						"prize_category"			=> @$arrParams["prize_category"],
						"prize_description"			=> @$arrParams["prize_description"],
						"image_id"					=> @$arrParams["image_id"],
						"add_on_restricted"			=> @$arrParams["add_on_restricted"],
						"one_per_user"				=> @$arrParams["one_per_user"],
						"prize_count"				=> @$arrParams["prize_count"],
						"points"					=> @$arrParams["points"],
						"prize_type"				=> @$arrParams["prize_type"],
						"installable_default_on"	=> @$arrParams["installable_default_on"],
						"prize_price"				=> @$arrParams["prize_price"],
						"is_active"					=> @$arrParams["is_active"]
					);
					if ($objRoles->isRole('Network') && !$this->_request->getParam('institution_id'))
						$arrInsertParams["network_id"] = $this->_user_session_data->network_id;
					else
						$arrInsertParams["institution_id"] = $intInstitution;
					if ($objRoles->isRole('Teacher'))
						$arrInsertParams["teacher_id"] = $this->_user_session_data->user_id;
					else if ($objRoles->isRole('Parent'))
						$arrInsertParams["parent_id"] = $this->_user_session_data->user_id;
					// insert prize
					$intPrize = $query->prize__insert($arrInsertParams);

					if ($objRoles->isAllowed('Super Administrator'))
					{
						// Install "Default ON" prizes
						if (@$arrParams["installable_default_on"] == 1)
						{
							parse_str($arrParams["school_types"], $arrSchoolTypes);
							$arrSchoolTypes = array_keys($arrSchoolTypes);
							$arrTempParams = array();
							$arrTempParams["institution_type"] = "School";
							$arrInstitutions = $objInstitutions->_institutions_select($arrTempParams);
							$arrPrize = $arrParams;
							$arrPrize["template_prize_id"] = $intPrize;
							foreach ($arrInstitutions as $objInstitution)
							{
								$arrPrize["institution_id"] = $objInstitution->institution_id;
								$query->prize__insert($arrPrize);
							}
						}
					}
				}

				// update school types
				if (
					isset($arrParams["school_types"])
					&& $objRoles->isAllowed('Super Administrator')
					&& $arrParams["school_types"] != @$strPrizeSchoolTypes
				) {
					$objStore->_prize_school_types_delete(array(
						"prize_id" => $intPrize
					));
					parse_str($arrParams["school_types"], $arrSchoolTypes);
					foreach ($arrSchoolTypes as $strSchoolType => $boolValue)
					{
						$objStore->_prize_school_types_insert(array(
							"prize_id" => $intPrize,
							"school_type" => $strSchoolType
						));
					}
				}

				// update classes
				else if (
					isset($arrParams["classes"])
					&& (
						$objRoles->isRole('Teacher')
						|| $objRoles->isRole('Institution Administrator')
					)
					&& $arrParams["classes"] != $strPrizeClasses
				) {
					$objStore->_prize_classes_delete(array(
						"prize_id" => $intPrize
					));
					parse_str($arrParams["classes"], $arrPrizeClasses);
					foreach ($arrPrizeClasses as $intClass => $boolValue)
					{
						if (
							$boolValue == "1"
							&& isset($arrClasses[$intClass])
						) {
							$objStore->_prize_classes_insert(array(
								"prize_id" => $intPrize,
								"class_id" => $intClass
							));
						}
					}
				}

				// output
				print json_encode(array(
					"success" => "true",
					"prize_id" => $intPrize
				));
				exit;
			}
		}
	}

	function prizelistexactAction()
	{
		$query = new QueryGen();
		$objPrizes = new Store();
		$arrParams = array();
		$objRoles = new Roles();
		if ($objRoles->isAllowed('Super Administrator') && !($this->_request->getParam("all") == "true" && $this->_user_session_data->institution_type == "Host"))
			$arrParams["institution_id"] = $this->_user_session_data->institution_id;
		else if ($objRoles->isAllowed('Network') && $this->_request->getParam("institution_id"))
			$arrParams["institution_id"] = $this->_request->getParam("institution_id");
		else if ($objRoles->isAllowed('Network'))
			$arrParams["network_id"] = $this->_user_session_data->network_id;
		else
			$arrParams["institution_id"] = $this->_user_session_data->institution_id;
		if ($this->_request->getParam("add_ons"))
			$this->view->boolAddOns = $arrParams["add_on_restricted"] = $this->_request->getParam("add_ons") == "on" ? 1 : 0;
		if ($this->_request->getParam("host_prizes") == "off")
			$arrParams["template_prize_id"] = 0;
		$arrParams["parent_prize_id"] = $this->_request->getParam("parent_prize_id") ? $this->_request->getParam("parent_prize_id") : 0;
		$this->view->boolIsActive = $arrParams["is_active"] = $this->_request->getParam("is_active") == "0" ? 0 : 1;
		$this->view->arrPrizes = $query->prize__select($arrParams);
		$this->view->institution_id = $arrParams["institution_id"];
		$this->view->tstyle = $this->_request->getParam("tstyle");
	}

	function prizeviewAction()
	{
		$host_id = $network_id = $institution_id = 0;
		$intInstitution = $this->_user_session_data->institution_id;
		if(isset($this->_request->institution_id)){
		   $this->view->institution_id = $intInstitution = $institution_id = $this->_request->getParam("institution_id");
		}elseif(isset($this->_request->network_id)){
		   $this->view->network_id = $intInstitution = $network_id = $this->_request->getParam("network_id");
		}elseif(isset($this->_request->host_id)){
		   $this->view->host_id = $intInstitution = $host_id = $this->_request->getParam("host_id");
		}
		$this->view->parent_prize_id = $intParentId = intval($this->_request->getParam("parent_prize_id"));
		$strSortBy = mysql_real_escape_string($this->_request->getParam("sort_by"));
		if ($strSortBy)
			$strSortBy .= "+0 DESC";
		$boolInactive = $this->_request->getParam("is_active") == "0" ? 0 : 1;

		$objStore = new Store();

		if($this->_request->view_mode == "all"){ //institution admin view - view all prizes (THHQ AND School)
			if(isset($this->_request->institution_id)){
			   $this->view->institution_id = $intInstitution = $institution_id = $this->_request->getParam("institution_id");
			}if(isset($this->_request->network_id)){
			   $this->view->network_id = $intInstitution = $network_id = $this->_request->getParam("network_id");
			}if(isset($this->_request->host_id)){
			   $this->view->host_id = $intInstitution = $host_id = $this->_request->getParam("host_id");
			}
			$this->view->arrResults = $objStore->prize_select_by_thhq($host_id, $network_id, $institution_id, $boolInactive,$strSortBy);
		} else {
			$this->view->arrResults = $objStore->prizes_select_by_institution_id($intInstitution,$strSortBy, $intParentId);
		}
	}

	function prizeaddtemplatesAction()
	{
		$objUsers = new Users();
		$intHost = $this->view->intHost = $this->_request->getParam("host_id");
		$intNetwork = $this->view->intNetwork = $this->_request->getParam("network_id");
		$intInstitution = $this->view->intInstitution = $this->_request->getParam("institution_id");
		$arrResult = $objUsers->user_select_parentids($this->_user_session_data->user_id);
		$intHost = $arrResult["host_id"];
		$intClassId = $this->view->intClass = $this->_request->getParam("class_id");

		$objStore = new Store();
		$this->view->arrTemplates = $objStore->prizes_select_templates($intHost);
	}


	public function showImageAction()
	{
		$store = new Store();

		$image = $store->show_picture($this->_request->getParam('image_id'));
		header('Content-type: ' . $image->photo_type);
		echo $image->photo;
		exit;
	}


	function rulesAction()
	{
		$intRemove = $this->_request->getParam("remove");
		if ($intRemove) {
			$objRules = new Rules();
			$boolResult = $objRules->rule_delete_id($intRemove);
			print $boolResult;
			exit;
		}
		$this->view->intPrize = $intPrize = $this->_request->getParam("prize_id");

		if ($this->_request->getParam("host_id"))
			$intInstitutionId = $this->_request->getParam("host_id");
		if ($this->_request->getParam("network_id"))
			$intInstitutionId = $this->_request->getParam("network_id");
		if ($this->_request->getParam("institution_id"))
			$intInstitutionId = $this->_request->getParam("institution_id");

		$this->view->intRule = $intRule = $this->_request->getParam("rule_id");


		if ($intRule > 0) // Update mode
		{
			$objRules = new Rules();
			$this->view->objRule = $objRules->rule_select_id($intRule);
			if (!isset($intInstitutionId)) {
				$intInstitutionId = $this->view->objRule->institution_id;
			}
		}
		else { // Insert mode
			$objInstitutions = new Institutions();
			$objInstitution = $objInstitutions->institutions_select_id($intInstitutionId);
			$intHostId = $objInstitution->host_id;
			$intNetworkId = $objInstitution->network_id;
		}

		$this->view->intInstitutionId = $intInstitutionId;

		if ($this->_request->isPost()) // Ajax
		{
			// Define
			$intRule = $this->_request->getPost("rule_id");
			$strRuleType = $this->_request->getPost("rule_type");
			$strAppliesTo = $this->_request->getPost("applies_to");
			$strRules = $this->_request->getPost("rules");

			// Filter
			Zend_Loader::loadClass('Zend_Filter_StripTags');
			$objFilter = new Zend_Filter_StripTags();
			$strRuleType = $objFilter->filter($strRuleType);
			$strAppliesTo = $objFilter->filter($strAppliesTo);
			$strRules = $objFilter->filter($strRules);

			// Validate
			if (!isset($strRuleType) || empty($strRuleType))
			{
				print text("Sorry, there was an error") . ": VSC-R103-D6F5G4F";
				exit;
			}
			if (!isset($strAppliesTo) || empty($strAppliesTo))
			{
				print text("Sorry, there was an error") . ": VSC-R102-SDF231";
				exit;
			}

			// Process
			$objRules = new Rules();

			if (!$intRule) { // Insert

				$intAI = $objRules->rule_insert(
					array  (
						"host_id"			=> $intHostId,
						"network_id"		=> $intNetworkId,
						"institution_id"	=> $intInstitutionId,
						"prize_id"			=> $intPrize,
						"rule_type"			=> $strRuleType,
						"applies_to" 		=> $strAppliesTo,
						"rules" 			=> $strRules
					)
				);
			}
			else { // Update
				$intAI = $objRules->rule_update(
					array  (
						"rule_id"			=> $intRule,
						"prize_id"			=> $intPrize,
						"rule_type"			=> $strRuleType,
						"applies_to" 		=> $strAppliesTo,
						"rules" 			=> $strRules
					)
				);
			}

			// Result
			print $intAI;
			exit;

		}
	}

	function rulesviewallAction()
	{
		$this->view->intPrize = $intStore = $this->_request->getParam("prize_id");
		$objRules = new Rules();
		$this->view->arrResults = $objRules->rules_select_r_institutions(
			array(
				"prize_id" => $this->view->intPrize
			)
		);
	}

	public function installprizeAction()
	{
		$Store = new Store();

		$prize_id = $this->_request->getParam("prize_id");
		$institution_id = $this->_request->getParam("institution_id");
		$install = $this->_request->getParam("install");

		if ($install == 1)
			$result = $Store->prize_install($prize_id, $institution_id);
		else
			$result = $Store->prize_uninstall($prize_id);

		print $result;
		exit;
	}

	public function configurationAction()
	{
		$store = new Store();
		$this->view->objRoles = $objRoles = new Roles();



		if($this->_request->isPost()){
			$army_points = (isset($this->_request->army_points)) ? 1 : 0;
			$base_points = (isset($this->_request->base_points)) ? 1 : 0;
			$arrSave = array(	"army_points" 		=> $army_points,
								"base_points"		=> $base_points,
								"institution_id"	=> $this->_request->institution_id);
			$result = $store->store_configuration_save($arrSave);
			echo $result;
			exit;
		}

		if(isset($this->_request->institution_id)){
			$this->view->institution_id = $institution_id = $this->_request->institution_id;
			$this->view->configuration = $store->store_configuration_get($institution_id);
		}else{
			echo "You can not modify the configuration information";
			exit;
		}
	}

	public function processuploadAction()
	{
		// If a file is available, upload it
	 	if (!empty($_FILES['Filedata']['name']) && $_FILES['Filedata']['size'] > 0)
		{
			// Set the variables
			Zend_Loader::loadClass('Zend_Filter_StripTags');
			$f = new Zend_Filter_StripTags();
			$file_name = $_FILES['Filedata']['name'];
			//$file_name = str_replace(" ", "_");
			$size = $_FILES['Filedata']['size'];

			//echo $file_name; exit;

			// Retrieve the location where we're uploading the image to from the configuration file
			$upload_location = IMAGE_UPLOADER_DIRECTORY;

			if (!file_exists($upload_location))
			{	print "error"; exit;	}

			// Start the HTTP adapter to receive the file
			$adapter = new Zend_File_Transfer_Adapter_Http();

			// Get the file's exension
			$file_information = pathinfo($file_name);
			$extension = $file_information['extension'];

			// Set the save destination
			$adapter->setDestination($upload_location);
			// Receive the file & save it
			$adapter->receive($file_name);

			$this->getHelper('viewRenderer')->setNoRender();
			$strRand = rand(1000000,9999999);

			rename(
				IMAGE_UPLOADER_DIRECTORY . '/' . $file_name,
				IMAGE_UPLOADER_DIRECTORY . '/student_list_' . $this->_user_session_data->user_id . ".csv"
			);
			exit;
	   	}
	}
}
?>