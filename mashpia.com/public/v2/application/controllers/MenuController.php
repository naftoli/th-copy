<?php
class MenuController extends Zend_Controller_Action
{
	private $_user_session_data;
	private $objPermission; // permission instance
	private $boolVerbose = 0;
	private $arrUserTypes = array(
		"super" => "Super Administrator",
		"admin" => "Institution Administrator",
		"teacher" => "Teacher",
		"parent" => "Parent",
		"student" => "Student"
	);

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

	public function announcementsAction()
	{
		$query = new QueryGen();
		$objRoles = new Roles();

		$strForwardTo = trim(urldecode($this->_request->getParam("url")));
		$strTitlePrefix = trim($this->_request->getParam("pre"));

		$this->view->strTitle = ucwords(trim($strTitlePrefix)) . (strlen($strTitlePrefix) ? " " : "") . "Announcements";

		$arrGet = $this->_request->getParams();
		if (empty($arrGet['status']))
		{
			print "Sorry, there was an error: CM-A101-sdf7dd";
			exit;
		}
		$arrAnnouncementParams = array(
			'status' => $arrGet['status'],
			'_ORDER' => 'created DESC'
		);
		if (
			$arrGet['status'] != 'Publish Request'
			&& $arrGet['status'] != 'Denied Request'
		)
			$arrAnnouncementParams['created_by'] = $this->_user_session_data->user_id;
		$arrAnnouncementParams['institution_id'] = $this->_user_session_data->institution_id;
		$arrAnnouncements = $query->announcements__select($arrAnnouncementParams);
		if ($objRoles->isAllowed('Super Administrator') || isset($arrGet['all']))
		{
			$arrInstitutions = array_hash('institution_id', $query->institutions__select(array(
				'institution_id' => array_keys(array_stack('institution_id', $arrAnnouncements))
			)));
		}
		$arrList = array();
		foreach ($arrAnnouncements as $intItr => $objAnnouncement)
		{
			$arrList[$intItr]["link"] = $strForwardTo . (preg_match("/\/$/", $strForwardTo) ? "" : "/") .  "announcement_id/" . $objAnnouncement->announcement_id;
			$arrList[$intItr]["text"] = $objAnnouncement->headline;
			$arrList[$intItr]["date"] = $objAnnouncement->created;
			if ($objRoles->isAllowed('Super Administrator') || isset($arrGet['all']))
				$arrList[$intItr]["name"] = $arrInstitutions[$objAnnouncement->institution_id]->name;
		}
		$this->view->arrList = array_clean_slashes($arrList);
	}

	public function campaignsAction()
	{
		$objCampaigns = new Campaigns();
		$objClasses = new Classes();

		$strForwardTo = trim(urldecode($this->_request->getParam("url")));
		$strTitlePrefix = trim($this->_request->getParam("pre"));

		$this->view->strTitle = ucwords(trim($strTitlePrefix)) . (strlen($strTitlePrefix) ? " " : "") . "Campaigns";

		$arrParams = $this->_request->getParams();

		// Find only students who are enrolled if enrolled is 1
		if (
			(isset($arrParams["enrolled"]) && $arrParams["enrolled"] == "1")
			|| (isset($arrParams["class_id"]) && $arrParams["class_id"] > 0)
		) {
			if (isset($arrParams["class_id"]) && $arrParams["class_id"] > 0)
			{
				$arrUserClasses = $objClasses->_user_classes_select(array(
					"class_id" => $arrParams["class_id"],
					"class_role" => "Student"
				));
				$arrUserIds = array();
				foreach ($arrUserClasses as $objUserClass)
				{
					$arrUserIds[] = $objUserClass->user_id;
				}
			}
			$arrSql = array();
			if (isset($arrParams["institution_id"]))
				$arrSql["institution_id"] = $arrParams["institution_id"];
			else
				$arrSql["institution_id"] = $this->_user_session_data->institution_id;
			$arrSql["status"] = "Enrollment";
			if (isset($arrUserIds))
				$arrSql["user_id"] = $arrUserIds;
			$arrUserCampaign = $objCampaigns->_user_campaigns_select($arrSql);
			$arrCampaignIds = array();
			foreach ($arrUserCampaign as $objUserCampaign)
			{
				$arrCampaignIds[] = $objUserCampaign->campaign_id;
			}
		}
		$arrSql = array();
		if (isset($arrParams["installed_campaign_id"]))
			$arrSql["campaign_id"] = explode(',',$arrParams["installed_campaign_id"]);
		else if (isset($arrParams["campaign_id"]))
			$arrSql["campaign_id"] = explode(',',$arrParams["campaign_id"]);
		else if (isset($arrParams["institution_id"]))
			$arrSql["institution_id"] = $arrParams["institution_id"];
		else
			$arrSql["institution_id"] = $this->_user_session_data->institution_id;
		if (isset($arrParams["is_active"]))
			$arrSql["is_active"] = $arrParams["is_active"];
		$arrCampaigns = $objCampaigns->_campaigns_select($arrSql);

		$arrList = array();
		$boolAllPossible = $this->_request->getParam("all") == "true";
		if ($boolAllPossible)
		{
			$arrList[" 0"]["link"] = $strForwardTo;
			$arrList[" 0"]["text"] = "Select All";
		}

		foreach ($arrCampaigns as $intItr => $objCampaign)
		{
			$strNewParam = '/' . $strForwardTo . (preg_match("/\/$/", $strForwardTo) ? "" : "/");
			$strNewParam .= "campaign_id/" . $objCampaign->campaign_id;
			if ($this->_request->getParam("pt") == "1") // pass through
				$strNewParam = dblencode($strNewParam);

			$arrList[$objCampaign->campaign_name . " " . $intItr]["link"] = $strNewParam;
			$arrList[$objCampaign->campaign_name . " " . $intItr]["text"] = $objCampaign->campaign_name;
		}
		ksort($arrList);
		$this->view->arrList = array_clean_slashes($arrList);
	}

	public function users2Action()
	{
		$objUsers = new Users();
		$objClasses = new Classes();
		$objRoles = new Roles();
		$query = new QueryGen();

		$intInstitution = $this->_user_session_data->institution_id;
		if ($objRoles->isAllowed('Network')) {
			$intInstitution = $this->_request->getParam("institution_id");
		}
		//$this->view->in

		// Configure list
		$strForwardTo = trim(urldecode($this->_request->getParam("url")));
		$strTitlePrefix = trim($this->_request->getParam("pre"));

		$this->view->strTitle = ucwords(trim($strTitlePrefix)) . (strlen($strTitlePrefix) ? " " : "") . "Accounts";

		$arrParams = $this->_request->getParams();
		$this->view->is_active = $this->_request->getParam('is_active');
		$this->view->is_registered = $this->_request->getParam('is_registered');
		$this->view->class_id = $this->_request->getParam('class_id');
		$this->view->permission = $this->_request->getParam('permission');
		$this->view->url = urlencode($this->_request->getParam('url'));
		$this->view->pt = $this->_request->getParam("pt");
		$arrPost = $this->_request->getPost();
		$this->view->arrPost = $arrPost;
		$arrSql = array();

		$arrColumnOrder = array('first_name', 'last_name', 'class_list');

		if (isset($arrParams["is_active"]))
			$arrSql["is_active"] = $arrParams["is_active"];
		$arrSql[0]["_IN"] = array(
			"_TABLE" => "permissions",
			"_DEPENDENT" => "user_id",
			"_INDEPENDENT" => "user_id",
			'institution_id' => $intInstitution
		);
		if (isset($arrParams["is_registered"]))
			$arrSql[0]['_IN']['_GREATER']['registration_expiration'] = time();
		if (isset($arrParams["class_id"]))
			$arrSql[1]["_IN"] = array(
				"_TABLE" => "user_classes",
				"_DEPENDENT" => "user_id",
				"_INDEPENDENT" => "user_id",
				'class_id' => $arrParams["class_id"]
			);
		// Select the users
		if ($objRoles->isAllowed("Institution Administrator"))
		{
			$arrPermissionTypes = array();
			if (isset($arrParams["permission"]))
			{
				$arrMultiPermissions = explode(",", $arrParams["permission"]);
				if (count($arrMultiPermissions) > 1)
				{
					foreach ($arrMultiPermissions as $strMultiPermission)
					{
						if (isset($this->arrUserTypes[$arrParams["permission"]]))
							$arrPermissionTypes[] = $this->arrUserTypes[$arrParams["permission"]];
					}
				}
				else if (isset($this->arrUserTypes[$arrParams["permission"]]))
				{
					$arrPermissionTypes[] = $this->arrUserTypes[$arrParams["permission"]];
				}
			}
			$arrSql[0]["_IN"]['permission'] = $arrPermissionTypes;
			$arrUsers = $query->users__select($arrSql);
		}
		else if ($objRoles->isRole("Teacher"))
		{
			if (isset($arrParams["class_id"]))
			{
				$arrClasses = array($arrParams["class_id"]);
			}
			else
			{
				$arrTeacherClasses = array_hash("class_id", $objClasses->_user_classes_select(array(
					"user_id" => $this->_user_session_data->user_id,
					"user_role" => "Teacher"
				)));
				$arrClasses = array_keys($arrTeacherClasses);
			}
			// Get the students that are in those classes and create their encoded values
			$arrStudentIds = array_keys(array_hash("user_id", $objClasses->_user_classes_select(array(
				"class_id" => $arrClasses,
				"class_role" => "Student"
			))));
			$arrUsers = $query->users__select(array(
				'is_active' => 1,
				"user_id" => $arrStudentIds
			));
		}
		else
		{
			$arrUsers = $query->users__select($arrSql);
		}


		// Query additional information about users
		$arrUserClasses = $query->user_classes__select(array(
			'user_id' => array_keys(array_stack('user_id', $arrUsers))
		));
		$arrUserClassesHash = array_bubble_hash('user_id', $arrUserClasses);
		$arrClasses = array_hash('class_id', $objClasses->_classes_select(array(
			'class_id' => array_keys(array_stack('class_id', $arrUserClasses))
		)));

		// Build the list of data
		$arrList = array();
		$boolAllPossible = $this->_request->getParam("all") == "true";
		foreach ($arrUsers as $intItr => $objUser)
		{
			$strNewParam = (preg_match("/\/$/", $strForwardTo) ? "" : "/") .  "user_id/" . $objUser->user_id;
			if ($this->_request->getParam("pt") == "1") // pass through
				$strNewParam = dblencode($strNewParam);
			$intIndex = count($arrList);
			$arrList[$intIndex] = array(
				'intOriginalIndex' => $intIndex,
				'strForwardTo' => "/" . $strForwardTo . $strNewParam,
				'arrParams' => array('user_id' => $objUser->user_id),
				'first_name' => $objUser->first_name,
				'last_name' => $objUser->last_name
			);
			// Build class list
			$arrClassNames = array();
			$arrCurrentUserClasses = isset($arrUserClassesHash[$objUser->user_id]) ? $arrUserClassesHash[$objUser->user_id] : array();
			foreach ($arrCurrentUserClasses as $objUserClass)
			{
				if (isset($arrClasses[$objUserClass->class_id]))
				{
					$objClass = $arrClasses[$objUserClass->class_id];
					$arrClassNames[] = $objClass->custom_name1;
				}
			}
			$arrList[$intIndex]['class_list'] = join(', ', $arrClassNames);
		}

		// Filter
		if (isset($arrPost['strSearchFilter']))
		{
			$strSearchFilter = $arrPost['strSearchFilter'];
			$arrExactMatches = array();
			while (preg_match('/"([^"]+)"/', $strSearchFilter, $arrMatched)) {
				$strSearchFilter = preg_replace('/"' . preg_quote($arrMatched[1], '/') . '"/', '', $strSearchFilter);
				$arrExactMatches[] = $arrMatched[1];
			}
			$strSearchFilter = preg_replace('/"/', '', $strSearchFilter);
			$strSearchFilter = preg_replace('/^ +/', '', $strSearchFilter);
			$strSearchFilter = preg_replace('/ +$/', '', $strSearchFilter);
			$strSearchFilter = preg_replace('/^ +$/', '', $strSearchFilter);
			$strSearchFilter = preg_quote($strSearchFilter, '/');
			$arrSearchFilter = array();
			if (!empty($strSearchFilter))
				$arrSearchFilter = preg_split('/ +/', $strSearchFilter);
			foreach ($arrExactMatches as $strExactMatch)
			{
				if (!empty($strExactMatch))
					array_unshift($arrSearchFilter, $strExactMatch);
			}
			//if (!empty($arrSearchFilter))
			//	dumper(array($arrExactMatches,$arrSearchFilter),1,1);
			if (count($arrSearchFilter))
			{
				foreach ($arrList as $intKey => $arrRow)
				{
					$intMatchFound = 0;
					foreach ($arrColumnOrder as $strColumn)
					{
						$strCellData = $arrRow[$strColumn];
						foreach ($arrSearchFilter as $strKeyword)
						{
							if (preg_match('/' . $strKeyword . '/i', $strCellData))
								$intMatchFound++;
						}
					}
					if ($intMatchFound < count($arrSearchFilter))
						unset($arrList[$intKey]);
				}
			}
		}

		// Sort the data
		$arrSortParams = array();
		if (isset($arrPost['strSortOrder']))
		{
			$arrSortOrder = json_decode($arrPost['strSortOrder']);
			foreach ($arrSortOrder as $intItr => $strOrder)
			{
				$strColumn = $arrColumnOrder[$intItr];
				array_push($arrSortParams, $strColumn);
				if ($strOrder == 'desc')
					array_push($arrSortParams, SORT_ASC);
				else if ($strOrder == 'asc')
					array_push($arrSortParams, SORT_DESC);
				array_push($arrSortParams, SORT_STRING);
			}
			//dumper($arrSortParams,1,1);
		}
		array_push($arrSortParams, $arrList);
		$arrList = call_user_func_array("msort", $arrSortParams);

		// Paging
		$this->view->intTotalItems = $intTotalItems = count($arrList);
		$this->view->intItemsPerPage = $intItemsPerPage = 16;
		$this->view->intTotalPages = $intTotalPages = floor($intTotalItems / $intItemsPerPage)+1;
		$this->view->intPage = $intPage = isset($arrPost["page"]) ? $arrPost["page"] : 0;
		if ($intPage+1 > $intTotalPages)
			$intPage = $intTotalPages;
		$this->view->intFirstItem = $intFirstItem = $intPage * $intItemsPerPage;

		// Output
		$arrList = array_slice($arrList, $intFirstItem, $intItemsPerPage);
		$this->view->intPageCount = count($arrList);
		$this->view->arrList = array_clean_slashes($arrList);
	}

	public function usersAction()
	{
		$objUsers = new Users();
		$objClasses = new Classes();
		$objRoles = new Roles();

		$strForwardTo = trim(urldecode($this->_request->getParam("url")));
		$strTitlePrefix = trim($this->_request->getParam("pre"));

		$this->view->strTitle = ucwords(trim($strTitlePrefix)) . (strlen($strTitlePrefix) ? " " : "") . "Accounts";

		$arrParams = $this->_request->getParams();
		$arrSql = array();
		$arrSql["institution_id"] = $this->_user_session_data->institution_id;
		if ($objRoles->isAllowed('Network')) {
			$arrSql["institution_id"] = $this->_request->getParam('institution_id');
		}
		if (isset($arrParams["class_id"]))
			$arrSql["class_id"] = $arrParams["class_id"];
		if (isset($arrParams["is_active"]))
			$arrSql["is_active"] = $arrParams["is_active"];

		if ($objRoles->isAllowed("Institution Administrator"))
		{
			if (isset($arrParams["permission"]))
			{
				$arrMultiPermissions = explode(",", $arrParams["permission"]);
				if (count($arrMultiPermissions) > 1)
				{
					$arrSql["permission"] = array();
					foreach ($arrMultiPermissions as $strMultiPermission)
					{
						if (isset($this->arrUserTypes[$arrParams["permission"]]))
							$arrSql["permission"][] = $this->arrUserTypes[$arrParams["permission"]];
					}
				}
				else if (isset($this->arrUserTypes[$arrParams["permission"]]))
				{
					$arrSql["permission"] = $this->arrUserTypes[$arrParams["permission"]];
				}
			}
			$arrUsers = $objUsers->_users_select_hierarchal($arrSql);
		}
		else if ($objRoles->isRole("Teacher"))
		{
			$arrTeacherClasses = array_hash("class_id", $objClasses->_user_classes_select(array(
				"user_id" => $this->_user_session_data->user_id,
				"user_role" => "Teacher"
			)));
			// Get the students that are in those classes and create their encoded values
			$arrStudentIds = array_keys(array_hash("user_id", $objClasses->_user_classes_select(array(
				"class_id" => array_keys($arrTeacherClasses),
				"class_role" => "Student"
			))));
			$arrUsers = $objUsers->_users_select(array(
				"user_id" => $arrStudentIds
			));
		}
		else
		{
			$arrUsers = $objUsers->_users_select_hierarchal($arrSql);
		}

		$arrList = array();
		$boolAllPossible = $this->_request->getParam("all") == "true";
		if ($boolAllPossible)
		{
			$arrList["0"]["link"] = $strForwardTo;
			$arrList["0"]["text"] = "Select All";
		}
		foreach ($arrUsers as $intItr => $objUser)
		{
			$strNewParam = (preg_match("/\/$/", $strForwardTo) ? "" : "/") .  "user_id/" . $objUser->user_id;
			if ($this->_request->getParam("pt") == "1") // pass through
				$strNewParam = dblencode($strNewParam);
			$arrList[$objUser->first_name . " " . $objUser->last_name . " " . $intItr]["link"] = $strForwardTo . $strNewParam;
			$arrList[$objUser->first_name . " " . $objUser->last_name . " " . $intItr]["text"] = $objUser->first_name . " " . $objUser->last_name . ($objUser->user_serial ? " (" . $objUser->user_serial . ")" : "");
		}
		ksort($arrList);
		$this->view->arrList = array_clean_slashes($arrList);
	}

	public function classesAction()
	{
		$query = new QueryGen();
		$objRoles = new Roles();
		$objClasses = new Classes();
		$objInstitutions = new Institutions();

		$objInstitution = first($objInstitutions->_institutions_select(array(
			"institution_id" => $this->_user_session_data->institution_id
		)));
		if (!$objInstitution)
		{
			print text("Sorry, there was an error") . ": CM-C101-8DFDSS";
			exit;
		}

		$strForwardTo = trim(urldecode($this->_request->getParam("url")));
		$strTitlePrefix = trim($this->_request->getParam("pre"));

		$this->view->strTitle = ucwords(trim($strTitlePrefix)) . (strlen($strTitlePrefix) ? " " : "") . "Bunks";
		$this->view->strViewMod = $this->_request->getParam("viewmod");

		$arrSql = array();
		$arrSql["institution_id"] = $this->_user_session_data->institution_id;
		
		if ($objRoles->isRole("Teacher"))
		{
			/*
			$arrUserClasses = $query->user_classes__select(array(
				"class_role" => "Teacher",
				"user_id" => $this->_user_session_data->user_id,
				"institution_id" => $this->_user_session_data->institution_id
			));
			$arrSql["class_id"] = array_keys(array_stack("class_id", $arrUserClasses));
			*/
			$arrSql["class_id"] = $this->_user_session_data->class_id;
		}
		
		$arrClasses = $objClasses->_classes_select($arrSql);

		$arrList = array();

		$boolAllPossible = $this->_request->getParam("all") == "true";
		if ($boolAllPossible)
		{
			$arrList["0"]["link"] = $strForwardTo;
			$arrList["0"]["text"] = "Select All";
		}

		foreach ($arrClasses as $objClass)
		{
			$strNewParam = (preg_match("/\/$/", $strForwardTo) ? "" : "/");
			$strNewParam .= "class_id/" . $objClass->class_id;
			if ($this->_request->getParam("pt") == "1") // pass through
				$strNewParam = dblencode($strNewParam);
			$arrList[preg_replace("/^pre(?:\-?school)? *([0-9a-z]+)/i", "0\1", $objClass->class_grade)  ." ". $objClass->class_sub]["link"] = $strForwardTo . $strNewParam;
			$arrList[preg_replace("/^pre(?:\-?school)? *([0-9a-z]+)/i", "0\1", $objClass->class_grade)  ." ". $objClass->class_sub]["text"] = $objClass->class_grade  ." ". $objClass->class_sub;
		}
		//ksort($arrList);
		$this->view->arrList = array_clean_slashes($arrList);
	}

	public function institutionsAction()
	{
		$query = new QueryGen();
		$role = new Roles();
		if (!$role->isAllowed('Network')) {
			print "Sorry, there was an error: CM-I101-asdsdw";
		}

		$strForwardTo = trim(urldecode($this->_request->getParam("url")));
		$strTitlePrefix = trim($this->_request->getParam("pre"));

		$this->view->strTitle = ucwords(trim($strTitlePrefix)) . (strlen($strTitlePrefix) ? " " : "") . "[|Schools|]";
		$this->view->strViewMod = $this->_request->getParam("viewmod");

		$arrSql = array();
		$arrInstitutions = $query->institutions__select(array(
			'template_style' => $this->_user_session_data->template_style,
			'is_active' => 1
		));

		$arrList = array();
		foreach ($arrInstitutions as $objInstitution)
		{
			$strNewParam = (preg_match("/\/$/", $strForwardTo) ? "" : "/");
			$strNewParam .= "institution_id/" . $objInstitution->institution_id;
			if ($this->_request->getParam("pt") == "1") // pass through
				$strNewParam = dblencode($strNewParam);
			$arrList[$objInstitution->name] = array();
			$arrList[$objInstitution->name]["link"] = $strForwardTo . $strNewParam;
			$arrList[$objInstitution->name]["text"] = $objInstitution->name;
		}
		//ksort($arrList);
		$this->view->arrList = array_clean_slashes($arrList);
	}

	public function prizesAction()
	{
		$objPrizes = new Store();
		$objRoles = new Roles();

		$strForwardTo = trim(urldecode($this->_request->getParam("url")));
		$strTitlePrefix = trim($this->_request->getParam("pre"));
		$this->view->strTitle = ucwords(trim($strTitlePrefix)) . (strlen($strTitlePrefix) ? " " : "") . "Prizes";

		$arrParams = $this->_request->getParams();

		$arrSql = array();
		if (isset($arrParams["parent_prize_id"]))
			$arrSql["parent_prize_id"] = $arrParams["parent_prize_id"];
		if (isset($arrParams["institution_id"]))
			$arrSql["institution_id"] = $arrParams["institution_id"];
		else
			$arrSql["institution_id"] = $this->_user_session_data->institution_id;

		$arrPassThrough = array("prize_type","is_active","add_on_restricted");
		foreach ($arrPassThrough as $strCol)
		{
			if (isset($arrParams[$strCol]))
				$arrSql[$strCol] = $arrParams[$strCol];
		}
		if (@$arrParams["prize_type"] == "Template")
		{
			$arrSql["parent_prize_id"] = 0;
		}
		if ($objRoles->isRole("Teacher") && @$arrParams["prize_type"] != "Template")
			$arrSql["teacher_id"] = $this->_user_session_data->user_id;

		$arrPrizes = $objPrizes->_prizes_select($arrSql);

		$arrList = array();

		$boolAllPossible = $this->_request->getParam("all") == "true";
		if ($boolAllPossible)
		{
			$arrList["0"]["link"] = $strForwardTo;
			$arrList["0"]["text"] = "Select All";
		}

		foreach ($arrPrizes as $intItr => $objPrize)
		{
			$strPrizeIdAttr = (isset($arrParams["prize_type"]) && $arrParams["prize_type"] == "Template") ? "template_id" : "prize_id";
			$arrList[$objPrize->prize_id]["link"] = $strForwardTo . (preg_match("/\/$/", $strForwardTo) ? "" : "/") . $strPrizeIdAttr . "/" . $objPrize->prize_id;
			$arrList[$objPrize->prize_id]["text"] = $objPrize->prize_name;
		}
		ksort($arrList, SORT_NUMERIC);
		$this->view->arrList = array_clean_slashes($arrList);
	}

	public function schooltypesAction()
	{
		global $arrTemplateTypes;

		$this->view->strViewMod = $this->_request->getParam("viewmod");

		foreach ($arrTemplateTypes as $strName => $strKeyword)
		{
			$arrList[$strName]["link"] = $strKeyword;
			$arrList[$strName]["text"] = $strName;
		}
		ksort($arrList);
		$this->view->arrList = array_clean_slashes($arrList);
	}

	public function applanguagesAction()
	{
		$query = new QueryGen();

		$arrParams = $this->_request->getParams();
		$strForwardTo = trim(urldecode($this->_request->getParam("url")));
		$strTitlePrefix = trim($this->_request->getParam("pre"));
		$this->view->strTitle = ucwords(trim($strTitlePrefix)) . (strlen($strTitlePrefix) ? " " : "") . "Languages";

		$arrLangParams = array(
			"_ORDER" => "hierarchy"
		);
		if (isset($arrParams["is_active"]))
			$arrLangParams["is_active"] = $arrParams["is_active"] ? 1 : 0;
		if (isset($arrParams["no_default"]) && $arrParams["no_default"])
			$arrLangParams["_NOT"] = array(
				"app_text_language_id" => 0
			);
		$arrAppLanguages = $query->app_text_languages__select($arrLangParams);
		foreach ($arrAppLanguages as $objAppLanguage)
		{
			$strNewParam = (preg_match("/\/$/", $strForwardTo) ? "" : "/") .  "app_text_language_id/" . $objAppLanguage->app_text_language_id;
			if ($this->_request->getParam("pt") == "1") // pass through
				$strNewParam = dblencode($strNewParam);
			$arrList[]["link"] = $strForwardTo . $strNewParam;
			$arrList[count($arrList)-1]["text"] = $objAppLanguage->app_text_language . " " . (!$objAppLanguage->is_active ? "(Inactive)" : "");
			if (isset($arrParams["urgent_value"]))
			{
				$arrAppTextParams = array(
					"_COUNT" => "app_text_id",
					"priority" => 0,
					"language_id" => 0,
					"_NOT" => array(
						"_IN" => array(
							"_TABLE" => "app_text",
							"_DEPENDENT" => "primary_app_text_id",
							"_INDEPENDENT" => "app_text_id",
							"language_id" => $objAppLanguage->app_text_language_id
						)
					)
				);
				if (isset($arrParams["kiosk"]))
				{
					$arrAppTextParams["controller"] = "hebrewschools";
				}
				else if (isset($arrParams["admin"]))
				{
					$arrAppTextParams["_NOT"]["controller"] = "hebrewschools";
				}
				//$arrAppTextParams["_VERBOSE"] = 4;
				$objAppTextCount = first($query->app_text__select($arrAppTextParams));
				$arrList[count($arrList)-1]["count"] = $objAppTextCount->_count_app_text_id . " Urgent";
			}
		}
		$this->view->arrList = $arrList;
	}
}
?>