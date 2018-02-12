<?php
class Config
{
	//private $_db;

	public function __construct()
	{
		// No need atm
		//$this->_db = Zend_Registry::get('db');
		//$this->_db->setFetchMode(Zend_Db::FETCH_OBJ);
   	}

	/*
	 * Load the config settings into a 3 layer array with the host values as default
	 */
	public function load($arrParams)
	{
		$query = new QueryGen();
		// if a user id is provided try without a user id for global settings
		if (isset($arrParams["user_id"]))
		{
			$intUser = $arrParams["user_id"];
			unset($arrParams["user_id"]);
			$intInstitution = $arrParams["institution_id"];
			unset($arrParams["institution_id"]);
			if (isset($arrParams['_NOHOST']))
				$arrConfigData = array();
			else
			{
				$arrConfigData = object_extract("val", array_hash("set", "key", $query->config_settings__select($arrParams)));
			}
			$arrParams["user_id"] = $intUser;
			$arrParams["institution_id"] = $intInstitution;
			$arrConfigData = array_merge_real_recursive($arrConfigData, object_extract("val", array_hash("set", "key", $query->config_settings__select($arrParams))));
		}
		else
			$arrConfigData = object_extract("val", array_hash("set", "key", $query->config_settings__select($arrParams)));
		//$arrConfigData = array();
		// host settings
		$arrParams["institution_id"] = isset($arrParams["host_id"]) ? $arrParams["host_id"] : 1;
		$arrDefaultConfigOptions = object_extract("val", array_hash("set", "key", $query->config_settings__select($arrParams)));
		return array_merge_real_recursive($arrDefaultConfigOptions, $arrConfigData);
	}

	/*
	 * First param: Provide the set->key->val data structre
	 * Second param: Provide extra conditions such as institution_id, class_id, user_id, ...
	 */
	public function save($arrValues, $arrWhere)
	{
		$query = new QueryGen();
		// Load current config data
		$arrConfigData = object_extract("val", array_hash("set", "key", $query->config_settings__select($arrWhere)));
		// Load host config defaults
		$arrUpdateParams = array();
		$arrAllowed = array("institution_id", "user_id","class_id");
		foreach ($arrAllowed as $strAllowedCol)
		{
			if (isset($arrWhere[$strAllowedCol]))
				$arrUpdateParams[$strAllowedCol] = $arrWhere[$strAllowedCol];
		}
		$arrWhereHost = $arrWhere;
		$arrWhereHost["institution_id"] = isset($arrWhere["host_id"]) ? $arrWhere["host_id"] : 1;
		if (isset($arrWhereHost["user_id"]))
			unset($arrWhereHost["user_id"]);
		$arrDefaultConfigOptions = object_extract("val", array_hash("set", "key", $query->config_settings__select($arrWhereHost)));
		$arrBlankConfigOptions = array_fill_recursive(0, $arrDefaultConfigOptions);
		// Current config values
		$arrCurrentData = array_merge_real_recursive($arrDefaultConfigOptions, $arrConfigData);
		// New config values with current
		$arrPostWithDefault = array_merge_real_recursive($arrBlankConfigOptions, $arrValues);
		foreach ($arrPostWithDefault as $strSet => $arrData)
		{
			$arrUpdateParams["set"] = $strSet;
			foreach ($arrData as $strKey => $mixValue)
			{
				$arrUpdateParams["key"] = $strKey;
				// Check if something has been changed
				if ($mixValue != @$arrCurrentData[$strSet][$strKey])
				{
					/*if (
						is_null($arrConfigData[$strSet][$strKey])
						&& isset($arrUpdateParams['_INCLUSIVE'])
					) {
						$query->config_settings__delete($arrUpdateParams);
					}
					else */if (isset($arrConfigData[$strSet][$strKey]))
					{
						$query->config_settings__update(array(
							"where" => $arrUpdateParams,
							"values" => array(
								"val" => $mixValue
							)
						));
					}
					else
					{
						$arrInsert = $arrUpdateParams;
						$arrInsert["val"] = $mixValue;
						$query->config_settings__insert($arrInsert);
					}
				}
			}
		}
	}
}
?>