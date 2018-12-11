<?php
class MissionsappController extends Zend_Controller_Action
{
	public function preDispatch() {
		$arrActions = array("iframe", "index", "register", "card", "idcard", "idcardqr", "login", "missions");
		if (
			preg_match("/^kids\.(.+)/", $_SERVER['HTTP_HOST'], $arrMatched)
			&& !in_array($this->_request->action, $arrActions)
		) {
			header("Location: http://www.".$arrMatched[1]."/kids");
			exit;
		}
	}

	function iframeAction()
	{

	}

	function indexAction()
	{

	}
	
	function missionappreportAction()
	{
		$query = new QueryGen();
		
	}

	function registerAction()
	{
		$query = new QueryGen();
		$objOrder = $this->view->objOrder = FALSE;
		$strBarcode = $this->view->bc = $this->_request->getParam("bc");
		if (!empty($strBarcode)) {
			$this->view->objUser = $objUser = first($query->users__select(array(
				"bar_code" => $strBarcode
			)));
			if (!$objUser) {
				print "Unexpected error: MAsAdsffEF34";
				exit;
			}
			$this->view->objPermission = first($query->permissions__select(array(
				'user_id' => $objUser->user_id,
				'permission' => 'MissionsApp'
			)));
			$objOrder = $this->view->objOrder = first($query->ckids_mission_cards__select(array(
				'user_id' => $objUser->user_id
			)));
		}

		if ($this->_request->isPost())
		{
			if (isset($_POST['action']) && $_POST['action'] == "registration")
			{
				$arrResult = array();
				if (empty($_POST["gender"]) || !in_array($_POST["gender"], array("B","G"))) {
					$arrResult['item'] = "gender";
					$arrResult['error'] = "Child gender is required";
					print json_encode($arrResult);
					exit;
				}
				$strGender = "M";
				if ($_POST["gender"] == "G")
					$strGender = "F";
				if (empty($_POST["firstname"])) {
					$arrResult['item'] = "firstname";
					$arrResult['error'] = "First name is required";
					print json_encode($arrResult);
					exit;
				}
				if (empty($_POST["lastname"])) {
					$arrResult['item'] = "lastname";
					$arrResult['error'] = "Last name is required";
					print json_encode($arrResult);
					exit;
				}
				if (empty($_POST["dob"])) {
					$arrResult['item'] = "dob";
					$arrResult['error'] = "Date of birth is required";
					print json_encode($arrResult);
					exit;
				}
				$strDOB = "";
				if (preg_match("/^ *([0-9]+) +([a-z]+) +([0-9]{4}) *$/i", $_POST["dob"], $arrMatched)) {
					list($strFull, $strDay, $strMonth, $intYear) = $arrMatched;
					$arrMonthList = array(
						"january","february","march","april","may","june",
						"july","august","september","october","november","december"
					);
					$arrMonthHash = array_flip($arrMonthList);
					$intDay = $strDay * 1;
					if (!isset($arrMonthHash[strtolower($strMonth)])) {
						$arrResult['item'] = "dob";
						$arrResult['error'] = "Month $strMonth is invalid";
						print json_encode($arrResult);
						exit;
					}
					$intMonth = $arrMonthHash[strtolower($strMonth)];
					$intDate = mktime(0,0,0,$intMonth+1,$intDay,$intYear);
					// convert date to format 04/23/2001
					$strDOB = date("m/j/Y", $intDate);
				} else {
					$arrResult['item'] = "dob";
					$arrResult['error'] = "Date of birth is an incorrect format";
					print json_encode($arrResult);
					exit;
				}
				if (
					!isset($objUser)
					|| empty($_POST["email1"])
					|| $objUser->student_email != $_POST["email1"]
				) {
					if (empty($_POST["email1"])) {
						$arrResult['item'] = "email1";
						$arrResult['error'] = "Parents email is required";
						print json_encode($arrResult);
						exit;
					}
					if (!preg_match("/^[^@].*?@[^.].*?\.[^.]+$/", $_POST["email1"])) {
						$arrResult['item'] = "email1";
						$arrResult['error'] = "The email doesn't appear to be valid";
						print json_encode($arrResult);
						exit;
					}
					if (empty($_POST["email2"]) || $_POST["email1"] != $_POST["email2"]) {
						$arrResult['item'] = "email2";
						$arrResult['error'] = "The emails don't match";
						print json_encode($arrResult);
						exit;
					}
				}

				if (empty($_POST["phone"])) {
					$arrResult['item'] = "phone";
					$arrResult['error'] = "Parents phone number is required";
					print json_encode($arrResult);
					exit;
				}
				if (isset($_POST['send_card']) || $objOrder)
				{
					if (empty($_POST["address"])) {
						$arrResult['item'] = "address";
						$arrResult['error'] = "Address is required to send a card";
						print json_encode($arrResult);
						exit;
					}
					if (empty($_POST["city"])) {
						$arrResult['item'] = "city";
						$arrResult['error'] = "City is required to send a card";
						print json_encode($arrResult);
						exit;
					}
					if (empty($_POST["state"])) {
						$arrResult['item'] = "state";
						$arrResult['error'] = "State is required to send a card";
						print json_encode($arrResult);
						exit;
					}
					if (empty($_POST["zip"])) {
						$arrResult['item'] = "zip";
						$arrResult['error'] = "Zip code is required to send a card";
						print json_encode($arrResult);
						exit;
					}
					if (empty($_POST["country"])) {
						$arrResult['item'] = "country";
						$arrResult['error'] = "Country is required to send a card";
						print json_encode($arrResult);
						exit;
					}
				}
				if (isset($objUser))
				{
					$query->users__update(array(
						"where" => array(
							'user_id' => $objUser->user_id
						),
						"values" => array(
							'first_name' => $_POST["firstname"],
							'last_name' => $_POST["lastname"],
							'dob' => $strDOB,
							'student_email' => $_POST["email1"],
							'gender' => $strGender,
							'address' => $_POST["address"],
							'city' => $_POST["city"],
							'state' => $_POST["state"],
							'country' => $_POST["country"],
							'postal' => $_POST["zip"],
							'phone' => $_POST["phone"],
							'image_id' => $_POST["image_name"],
							'is_active' => 1,
							'created_by' => 3213
						)
					));
					$query->permissions__update(array(
						"where" => array(
							'user_id' => $objUser->user_id,
							'institution_id' => 601
						),
						"values" => array(
							'email_notification' => isset($_POST['send_email']) ? '1' : '0'
						)
					));
					if (isset($_POST['send_card']))
					{
						if (empty($objOrder)) {
							$intOrder = $query->ckids_mission_cards__insert(array(
								'user_id' => $objUser->user_id,
								'order_status' => 'ordered'
							));	
						}
					}
					json(array(
						"success" => "true",
						"go_to_mission" => 'true',
						'bar_code' => $objUser->bar_code
					));
				}
				else
				{
					do {
						$intBarCode = rand_num_string(16);
						$objBarcode = first($query->users__select(array(
							'bar_code' => $intBarCode
						)));
					} while ($objBarcode);
					$strPassword = rand_num_string(7);
					$intUser = $query->users__insert(array(
						'bar_code' => $intBarCode,
						'password' => $strPassword,
						'first_name' => $_POST["firstname"],
						'last_name' => $_POST["lastname"],
						'dob' => $strDOB,
						'student_email' => $_POST["email1"],
						'gender' => $strGender,
						'address' => $_POST["address"],
						'city' => $_POST["city"],
						'state' => $_POST["state"],
						'country' => $_POST["country"],
						'postal' => $_POST["zip"],
						'phone' => $_POST["phone"],
						'image_id' => $_POST["image_name"],
						'is_active' => 1,
						'created_by' => 3213
					));
					$strLocation = preg_replace("/^kids\./", "", $_SERVER['HTTP_HOST']);
					$intPermission = $query->permissions__insert(array(
						'user_id' => $intUser,
						'template_style' => 'chabadhebrewschool',
						'institution_id' => 601,
						'registration_location' => $strLocation,
						'permission' => 'MissionsApp',
						'email_notification' => isset($_POST['send_email']) ? '1' : '0',
						'default_permission' => 1,
						'created_by' => 3213
					));
					if (isset($_POST['send_card']))
					{
						$intOrder = $query->ckids_mission_cards__insert(array(
							'user_id' => $intUser,
							'order_status' => 'ordered'
						));
					}
					$strHeaders = 'From: ChabadChildren.com Missions <ckids@chabadchildren.com>' . "\r\n";
					$strTo = $_POST["email1"];
					$strSubject = "ChabadChildren.com Missions App Registration";
					$strMessage = "Thank you for enrolling your child in the CKids Brigade of Tzivos Hashem. Here is a link to your child's ID card, keep it handy so he can login to his personal account.\n\n";
					$strMessage .= "http://kids." . $strLocation . "/missionsapp/card/code/" . $strPassword . "/bc/" . $intBarCode . "\n\n";
					$strMessage .= "Tzivos Hashem CKids is the Jewish boys’ and girls’ brigade of Hashem's (G-d’s) army, made up of kids who do acts of kindness, show care and concern for others, learn Torah and fulfill mitzvot (good deeds).\n\n";
					$strMessage .= "When one enrolls in Tzivos Hashem, you start out as a Private, but as you do more good deeds and complete missions, you’ll earn Mitzva badges. After collecting enough badges, you’ll be awarded medals. When you get enough medals, you’ll be promoted in rank. If you keep working at it, you just might become a general!\n\n";
					$strMessage .= "For more information\nTel: 718-467-4400 ext #4359\nemail: ckids@chabadchildren.com\nwww.jewishkids.org\n";
					mail($strTo, $strSubject, $strMessage, $strHeaders);
					json(array(
						"success" => "true",
						'pass' => $strPassword,
						'bar_code' => $intBarCode
					));
				}
			}
			else if (isset($_POST['action']) && $_POST['action'] == "inputimg")
			{
				$arrResult = array();
				if (
					empty($_FILES['image']['name'])
					|| $_FILES['image']['size'] == 0
				) {
					$arrResult['error'] = "Unexpected error: mf2wo9k";
					print json_encode($arrResult);
					exit;
				}
				if (!preg_match("/^image[\\\\\/]+(.+)/", $_FILES['image']['type'], $arrMatched))
				{
					$arrResult['error'] = 'This file type is not recognized: ' . $_FILES['image']['type'];
					print json_encode($arrResult);
					exit;
				}
				$strExtention = $arrMatched[1];
				if (!in_array($strExtention, array('jpeg', 'jpg', 'png', 'gif')))
				{
					$arrResult['error'] = 'This file type is not recognized: ' . $_FILES['image']['type'];
					print json_encode($arrResult);
					exit;
				}
				$objImgs = new Imgs();
				$strRand = rand(1000000,9999999);
				$intImgID = $objImgs->_imgs_insert(array(
					"img_category" => "",
					"img_type" => $strExtention,
					"user_id" => 1234,
					"img_name" => 'Pending'
				));
				$strImageName = $strRand . "_" . $intImgID. "." . $strExtention;
				$objImgs->_imgs_update(array(
					"where" => array(
						"img_id" => $intImgID
					),
					"values" => array(
						"img_name" => $strImageName
					)
				));
				$strRepoPath = SERVER_ROOT . "images/imgsrepo/";
				rename($_FILES['image']['tmp_name'], $strRepoPath . $strImageName);
				$arrResult['success'] = 'true';
				$arrResult['name'] = $strImageName;
				print json_encode($arrResult);
				exit;
			}
		}
	}

	function cardAction()
	{
		$query = new QueryGen();
		$this->view->boolURLError = FALSE;
		$arrGet = $this->_request->getParams();
		if (
			empty($arrGet['bc'])
			|| empty($arrGet['code'])
			|| !preg_match("/^[0-9]{16}$/", $arrGet['bc'])
			|| !preg_match("/^[0-9]{7}$/", $arrGet['code'])
		) {
			$this->view->boolURLError = TRUE;
			return;
		}
		$objUser = first($query->users__select(array(
			'bar_code' => $arrGet['bc'],
			'password' => $arrGet['code']
		)));
		if (!$objUser)
		{
			$this->view->boolURLError = TRUE;
			return;
		}
		$this->view->objUser = $objUser;
		$this->view->objInstitution = first($query->institutions__select(array(
			'institution_id' => 601
		)));
	}

	function idcardAction()
	{
		$query = new QueryGen();
		$objConfig = new Config();
		$arrGet = $this->_request->getParams();
		if (empty($arrGet["bc"]))
		{
			print "Sorry, there was an error: CMA-IC101-SD0F9D";
			exit;
		}
		if (empty($arrGet["code"]))
		{
			print "Sorry, there was an error: CMA-IC102-S3B3AS";
			exit;
		}
		$arrConfigOptions = $this->view->arrConfigOptions = $objConfig->load(array(
			"set" => array("system"),
			"institution_id" => 601
		));
		$this->view->arrUsers = $arrUsers = $query->users__select(array(
			"bar_code" => $arrGet["bc"],
			"password" => $arrGet["code"]
		));
		$objInstitution = $this->view->objInstitution = first($query->institutions__select(array(
			"institution_id" => 601
		)));
	}
	
	function idcardqrAction()
	{
		$query = new QueryGen();
		$objConfig = new Config();
		$arrGet = $this->_request->getParams();
		if (empty($arrGet["bc"]))
		{
			print "Sorry, there was an error: CMA-IC101-SD0F9D";
			exit;
		}
		if (empty($arrGet["code"]))
		{
			print "Sorry, there was an error: CMA-IC102-S3B3AS";
			exit;
		}
		$arrConfigOptions = $this->view->arrConfigOptions = $objConfig->load(array(
			"set" => array("system"),
			"institution_id" => 601
		));
		$this->view->arrUsers = $arrUsers = $query->users__select(array(
			"bar_code" => $arrGet["bc"],
			"password" => $arrGet["code"]
		));
		$objInstitution = $this->view->objInstitution = first($query->institutions__select(array(
			"institution_id" => 601
		)));
	}

	function loginAction()
	{
		$query = new QueryGen();
		if ($this->_request->isPost())
		{
			$arrPost = $this->_request->getPost();
			if (empty($arrPost['bar_code']))
			{
				json(array(
					'error' => "Unexpected error: ASM14fj13023f"
				));
			}
			$objUser = first($query->users__select(array(
				'bar_code' => $arrPost['bar_code']
			)));
			if (!$objUser)
			{
				json(array(
					"error" => "Barcode not found"
				));
			}
			json(array(
				'success' => 'true'
			));
		}
	}

	function missionsAction()
	{
		$query = new QueryGen();
		$arrGet = $this->_request->getParams();
		$this->view->boolShowMarkedOnly = $boolShowMarkedOnly = !empty($arrGet["cm"]) && $arrGet["cm"] == "true"; 
		if (empty($arrGet["bc"]))
		{
			print "Sorry, there was an error: CMA-IC101-SD0F9D";
			exit;
		}
		$this->view->objUser = $objUser = first($query->users__select(array(
			'bar_code' => $arrGet["bc"]
		)));

		$this->view->bc = $arrGet["bc"];
		if ($this->_request->isPost())
		{
			if (!$objUser)
			{
				json(array(
					'error' => "Unexpected error: MASSwadasois"
				));
			}
			$arrPost = $this->_request->getPost();
			if (empty($arrPost['task_id']))
			{
				json(array(
					'error' => "Unexpected error: Mafskfeoqlask"
				));
			}
			$objTask = first($query->ckids_mission_app__select(array(
				'task_id' => $arrPost['task_id']
			)));
			if (!$objTask)
			{
				json(array(
					'error' => "Unexpected error: SKsfkdie2o3k"
				));
			}
			$boolDateActive = $objTask->start_date <= time();
			$boolExpired = $objTask->end_date <= time();
			if (!$boolDateActive)
			{
				json(array(
					'failure' => "This mission is not active yet."
				));
			}
			if ($boolExpired)
			{
				json(array(
					'failure' => "This mission is no longer available."
				));
			}
			// check if its already been marked before marking
			$objMarking = first($query->ckids_mission_marking__select(array(
				'task_id' => $objTask->task_id,
				'user_id' => $objUser->user_id,
				"network_id" => 1
			)));
			if (!$objMarking)
			{
				if ($arrPost["marked"] == "0")
				{
					$intMission = $query->ckids_mission_marking__insert(array(
						'task_id' => $objTask->task_id,
						'user_id' => $objUser->user_id,
						"network_id" => 1
					));
					json(array(
						'success' => "true"
					));
				}
				else
				{
					json(array(
						'success' => "true",
						'bypass' => "true"
					));
				}
			}
			else
			{
				if ($arrPost["marked"] == "1")
				{
					$query->ckids_mission_marking__delete(array(
						'marking_id' => $objMarking->marking_id,
						'user_id' => $objUser->user_id,
						"network_id" => 1
					));
					json(array(
						'success' => "true",
						'unmark' => "true"
					));
				}
				else
				{
					json(array(
						'success' => "true",
						'bypass' => "true"
					));
				}

			}
			json(array(
				'error' => "Unexpected Error: MSFA2fqhs82nd"
			));
		}
		if (!$objUser)
		{
			$this->view->boolURLError = TRUE;
			return;
		}
		if ($boolShowMarkedOnly)
		{
			$arrMarking = $this->view->arrMarking = $query->ckids_mission_marking__select(array(
				"network_id" => 2,
				'user_id' => $objUser->user_id
			));
			$this->view->arrMissions = array_hash("task_id", $query->ckids_mission_app__select(array(
				"network_id" => 2,
				'task_id' => array_stack("task_id", $arrMarking)
			)));
		} 
		else
		{
			$this->view->arrMissions = $query->ckids_mission_app__select(array(
				"network_id" => 1,
				'_ORDER' => 'start_date+0 ASC'
			));
			$this->view->arrMarking = array_hash('task_id', $query->ckids_mission_marking__select(array(
				"network_id" => 1,
				'user_id' => $objUser->user_id
			)));
		}
		

	}
/*
	function setuppicsAction()
	{
		exit;
		$query = new QueryGen();
		$arrMissions = $query->ckids_mission_app__select(array(
			"image_id" => 0
		));
		foreach ($arrMissions as $objMission)
		{
			if ($objMission->pic_source == "https://www.dropbox.com/home/CKids/Mission%20Pictures/Ready?preview=06-sukkah.jpg")
				continue;
			$strSource = file_get_contents($objMission->pic_source);

			preg_match_all('/<meta content="([^"]+)" property="og:image" \/>/', $strSource, $arrMatched);
			if (count($arrMatched) != 2) {
				die("unexpected 12j12n2h241");
			}
			if (empty($arrMatched[1][0])) {
				die("unexpected msankf23r2jr");
			}
			$strPic = $arrMatched[1][0];

			$strPicSource = file_get_contents($strPic);
			$strRepoPath = SERVER_ROOT2 . "images/imgsrepo";





			$strRand = rand(1000000,9999999);
			$strExtention = "jpg";
			$objImgs = new Imgs();
			$intImgID = $objImgs->_imgs_insert(array(
				"img_category" => "",
				"img_type" => $strExtention,
				"user_id" => 1337,
				"img_name" => 'Pending'
			));
			$objImgs->_imgs_update(array(
				"where" => array(
					"img_id" => $intImgID
				),
				"values" => array(
					"img_name" => $strRand . "_" . $intImgID. "." . $strExtention
				)
			));

			file_put_contents($strRepoPath . "/" . $strRand . "_" . $intImgID. "." . $strExtention, $strPicSource);


			$query->ckids_mission_app__update(array(
				"where" => array(
					"task_id" => $objMission->task_id
				),
				"values" => array(
					"image_id" => $strRand . "_" . $intImgID. "." . $strExtention
				)
			));

			print "task: " . $objMission->task_id . " complete <br>\n";
			print "<img onload='window.setTimeout(function () {document.location.reload();},3000);' src='https://v2.mashpia.com/imgs/v/src/" . $strRand . "_" . $intImgID. "." . $strExtention . "' >";
			exit;
		}
		print "Nothing left";
		exit;
	}

	function setupdateepochsAction()
	{
		print "complete";
		exit;
		$query = new QueryGen();
		$arrMissions = $query->ckids_mission_app__select(array(
			"_ALL" => TRUE
		));
		foreach ($arrMissions as $objMission)
		{
			if (preg_match("/^([0-9]+)\/([0-9]+)\/([0-9]+)$/", $objMission->start_date, $arrMatched))
			{
				list ($strFull, $intMonth, $intDay, $intYear) = $arrMatched;
				$intEpoch = mktime(0,0,0,$intMonth,$intDay,$intYear);
				$query->ckids_mission_app__update(array(
					"where" => array(
						"task_id" => $objMission->task_id
					),
					"values" => array(
						"start_date_epoch" => $intEpoch
					)
				));
			} else {
				die("This is unexpected mfwmf1233");
			}
			if (preg_match("/^([0-9]+)\/([0-9]+)\/([0-9]+)$/", $objMission->end_date, $arrMatched))
			{
				list ($strFull, $intMonth, $intDay, $intYear) = $arrMatched;
				$intEpoch = mktime(0,0,0,$intMonth,$intDay,$intYear);
				$query->ckids_mission_app__update(array(
					"where" => array(
						"task_id" => $objMission->task_id
					),
					"values" => array(
						"end_date_epoch" => $intEpoch
					)
				));
			} else {
				die("This is unexpected smfkgn323");
			}
		}
		//var_dump($arrMissions);
		//dumper($arrMissions,1,1);
		print "done";
		exit;
	}
*/
}
?>