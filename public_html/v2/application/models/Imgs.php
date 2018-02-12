<?php
class Imgs
{
    private $_db;
	private $_tools;
	private $_user_session_data;

    public function __construct()
    {
		// Start the DB objects
		$this->_db = Zend_Registry::get('db');
		$this->_db->setFetchMode(Zend_Db::FETCH_OBJ);
		$this->_tools = new ToolsModels();
		$this->_user_session_data = new Zend_Session_Namespace('user_session_data');
    }

	public function process_upload($arrParams)
	{

	}

	public function _imgs_select($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);

		// Possible column selections
		$arrColumns = array (
			//"img_id","img_category","img_type","img_name","user_id","created","modified","created_by"
			"img_id"				=> @$arrParams["img_id"],
			"img_category"			=> @$arrParams["img_category"],
			"img_type"				=> @$arrParams["img_type"],
			"img_name"				=> @$arrParams["img_name"],
			"user_id"			=> @$arrParams["user_id"],
			"created"				=> @$arrParams["created"],
			"modified"				=> @$arrParams["modified"],
			"created_by"			=> @$arrParams["created_by"]
		);

		$strSql = "
			SELECT
				*
			FROM
				imgs
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

		$strSql .= "
			ORDER BY
				modified DESC, created DESC";

		$arrResult = $this->_db->fetchAll($strSql);
		return $arrResult;
	}

	public function _imgs_insert($arrParams)
	{
		$arrParams = $this->_tools->rsqlclean($arrParams);

		if (!isset($arrParams["created_by"]))
		{
			$arrParams["created_by"] = $this->_user_session_data->user_id;
		}

		$arrFeilds = array (
			"img_category"			=> @$arrParams["img_category"],
			"img_type"				=> @$arrParams["img_type"],
			"img_name"				=> @$arrParams["img_name"],
			"user_id"				=> @$arrParams["user_id"],
			"created"				=> date("Y-m-d H:i:S"),
		);
		// Execute
		$boolResult = $this->_db->insert("imgs", $arrFeilds);
		if ($boolResult)
		{
			return $this->_db->lastInsertId();
		}
	}

	public function _imgs_update($arrParams)
	{
		$arrValuesParams = array("img_category","img_type","img_name","user_id");
		$arrWhereParams = array("img_id","img_category","img_type","img_name","user_id","created","modified","created_by");

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
		$boolResult = $this->_db->update("imgs", $arrValues, $arrWhere);
		return $boolResult;
	}

	public function _imgs_delete($arrParams)
	{
		$arrWhereParams = array("img_id","img_category","img_type","img_name","user_id","created","modified","created_by");
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
		$boolResult = $this->_db->delete("imgs", $arrFeilds);
		return $boolResult;
	}
}
?>