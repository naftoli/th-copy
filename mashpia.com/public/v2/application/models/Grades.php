<?php

/*
	// Table of contents

*/

class Grades
{
	private $_db;
	private $_user_session_data;
	private $_tools;

	public function __construct()
	{
		// Start the DB objects
		$this->_db = Zend_Registry::get('db');
		$this->_db->setFetchMode(Zend_Db::FETCH_OBJ);

		// Start the session object
		$this->_user_session_data = new Zend_Session_Namespace('user_session_data');

		$this->_tools = new ToolsModels();
   	}

	// Generic functions
	public function _grades_select ($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);

		// Possible column selections
		$arrColumns = array (
			"grade_id"	         => @$arrParams["grade_id"],
			"institution_id"	 => @$arrParams["institution_id"],
			"grade_name"		 => @$arrParams["grade_name"],
			"grade_hierarchy"	 => @$arrParams["grade_hierarchy"],
			"is_active"			 => @$arrParams["is_active"],
			"created"			 => @$arrParams["created"],
			"modified"			 => @$arrParams["modified"],
			"created_by"		 => @$arrParams["created_by"]
		);

		$strSql = "
			SELECT
				*
			FROM
				grades
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
		$strSql .= "
			ORDER BY
				grade_hierarchy ASC";
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;

	}

	public function _grades_update($arrParams)
	{
		$arrValuesParams = array("institution_id","grade_name","grade_hierarchy","is_active");
		$arrWhereParams = array("grade_id","institution_id","grade_name","grade_hierarchy","is_active","created","modified","created_by");

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
			print "Sorry, there was an error: MB-BU101-TRTHTT";
			exit;
		}

		// Execute
		$boolResult = $this->_db->update("grades", $arrValues, $arrWhere);
		return $boolResult;
	}

	/*
	 * Select grades in a fashion which will automatically select the grades from the first institution in the hierarchy with
	 * grades available.
	 */
	public function _grades_select_hierarchal ($arrParams)
	{
		if (
			!isset($arrParams["institution_id"])
			|| !$arrParams["institution_id"]
		) {
			print "Sorry, there was an error: MG-GSH101-SDF87D";
			exit;
		}
		$arrParams = $this->_tools->rsqlclean($arrParams);
		$objInstitutions = new Institutions();
		$objInstitution = current($objInstitutions->_institutions_select(array(
			"institution_id" => $arrParams["institution_id"]
		)));
		$strSql = "
			SELECT
				*
			FROM
				grades
			WHERE
				institution_id=" . $objInstitution->institution_id;
		$arrResult = $this->_db->fetchAll($strSql);
		if (!count($arrResult) && $objInstitution->network_id) // Nothing resulted from the instituion level
		{
			$strSql = "
				SELECT
					*
				FROM
					grades
				WHERE
					institution_id=" . $objInstitution->network_id;
			$arrResult = $this->_db->fetchAll($strSql);
		}

		if (!count($arrResult) && $objInstitution->host_id) // Nothing resulted from the network level
		{
			$strSql = "
				SELECT
					*
				FROM
					grades
				WHERE
					institution_id=" . $objInstitution->host_id;
			$arrResult = $this->_db->fetchAll($strSql);
		}
		return $arrResult;
	}

	public function _velocity_grades_select ($arrParams)
	{
		// "velocity_grade_id","campaign_id","grade_hierarchy","velocity","created","modified","created_by"
		$arrParams = $this->_tools->rsqlclean($arrParams);

		// Possible column selections
		$arrColumns = array (
			"velocity_grade_id"=> @$arrParams["velocity_grade_id"],
			"campaign_id"=> @$arrParams["campaign_id"],
			"grade_hierarchy"=> @$arrParams["grade_hierarchy"],
			"velocity"=> @$arrParams["velocity"],
			"created"=> @$arrParams["created"],
			"modified"=> @$arrParams["modified"],
			"created_by"=> @$arrParams["created_by"]
		);

		$strSql = "
			SELECT
				*
			FROM
				velocity_grades
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

		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
	}

	public function _velocity_ladders_select ($arrParams)
	{
		//"velocity_ladder_id","campaign_id","ladder","velocity","created","modified","created_by"

		$arrParams = $this->_tools->rsqlclean($arrParams);

		// Possible column selections
		$arrColumns = array (
			"velocity_ladder_id"=> @$arrParams["velocity_ladder_id"],
			"campaign_id"=> @$arrParams["campaign_id"],
			"ladder"=> @$arrParams["ladder"],
			"velocity"=> @$arrParams["velocity"],
			"created"=> @$arrParams["created"],
			"modified"=> @$arrParams["modified"],
			"created_by"=> @$arrParams["created_by"]
		);

		$strSql = "
			SELECT
				*
			FROM
				velocity_ladders
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

		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;

	}

	public function _grades_insert($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);

		if (!isset($arrParams["created_by"]))
		{
			$arrParams["created_by"] = $this->_user_session_data->user_id;
		}

		$arrFeilds = array (
			"grade_id"	         => @$arrParams["grade_id"],
			"institution_id"	 => @$arrParams["institution_id"],
			"grade_name"	     => @$arrParams["grade_name"],
			"grade_hierarchy"	  => @$arrParams["grade_hierarchy"],
			"is_active"			 => @$arrParams["is_active"],
			"created"	         => date("Y-m-d H:i:S"),
			"created_by"	     => $arrParams["created_by"]
		);
		//var_dump($arrFeilds); exit;
		// Execute
		$boolResult = $this->_db->insert("grades", $arrFeilds);
		if ($boolResult)
		{
			return $this->_db->lastInsertId();
		}
	}

	public function _grades_delete($arrParams)
	{//"grade_id","institution_id","grade_hierarchy","grade_name","created","created_by"
		$arrParams = $this->_tools->rsqlclean($arrParams);
		$arrFeilds = array();
		if (isset($arrParams["grade_id"]))
			$arrFeilds[] = $this->_db->quoteInto('grade_id = ?', $arrParams["grade_id"]);
		if (isset($arrParams["institution_id"]))
			$arrFeilds[] = $this->_db->quoteInto('institution_id = ?', $arrParams["institution_id"]);
		if (isset($arrParams["grade_hierarchy"]))
			$arrFeilds[] = $this->_db->quoteInto('grade_hierarchy = ?', $arrParams["grade_hierarchy"]);
		if (isset($arrParams["grade_name"]))
			$arrFeilds[] = $this->_db->quoteInto('grade_name = ?', $arrParams["grade_name"]);
		if (isset($arrParams["created"]))
			$arrFeilds[] = $this->_db->quoteInto('created = ?', $arrParams["created"]);
		if (isset($arrParams["created_by"]))
			$arrFeilds[] = $this->_db->quoteInto('created_by = ?', $arrParams["created_by"]);
		if (!count($arrFeilds))
		{
			print "Sorry, there was an error: MM-GMD101-97F78S";
			exit;
		}

		/*
		 * Because deleting a grade will break the hierarchy we need to run autocorrect_hierarchy function right afterwords.
		 */
		$objGrade = current($this->_grades_select($arrFeilds));

		$intInstitution = $objGrade->institution_id;
		$boolResult = $this->_db->delete("grades", $arrFeilds);

		// Fill any gaps in the hierarchy created from deleting a grade
		$this->autocorrect_hierarchy(array(
			"institution_id" => $intInstitution
		));
		return $boolResult;
	}

	/*
	 * Correct any flaws in grade hierarchy, 0,2,3,4 should look like 0,1,2,3
	 * Params: institution_id
	 */
	public function autocorrect_hierarchy($arrParams)
	{
		if (!isset($arrParams["institution_id"]))
		{
			print "Sorry, there was an error: MM-MH101-SD98F7";
			exit;
		}
		$arrGrades = $this->_grades_select(array(
			"institution_id" => $arrParams["institution_id"]
		));

		foreach ($arrGrades as $intKey => $objGrade)
		{
			if ($intKey != $objGrade->grade_hierarchy)
			{
				$this->_grades_update(array(
					"where" => array(
						"grade_id" => $objGrade->grade_id
					),
					"values" => array(
						"grade_hierarchy" => $intKey
					)
				));
			}
		}
	}
	// Generic functions end

	/*
	 * Select what grade a user is on based on the classes the user is currently
	 * assigned to. If the user is in 5 classes in grade 3 and 1 class in grade
	 * 4 then the user is in grade 3 and 4.
	 * Result: array(3,4)
	 */
	public function classes_select_grades ($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);

		if (!isset($arrParams["user_id"]) || !$arrParams["user_id"])
		{
			print "Sorry, there was an error: MG-CSG101-D8D9S9";
			exit;
		}

		$strSql = "
			SELECT
				classes.grade,
				grades.grade_hierarchy
			FROM
				user_classes,
				classes,
				grades
			WHERE
				user_classes.class_id = classes.class_id
				AND user_classes.user_id = " . $arrParams["user_id"] . "
				AND grades.grade_name = classes.grade
				AND grades.institution_id = 1";

		if (isset($arrParams["institution_id"]))
		{
			$strSql .= "
				AND classes.institution_id = " . $arrParams["institution_id"];
		}
		//print $strSql;
		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
	}

	public function insert_grade($arrInsert)
	{
		try{
			$result = $this->_db->insert('grades', $arrInsert);
		}catch(Zend_Exception $e){
			echo 'There was an error MG-IG-JHDGT5';
			if(DEV_ENV == 'devel'){
				print_r($arrInsert);
				echo $e->getMessage();
			}
		}
	}

	/**
	 * Gets all grades for a given institution
	 *
	 * @param institution_id
	 * @return arr arrResult
	 *
	 */
	public function get_grades_by_id($institution_id=null)
	{
		if($institution_id!=null){
			$where = ' WHERE institution_id = '.$institution_id;
		} else {
			$where ='';
		}

		$strSql = 'SELECT * FROM grades' . $where;
		try{
			$arrResult = $this->_db->fetchAll($strSql);
		}catch(Zend_Exception $e){
			echo 'There was an error MG-GGBI-S4GHU8';
			if(DEV_ENV == 'devel'){
				echo $strSql;
				echo $e->getMessage();
			}
		}
		return $arrResult;
	}

	/**
	 * Gets a grade for a given grade_id
	 *
	 * @param grade_id
	 * @return arr arrResult
	 *
	 */
	public function get_grade_by_id($grade_id=null)
	{

		$strSql = 'SELECT * FROM grades WHERE grade_id = ' . $grade_id;
		try{
			$arrResult = $this->_db->fetchRow($strSql);
		}catch(Zend_Exception $e){
			echo 'There was an error MG-GGBI-LK987G';
			if(DEV_ENV == 'devel'){
				echo $strSql;
				echo $e->getMessage();
			}
		}
		//echo $arrResult->grade_name; $strSql; exit;
		return $arrResult;
	}

	/**
	 * Checks if a duplicate exists in our table, Records can be searched by
	 * name or hierarchy.
	 *
	 * @param $needle - the string to search for
	 * @param int $institution_id
	 * @param string $search_by - can be "name" or "hierarchy"
	 *
	 * @return bool
	 *
	 */
	public function grade_is_duplicate($needle, $institution_id, $search_by)
	{
		switch($search_by){
			case "name":
				$and = ' AND grade_name = "'.$needle.'"';
				break;
			case "hierarchy":
				$and = ' AND grade_hierarchy = '.$needle;
				break;
			default:
				return true;
		}

		$strSql = 'SELECT * FROM grades WHERE institution_id = '.$institution_id . $and;
		$result = $this->_db->fetchAll($strSql);

		if(count($result)>0){
			return true;
		} else{
			return false;
		}
	}

	/**
	 * This function is not yet finished, it simply updates the current record.
	 * Once completed, it will do proper sorting so as to ensure, that we have no
	 * duplicate values and the values we have are in order.
	 *
	 */
	public function grade_update($arrUpdate)
	{
		//check if we have duplicates for what we just updated
		$strSql = '
		SELECT * FROM grades
		WHERE institution_id = '.$arrUpdate['institution_id'].'
		AND grade_name = '.$arrUpdate['grade_name'];
		//print $strSql; exit;

		$rows = $this->_db->fetchAll($strSql);

		if(count($rows) > 0 ){
			print "Sorry, a record with this name already exists in your institution. Please choose another one.";
			exit;
		}
		else{
			//update the record in question
			$strSql = '
			UPDATE grades
			SET
				grade_name = "'.$arrUpdate['grade_name'] .'"
			WHERE grade_id = '.$arrUpdate['grade_id'];

			$result = $this->_db->query($strSql);
		}
		return $result;
	}

	/**
	 * Function selects all grades based on institution id.
	 *
	 * @param int $institution_id
	 *
	 * @return arr $result
	 *
	 */
	public function grades_select_by_institution_id($institution_id)
	{

		if(!isset($institution_id) || $institution_id==0){
			$sql = 'SELECT * FROM grades';
		}else{
			$utility = new Utilities();
			$childIds = $utility->institution_reverse_lookup($institution_id);
			$sql = 'SELECT * FROM grades WHERE institution_id IN ('.$childIds.')';
			//print $sql; exit;
		}

		try{
			$result = $this->_db->fetchAll($sql);
		} catch (Zend_Exception $e){
			echo "There was an error: MC-CSBII-JHSGDT";
			if(DEV_ENV == 'devel'){
				echo $sql;
				echo $e->getMessage();
			}
		}
		//echo $sql; exit;
		return $result;
	}
	/*
	 * Process the movment of a grade up or down.
	 * Params: move = up or down, grade_id
	 * Return the resulted hierarchy.
	 */
	public function move_hierarchy($arrParams)
	{
		$query = new QueryGen();

		if (!isset($arrParams["grade_id"]))
		{
			print "Sorry, there was an error: MG-MH101-SD8F7D";
			exit;
		}

		// Load the current grade
		$objGrade = current($this->_grades_select(array(
			"grade_id" => $arrParams["grade_id"]
		)));
		if (!$objGrade)
		{
			print "Sorry, there was an error: MG-MH102-98SDD8";
			exit;
		}
		$intGradeHierarchy = $objGrade->grade_hierarchy;
		if ($arrParams["move"] == "up")
		{
			// Check if the hierarchy is already at the max
			if ($intGradeHierarchy == 0)
				return 0;
			$intGradeHierarchyTo = $objGrade->grade_hierarchy-1;
		}
		else if ($arrParams["move"] == "down")
		{
			$intGradeHierarchyTo = $objGrade->grade_hierarchy+1;
		}

		// Find the grade that is being moved into to complete the translation.
		$objGradeTo = current($this->_grades_select(array(
			"institution_id" => $objGrade->institution_id,
			"grade_hierarchy" => $intGradeHierarchyTo
		)));
		if (!$objGradeTo)
			return $intGradeHierarchyTo;

		// Move the "from" grade
		$this->_grades_update(array(
			"where" => array(
				"grade_id" => $objGrade->grade_id
			),
			"values" => array(
				"grade_hierarchy" => $intGradeHierarchyTo
			)
		));
		$query->classes__update(array(
			"where" => array(
				"grade_id" => $objGrade->grade_id
			),
			"values" => array(
				"class_hierarchy" => $intGradeHierarchyTo
			)
		));
		// Move the "to" grade
		$this->_grades_update(array(
			"where" => array(
				"grade_id" => $objGradeTo->grade_id
			),
			"values" => array(
				"grade_hierarchy" => $intGradeHierarchy
			)
		));
		$query->classes__update(array(
			"where" => array(
				"grade_id" => $objGradeTo->grade_id
			),
			"values" => array(
				"class_hierarchy" => $intGradeHierarchy
			)
		));
		$this->autocorrect_hierarchy(array(
			"institution_id" => $objGrade->institution_id
		));

		return $intGradeHierarchyTo;
	}


}
?>