<?php
/*
	// Table of contents
*/

class Kiosk
{
	 private $_db;
	 private $_cart_session_data;
	 private $_user_session_data;
	 private $_kiosk_user_session_data; //hold session info about logged in KIOSK users
	 private $_tools;

	 public function __construct()
	 {
		// Start the DB objects
		$this->_db = Zend_Registry::get('db');
		$this->_db->setFetchMode(Zend_Db::FETCH_OBJ);

		// Start the session object
		$this->_user_session_data = new Zend_Session_Namespace('user_session_data');

		$this->_kiosk_user_session_data = new Zend_Session_Namespace("userInfo");

		// Start the session object
		$this->_cart_session_data = new Zend_Session_Namespace('cart');

		$this->_tools = new ToolsModels();
	 }


	 /**
	* Function that gets all the distinct prices for prizes based upon
	* institution id and class id
	**/
	 public function getDistinctPrizes($institution_id, $arrClasses)
	 {
		if (!isset($institution_id))
		{
			print "Sorry, there was an error: MK-GDP101-GHFG4F\n";
			exit;
		}
		$arrClassList = array();
        foreach($arrClasses as $objClass)
        {
			if (isset($objClass->class_id))
				$arrClassList[] = $objClass->class_id;
			else if (preg_match("/^[0-9]+$/",$objClass))
				$arrClassList[] = $objClass;
        }
		//Retrives distinct values of prizes
		$sql = "SELECT DISTINCT(prizes.points) ";
		$sql = $sql . "FROM prizes ";
		$sql = $sql . "WHERE prizes.prize_count > 0 ";
		$sql = $sql . "AND prizes.institution_id=" . $institution_id . " ";

		if (count($arrClassList)){
			$sql = $sql . "AND (prizes.class_id IS NULL OR prizes.class_id=0 OR prizes.class_id IN (". join(",",$arrClassList) . ")) ";
		}
		$sql = $sql . "AND prizes.is_active = 1 ORDER BY prizes.points ASC";

		//echo $sql . "<br />";exit;

		$arrResult = $this->_db->fetchAll($sql);
		return $arrResult;
	 }


	 /**
	* This function will be used on the NEW store front that is completely running on the new code
	**/
	 public function getDistinctPrizes_a($host_id, $network_id, $institution_id, $arrClasses)
	 {
		if (!isset($institution_id))
		{
			print "Sorry, there was an error: MK-GDP101-GHFG4F\n";
			exit;
		}

		$sql = '
		SELECT DISTINCT(prizes.points)
		FROM prizes
		WHERE (
			institution_id = '.$host_id.' OR
			institution_id = '.$network_id.' OR
			institution_id = '.$institution_id.'
			)
        AND prize_type!="Template"';
		//echo $sql; exit;
		$arrResult = $this->_db->fetchAll($sql);
		return $arrResult;
	 }

	 /**
	* Function that gets all the distinct prices for prizes based upon
	* institution id, class id, and the price
	**/
	 public function getPrizesByPoints($points, $institution_id, $arrClasses)
	 {
		$intClasses = array();
        foreach($arrClasses as $objClasses)
        {
			if (isset($objClasses->class_id))
				$intClasses[] = $objClasses->class_id;
        }
		$sql = "SELECT * ";
		$sql = $sql . "FROM prizes AS p ";
		$sql = $sql . "WHERE p.points=" . $points . " ";
		$sql = $sql . "AND p.prize_count > 0 ";
		if (count($intClasses))
			$sql = $sql . "AND (p.class_id IS NULL OR p.class_id=0 OR p.class_id IN (" . join(",", $intClasses) . ")) ";
		$sql = $sql . "AND p.is_active=1 AND p.institution_id=" . $institution_id;
		//print $sql; exit;
		$arrResult = $this->_db->fetchAll($sql);
		return $arrResult;

	 }

	 /**
	* This function will be used in the new store
	**/
	 public function getPrizesByPoints_a($points, $host_id, $network_id, $institution_id, $arrClasses)
	 {
		$sql = "SELECT * ";
		$sql = $sql . "FROM prizes AS p ";
		$sql = $sql . "WHERE p.points=" . $points . " ";
		if (count($arrClasses))
			$sql = $sql . "AND (p.class_id IS NULL OR p.class_id=0 OR p.class_id IN (" . join(",", $arrClasses) . ")) ";
		$sql = $sql . "AND p.institution_id=" . $institution_id;

		$sql = '
		SELECT * FROM prizes
		WHERE points = '.$points.'
		AND (
			institution_id = '.$host_id.' OR
			institution_id = '.$network_id.' OR
			institution_id = '.$institution_id.'
		)
        AND prize_type!="Template" AND prize_type!="Installable"';
		//print $sql; exit;
		$arrResult = $this->_db->fetchAll($sql);
		return $arrResult;

	 }


	public function getUserClasses($intUserId)
	{
		if (!$intUserId)
		{
			print "Sorry, there was an error: MK-GUC101-D8DFS8";
			exit;
		}
		$classes = array();
		$sql = "SELECT class_id FROM user_classes WHERE user_id=" . $intUserId;

		//echo $sql; exit;
		$arrResult = $this->_db->fetchAll($sql);

		foreach ($arrResult as $class) {
			 array_push($classes, $class->class_id);
		}

		return $classes;
	}

	public function getAllPrizes($institution_id, $arrClasses)
	{
		$sql = "SELECT * ";
		$sql = $sql . "FROM prizes AS p ";
		$sql = $sql . "WHERE p.prize_count > 0 ";
		$sql = $sql . "AND (p.class_id IS NULL OR p.class_id=0" . (count($arrClasses) ? " OR p.class_id IN (" . join(",", $arrClasses) . ")" : "") . ") ";
		$sql = $sql . "AND p.is_active = 1 AND p.institution_id=" . $institution_id . " ";

		//echo $sql; exit;
		$arrResult = $this->_db->fetchAll($sql);
		return $arrResult;
	}


	public function insertUserPrize($intUserId, $prize_id, $quantity)
	{
		//echo $intUserId ."--" . $prize_id . " == " . $quantity; exit;
		$arrFields = array (
			 "prize_id"			=> $prize_id,
			 "user_id"    		=> $intUserId,
			 "quantity"   		=> $quantity,
			 "status"     		=> "Checked Out",
			 "institution_id"	=> $this->_kiosk_user_session_data->institution_id,
			 "created"    		=> date("Y-m-d H:i:S"),
			 "created_by" 		=> $this->_kiosk_user_session_data->user_id);

		//var_dump($arrFields);
		$boolResult = $this->_db->insert("user_prizes", $arrFields);

		if ($boolResult) {
			 $intResult = $this->_db->lastInsertId();
			 return $intResult;
	   }
	}

	public function insertUserPoints($intUserId, $prize_id, $quantity)
	{
	   $sql = '
	   SELECT * FROM prizes WHERE prize_id = '.$prize_id;
	   $result = $this->_db->fetchAll($sql);
	   $date = date("Y-m-d H:i:s", time());

	   foreach($result as $r){
		   $arrInsert = array("user_id"		=> $intUserId,
							  "created" 	=> $date,
							  "created_by"	=> $intUserId,
							  "points"		=> (-1 * $r->points * $quantity)
							  );
			$this->_db->insert("user_points", $arrInsert);
	   }
	}

	public function user_points_sum_select($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);

		if (!isset($arrParams["user_id"]))
		{
			print "Sorry, there was an error: MK-UPSS101-BD727D";
			exit;
		}
		if (!is_array($arrParams["user_id"]))
			$arrParams["user_id"] = array($arrParams["user_id"]);
		$strSql = '
			SELECT
				SUM(points) AS total
			FROM
				user_points
			WHERE
				user_id IN (' . join(",", $arrParams["user_id"]) . ')';
		if (isset($arrParams["no_negs"]) && $arrParams["no_negs"])
		{
			$strSql .= '
				AND points > 0';
		}
		if (isset($arrParams["jd_date"]))
			$strSql .= '
				AND UNIX_TIMESTAMP(created) >= ' . jdtounix ($arrParams["jd_date"]);
		$objResult = $this->_db->fetchRow($strSql);
		return $objResult->total;
	}

	public function user_points_sum_multi_select($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);

		if (!isset($arrParams["user_id"]))
		{
			print "Sorry, there was an error: MK-UPSS101-BD727D";
			exit;
		}
		if (!is_array($arrParams["user_id"]))
			$arrParams["user_id"] = array($arrParams["user_id"]);
		$strSql = '
			SELECT
				SUM(points) AS total,
				user_id
			FROM
				user_points
			WHERE
				user_id IN (' . join(",", $arrParams["user_id"]) . ')';
		if (isset($arrParams["no_negs"]) && $arrParams["no_negs"])
		{
			$strSql .= '
				AND points > 0';
		}
		if (isset($arrParams["jd_date"]))
			$strSql .= '
				AND UNIX_TIMESTAMP(created) >= ' . jdtounix ($arrParams["jd_date"]);
		$strSql .= "
			GROUP BY
				user_id";
		//print $strSql;
		$arrResults = $this->_db->fetchAll($strSql);
		$arrResults = array_hash("user_id", $arrResults);
		return $arrResults;
	}

	public function dateThisYear($month, $day, $starting = 0, $year_offset = 0) {
		if(!$starting)
			$starting = unixtojd();

		$today = cal_from_jd($starting, CAL_JEWISH);
		$strDate = cal_to_jd(CAL_JEWISH, $month, $day, $today['year']+$year_offset-(cal_to_jd(CAL_JEWISH, $month, $day, $today['year']) >= $starting ? 1 : 0));
		return $strDate;
	}

	public function user_points_sum_select_hebrew($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);

		if (!isset($arrParams["user_id"]) || !$arrParams["user_id"])
		{
			print "Sorry, there was an error: MK-UPSS101-SD7D7D";
			exit;
		}

		$intMonth = 13;
		$intDay = 18;
		$year_offset = 0;
		$starting = 0;

		$strSql = '
			SELECT
				SUM(points) AS total
			FROM
				user_points
			WHERE
				user_id = ' . $arrParams["user_id"];
		$strSql .= '
				AND UNIX_TIMESTAMP(created) >= ' . jdtounix ($this->dateThisYear($intMonth, $intDay, $starting, $year_offset));
		$objResult = $this->_db->fetchRow($strSql);
		return $objResult->total;
	}

	public function getUserPointsTotal($user_id)
	{
		$strSql = '
			SELECT
				SUM(points) AS total
			FROM
				user_points
			WHERE
				user_id = ' . $user_id;
		$objResult = $this->_db->fetchRow($strSql);
		return $objResult->total;

	}
	/**
	* Function that gets all the checked out prizes for a user
	**/
	 function getCheckedOutUserPrizes($intUserId)
	 {
		  $strSql = "
			  SELECT
				up.*,
				p.prize_name
			FROM user_prizes AS up
			JOIN prizes AS p USING (prize_id) ";
		  $strSql = $strSql . "WHERE user_id=" . $intUserId . " ";
		  $strSql = $strSql . "AND status='Checked Out'";

		  $arrResult = $this->_db->fetchAll($strSql);
		  return $arrResult;

	 }

	 public function prizePrinted($user_prize_id)
	 {
			$strSql = '
			SELECT
				 *
			FROM
				 user_prizes
			WHERE
				 user_prize_id = '.$user_prize_id.'
			AND
				 status = "Printed"';



			$row = $this->_db->fetchAll($strSql);
			return (count($row)==0) ? 0 : 1;
	 }

	 public function userPrizeExist($user_prize_id)
	 {
			$strSql = '
			SELECT
				 *
			FROM
				 user_prizes
			WHERE
				 user_prize_id = '.$user_prize_id;

			$row = $this->_db->fetchAll($strSql);
			return (count($row)==0) ? 0 : 1;
	 }

	 public function updateUserPrize($user_prize_id, $serial="000")
	 {
			$strSql = '
			UPDATE
				 user_prizes
			SET
				 status = "Printed",
				 serial = "'.$serial.'"
			WHERE
				 user_prize_id = '.$user_prize_id;
			$this->_db->query($strSql);
	 }

	 public function insertOrder($user_prize_id, $serial)
	 {
		$user = new Zend_Session_Namespace("userInfo");

		$arrHQPrizesHash = array();
		foreach ($user->arrTHHQPrizes as $objItems)
		{
			$arrHQPrizesHash[$objItems["store_item"]] = $objItems;
		}

		$strSql = '
			SELECT
				 *
			FROM
				 user_prizes
			INNER JOIN
				 prizes
			ON
				 user_prizes.prize_id = prizes.prize_id
			WHERE
				 user_prize_id = '.$user_prize_id;

		$row = $this->_db->fetchAll($strSql);
		foreach($row as $r){
			 $description = $r->quantity . " " . $r->prize_name;
			 if (isset($arrHQPrizesHash[$r->prize_name]))
			 {
				$description .= " size: " . $arrHQPrizesHash[$r->prize_name]["item_size"];
			 }
			 $userId = $r->user_id;
			 $totalPrice = $r->prize_price * $r->quantity;
			 $createdBy = $r->user_id;
			 $currency = $r->currency;
		}

		$strSql = '
		INSERT INTO
			 orders
		(
			 user_id,
			 description,
			 currency,
			 total_price,
			 created,
			 created_by,
			 serial
		)
		VALUES
		(
			 '.$userId.',
			 "'.$description.'",
			 "'.$currency.'",
			 "'.$totalPrice.'",
			 "'.date("Y-m-d H:i:s", time()).'",
			 '.$createdBy.',
			 "'.$serial.'"
		)
		';

		return $this->_db->query($strSql);
	 }

	 public function validateOrder($serial)
	 {
		$arrFields = array(
			  "status" => "Redeemed"
		);

		$this->_db->beginTransaction();
		try{
			$this->_db->update("orders", $arrFields, "serial=" . $serial);
			$this->_db->update("user_prizes", $arrFields, "serial=" . $serial);
			$intResult = 1;
		} catch (Zend_Exception $e) {
			$db->rollBack();
			echo "There was an error KM-VO-JHSGFD";
			echo $e->getMessage();
		}

		return $intResult;

	 }

	 public function getOrder($serial)
	 {
		  $strSql = '
		  SELECT
				*
		  FROM
				orders
		  WHERE
				serial = "'.$serial.'"';
		  $row = $this->_db->fetchAll($strSql);
		  return $row;
	 }

	 public function getOldUserId($intUserId)
	 {
		 $strSql = "SELECT user_id FROM WHERE old_user_id=" . $intUserId;
	 }

	 public function populateOrders()
	 {
		$sql = 'SELECT user_prizes.user_prize_id, permissions.institution_id FROM user_prizes INNER JOIN permissions ON user_prizes.user_id = permissions.user_id';
		$rs = $this->_db->fetchAll($sql);

		//print_r($rs);

		foreach($rs as $r){
			$sql = 'UPDATE user_prizes SET institution_id = '.$r->institution_id.' WHERE user_prizes.user_prize_id = '.$r->user_prize_id;
			$this->_db->query($sql);
			echo $sql . " <br>";
		}
	 }

	 public function get_orders_by_user_id($user_id)
	 {
		$sql = '
		SELECT
			*
		FROM
			user_prizes
			INNER JOIN prizes
				ON user_prizes.prize_id = prizes.prize_id
			INNER JOIN users
				ON user_prizes.user_id = users.user_id
			INNER JOIN user_classes
				ON user_prizes.user_id = user_classes.user_id
			INNER JOIN classes
				ON user_classes.class_id = classes.class_id
			WHERE
				user_prizes.user_id = '.$user_id.'
			ORDER BY
				users.first_name, users.last_name
		';

		try{
			$result = $this->_db->fetchAll($sql);
		}catch (Zend_Exception $e) {
			echo 'There was an error: MK-GOBUI-KJSHGD' . "<hr>";
			//echo $sql;

		}

		return $result;
	 }

	public function get_order($user_prize_id)
	{
		$sql = '
		SELECT * FROM user_prizes
		INNER JOIN prizes
		ON user_prizes.prize_id = prizes.prize_id
		WHERE user_prizes.user_prize_id = '.$user_prize_id;
		//echo $sql; exit;
		try{
			$result = $this->_db->fetchRow($sql);
		} catch (Zend_Exception $e){
			echo 'There was an error: MK-GO-AJHSGD';
		}

		return $result;
	}

	public function update_order($arrUpdate)
	{
		$arrUpdateUserPrize = array("status"	=> $arrUpdate['status']);

		//$result = $this->_db->update("user_prizes", $arrUpdateUserPrize, "user_prize_id=".$arrUpdate['user_prize_id']);
		$sql = 'UPDATE user_prizes SET status = "'.$arrUpdate['status'].'" WHERE user_prize_id = '.$arrUpdate['user_prize_id'];
		$result = $this->_db->query($sql);

		if($arrUpdate['status'] == "Redeemed" && $arrUpdate['serial'] == ''){
			$arrInsert = array("user_id"		=> $arrUpdate['user_id'],
							   "status" 		=> $arrUpdate['status'],
							   "description"	=> $arrUpdate['description'],
							   "currency"		=> $arrUpdate['currency'],
							   "total_price"	=> $arrUpdate['price'],
							   "created"		=> date("Y-m-d H:i:s", time()),
							   "created_by"		=> $this->_user_session_data->user_id);
			$result = $this->_db->insert("orders", $arrInsert);
		} else {
			$sql = '
			UPDATE orders
			SET
				status = "Redeemed"
			WHERE serial = '.$arrUpdate['serial'];

			$result = $this->_db->query($sql);
		}
	}


	/**
	 * returns data to be used to print batches of vouchers from admin panel
	 *
	 * @param $arrUserPrizeIds - holds user_prize_id batches
	 *
	 * @return $arrPrizes
	 */
	function batch_print($arrUserPrizeIds)
	{
		$user_prize_ids = join(",", $arrUserPrizeIds);
		$sql = '
		SELECT
			user_prizes.serial,
			user_prizes.user_prize_id,
			user_prizes.user_id,
			user_prizes.quantity,
			institutions.name,
			users.first_name,
			users.last_name,
			prizes.prize_description,
			prizes.prize_name,
			prizes.prize_price,
			prizes.currency
		FROM user_prizes
		INNER JOIN institutions
			ON user_prizes.institution_id = institutions.institution_id
		INNER JOIN users
			ON user_prizes.user_id = users.user_id
		INNER JOIN prizes
			ON user_prizes.prize_id = prizes.prize_id
		WHERE user_prize_id IN ('.$user_prize_ids.')';

		try{
			$arrPrizes = $this->_db->fetchAll($sql);
		} catch (Zend_Exception $e){
			echo "There was an error: MK-BP-KJ876S";
			echo $e->getMessage();
		}

		foreach($arrPrizes as $r){
			if($r->serial == '' ){
				//generate serial and insert it into the orders table
				$serial = Utilities::generateSerial();
				$sql = '
				UPDATE user_prizes
				SET
					serial = "'.$serial.'",
					status = "Printed"
				WHERE user_prize_id = '.$r->user_prize_id;

				$r->serial = $serial;
				$result = $this->_db->query($sql);

				//create new record in orders table
				$arrInsert = array(	"user_id" 		=> $r->user_id,
									"description" 	=> $r->prize_name,
									"status" 		=> "Printed",
									"currency"		=> $r->currency,
									"item_id"		=> $r->user_prize_id,
									"item_id_ref"	=> "user_prizes",
									"total_price"	=> $r->prize_price,
									"serial" 		=> $serial,
									"created" 		=> date("Y-m-d H:i:s", time()),
									"created_by"	=> $this->_user_session_data->user_id);
				//print_r($arrInsert); exit;
				try{
					$result = $this->_db->insert("orders", $arrInsert);
				} catch (Zend_Exception $e) {
					//echo $e->getMessage();
				}
			} else {
				//make sure status is set to Printed in both orders and user_prizes table
				$sql = '
				UPDATE user_prizes
				SET status = "Printed"
				WHERE serial = "'.$r->serial.'"';

				$result = $this->_db->query($sql);

				$sql = '
				UPDATE orders
				SET status = "Printed"
				WHERE serial = "'.$r->serial.'"';

				$result = $this->_db->query($sql);
			}
		}

		return $arrPrizes;
	}

	/**
	 * Function selects all user_prizes based on institution id.
	 *
	 * @param int $institution_id
	 *
	 * @return arr $result
	 *
	 */
	public function orders_select_by_institution_id($institution_id)
	{

		if(!isset($institution_id) || $institution_id==0){
			$sql = 'SELECT * FROM user_prizes';
			$sql .= ' INNER JOIN prizes ON user_prizes.prize_id = prizes.prize_id
                     INNER JOIN users ON user_prizes.user_id = users.user_id
                     INNER JOIN user_classes ON user_prizes.user_id = user_classes.user_id
                     INNER JOIN classes ON user_classes.class_id = classes. class_id
                     WHERE prizes.prize_id = user_prizes.prize_id
                     ORDER BY users.last_name, users.first_name';
		}else{
			$utility = new Utilities();
			$childIds = $utility->getChildInstitutions($institution_id);
			$sql = 'SELECT * FROM user_prizes
            INNER JOIN prizes ON user_prizes.prize_id = prizes.prize_id
            INNER JOIN users
            ON user_prizes.user_id = users.user_id
            INNER JOIN user_classes
            ON user_prizes.user_id = user_classes.user_id
            INNER JOIN classes
            ON user_classes.class_id = classes.class_id
            WHERE user_prizes.institution_id IN ('.$childIds.')';
			$sql .= ' AND prizes.prize_id = user_prizes.prize_id
            ORDER BY users.last_name, users.first_name';
		}

		try{
            //echo $sql; exit;
			$result = $this->_db->fetchAll($sql);
		} catch (Zend_Exception $e){
			echo "There was an error: MK-OSBII-LHW189";
			if(DEV_ENV == 'devel'){
				//echo $sql;
				//echo $e->getMessage();
			}
		}
		return $result;
	}

    public function orders_select_by_institution_id_goup_by_classes($institution_id)
	{
        $sql = '
			SELECT
				*
			FROM
				user_prizes
				INNER JOIN prizes ON user_prizes.prize_id = prizes.prize_id
				INNER JOIN users ON user_prizes.user_id = users.user_id
				INNER JOIN user_classes ON user_prizes.user_id = user_classes.user_id
				INNER JOIN classes ON user_classes.class_id = classes.class_id
			WHERE
				user_prizes.institution_id IN ('.$institution_id.')
				AND prizes.prize_id = user_prizes.prize_id
			ORDER BY
				prizes.prize_name ASC';
		try{
            //echo $sql; exit;
			$result = $this->_db->fetchAll($sql);
		} catch (Zend_Exception $e){
			echo "There was an error: MK-OSBII-LHW189";
			if(DEV_ENV == 'devel'){
				//echo $sql;
				//echo $e->getMessage();
			}
		}
		return $result;
	}

	/**
	 * Function select all user_prizes based on class_id
	 *
	 * @param int $class_id
	 *
	 * @return arr $result
	 *
	 */
	public function orders_select_by_class_id($class_id)
	{
		$sql = '
		SELECT * FROM user_prizes
		INNER JOIN user_classes
		ON user_prizes.user_id = user_classes.user_id
        INNER JOIN users
        ON user_prizes.user_id = users.user_id
		INNER JOIN prizes
		ON prizes.prize_id = user_prizes.prize_id
        INNER JOIN classes
        ON user_classes.class_id = classes.class_id
		WHERE user_classes.class_id = '.$class_id .'
        ORDER BY users.last_name, users.first_name';

		try{
			$result = $this->_db->fetchAll($sql);
		}catch (Zend_Exception $e) {
			echo 'There was an error: MK-OSBUI-KJSHGD' . "<hr>";
			//echo $sql;

		}
		return $result;
	}
    public function orders_select_institutions ($intHost=0, $intNetworks=0, $intInstitutions=0, $status=1)
	{
		$strSql = "
			SELECT
				*
			FROM
				institutions
			WHERE
				" . (
					$intHost
					? "host_id=$intHost"
					: "host_id!=0"
				) . "
				 " . (
					$intNetworks
					? " AND network_id=$intNetworks"
					: "AND network_id!=0"
				) . "
				 " . (
					$intInstitutions
					? " AND institution_id=$intInstitutions"
					: "AND institution_id!=0"
				) . "
				AND is_active = ".$status;

		//echo $strSql; exit;
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
	}

	public function adjust_prize_quantity($prize_id, $quantity)
	{
		$sql = '
		UPDATE prizes
		SET prize_count = prize_count - '.$quantity.'
		WHERE prize_id = '.$prize_id;

		$return = $this->_db->query($sql);
	}

	/**
	 * Checks if there are any restrictions on the prize. Fro now we are only
	 * checking for a single rule which is one prize per student per year. This
	 * rule is currently hardcoded into the function
	 *
	 * @param $prize_id int
	 * @param $quantity int
	 * @param $user_id int
	 *
	 * @return $result obj
	 */
	public function prize_rules_apply($prize_id, $quantity, $user_id)
	{
		/*
		 is there a record in rules table for prize_id
			[YES]
				Is quantity within limits
					[YES]
						Is this user bought this item in the past?
							[YES]
								return false;
							[NO]
								return true;
					[NO]
						return false;
			[NO]
				return true;
		*/

		$result = new stdClass();

		$sql = '
		SELECT * FROM rules
		WHERE rules.prize_id = '.$prize_id.'
		AND rule_applies_to = "Prize"';
		if($this->_db->fetchAll($sql)){
			if($quantity > 1){
				$result->response_code = 0;
				$result->response = "There is a limit of one item per student.";
				return $result;
			} else {
				$sql = '
				SELECT * FROM rules
				WHERE rules.user_id = '.$user_id.'
				AND rules.prize_id = '.$prize_id;

				if($this->_db->fetchAll($sql)){
					$result->response_code = 0;
					$result->response = "It seems like you already bought this item in the past";
					return $result;
				}else{
					$result->response_code = 1;
					$result->response = "OK";
					return $result;
				}
			}
		}else{
			$result->response_code = 1;
			$result->response = "OK";
			return $result;
		}
	}

	public function insertUserRules($institution_id, $user_id, $prize_id)
	{
		//check to see if there is any reference to this rule in our rules table
		$sql = 'SELECT * FROM rules WHERE rules.prize_id = '.$prize_id;
		//echo $sql;
        $intDate = date("Y-m-d H:i:S");
		if($this->_db->fetchAll($sql)){
			$arrInsert = array("institution_id"		=> $institution_id,
							   "user_id"			=> $user_id,
							   "prize_id"			=> $prize_id,
							   "created"			=> $intDate,
							   "rule_applies_to"	=> "Prize",
							   "rule_type"			=> "Deny");
			$this->_db->insert("rules", $arrInsert);
		}
	}
}
?>
