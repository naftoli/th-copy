<?php
class Store
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

	public function _prizes_select($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);

		// Possible column selections
		$arrColumns = array (
			"prize_id"					=> @$arrParams["prize_id"],
			"template_prize_id"			=> @$arrParams["template_prize_id"],
			"parent_prize_id"			=> @$arrParams["parent_prize_id"],
			"teacher_id"				=> @$arrParams["teacher_id"],
			"guardian_id"				=> @$arrParams["guardian_id"],
			"institution_id"			=> @$arrParams["institution_id"],
			"prize_name"				=> @$arrParams["prize_name"],
			"prize_category"			=> @$arrParams["prize_category"],
			"prize_description"			=> @$arrParams["prize_description"],
			"image_id"					=> @$arrParams["image_id"],
			"add_on_restricted"			=> @$arrParams["add_on_restricted"],
			"use_sub_prizes"			=> @$arrParams["use_sub_prizes"],
			"one_per_user"				=> @$arrParams["one_per_user"],
			"prize_count"				=> @$arrParams["prize_count"],
			"points"					=> @$arrParams["points"],
			"prize_type"				=> @$arrParams["prize_type"],
			"installable_default_on"	=> @$arrParams["installable_default_on"],
			"prize_price"				=> @$arrParams["prize_price"],
			"prize_discounted_price"	=> @$arrParams["prize_discounted_price"],
			"is_active"					=> @$arrParams["is_active"],
			"created"					=> @$arrParams["created"],
			"modified"					=> @$arrParams["modified"],
			"created_by"				=> @$arrParams["created_by"]
		);

		$strSql = "
			SELECT
				*
			FROM
				prizes
			WHERE
				1
		";
		//var_dump($arrColumns);
		foreach ($arrColumns as $strColumn => $Value)
		{
			if (is_array($Value))
			{
				$arrValues = array();
				foreach ($Value as $SubValue)
				{
					if (
						$SubValue != NULL
					) {
						if (!is_int($SubValue))
						{
							$SubValue = '"' . $SubValue . '"';
						}
						$arrValues[] = $SubValue;
					}
				}
				if (count($arrValues))
				{
					$strSql .= "
						AND `" . $strColumn . "` IN (" . join(",", $arrValues) . ")
					";
				}
			}
			else if (
				isset($Value)
				&& (
					$Value != NULL
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
		if (isset($arrParams["_ORDER"]))
		{
			$strSql .= "
				ORDER BY
					" . $arrParams["_ORDER"];
		}
		else
		{
			$strSql .= "
				ORDER BY
					created DESC";
		}
		//print $strSql;
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
	}

	public function _prizes_insert($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);

		if (!isset($arrParams["created_by"]))
		{
			$arrParams["created_by"] = $this->_user_session_data->user_id;
		}

		$arrFeilds = array (
			"template_prize_id"			=> @$arrParams["template_prize_id"],
			"parent_prize_id"			=> @$arrParams["parent_prize_id"],
			"teacher_id"				=> @$arrParams["teacher_id"],
			"guardian_id"				=> @$arrParams["guardian_id"],
			"institution_id"			=> @$arrParams["institution_id"],
			"prize_name"				=> @$arrParams["prize_name"],
			"prize_category"			=> @$arrParams["prize_category"],
			"prize_description"			=> @$arrParams["prize_description"],
			"image_id"					=> @$arrParams["image_id"],
			"add_on_restricted"			=> @$arrParams["add_on_restricted"],
			"use_sub_prizes"			=> @$arrParams["use_sub_prizes"],
			"one_per_user"				=> @$arrParams["one_per_user"],
			"prize_count"				=> @$arrParams["prize_count"],
			"points"					=> @$arrParams["points"],
			"prize_type"				=> @$arrParams["prize_type"],
			"installable_default_on"	=> @$arrParams["installable_default_on"],
			"prize_price"				=> @$arrParams["prize_price"],
			"prize_discounted_price"	=> @$arrParams["prize_discounted_price"],
			"is_active"					=> @$arrParams["is_active"],
			"created"					=> @$arrParams["created"]
		);

		// Execute
		$boolResult = $this->_db->insert("prizes", $arrFeilds);
		if ($boolResult)
		{
			return $this->_db->lastInsertId();
		}
	}

	public function _prizes_update($arrParams)
	{
		$arrValuesParams = array("template_prize_id", "parent_prize_id", "teacher_id", "guardian_id", "institution_id", "prize_name", "prize_category", "prize_description", "image_id", "add_on_restricted", "use_sub_prizes", "one_per_user", "prize_count", "points", "prize_type", "installable_default_on", "prize_price", "prize_discounted_price", "is_active");
		$arrWhereParams = array("prize_id", "template_prize_id", "parent_prize_id", "teacher_id", "guardian_id", "institution_id", "prize_name", "prize_category", "prize_description", "image_id", "add_on_restricted", "use_sub_prizes", "one_per_user", "prize_count", "points", "prize_type", "installable_default_on", "prize_price", "prize_discounted_price", "is_active", "created", "modified", "created_by");

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
			print "Sorry, there was an error: MC-BU101-TRTHTT";
			exit;
		}

		// Execute
		$boolResult = $this->_db->update("prizes", $arrValues, $arrWhere);
		return $boolResult;
	}

	public function _prizes_delete($arrParams)
	{
		$arrWhereParams = array("prize_id", "template_prize_id", "parent_prize_id", "teacher_id", "guardian_id", "institution_id", "prize_name", "prize_category", "prize_description", "image_id", "add_on_restricted", "use_sub_prizes", "one_per_user", "prize_count", "points", "prize_type", "installable_default_on", "prize_price", "prize_discounted_price", "is_active", "created", "modified", "created_by");
		$arrParams = $this->_tools->rsqlclean($arrParams);
		$arrFeilds = array();
		foreach ($arrWhereParams as $strKey)
		{
			if (isset($arrParams[$strKey]))
				$arrFeilds[] = $this->_db->quoteInto($strKey . ' = ?', $arrParams[$strKey]);
		}
		if (!count($arrFeilds))
		{
			print "Sorry, there was an error: MB-BLD101-SD7SD7";
			exit;
		}
		$boolResult = $this->_db->delete("prizes", $arrFeilds);
		return $boolResult;
	}

	public function _prize_classes_select ($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);

		// Possible column selections
		$arrColumns = array (
			"prize_class_id"	=> @$arrParams["prize_class_id"],
			"prize_id"				=> @$arrParams["prize_id"],
			"class_id"				=> @$arrParams["class_id"]
		);

		$strSql = "
			SELECT
				*
			FROM
				prize_classes
			WHERE
				1
		";

		foreach ($arrColumns as $strColumn => $Value)
		{
			if (
				isset($Value)
				&& (
					$Value === 0
					|| is_string($Value)
					|| $Value
				)
			) {
				if (
					is_array($Value)
					&& count($Value)
				) {
					$arrValues = array();
					foreach ($Value as $Key1 => $Value1)
					{
						if (!is_int($Value1))
						{
							$Value1 = '"' . $Value1 . '"';
						}
						$arrValues[] = $Value1;

					}
					$strSql .= "
						AND `" . $strColumn . "` IN (" . join(",", $arrValues) . ")
					";
				}
				else
				{
					if (!is_int($Value))
					{
						$Value = '"' . $Value . '"';
					}
					$strSql .= "
						AND `" . $strColumn . "` = " . $Value . "
					";
				}
			}
		}
		if (isset($arrParams["_ORDER"]))
		{
			$strSql .= "
				ORDER BY
					" . $arrParams["_ORDER"];
		}
		//print $strSql;
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
	}

	public function _prize_classes_insert($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);

		if (!isset($arrParams["created_by"]))
		{
			$arrParams["created_by"] = $this->_user_session_data->user_id;
		}

		$arrFeilds = array (
			"prize_id"				=> @$arrParams["prize_id"],
			"class_id"				=> @$arrParams["class_id"]
		);

		// Execute
		$boolResult = $this->_db->insert("prize_classes", $arrFeilds);
		if ($boolResult)
		{
			return $this->_db->lastInsertId();
		}
	}

	public function _prize_classes_update($arrParams)
	{
		$arrValuesParams = array("prize_id", "class_id");
		$arrWhereParams = array("prize_class_id", "prize_id", "class_id");

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
			print "Sorry, there was an error: MC-BU101-TRTHTT";
			exit;
		}

		// Execute
		$boolResult = $this->_db->update("prize_classes", $arrValues, $arrWhere);
		return $boolResult;
	}

	public function _prize_classes_delete($arrParams)
	{
		$arrWhereParams = array("prize_class_id", "prize_id", "class_id");
		$arrParams = $this->_tools->rsqlclean($arrParams);
		$arrFeilds = array();
		foreach ($arrWhereParams as $strKey)
		{
			if (isset($arrParams[$strKey]))
				$arrFeilds[] = $this->_db->quoteInto($strKey . ' = ?', $arrParams[$strKey]);
		}
		if (!count($arrFeilds))
		{
			print "Sorry, there was an error: MB-BLD101-SD7SD7";
			exit;
		}
		$boolResult = $this->_db->delete("prize_classes", $arrFeilds);
		return $boolResult;
	}

	public function _prize_school_types_select ($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);

		// Possible column selections
		$arrColumns = array (
			"prize_class_id"	=> @$arrParams["prize_class_id"],
			"prize_id"			=> @$arrParams["prize_id"],
			"school_type"		=> @$arrParams["school_type"]
		);

		$strSql = "
			SELECT
				*
			FROM
				prize_school_types
			WHERE
				1
		";

		foreach ($arrColumns as $strColumn => $Value)
		{
			if (
				isset($Value)
				&& (
					$Value === 0
					|| is_string($Value)
					|| $Value
				)
			) {
				if (
					is_array($Value)
					&& count($Value)
				) {
					$arrValues = array();
					foreach ($Value as $Key1 => $Value1)
					{
						if (!is_int($Value1))
						{
							$Value1 = '"' . $Value1 . '"';
						}
						$arrValues[] = $Value1;

					}
					$strSql .= "
						AND `" . $strColumn . "` IN (" . join(",", $arrValues) . ")
					";
				}
				else
				{
					if (!is_int($Value))
					{
						$Value = '"' . $Value . '"';
					}
					$strSql .= "
						AND `" . $strColumn . "` = " . $Value . "
					";
				}
			}
		}
		if (isset($arrParams["_ORDER"]))
		{
			$strSql .= "
				ORDER BY
					" . $arrParams["_ORDER"];
		}
		//print $strSql;
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
	}

	public function _prize_school_types_insert($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);

		if (!isset($arrParams["created_by"]))
		{
			$arrParams["created_by"] = $this->_user_session_data->user_id;
		}

		$arrFeilds = array (
			"prize_id"				=> @$arrParams["prize_id"],
			"school_type"				=> @$arrParams["school_type"]
		);

		// Execute
		$boolResult = $this->_db->insert("prize_school_types", $arrFeilds);
		if ($boolResult)
		{
			return $this->_db->lastInsertId();
		}
	}

	public function _prize_school_types_update($arrParams)
	{
		$arrValuesParams = array("prize_id", "school_type");
		$arrWhereParams = array("prize_class_id", "prize_id", "school_type");

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
			print "Sorry, there was an error: MC-BU101-TRTHTT";
			exit;
		}

		// Execute
		$boolResult = $this->_db->update("prize_school_types", $arrValues, $arrWhere);
		return $boolResult;
	}

	public function _prize_school_types_delete($arrParams)
	{
		$arrWhereParams = array("prize_class_id", "prize_id", "school_type");
		$arrParams = $this->_tools->rsqlclean($arrParams);
		$arrFeilds = array();
		foreach ($arrWhereParams as $strKey)
		{
			if (isset($arrParams[$strKey]))
				$arrFeilds[] = $this->_db->quoteInto($strKey . ' = ?', $arrParams[$strKey]);
		}
		if (!count($arrFeilds))
		{
			print "Sorry, there was an error: MB-BLD101-SD7SD7";
			exit;
		}
		$boolResult = $this->_db->delete("prize_school_types", $arrFeilds);
		return $boolResult;
	}

	public function _user_prizes_select($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);

		// Possible column selections
		$arrColumns = array (
			"user_prize_id"		=> @$arrParams["user_prize_id"],
			"prize_id"			=> @$arrParams["prize_id"],
			"user_id"			=> @$arrParams["user_id"],
			"institution_id"	=> @$arrParams["institution_id"],
			"quantity"			=> @$arrParams["quantity"],
			"serial"			=> @$arrParams["serial"],
			"status"			=> @$arrParams["status"],
			"created"			=> @$arrParams["created"],
			"modified"			=> @$arrParams["modified"],
			"created_by"		=> @$arrParams["created_by"]
		);

		$strSql = "
			SELECT
				*
			FROM
				user_prizes
			WHERE
				1
		";

		foreach ($arrColumns as $strColumn => $Value)
		{
			if (is_array($Value))
			{
				$arrValues = array();
				foreach ($Value as $SubValue)
				{
					if (
						$SubValue === "0"
						|| $SubValue === 0
						|| $SubValue
					) {
						if (!is_int($SubValue))
						{
							$SubValue = '"' . $SubValue . '"';
						}
						$arrValues[] = $SubValue;
					}
				}
				if (count($arrValues))
				{
					$strSql .= "
						AND `" . $strColumn . "` IN (" . join(",", $arrValues) . ")
					";
				}
			}
			else if (
				isset($Value)
				&& (
					$Value === "0"
					|| $Value === 0
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

		if (isset($arrParams["LIMIT"]))
		{
			$strSql .= "
				LIMIT " . $arrParams["LIMIT"];
		}

		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
	}

	public function _user_prizes_insert($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);

		if (!isset($arrParams["created_by"]))
		{
			$arrParams["created_by"] = $this->_user_session_data->user_id;
		}

		$arrFeilds = array (
			"user_prize_id"		=> @$arrParams["user_prize_id"],
			"prize_id"			=> @$arrParams["prize_id"],
			"user_id"			=> @$arrParams["user_id"],
			"institution_id"	=> @$arrParams["institution_id"],
			"quantity"			=> @$arrParams["quantity"],
			"serial"			=> @$arrParams["serial"],
			"status"			=> @$arrParams["status"],
			"created"			=> @$arrParams["created"],
			"modified"			=> @$arrParams["modified"],
			"created_by"		=> @$arrParams["created_by"]
		);

		// Execute
		$boolResult = $this->_db->insert("user_prizes", $arrFeilds);
		if ($boolResult)
		{
			return $this->_db->lastInsertId();
		}
	}

	public function _user_prizes_update($arrParams)
	{
		$arrValuesParams = array("prize_id","user_id","institution_id","quantity","serial","status","modified","created_by");
		$arrWhereParams = array("user_prize_id","prize_id","user_id","institution_id","quantity","serial","status","created","modified","created_by");

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

		foreach ($arrWhereParams as $strKey)
		{

			if (isset($arrParams["where"][$strKey]))
			{
				$Value = $arrParams["where"][$strKey];
				if (is_array($Value))
				{
					$arrTempValues = array();
					foreach ($Value as $SubValue)
					{
						if (
							$SubValue === "0"
							|| $SubValue === 0
							|| $SubValue
						) {
							if (!is_int($SubValue))
							{
								$SubValue = '"' . $SubValue . '"';
							}
							$arrTempValues[] = $SubValue;
						}
					}
					if (count($arrTempValues))
					{
						$arrWhere[] = "`" . $strKey . "` IN (" . join(",", $arrTempValues) . ")";
					}
				}
				else
					$arrWhere[] = $this->_db->quoteInto($strKey . ' = ?', $Value);
			}
		}

		if (!count($arrWhere))
		{
			print "Sorry, there was an error: MB-BLU101-ASDASD";
			exit;
		}

		// Execute
		$boolResult = $this->_db->update("user_prizes", $arrValues, $arrWhere);
		return $boolResult;
	}

	public function _user_prizes_delete($arrParams)
	{
		$arrWhereParams = array("user_prize_id","prize_id","user_id","institution_id","quantity","serial","status","created","modified","created_by");
		$arrParams = $this->_tools->rsqlclean($arrParams);
		$arrFeilds = array();
		foreach ($arrWhereParams as $strKey)
		{
			if (isset($arrParams[$strKey]))
				$arrFeilds[] = $this->_db->quoteInto($strKey . ' = ?', $arrParams[$strKey]);
		}
		if (!count($arrFeilds))
		{
			print "Sorry, there was an error: MB-BLD101-SD7SD7";
			exit;
		}
		$boolResult = $this->_db->delete("user_prizes", $arrFeilds);
		return $boolResult;
	}

	public function user_available_prizes($arrParams)
	{
		if (!isset($arrParams["user_id"]))
		{
			print "Sorry, there was an error: MS-UAP101-DDFFA9";
			exit;
		}
		if (!isset($arrParams["institution_id"]))
			$arrParams["institution_id"] = $this->_user_session_data->institution_id;
		$query = new QueryGen();
		$arrUserAddons = array_stack("prize_id", $query->user_addons__select(array(
			"user_id" => $arrParams["user_id"]
		)));
		$arrUserClasses = array_stack("class_id", $query->user_classes__select(array(
			"user_id" => $arrParams["user_id"]
		)));
		// Load all the aplicable prizes
		$arrPrizes = array_hash("prize_id", $query->prize__select(array(
			"institution_id" => $arrParams["institution_id"],
			"parent_prize_id" => "0",
			"is_active" => 1,
			"_ORDER" => "prize_id+0 ASC",
			"_NOT" => array(
				"prize_count" => 0
			)
		)));
		$arrPrizeClasses = array_stack("prize_id", "class_id", $query->prize_classes__select(array(
			"prize_id" => array_keys($arrPrizes)
		)));
		// Loop through prizes to find the sub category
		$arrNewPrizes = array();
		foreach ($arrPrizes as $intPrize => $objPrize)
		{
			if (
				isset($arrPrizeClasses[$intPrize])
				&& count($arrPrizeClasses[$intPrize])
				&& !array_in_array(array_keys($arrUserClasses), array_keys($arrPrizeClasses[$intPrize]))
			) {
				continue;
			}
			if ($objPrize->add_on_restricted)
			{
				if (isset($arrUserAddons[$objPrize->prize_id]))
				{
					if ($objPrize->use_sub_prizes)
					{
						$arrChildPrizes = $query->prize__select(array(
							"institution_id" => 1,
							"parent_prize_id" => $objPrize->template_prize_id,
							"is_active" => 1,
							"_ORDER" => "prize_id+0 ASC"
						));
						foreach ($arrChildPrizes as $objParentPrize)
						{
							$arrNewPrizes[$objParentPrize->prize_id] = $objParentPrize;
						}
					}
					else
					{
						$arrNewPrizes[$objPrize->prize_id] = $objPrize;
					}
				}
			}
			else
				$arrNewPrizes[$objPrize->prize_id] = $objPrize;
		}
		$arrPrizes = array_hash("prize_id", $arrNewPrizes);
		$arrPrizesOnePerUser = array_stack("one_per_user", "prize_id", $arrPrizes);
		$arrUserPrizesOnePerUser = $query->user_prizes__select(array(
			"prize_id" => array_keys($arrPrizesOnePerUser),
			"user_id" => $arrParams["institution_id"]
		));
		foreach ($arrUserPrizesOnePerUser as $objUserPrizeOnePerUser)
		{
			unset($arrPrizes[$objUserPrizeOnePerUser->prize_id]);
		}
		return $arrPrizes;
	}

	public function prizes_install($intPrize)
	{
		$intPrize = intval($intPrize);
		if (!$intPrize)
		{
			print "Sorry, there was an error: CS-PI101-S9D9S0";
			exit;
		}

		// Load prize
		$arrPrize = (array) first($this->_prizes_select(array(
			"prize_id" => $intPrize
		)));
		if (!$arrPrize)
		{
			print "Sorry, there was an error: CS-PI102-SDF7DD";
			exit;
		}

		// Install prize
		$arrPrize["institution_id"] = $this->_user_session_data->institution_id;
		$arrPrize["template_prize_id"]	= $arrPrize["prize_id"];
		$arrPrize["prize_id"] = false;
		$arrPrize["created_by"] = $this->_user_session_data->user_id;
		$intPrizeAI = $this->_prizes_insert($arrPrize);

		return $intPrizeAI;

	}

	public function prizes_uninstall($intPrizeId)
	{
		$intPrizeId = intval($intPrizeId);
		if (!$intPrizeId)
		{
			print "Sorry, there was an error: MC-UC102-7SDFD7";
			exit;
		}
		$objPrize = first($this->_prizes_select(array(
			"institution_id" => $this->_user_session_data->institution_id,
			"prize_id" => $intPrizeId
		)));
		if (!$objPrize)
		{
			print "Sorry, there was an error: MS-PU102-8SDDSS";
			exit;
		}
		$this->_prizes_delete(array(
			"prize_id" => $intPrizeId
		));

		return $objPrize->template_prize_id;
	}

















	public function installable_to_template($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);
		if (!isset($arrParams["institution_id"]))
		{
			print "Sorry, there was an erorr: MS-PRITT101-DS98DS";
			exit;
		}
		$strSql = "
			INSERT INTO
				prizes (template_prize_id, parent_prize_id,institution_id,prize_name,prize_category,prize_description,image_id,add_on_restricted,prize_count,points,prize_type,installable_default_on,prize_price,is_active,created,created_by)
			SELECT
				prize_id, parent_prize_id," . $arrParams["institution_id"] . ",prize_name,prize_category,prize_description,image_id,add_on_restricted,prize_count,points,prize_type,installable_default_on,prize_price,is_active,NOW(),created_by
			FROM
				prizes
			where
				institution_id = 1
				and installable_default_on = 1
				AND template_prize_id IS NULL;";
		$boolResult = $this->_db->query($strSql);
		return $boolResult;
	}

	public function prizes_select ($arrQuery=0)
	{
		$strSql = "
			SELECT
				*
			FROM
				prizes";
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
	}

	public function prizes_select_id ($intId=0)
	{
		if (!$intId) {
			print "Sorry, there was an error: M-S101-GHJ1GH";
			exit;
		}
		$strSql = "
			SELECT
				*
			FROM
				prizes
			WHERE
				prize_id=" . $intId;
		$objResult = $this->_db->fetchRow($strSql);
		return $objResult;
	}

	public function prizes_select_templates ($intHost=0)
	{
		$strSql = "
			SELECT
				*
			FROM
				prizes
			WHERE
				prize_type='Template'";
		if ($intHost)
			$strSql .= "
				AND institution_id=" . $intHost;
		$objResult = $this->_db->fetchAll($strSql);
		return $objResult;
	}

	public function prizes_select_host ($intId=0)
	{
		if (!$intId) {
			print "Sorry, there was an error: M-S101-GHJ1GH";
			exit;
		}
		$strSql = "SELECT * FROM prizes WHERE prize_type='Installable' ";
		$objResult = $this->_db->fetchRow($strSql);
		return $objResult;
	}

	/**
	 * Selects prizes that were created by a specific user (eg; principal)
	 *
	 * @var int user id
	 */
	public function prizes_select_by_user($user_id, $status)
	{
		$strSql = '
		SELECT * FROM prizes
		WHERE
			created_by = '.$user_id.'
			AND
			is_active = '.$status;

		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
	}

	/**
	 * This function selects all prizes by instituion_id, network_id or host_id.
	 * If none is set, it return all prizes
	 *
	 * @param int $host_id
	 * @param int $network_id
	 * @param int $institution_id
	 *
	 * @return arr $arrResult
	 */
	public function prize_select_by_institutions($host_id=null, $network_id=null, $institution_id=null)
	{
		//echo $host_id . "--" . $network_id . "--" . $institution_id; exit;
		if($institution_id!=null){
			$strSql = '
			SELECT * FROM prizes
			WHERE institution_id = '.$institution_id;
		} elseif($network_id!=null) {
			$strSql = '
			SELECT b.institution_id AS id
			FROM institutions AS a
			JOIN institutions AS b
			ON a.institution_id = b.network_id
			WHERE b.network_id = '.$network_id;

			$result = $this->_db->fetchAll($strSql);
			foreach($result as $r){
				$buffer[] = $r->id;
			}

			$where = join(' OR institution_id=', $buffer);

			$strSql = '
			SELECT * FROM prizes
			WHERE institution_id = '.$network_id;

			$strSql .= $where;

		} elseif($host_id!=null) {
			$strSql = '
			SELECT b.institution_id AS id
			FROM institutions AS a
			JOIN institutions AS b
			ON a.institution_id = b.host_id
			WHERE b.host_id = '.$host_id;

			$result = $this->_db->fetchAll($strSql);
			foreach($result as $r){
				$buffer[] = $r->id;
			}

			$where = join(' OR institution_id=', $buffer);

			$strSql = '
			SELECT * FROM prizes
			WHERE institution_id = '.$host_id;

			$strSql .= $where;

		} else {
			$strSql = 'SELECT * FROM prizes';
		}
		//echo $strSql; exit;
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
	}

   	public function prize_insert($arrQuery)
	{
	    $created = date("Y-m-d H:i:S");

	    // Filter everything for the query
		foreach ($arrQuery as $intKey => $strValue)
		{
			$strValue = mysql_real_escape_string($strValue);
			$arrQuery[$intKey] = trim($strValue);
		}

	    // Build the insert
	    $arrFields = array (
			"template_prize_id" => @$arrQuery["template_prize_id"],
			"parent_prize_id"	=> @$arrQuery["parent_prize_id"],
			"list"              => $arrQuery["list"],
			"institution_id"    => $arrQuery["institution_id"],
			"prize_name"        => $arrQuery["prize_name"],
			"prize_category"    => $arrQuery["prize_category"],
			"image_id"  		=> $arrQuery["image_id"],
			"add_on_restricted"	=> $arrQuery["add_on_restricted"],
			"prize_description" => $arrQuery["prize_description"],
			"prize_count"       => $arrQuery["prize_count"],
			"points"            => $arrQuery["points"],
			"prize_type"        => $arrQuery["prize_type"],
			"installable_default_on"	=> @$arrQuery["installable_default_on"],
			"prize_price"       => $arrQuery["prize_price"],
			"is_active"  		=> $arrQuery["is_active"],
			"created"           => $created,
			"created_by"        => $this->_user_session_data->user_id
	    );

	    // Execute
		$boolResult = $this->_db->insert("prizes", $arrFields);
		if ($boolResult) {
			$intResult = $this->_db->lastInsertId();
		}
		//insert prize rule if any
		if(@$arrQuery["rule"]){
			$arrInsert = array(	"rule_applies_to" 	=> "Prize",
								"rule_type" 		=> "Deny",
								"rule"				=> "One item per student",
								"institution_id"	=> $arrQuery["institution_id"],
								"prize_id"			=> $intResult,
								"created"           => $created,
								"created_by"        => $this->_user_session_data->user_id
							   );
			$this->_db->insert("rules", $arrInsert);
		}
		return $intResult;
	}

   	public function prize_update($arrQuery)
	{
		$created = date("Y-m-d H:i:S");
		$intResult = 0;

		// Filter everything for the query
		foreach ($arrQuery as $intKey => $strValue)
		{
			$strValue = mysql_real_escape_string($strValue);
			$arrQuery[$intKey] = trim($strValue);
		}

		// Build the update
		if($arrQuery["updateMode"] == "full"){
			$arrFields = array (
			"list"              => $arrQuery["list"],
			"prize_name"        => $arrQuery["prize_name"],
			"prize_category"    => $arrQuery["prize_category"],
			"prize_description" => $arrQuery["prize_description"],
			"prize_count"       => $arrQuery["prize_count"],
			"points"            => $arrQuery["points"],
			"prize_type"        => $arrQuery["prize_type"],
			"installable_default_on"        => $arrQuery["installable_default_on"],
			"prize_price"       => $arrQuery["prize_price"],
			"modified"           => $created,
			"add_on_restricted"	=> $arrQuery["add_on_restricted"],
			"is_active"  		=> isset($arrQuery["is_active"]) ? intval($arrQuery["is_active"]) : 0
			);
		} else{
			$arrFields = array (
			"prize_type"        => $arrQuery["prize_type"],
			"installable_default_on"        => $arrQuery["installable_default_on"],
			"prize_name"        => $arrQuery["prize_name"],
			"prize_description" => $arrQuery["prize_description"],
			"prize_count"       => $arrQuery["prize_count"],
			"prize_price"       => $arrQuery["prize_price"],
			"points"            => $arrQuery["points"],
			"modified"           => $created,
			"add_on_restricted"	=> $arrQuery["add_on_restricted"],
			"is_active"  		=> isset($arrQuery["is_active"]) ? intval($arrQuery["is_active"]) : 0
			);
		}


		$strWhere = "prize_id=" . $arrQuery["prize_id"];
		//echo $strWhere;
		//var_dump($arrFields); exit;
		// Execute
		try{
			$boolResult = $this->_db->update("prizes", $arrFields, $strWhere);
		}
		catch(Zend_Exception $e){
			echo "There was an error: MS-PU101-ADD98F";
			if(DEV_ENV == "devel") print_r($arrFields);
		}


		if ($boolResult) {
			try{
				$intResult = $this->_db->lastInsertId();
			}
			catch(Zend_Exception $e){
				echo "There was an error: MS-PU102-AV5GGF";
				if(DEV_ENV == "devel") print_r($arrFields);
			}
		}

		//update prize rule
		if($arrQuery["rule"] && !$this->prize_rule_exists($arrQuery['prize_id'])){
			$arrInsert = array(	"rule_applies_to" 	=> "Prize",
								"rule_type" 		=> "Deny",
								"rule"				=> "One item per student",
								"institution_id"	=> $arrQuery["institution_id"],
								"prize_id"			=> $arrQuery["prize_id"],
								"created"           => $created,
								"created_by"        => $this->_user_session_data->user_id
							   );
			$intResult = $this->_db->insert("rules", $arrInsert);
		}elseif(!$arrQuery["rule"]){
			$sql = 'DELETE FROM rules WHERE rules.prize_id = '.$arrQuery["prize_id"];
			//echo $sql;
			$intResult = $this->_db->query($sql);
		}

		return $intResult;
	}

	public function prize_rule_exists($prize_id)
	{
		if(empty($prize_id)) return false;

		$sql = 'SELECT * FROM rules WHERE rules.prize_id = '.$prize_id;
		try{
			if($this->_db->fetchAll($sql)){
				return true;
			}else{
				return false;
			}
		}catch(Zend_Exception $e){
			echo "There was an error MS-PRE-JSHGTD";
			if(DEV_ENV == 'staging' || DEV_ENV == 'devel') echo $e->getMessage() . $sql;
		}

	}

	/**
	 *	Checks the current user's permission against the permission
	 *	of the creator of the prize. If it is smaller, function returns false
	 *	e.g.; not editable
	 *
	 *	@params prize_id
	 *	@return bool
	 *
	 */
	public function prize_is_editable($prize_id)
	{
		$user_session_data = new Zend_Session_Namespace('user_session_data');

		// ***** Find out who created the prize so we can get the institutions permission ***** //
		$strSql = "SELECT * FROM prizes WHERE prizes.prize_id=" . $prize_id;

		try
		{
			$arrResult = $this->_db->fetchRow($strSql);
		}
		catch(Zend_Exception $e)
		{
			if(DEV_ENV == 'devel')
			{
				echo $strSql;
			}
			echo "There was an error: MS-PIE103-JDKLS9";
		}

		// determine institution type
		$strSql2 = "SELECT institution_type
					FROM institutions
					WHERE institutions.institution_id=" . $user_session_data->institution_id;

		try
		{
			$arrResultInstitution = $this->_db->fetchRow($strSql2);
		}
		catch(Zend_Exception $e)
		{
			if(DEV_ENV == 'devel')
			{
				echo $strSql2;
			}
			echo "There was an error: MS-PIE101-JSHYET";
		}

		switch ($arrResultInstitution->institution_type)
		{
			case "Host":
				$strSql = '
				SELECT * FROM institutions
				WHERE institution_id = '.$arrResult->institution_id.'
				AND host_id = '.$user_session_data->institution_id;
			break;

			case "Network";
				$strSql = '
				SELECT * FROM institutions
				WHERE institution_id = ' . $arrResult->institution_id .'
				AND network_id = '.$user_session_data->institution_id;
			break;

			default:
				return false;
		}

		try
		{
			$arrResultAllow = $this->_db->fetchRow($strSql);
		}
		catch(Zend_Exception $e)
		{
			if(DEV_ENV == 'devel')
			{
				echo $strSql;
			}
			echo "There was an error: MS-PIE102-HFJI87";
		}

		if ($arrResultAllow)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 *	Checks if the prize is installed. This information is used to enable/disable
	 *	the edit capabilities of the quantities
	 *
	 *	@params prize_id
	 *	@return bool
	 *
	 */
	public function prize_is_installed($prize_id)
	{
		if(empty($prize_id)) return false;

		$user_session_data = new Zend_Session_Namespace('user_session_data');

		$strSql = '
		SELECT * FROM prizes
		WHERE prizes.prize_id=' . $prize_id . '
		AND prizes.prize_type = "School Installed"';

		try
		{
			$arrResult = $this->_db->fetchRow($strSql);
		}
		catch(Zend_Exception $e)
		{
			if(DEV_ENV == 'devel')
			{
				echo $strSql;
			}
			echo "There was an error: MS-PII103-JHSGT9";
		}

		return ($arrResult) ? true : false;

	}

	public function select_host_prizes($intHostId, $intInstitutionId)
	{
		//if (!$intId) {
		//	print "Sorry, there was an error: M-S101-GHJ1GH";
		//	exit;
		//}

		$strSql = "SELECT hp.*, ip.prize_id AS installed ";
		$strSql = $strSql . "FROM prizes AS hp ";
		$strSql = $strSql . "LEFT JOIN prizes AS ip ON (hp.prize_id=ip.template_prize_id AND ip.institution_id=" . $intInstitutionId . ") ";
		$strSql = $strSql . "WHERE hp.prize_type='Installable' ";
		if($this->_user_session_data->permission != 'Super Administrator'){
			$strSql = $strSql . "AND hp.institution_id=" . $intHostId;
		}
		//echo $strSql; exit;
		$objResult = $this->_db->fetchAll($strSql);
		return $objResult;
	}

	public function get_campaign_image($campaign_id, $rgb)
	{
		if ($rgb == "r")
			$strSql = "SELECT photo, photo_type FROM campaign_photos WHERE campaign_id=" . $campaign_id;
		elseif ($rgb == "g")
			$strSql = "SELECT gold_photo AS photo, gold_photo_type AS photo_type FROM campaign_photos WHERE campaign_id=" . $campaign_id;
		elseif ($rgb == "b")
			$strSql = "SELECT black_photo AS photo, black_photo_type AS photo_type FROM campaign_photos WHERE campaign_id=" . $campaign_id;

		$objResult = $this->_db->fetchRow($strSql);
		return $objResult;
	}

	public function get_active_prizes_by_institution_id($institution_id)
	{
		$sql = "SELECT * ";
		$sql = $sql . "FROM prizes ";
		$sql = $sql . "WHERE is_active=1 ";
		$sql = $sql . "AND institution_id=" . $institution_id;
		$prizes = $this->_db->fetchAll($sql);
		return $prizes;
	}

	public function get_inactive_prizes_by_institution_id($institution_id)
	{
		$sql = "SELECT * ";
		$sql = $sql . "FROM prizes ";
		$sql = $sql . "WHERE is_active=0 ";
		$sql = $sql . "AND institution_id=" . $institution_id;
		$prizes = $this->_db->fetchAll($sql);
		return $prizes;
	}

	public function get_prize_by_prize_id($prize_id)
	{
		$sql = "SELECT * FROM prizes WHERE prize_id=" . $prize_id;
		$prize = $this->_db->fetchRow($sql);
		return $prize;
	}

	public function is_prize_editable($prize_id, $user_id, $permission)
	{
		$is_editable = false;

		$sql = "SELECT p.prize_type, p.created_by, pp.permission  ";
		$sql = $sql . "FROM prizes AS p ";
		$sql = $sql . "LEFT JOIN users AS u ON (p.created_by=u.user_id) ";
		$sql = $sql . "LEFT JOIN permissions AS pp ON (u.user_id=pp.user_id) ";
		$sql = $sql . "WHERE p.prize_id=" . $prize_id;
		$prize = $this->_db->fetchRow($sql);

		if ($prize->prize_type == "Template" && $permission == "Host Administrator")
		{
			$is_editable = true;
		}
		elseif ($prize->permission == "Institution Administrator" && $permission == "Institution Administrator")
		{
			$is_editable = true;
		}
		elseif ($prize->permission == "Teacher" && ($permission == "Institution Administrator" || $permission == "Teacher"))
		{
			$is_editable = true;
		}

		return $is_editable;
	}

	public function get_template_prizes_by_host_id($host_id, $institution_id)
	{
		$sql = "SELECT tp.*, ip.prize_id AS installed ";
		$sql = $sql . "FROM prizes AS tp ";
		$sql = $sql . "LEFT JOIN prizes AS ip ON (tp.prize_id=ip.template_prize_id AND ip.institution_id=" . $institution_id . ") ";
		$sql = $sql . "WHERE tp.prize_type='Template' ";
		$sql = $sql . "AND tp.institution_id=" . $host_id;

		//echo $sql; exit;

		$prizes = $this->_db->fetchAll($sql);
		return $prizes;
	}


	/**
	 * Displays a picture from the images table
	 *
	 * @para int $picture_id
	 * @return arr $objResult
	 *
	 */
	public function show_picture($picture_id){
		return FALSE;
	}

	/**
	 * Saves data into store configuration table. If there is an existing record,
	 * then we will perform an update, otherwise we will create a new record
	 *
	 * @param $arrSave arr
	 * @return $result bool
	 *
	 */
	public function store_configuration_save($arrSave)
	{
		//check to see if we have a record
		$sql = '
		SELECT * FROM config_store
		WHERE institution_id = ' .$arrSave["institution_id"];

		try{
			$result = $this->_db->fetchRow($sql);
		} catch(Zend_Exception $e){
			echo "There was an error MS-SCG-KKJD6T";
			if(DEV_ENV == "devel") echo $e->getMessage();
		}

		if($result){
			//update record
			$result = $this->_db->update("config_store", $arrSave, "institution_id = ".$arrSave["institution_id"]);
		}else{
			//insert record
			$arrSave['created'] = date("Y-m-d H:i:s", time());
			$arrSave['created_by'] = $this->_user_session_data->user_id;
			$result = $this->_db->insert("config_store", $arrSave);
		}

		return $result;
	}

	/**
	 * Get store configuration data. We pull data by institution_id since this field
	 * is a unique value
	 *
	 * @param $institution_id int
	 * @return $result obj
	 *
	 */
	public function store_configuration_get($institution_id)
	{
		$config = new Zend_Config_Ini('./application/config.ini', DEV_ENV);

		$sql = '
		SELECT * FROM config_store
		WHERE institution_id = ' .$institution_id;

		try{
			$result = $this->_db->fetchRow($sql);
		} catch(Zend_Exception $e){
			echo "There was an error MS-SCG-KKJD6T";
			if(DEV_ENV == "devel") echo $e->getMessage();
		}

		if($result){
			return $result;
		}else{
			$myResult = new stdClass();
			$myResult->army_points = $config->points->army_points->default;
			$myResult->base_points = $config->points->base_points->default;

			$result = $myResult;
			return $result;
		}
	}

}
?>