<?php
class TestController extends Zend_Controller_Action
{
	public function indexAction () {
		phpinfo();
		exit;
	}
	
	public function pointshistoryAction()
	{
		if ($this->_request->getParam("h") != "ioh2fohf223iohf")
			exit;
		$intIMSUser = $this->_request->getParam("user_id");
		$intInstitution = $this->_request->getParam("institution_id");
		if (empty($intIMSUser))
			$intIMSUser = 20505;
		
		$query = new QueryGen();
		$objUser = first($query->users__select(array(
			"user_id" => $intIMSUser
		)));
		$arrUserPoints = $query->user_points__select(array(
			"user_id" => $intIMSUser,
			"institution_id" => $intInstitution,
			"_ORDER" => "created ASC"
		));
		
		$intBalance = 0;
		$intOtherSystemBalance = 0;
		$intCorrectedBalance = 0;
		$intStartBal = 0;
		print "<style>td {padding:6px 12px;}</style>";
		print "<table>";
		print "<tr><td>resource</td><td>date</td><td>item</td><td>v2 balance</td><td>&nbsp;</td><td>&nbsp;</td><td>corrected balance</td><td>last known balance sum</td></tr>";
		foreach ($arrUserPoints as $objUserPoint) {
			if ($objUserPoint->resource_name == "admin_users_manual_store" && $objUserPoint->description == "legacy points reset") {
				$intOtherSystemBalance -= round($objUserPoint->points + $intOtherSystemBalance, 2);
				$intCorrectedBalance = round($intBalance + $intOtherSystemBalance + $objUserPoint->points, 2);
			}
			else
				$intCorrectedBalance += round($objUserPoint->points, 2);
			$intBalance += $objUserPoint->points;
			if ($objUserPoint->resource_name == "admin_users_manual_store" && $objUserPoint->description == "legacy points reset") {
				print "<tr><td>&nbsp</td><td>&nbsp</td><td>&nbsp</td><td>&nbsp</td><td>mashpia points</td><td>v2 points</td><td>&nbsp</td><td>&nbsp</td></tr>";
			}
			print "<tr><td width='100'>" . $objUserPoint->resource_name . " " . $objUserPoint->description . "</td><td>" . $objUserPoint->created . "</td><td>" . $objUserPoint->points . "</td><td>" . $intBalance . "</td><td>" . ($objUserPoint->resource_name == "admin_users_manual_store" ? $intOtherSystemBalance : "") . "</td><td>" . ($objUserPoint->resource_name == "admin_users_manual_store" ?  $intStartBal : "") . "</td><td>" . $intCorrectedBalance . "</td><td>" . ($intBalance + $intOtherSystemBalance + $intOtherSystemBalance) . "</td></tr>";
		}

		$objPoints = new Points();
		print "</table>";
		
		
		$arrLegacyUsers = array_hash('legacy_id', $query->legacy_lookup__select(array(
			'ims_id' => $intIMSUser,
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
		//var_dump($arrLegacyPoints);
		$intNew = floatval(@$objPoints->user_points_sums(array(
			"user_id" => $intIMSUser,
			"institution_id" => $intInstitution,
		))[$intIMSUser]["store"]);
		$intOld = $arrLegacyPoints[$intIMSUser];
		$intSum = round($intOld, 2) + round($intNew, 2);
		print "old: " . $intOld . "<br>\n";
		print "new: " . $intNew . "<br>\n";
		print "sum: " . $intSum . "<br>\n";
		exit;
	}
	
	public function qrAction() {
		print "Please let us know the model of your phone and which qr image your were able to scan without too much difficulty.<br>&nbsp;<br>";
		print "To test you can use the current mashpia barcode scanner. When the card is successfully scanned you will be logged into a test user account named \"adsasdd asdz\", this means the test worked. If nothing happens it means your camera likely couldnt see the image clearly enough so try a bigger code by going down the list.<br>&nbsp;<br>&nbsp;<br>&nbsp;<br>&nbsp;";
		print "<style>img {margin:30px;}</style>";
		print "<b>Very small</b><br>";
		print "The size of the images currently on the achievement cards.<br>";
		print "<img src='http://v2dev1.mashpia.com/test/randomqr' width='55' height='55' />";
		print "<div style='height:500px;border-top:1px solid #000;'></div>";

		print "<b>Standard size</b><br>";
		print "The default output size by the library.<br>";
		print "<img src='http://v2dev1.mashpia.com/test/randomqr' />";
		print "<div style='height:400px;border-top:1px solid #000;'></div>";

		print "<b>Enlarged</b><br>";
		print "A bit bigger than the standard.<br>";
		print "<img src='http://v2dev1.mashpia.com/test/randomqr' width='124' height='124' />";
		print "<div style='height:300px;border-top:1px solid #000;'></div>";

		print "<b>More enlarged</b><br>";
		print "About 75% the size of an achievement card<br>";
		print "<img src='http://v2dev1.mashpia.com/test/randomqr' width='145' height='145' />";
		print "<div style='height:300px;border-top:1px solid #000;'></div>";

		print "<b>Extra enlarged</b><br>";
		print "About current height of an achievement card<br>";
		print "<img src='http://v2dev1.mashpia.com/test/randomqr' width='190' height='190' />";
		print "<div style='height:200px;border-top:1px solid #000;'></div>";
		exit;
	}

	public function randomqrAction() {
		include SERVER_ROOT . "modules/phpqrcode/qrlib.php";
		QRcode::png('6256288851923084', NULL, QR_ECLEVEL_L, 4, 0);
		exit;
	}
	
	public function idandachievementAction() {
		if ($this->_request->getParam("q") != "kjqnkn23b4mn341kj124")
			exit;
		print "<h2>Id Card</h2>";
		print "<p>Used to sign in</p>";
		print "<img src='/images/testing/TestIDCard.jpg' />";
		print "<br>&nbsp;<br>&nbsp;<br>&nbsp;<br>&nbsp;<br>&nbsp;<h2>Point Cards</h2>";
		print "<p>Used to award points</p>";
		print "<img src='/images/testing/pointcards.jpg' />";
		exit;
	}
	
	public function fakeresetAction() {
		$query = new QueryGen();
		$objPoints = new Points();
		$roles = new Roles();
		$intUser = 10117;
		$intInstitution = 22;
		
		
		if (TRUE)
		{
			$arrParams = array(
			
			);
			$arrPost = array(
				"retro_active_date" => TRUE,
				"retro_date" => "08/01/2015" //2015-09-01
			);
			if (TRUE)
			{
				$intRetroDate = 0;
				if (isset($arrPost['retro_active_date']))
				{
					// make sure retro date is the correct format
					if (!preg_match('/([0-9]{1,2}) *[^0-9] *([0-9]{1,2}) *[^0-9] *([0-9]{4})/', $arrPost['retro_date'], $arrMatched))
					{
						print json_encode(array(
							'error' => 'The retroactive date you specified is not valid.'
						));
						exit;
					}
					$intRetroDate = mktime(0,0,0,$arrMatched[1],$arrMatched[2],$arrMatched[3]);
					// make sure retro date is in the past
					if ($intRetroDate > time())
					{
						print json_encode(array(
							'error' => 'The retroactive date you specified is not in the past.'
						));
						exit;
					}
				}
				// set all students points to zero
				$arrPermissions = array_hash('user_id', $query->permissions__select(array(
					'permission' => "Student",
					'user_id' => $intUser
				)));
				$arrUsersPointsParams = array(
					'user_id' => array_keys($arrPermissions)
				);
				if ($intRetroDate) {
					$arrUsersPointsParams['_LESSER']['_TIMESTAMP'] = array(
						'created' => $intRetroDate
					);
				}
				$arrUserResetPointsParams = $arrUsersPointsParams; 
				$arrUsersPoints = $objPoints->user_points_sums($arrUsersPointsParams);
				$arrUserResetPointsParams["resource_name"] = "admin_users_manual_store";
				$arrUserResetPointsParams["description"] = "legacy points reset";
				$arrUserResetPointsParams["_SUM"] = "points";
				$arrUserResetPointsParams["_COLUMNS"] = array();
				$objResetPointsSum = first($query->user_points__select($arrUserResetPointsParams));
				$intResetPointsSum = $objResetPointsSum->_sum_points;
				if (TRUE) {
					$arrUserIds = array_keys($arrPermissions);
					$arrLegacyUsers = array_hash('legacy_id', $query->legacy_lookup__select(array(
						'ims_id' => $arrUserIds,
						'legacy_table' => 'users',
						'ims_table' => 'users'
					)));
					$arrPost = array(
						'serialized_user_ids' => serialize(array_keys($arrLegacyUsers))
					);
					if ($intRetroDate)
						$arrPost['end_date'] = $intRetroDate;
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
					if (FALSE) {
						var_dump($arrLegacyPoints);
						exit;
					}
				}
				foreach ($arrLegacyPoints as $intUser => $intPoints)
				{
					if ($intPoints > 0 || $intPoints < 0)
					{
						$arrUserPointsInsertParams = array(
							'user_id' => $intUser,
							'resource_name' => 'admin_users_manual_store',
							'points' => -$intPoints,//-$intResetPointsSum,
							'description' => 'legacy points reset',
							'institution_id' => $intInstitution
						);
						if ($intRetroDate) {
							$arrUserPointsInsertParams['created'] = date( 'Y-m-d H:i:s', $intRetroDate );
						}
						var_dump($arrUserPointsInsertParams);
						$query->user_points__insert($arrUserPointsInsertParams);
					}
				}
				foreach ($arrUsersPoints as $intUser => $arrUserPoints)
				{
					if (TRUE)
					{
						$intStorePoints = $arrUserPoints['store'];
						if ($intStorePoints > 0 || $intStorePoints < 0)
						{
							$arrUserPointsInsertParams = array(
								'user_id' => $intUser,
								'resource_name' => 'admin_users_manual_store',
								'points' => -$intStorePoints,
								'description' => 'points reset',
								'institution_id' => $intInstitution
							);
							if ($intRetroDate) {
								$arrUserPointsInsertParams['created'] = date( 'Y-m-d H:i:s', $intRetroDate );
							}
							var_dump($arrUserPointsInsertParams);
							$query->user_points__insert($arrUserPointsInsertParams);

						}
					}
				}
				print json_encode(array(
					'intPermissionsCount' => count($arrPermissions),
					'success' => true
				));
				exit;
			}
		}
	}
}
?>