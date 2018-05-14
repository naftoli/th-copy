<?php
class Points
{
	private $_db;
	private $_user_session_data;

	public function __construct()
	{
		// Start the DB objects
		$this->_db = Zend_Registry::get('db');
		$this->_db->setFetchMode(Zend_Db::FETCH_OBJ);

		// Start the session object
		$this->_user_session_data = new Zend_Session_Namespace('user_session_data');

		$this->_tools = new ToolsModels();
   	}

	public function user_points_sums($arrParams)
	{
		$query = new QueryGen();
		
		// implemented to take over the task of user_points_sum_multi_select
		if (isset($arrParams["jd_date"])) 
		{
			$arrParams[] = array(
				"_GREATER" => array(
					"_TIMESTAMP" => array(
						"created" => jdtounix ($arrParams["jd_date"])
					)
				)
			);
			unset($arrParams["jd_date"]);
		}
		
		$arrUserPoints = array_hash("user_point_id", $query->user_points__select($arrParams));
		//$arrParams["_ORDER"] = "created ASC, user_point_id ASC";
		//if (!isset($arrParams["institution_id"]))
		//	$arrParams["institution_id"] = $this->_user_session_data->institution_id;
		$arrPointSums = array();
		foreach ($arrUserPoints as $intItr => $objUserPoint)
		{
			$intUser = $objUserPoint->user_id;
			if (!isset($arrPointSums[$intUser]["store"]))
				$arrPointSums[$intUser]["store"] = 0;
			if (!isset($arrPointSums[$intUser]["total"]))
				$arrPointSums[$intUser]["total"] = 0;
			if (
				// Items that should be calculated against the store only
				$objUserPoint->resource_name == "admin_users_manual_store"
				|| $objUserPoint->resource_name == "store"
				|| !empty($objUserPoint->prize_id)
			) {
				$arrPointSums[$intUser]["store"] += $objUserPoint->points;
			}
			else if (
				// total only
				$objUserPoint->resource_name == "admin_users_manual_total"
			) {
				$arrPointSums[$intUser]["total"] += $objUserPoint->points;
			}
			else
			{
				$arrPointSums[$intUser]["total"] += $objUserPoint->points;
				$arrPointSums[$intUser]["store"] += $objUserPoint->points;
			}
		}
		return $arrPointSums;
	}

	public function user_total($arrParams)
	{
		if (!isset($arrParams["user_id"]))
		{
			print "Sorry, there was an error: MU-TP101-G2G5H4";
			exit;
		}
		if (!isset($arrParams["institution_id"]))
		{
			print "Sorry, there was an error: MU-TP102-8GG249";
			exit;
		}
		
		$strSql = "select sum(points) as total from user_points
					where user_id = " . $arrParams["user_id"] . "
					and institution_id = " . $arrParams["institution_id"] . "
					and points > 0"; // make sure we don't take off subtracted points
		if (intval($arrParams['start_date']) > 0) {
			$strDate = jdtogregorian( $arrParams['start_date'] );
			$arrDate = explode('/', $strDate);
			$strDate = $arrDate[2] . '-' . $arrDate[0] . '-' . $arrDate[1];
			$strSql .= " and created >= '" . $strDate . "'";
		}
		$strSql .= " and resource_name not in ('store', 'transaction_manager_store')";
		//echo $strSql; exit;
		$arrResult = first($this->_db->fetchAll($strSql));
		return $arrResult->total;
		/*
		$query = new QueryGen();
		$arrUserPointsParams = array(
			"_SUM" => "points",
			"_GROUP_BY" => "user_id",
			array(
				'prize_id' => 0,
				"_IS_NULL" => array(
					'prize_id'
				)
			),
			'_NOT' => array(
				'resource_name' => array(
					'admin_users_manual_store'
				)
			),
			array(
				"_GREATER" => array(
					"points" => 0
				),
				array(
					"_LESSER" => array(
						"points" => 0
					),
					array(
						"resource_name" => array(
							"admin_users_manual",
							"admin_users_manual_total"
						),
						"_IS_NOT_NULL" => array(
							"reversed_user_point_id"
						),
						"_NOT" => array(
							"reversed_user_point_id" => 0
						)
					)
				)
			)
		);
		array_merge_push($arrUserPointsParams, $arrParams);
		$objUserPoints = first($query->user_points__select($arrUserPointsParams));
		return $objUserPoints->_sum_points;
		*/
	}
	
	public function user_auction($arrParams)
	{
		if (!isset($arrParams["user_id"]))
		{
			print "Sorry, there was an error: MU-TP101-G2G5H4";
			exit;
		}
		if (!isset($arrParams["institution_id"]))
		{
			print "Sorry, there was an error: MU-TP102-8GG249";
			exit;
		}
		if (!isset($arrParams['auction_date'])) {
			print "Sorry, there was an error: MU-TP103-H3N2K9";
			exit;
		}
		
		$date = jdtogregorian($arrParams['auction_date']);
		$arrDate = explode('/', $date);
		$sqlDate = $arrDate[2] . '-' . $arrDate[0] . '-' . $arrDate[1];
		
		$strSql = "select sum(points) as total from user_points
					where user_id = " . $arrParams["user_id"] . "
					and institution_id = " . $arrParams["institution_id"] . "
					and created >= '" . $sqlDate . "' 
					and resource_name not in ('store', 'transaction_manager_store')";
		if ($arrParams["institution_id"] == 176) $strSql .= " and points > 0"; // they only use subtraction of points for their internal store
		$arrResult = first($this->_db->fetchAll($strSql));
		return floor($arrResult->total);
	}

	public function user_store($arrParams)
	{
		if (!isset($arrParams["user_id"]))
		{
			print "Sorry, there was an error: MU-TP101-G2G5H4";
			exit;
		}
		if (!isset($arrParams["institution_id"]))
		{
			print "Sorry, there was an error: MU-TP102-8GG249";
			exit;
		}
		if (!isset($arrParams["start_date"]))
		{
			print "Sorry, there was an error: MU-TP103-CH7J8M";
			exit;
		}
		
		$strSql = "select sum(points) as total from user_points
					where user_id = " . $arrParams["user_id"] . "
					and institution_id = " . $arrParams["institution_id"];
		if (intval($arrParams['start_date']) > 0) {
			$strDate = jdtogregorian( $arrParams['start_date'] );
			$arrDate = explode('/', $strDate);
			$strDate = $arrDate[2] . '-' . $arrDate[0] . '-' . $arrDate[1];
			$strSql .= " and created >= '" . $strDate . "'";
		}
		//echo $strSql; exit;
		$arrResult = first($this->_db->fetchAll($strSql));
		return floor($arrResult->total);
	
		/*
		$query = new QueryGen();
		$arrUserPointsParams = array(
			'_NOT' => array(
				'resource_name' => array(
					'admin_users_manual_total'
				)
			),
			"_SUM" => "points",
			"_GROUP_BY" => "user_id",
			"user_id" => $arrParams["user_id"],
			"institution_id" => $arrParams["institution_id"]
		);
		array_merge_push($arrUserPointsParams, $arrParams);
		$objUserPoints = first($query->user_points__select($arrUserPointsParams));
		return $objUserPoints->_sum_points;
		*/
	}

	public function _user_points_select($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);

		// Possible column selections
		$arrColumns = array (
			"user_point_id"			=> @$arrParams["user_point_id"],
			"prize_id"				=> @$arrParams["prize_id"],
			"achievement_card_id"	=> @$arrParams["achievement_card_id"],
			"user_id"				=> @$arrParams["user_id"],
			"campaign_id"			=> @$arrParams["campaign_id"],
			"mission_id"			=> @$arrParams["mission_id"],
			"task_id"				=> @$arrParams["task_id"],
			"institution_id"		=> @$arrParams["institution_id"],
			"class_id"				=> @$arrParams["class_id"],
			"points"				=> @$arrParams["points"],
			"rule_id"				=> @$arrParams["rule_id"],
			"resource_name"			=> @$arrParams["resource_name"],
			"created"				=> date("Y-m-d H:i:S"),
			"created_by"			=> @$arrParams["created_by"]
		);

		$strSql = "
			SELECT
				*
			FROM
				user_points
			WHERE
				1
		";

		foreach ($arrColumns as $strColumn => $Value)
		{
			if (
				isset($Value)
				&& (
					$Value === 0
					|| $Value
				)
			) {
				if (!is_int($Value))
				{
					$Value = '"' . $Value . '"';
				}
				$strSql .= "
					AND `" . $strColumn . "` = " . $Value . "
				";
			}
		}

		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
	}

	public function _user_points_insert($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);

		if (!isset($arrParams["created_by"]))
		{
			$arrParams["created_by"] = $this->_user_session_data->user_id;
		}

		$arrFeilds = array (
			"user_point_id"			=> @$arrParams["user_point_id"],
			"prize_id"				=> @$arrParams["prize_id"],
			"achievement_card_id"	=> @$arrParams["achievement_card_id"],
			"user_id"				=> @$arrParams["user_id"],
			"campaign_id"			=> @$arrParams["campaign_id"],
			"mission_id"			=> @$arrParams["mission_id"],
			"task_id"				=> @$arrParams["task_id"],
			"institution_id"		=> @$arrParams["institution_id"],
			"class_id"				=> @$arrParams["class_id"],
			"points"				=> @$arrParams["points"],
			"rule_id"				=> @$arrParams["rule_id"],
			"resource_name"			=> @$arrParams["resource_name"],
			"created"				=> date("Y-m-d H:i:S"),
			"created_by"			=> @$arrParams["created_by"]
		);

		// Execute
		$boolResult = $this->_db->insert("user_points", $arrFeilds);
		if ($boolResult)
		{
			return $this->_db->lastInsertId();
		}
	}

	public function _user_points_update($arrParams)
	{
		$arrValuesParams = array("user_id","prize_id","achievement_card_id","campaign_id","mission_id","task_id","institution_id","class_id","points","rule_id","resource_name","created","created_by");
		$arrWhereParams = array("user_point_id","prize_id","achievement_card_id","user_id","campaign_id","mission_id","task_id","institution_id","class_id","points","rule_id","resource_name","created","modified","created_by");

		$arrParams = $this->_tools->rsqlclean($arrParams);

		$arrValues = array();

		// Values

		foreach ($arrValuesParams as $strKey)
		{
			if (isset($arrParams["values"][$strKey]))
				$arrValues[$strKey] = $arrParams["values"][$strKey];
		}
		$arrValues["modified"] = date("Y-m-d H:i:S");

		// Where
		$arrWhere = array();
		foreach ($arrWhereParams as $strKey)
		{
			if (isset($arrParams["where"][$strKey]))
				$arrWhere[] = $this->_db->quoteInto($strKey . ' = ?', $arrParams["where"][$strKey]);
		}

		if (!count($arrWhere))
		{
			print "Sorry, there was an error: MP-UPU101-7S7S8A";
			exit;
		}

		// Execute
		$boolResult = $this->_db->update("user_points", $arrValues, $arrWhere);
		return $boolResult;
	}

	/*
	 * Required: user_id
	 */
	public function user_points_sum($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);

		if (!isset($arrParams["user_id"]))
		{
			print "Sorry, there was an error: MP-UPS101-7DSFDD";
			exit;
		}

		$strSql = "
			SELECT
				SUM(points) AS total
			FROM
				user_points
			WHERE";
		if (is_array($arrParams["user_id"]))
		{
			$strSql .= "
				user_id IN (" . join(",", $arrParams["user_id"]) . ")";
		}
		else
		{
			$intUser = intval($arrParams["user_id"]);
			$strSql .= "
				user_id = " . $intUser;
		}

		$intTotalPoints = first($this->_db->fetchRow($strSql));
		return $intTotalPoints;
	}

	/*
	 * Required: institution_id
	 */
	public function institution_points_sum($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);

		if (!isset($arrParams["institution_id"]))
		{
			print "Sorry, there was an error: MP-UPS101-D8FDSS";
			exit;
		}
		$intInstitution = intval($arrParams["institution_id"]);

		$strSql = "
			SELECT
				SUM(points) AS total
			FROM
				user_points
			WHERE
				institution_id = " . $intInstitution;

		$intTotalPoints = first($this->_db->fetchRow($strSql));
		return $intTotalPoints;
	}


	public function points_insert($user_id, $class_id, $institution_id, $campaign_id, $points, $created_by)
	{

		$strSql = '
		INSERT INTO user_points
		(
			user_id,
			campaign_id,
			institution_id,
			class_id,
			points,
			created,
			created_by
		)
		VALUES
		(
			'.$user_id.',
			'.$campaign_id.',
			'.$institution_id.',
			'.$class_id.',
			'.$points.',
			"'.date("Y-m-d H:i:s", time()).'",
			'.$created_by.'
		)
		';
		//echo $strSql; exit;
		$result = $this->_db->query($strSql);
		return $result;
	}

	/**
	 * Returns a three-dimensional array containing the sum of of all points for
	 * each user for the specific campaign
	 *
	 * @param arr $arrUsers     contains the users
	 * @param arr $arrCampaigns contains the campaigns
	 *
	 * @return arr  arrUserData[user_id][campaign_id]=total_points
	 */
	public function pointsuserdata($arrUsers, $arrCampaigns)
	{
		foreach($arrUsers as $user){
			//echo $user->user_id . "<br>";
			foreach($arrCampaigns as $campaign){
				$strSql='
				SELECT SUM(points) AS total_points FROM user_points
				WHERE user_id = '.$user->user_id.'
				AND campaign_id = '.$campaign->campaign_id;
				$result = $this->_db->fetchRow($strSql);
				//echo $result->total_points . " points.<br>";

				if($result->total_points == '') $result->total_points = 0;

				$arrUserData[$user->user_id][$campaign->campaign_id] = $result->total_points;
			}
		}

		return $arrUserData;
	}

	public function user_points($arrParams)
	{
		if (!isset($arrParams['user_id']))
		{
			print "Sorry, there was an error: MP-UP101-HDS242";
			exit;
		}
		$query = new QueryGen();
		if (!isset($arrParams['institution_id']))
			$arrParams['institution_id'] = $this->_user_session_data->institution_id;
		$arrUserPointParams = array(
			"user_id" => $arrParams['user_id'],
			"institution_id" => $arrParams['institution_id'],
			"_ORDER" => "created ASC, user_point_id ASC"
		);
		$arrUserPoints = $query->user_points__select($arrUserPointParams);
		$arrResults = array();

		// sort the data
		$arrPointSums = array(
			"total" => 0,
			"store" => 0
		);
		foreach ($arrUserPoints as $intItr => $objUserPoint)
		{
			// store only
			if (
				$objUserPoint->resource_name == "admin_users_manual_store"
				|| $objUserPoint->resource_name == "store"
				|| !empty($objUserPoint->prize_id)
			) {
				$arrPointSums["store"] += $objUserPoint->points;
			}
			// total only
			else if ($objUserPoint->resource_name == "admin_users_manual_total")
			{
				$arrPointSums["total"] += $objUserPoint->points;
			}
			// all
			else
			{
				$arrPointSums["total"] += $objUserPoint->points;
				$arrPointSums["store"] += $objUserPoint->points;
			}
		}
		return (object) $arrPointSums;
	}
}