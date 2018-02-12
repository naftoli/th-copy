<?php
class Lists
{
	private $_user_session_data;

	public function __construct()
	{
		$this->_user_session_data = new Zend_Session_Namespace('user_session_data');
   	}

	public function load_data($arrConfigData, $arrPostBack=array())
	{
		if (
			!isset($arrConfigData["tables"])
			|| !is_array($arrConfigData["tables"])
			|| !count($arrConfigData["tables"])
		) {
			return array(
				"error" => "Sorry, there was an error: ML-LD102-GG33SS"
			);
		}
		// Select and aggregate data from tables
		$arrRows = array();
		foreach ($arrConfigData["tables"] as $strTable => $arrColumns)
		{
			if (
				!is_array($arrConfigData["tables"][$strTable])
				|| !count($arrConfigData["tables"][$strTable])
			)
				continue;
			// Get the params
			if (!is_callable(array($this, '_query_' . $strTable)))
			{
				return array(
					"error" => "Sorry, there was an error: ML-LD103-GG33SS"
				);
			}
			// dynamically call the method to the associated table with the
			// provided paramteres which must be allowed
			$arrTableParams = array();
			if (isset($arrConfigData["params"]["db_params"]))
				$arrTableParams = $arrConfigData["params"]["db_params"];
			if (isset($arrColumns["_params"]))
				$arrTableParams = array_merge($arrTableParams, $arrColumns["_params"]);
			$arrData = call_user_func(array($this, '_query_' . $strTable), $arrConfigData, $arrTableParams, $arrPostBack);
			if (!count($arrData))
				continue;
			if (isset($arrData["error"]))
				return $arrData;
			$intItr = 0;
			foreach ($arrData as $objRow)
			{
				foreach ($arrColumns as $strColName => $arrColumnParams)
				{
					if (
						!isset($arrColumnParams["data"])
						|| !isset($objRow->$arrColumnParams["data"])
						|| substr($strColName,0,1) == "_"
					)
						continue;
					$arrRows[$intItr][$arrColumnParams["data"]] = $objRow->$arrColumnParams["data"];
				}
				$intItr++;
			}
		}

		return array(
			"success" => "true",
			"arrConfigData" => $arrConfigData,
			"arrRows" => $arrRows
		);
	}

	private function save_to_processing($arrConfigData, $arrParams, $arrPost)
	{
		//$arrData = unserialize(stripslashes(@$arrPost["arrData"]));
		foreach ($arrConfigData["tables"] as $strTable => $arrTableParams)
		{
			if (
				isset($arrTableParams["_save_to"])
				&& is_array($arrTableParams["_save_to"])
				&& count($arrTableParams["_save_to"])
			) {
				foreach ($arrTableParams["_save_to"] as $strSaveToTable => $mixedValue)
				{
					$arrSaveToParam = array();
					if (is_array($mixedValue))
						$arrSaveToParam = $mixedValue;
					else
						$strSaveToTable = $mixedValue;
					if (count($arrSaveToParam))
						$arrPost = array_merge($arrPost, $arrSaveToParam);
					unset($arrConfigData["tables"][$strTable]["_save_to"]);
					call_user_func(array($this, '_query_' . $strSaveToTable), $arrConfigData, $arrTableParams, $arrPost);
				}
			}
		}
	}


	private function addon_processing($arrConfigData, $arrParams, $arrPost)
	{
		foreach ($arrConfigData["tables"] as $strTableName => $arrTableParams)
		{
			if (isset($arrTableParams["_addons"]))
			{
				foreach ($arrTableParams["_addons"] as $strAddOnName => $mixedValue)
				{
					$arrAddOnParam = array();
					if (is_array($mixedValue))
						$arrAddOnParam = $mixedValue;
					else
						$strAddOnName = $mixedValue;
					call_user_func(array($this, '_addon_' . $strAddOnName), $arrConfigData, $arrTableParams, $arrPost, $arrAddOnParam);
				}
			}
		}
	}




	/*
	 *  all methods for each table should be only have access to parametes which
	 *  have been set as allowed and custom
	 */
	private function _query_ranks($arrConfigData, $arrParams, $arrPost)
	{
		$query = new QueryGen();
		$objRoles = new Roles();

		if (!$objRoles->isAllowed("Super Administrator"))
		{
			$arrParams["institution_id"] = $this->_user_session_data->institution_id;
		}
		$arrResults = $query->ranks__select($arrParams);
		return $arrResults;
	}

	private function _query_app_keyword_types($arrConfigData, $arrParams, $arrPost)
	{
		$query = new QueryGen();
		$objRoles = new Roles();
		$arrResults = $query->app_keyword_types__select($arrParams);
		if (@$arrPost["save"] == "true")
		{
			$arrData = unserialize(stripslashes(@$arrPost["arrData"]));
			if (!isset($arrData) || !is_array($arrData) || !count($arrData))
			{
				print json_encode(array("error" => "Sorry, there was an error: ML-QAKT101-asdg2g"));
				exit;
			}
			// Create params for _proc_query_instructions2
			$arrRequiredParams = $arrPrimaryKeys = $arrPertinentParams = array();
			foreach ($arrConfigData["tables"] as $strTableName => $arrTableParams)
			{
				foreach ($arrTableParams as $strColumnName => $arrColumnParams)
				{
					if (isset($arrColumnParams["data"]))
					{
						if (isset($arrColumnParams["key"]))
							$arrPrimaryKeys[$arrColumnParams["data"]] = 1;
						if (isset($arrColumnParams["data"]) && @$arrColumnParams["input"] != "plaintext")
							$arrPertinentParams[] = $arrColumnParams["data"];
						if (isset($arrColumnParams["required"]) && $arrColumnParams["required"])
							$arrRequiredParams[$strTableName][] = $arrColumnParams["data"];
					}
				}
			}
			$arrPrimaryKeys = array_keys($arrPrimaryKeys);
			foreach ($arrData["tables"] as $strTableName => $arrTableParams)
			{
				if (!isset($arrRequiredParams[$strTableName]))
					$arrRequiredParams[$strTableName] = array();
				//dumper(array($arrTableParams, $arrResults), 1, 0);
				$arrInstructions = $query->_proc_query_instructions2($arrResults, $arrTableParams, $arrPrimaryKeys, $arrPertinentParams, $arrRequiredParams[$strTableName]);
				if (isset($arrInstructions["_ERROR"]))
				{
					return array(
						"error" => $arrInstructions["_ERROR"]
					);
				}
				//dumper(array($arrInstructions, $arrData), 1);
				// validation
				$arrErrors = array();
				foreach ($arrInstructions["_INSERT"] as $arrContextData)
				{
					$objAppTextLanguage = first($query->app_keyword_types__select(array(
						"keyword_type" => $arrContextData["keyword_type"]
					)));
					if ($objAppTextLanguage)
					{
						$arrErrors["keyword_type"][] = array(
							"value" => $arrContextData["keyword_type"],
							"message" => "This language already exists."
						);
					}
				}
				foreach ($arrInstructions["_UPDATE"] as $arrContextData)
				{
					if (!isset($arrContextData["values"]["keyword_type"]))
						continue;
					$objAppTextLanguage = first($query->app_keyword_types__select(array(
						"keyword_type" => $arrContextData["values"]["keyword_type"],
						"_NOT" => array(
							"app_keyword_type_id" => $arrContextData["where"]["app_keyword_type_id"]
						)
					)));
					if ($objAppTextLanguage)
					{
						$arrErrors["keyword_type"][] = array(
							"value" => $arrContextData["values"]["keyword_type"],
							"message" => "This language already exists."
						);
					}
				}
				if (count($arrErrors))
				{
					return array(
						"error" => $arrErrors
					);
				}

				// transactions
				//dumper($arrInstructions,1);
				foreach ($arrInstructions["_INSERT"] as $arrData)
				{
					$query->app_keyword_types__insert($arrData);
				}
				foreach ($arrInstructions["_UPDATE"] as $arrData)
				{
					$query->app_keyword_types__update($arrData);
				}
				foreach ($arrInstructions["_DELETE"] as $arrData)
				{
					$query->app_keyword_types__delete($arrData);
				}
			}

			return array(
				"success" => "true"
			);
		}
		return $arrResults;
	}

	private function _query_users($arrConfigData, $arrParams, $arrPost)
	{
		$query = new QueryGen();
		$objRoles = new Roles();

		if (!$objRoles->isAllowed("Super Administrator"))
		{
			$arrParams["institution_id"] = $this->_user_session_data->institution_id;
		}
		$arrParams["permission"] = "Student";
		$arrUserPermissions = $query->permissions__select($arrParams);
		$arrParams["user_id"] = array_keys(array_stack("user_id", $arrUserPermissions));
		$arrUsers = $query->users__select($arrParams);
		return $arrUsers;
	}



	private function _query_app_text($arrConfigData, $arrParams, $arrPost)
	{
		$query = new QueryGen();
		if (!is_array($arrParams))
			$arrParams = array();
		$arrParams["_ALL"] = 1;
		$arrResults = $query->app_text__select($arrParams);
		if (@$arrPost["save"] != "true")
			return $arrResults;
		else
		{
			// save_to processing
			$strSaveToResults = $this->save_to_processing($arrConfigData, $arrParams, $arrPost);
			if (!is_null($strSaveToResults))
				return $strSaveToResults;

			$arrData = unserialize(stripslashes(@$arrPost["arrData"]));

			if (!isset($arrData) || !is_array($arrData) || !count($arrData))
			{
				print json_encode(array("error" => "Sorry, there was an error: CU-BIU102"));
				exit;
			}
			// Create params for _proc_query_instructions2
			$arrPertinentParams
				= $arrRequiredParams
				= $arrPrimaryKeys
				= array();

			foreach ($arrConfigData["tables"] as $strTableName => $arrTableParams)
			{
				foreach ($arrTableParams as $strColumnName => $arrColumnParams)
				{
					if (isset($arrColumnParams["pertinent"]) && $arrColumnParams["pertinent"])
					{
						if (isset($arrColumnParams["name"]))
							$arrPertinentParams[] = $arrColumnParams["name"];
						else if (isset($arrColumnParams["data"]))
							$arrPertinentParams[] = $arrColumnParams["data"];
					}
					if (isset($arrColumnParams["data"]))
					{
						if (isset($arrColumnParams["key"]))
							$arrPrimaryKeys[$arrColumnParams["data"]] = 1;
						if (isset($arrColumnParams["required"]) && $arrColumnParams["required"])
							$arrRequiredParams[$strTableName][] = $arrColumnParams["data"];
					}
				}
			}
			if (!count($arrPertinentParams))
			{
				// if there are no pertinent params then all are pertinent
				foreach ($arrConfigData["tables"] as $strTableName => $arrTableParams)
				{
					foreach ($arrTableParams as $strColumnName => $arrColumnParams)
					{
						if (isset($arrColumnParams["name"]))
							$arrPertinentParams[] = $arrColumnParams["name"];
						else if (isset($arrColumnParams["data"]))
							$arrPertinentParams[] = $arrColumnParams["data"];
					}
				}
			}

			$arrPrimaryKeys = array_keys($arrPrimaryKeys);
			foreach ($arrData["tables"] as $strTableName => $arrTableParams)
			{
				if (!isset($arrRequiredParams[$strTableName]))
					$arrRequiredParams[$strTableName] = array();
				//dumper(array($arrTableParams, $arrResults), 1, 0);
				$arrInstructions = $query->_proc_query_instructions2($arrResults, $arrTableParams, $arrPrimaryKeys, $arrPertinentParams, $arrRequiredParams[$strTableName]);
				if (isset($arrInstructions["_ERROR"]))
				{
					return array(
						"error" => $arrInstructions["_ERROR"]
					);
				}

				// transactions
				//dumper($arrInstructions,1);
				foreach ($arrInstructions["_INSERT"] as $arrData)
				{
					$query->app_text__insert($arrData);
				}
				foreach ($arrInstructions["_UPDATE"] as $arrData)
				{
					$query->app_text__update($arrData);
				}
				foreach ($arrInstructions["_DELETE"] as $arrData)
				{
					$query->app_text__delete($arrData);
				}
			}

			return array(
				"success" => "true"
			);
		}
	}


	private function _addon_urgent_translations($arrConfigData, $arrParams, $arrPost, $arrAddOnParams)
	{
		$query = new QueryGen();

	}



	private function _query_app_text_languages($arrConfigData, $arrParams, $arrPost)
	{
		$query = new QueryGen();
		if (!is_array($arrParams))
			$arrParams = array();
		$arrResults = $query->app_text_languages__select($arrParams);
		if (@$arrPost["save"] == "true")
		{
			$arrData = unserialize(stripslashes(@$arrPost["arrData"]));
			if (!isset($arrData) || !is_array($arrData) || !count($arrData))
			{
				print json_encode(array("error" => "Sorry, there was an error: CU-BIU102"));
				exit;
			}
			// Create params for _proc_query_instructions2
			$arrRequiredParams = $arrPrimaryKeys = $arrPertinentParams = array();
			foreach ($arrConfigData["tables"] as $strTableName => $arrTableParams)
			{
				foreach ($arrTableParams as $strColumnName => $arrColumnParams)
				{
					if (isset($arrColumnParams["data"]))
					{
						if (isset($arrColumnParams["key"]))
							$arrPrimaryKeys[$arrColumnParams["data"]] = 1;
						if (isset($arrColumnParams["data"]) && @$arrColumnParams["input"] != "plaintext")
							$arrPertinentParams[] = $arrColumnParams["data"];
						if (isset($arrColumnParams["required"]) && $arrColumnParams["required"])
							$arrRequiredParams[$strTableName][] = $arrColumnParams["data"];
					}
				}
			}
			$arrPrimaryKeys = array_keys($arrPrimaryKeys);
			foreach ($arrData["tables"] as $strTableName => $arrTableParams)
			{
				if (!isset($arrRequiredParams[$strTableName]))
					$arrRequiredParams[$strTableName] = array();
				//dumper(array($arrTableParams, $arrResults), 1, 0);
				$arrInstructions = $query->_proc_query_instructions2($arrResults, $arrTableParams, $arrPrimaryKeys, $arrPertinentParams, $arrRequiredParams[$strTableName]);
				if (isset($arrInstructions["_ERROR"]))
				{
					return array(
						"error" => $arrInstructions["_ERROR"]
					);
				}
				//dumper(array($arrInstructions, $arrData), 1);
				// validation
				$arrErrors = array();
				foreach ($arrInstructions["_INSERT"] as $arrContextData)
				{
					$objAppTextLanguage = first($query->app_text_languages__select(array(
						"app_text_language" => $arrContextData["app_text_language"]
					)));
					if ($objAppTextLanguage)
					{
						$arrErrors["app_text_language"][] = array(
							"value" => $arrContextData["app_text_language"],
							"message" => "This language already exists."
						);
					}
				}
				foreach ($arrInstructions["_UPDATE"] as $arrContextData)
				{
					if (!isset($arrContextData["values"]["app_text_language"]))
						continue;
					$objAppTextLanguage = first($query->app_text_languages__select(array(
						"app_text_language" => $arrContextData["values"]["app_text_language"],
						"_NOT" => array(
							"app_text_language_id" => $arrContextData["where"]["app_text_language_id"]
						)
					)));
					if ($objAppTextLanguage)
					{
						$arrErrors["app_text_language"][] = array(
							"value" => $arrContextData["values"]["app_text_language"],
							"message" => "This language already exists."
						);
					}
				}
				if (count($arrErrors))
				{
					return array(
						"error" => $arrErrors
					);
				}

				// transactions
				//dumper($arrInstructions,1);
				foreach ($arrInstructions["_INSERT"] as $arrData)
				{
					$query->app_text_languages__insert($arrData);
				}
				foreach ($arrInstructions["_UPDATE"] as $arrData)
				{
					$query->app_text_languages__update($arrData);
				}
				foreach ($arrInstructions["_DELETE"] as $arrData)
				{
					$query->app_text_languages__delete($arrData);
				}
			}

			return array(
				"success" => "true"
			);
		}

		return $arrResults;
	}
}
?>