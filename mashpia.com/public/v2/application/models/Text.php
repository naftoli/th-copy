<?php
class Text
{
	// this will need to be used for ajax outputs

	public function single_translation($strInput, $intResourceID=0)
	{
		$query = new QueryGen();
		$strOutput = $strInput;
		$objRequest = new Zend_Controller_Request_Http();
		preg_match("/^(\/?[^\/]*\/?[^\/]*)/", $_SERVER["REQUEST_URI"], $arrMatched);
		$strURI = reset($arrMatched);
		// fix the uri
		if (preg_match("/^\/[^\/]*\/$/", $strURI))
			$strURI .= "index";
		else if ($strURI == "/")
			$strURI .= "index/index";
		preg_match("/^\/([^\/]+)\/([^\/]+)/", $strURI, $arrMatched);
		$strController = $arrMatched[1];
		$strAction = $arrMatched[2];

		// choose the session to observe
		$strSessionName = "user_session_data";
		$arrControllerSessions = array(
			"hebrewschools" => "hebrewschools"
		);
		if (isset($arrControllerSessions[$strController]))
			$strSessionName = $arrControllerSessions[$strController];
		$objUserSession = new Zend_Session_Namespace($strSessionName);
		if (!$objUserSession)
		{
			$objUserSession = new stdClass;
			$objUserSession->language_id = 1;
			$objUserSession->permission = "Not Logged In";
			$objUserSession->institution_id = 1;
		}

		if (!isset($objUserSession->template_style))
		{
			preg_match("/[^a-z\-_]tstyle\/([^\/]+)/", $_SERVER["REQUEST_URI"], $arrMatched);
			$strTemplateStyle = end($arrMatched);
		}
		else
			$strTemplateStyle = $objUserSession->template_style;

		// if no resource id was provided search using content itself
		if (!$intResourceID)
		{
			$arrTextSelectParams = array(
				"resource_name" => $strURI,
				"institution_id" => array(1),
				"content" => $strInput,
				"primary_app_text_id" => 0,
				array(
					"app_name" => array($strTemplateStyle, ""),
					"_IS_NULL" => array(
						"app_name"
					)
				)
			);
			if ($objUserSession)
			{
				$arrTextSelectParams["permission"] = $objUserSession->permission;
				$arrTextSelectParams["institution_id"][] = $objUserSession->institution_id;
			}
			// Text search
			$arrSearchResults = array_hash("app_text_id", $query->app_text__select($arrTextSelectParams));
		}
		else
		{
			$arrSearchResults = array_hash("app_text_id", $query->app_text__select(array(
				"resource_id" => $intResourceID
			)));
		}
		if (!count($arrSearchResults))
		{
			foreach ($arrMissingText as $intItr => $strMissingText)
			{
				$arrAppTextInsert = array(
					"primary_app_text_id" => 0,
					"order_found" => 0,
					"institution_id" => 1,
					"language_id" => 0,
					"permission" => $objUserSession->permission,
					"resource_name" => $strURI,
					"controller" => $strController,
					"action" => $strAction,
					"content" => $strMissingText,
					"priority" => 0,
					"app_name" => $strTemplateStyle
				);
				$query->app_text__insert($arrAppTextInsert);
			}

			// Create a list of all ids that were first created when the content was found
			$arrOriginalLanguageIds = array();
			foreach ($arrResultsFound as $arrResultItem)
			{
				if (isset($arrResultItem["objOriginalMatch"]))
				{
					$arrOriginalLanguageIds[] = $arrResultItem["objOriginalMatch"]->app_text_id;
				}
			}

			// Searching for the relatvent language
			if (count($arrOriginalLanguageIds))
			{
				// Find translations
				$arrTranslationsFound = $query->app_text__select(array(
					"primary_app_text_id" => $arrOriginalLanguageIds,
					"language_id" => array($objUserSession->language_id, 1),
					"institution_id" => array(1, $objUserSession->institution_id)
				));
				// keys are in order of priotiy
				$arrTranslationsFoundHash = array_bubble_hash("language_id", "institution_id", "priority", $arrTranslationsFound);
				// use this to find the record for the translation
				$arrOriginalTextResourceHash = array_hash("intOriginalTextId", $arrResultsFound);

				// Loop through the matched content and assign the hiest
				// priority items to their associated results
				ksort($arrTranslationsFoundHash, SORT_NUMERIC);
				foreach ($arrTranslationsFoundHash as $intLanguage => $arrInstitutions)
				{
					ksort($arrInstitutions, SORT_NUMERIC);
					foreach ($arrInstitutions as $intInstitution => $arrPriorities)
					{
						krsort($arrPriorities, SORT_NUMERIC);
						foreach ($arrPriorities as $intPriority => $arrContent)
						{
							foreach ($arrContent as $objContent)
							{
								if (isset($arrOriginalTextResourceHash[$objContent->primary_app_text_id]))
								{
									$intResourceKey = $arrOriginalTextResourceHash[$objContent->primary_app_text_id]["intRowKey"];
									$arrResultsFound[$intResourceKey]["objAlternateContent"] = $objContent;
								}
							}
						}
					}
				}
			}
			// Process the content and isolate keywords
			$arrKeywordsParsed = array();
			foreach ($arrResultsFound as $intKey => $arrResultItem)
			{
				if (isset($arrResultItem["objAlternateContent"]))
					$strContent = $arrResultItem["objAlternateContent"]->content;
				else if (isset($arrResultItem["objOriginalMatch"]))
					$strContent = $arrResultItem["objOriginalMatch"]->content;
				else
					$strContent = $arrResultItem["strInitialText"];
				$arrResultsFound[$intKey]["strMatchedContent"] = $strContent;
				$intLanguage = 1;
				if (isset($arrResultItem["objAlternateContent"]))
					$intLanguage = $arrResultItem["objAlternateContent"]->language_id;
				$arrResultsFound[$intKey]["intMatchedLanguage"] = $intLanguage;
				if (preg_match_all("/\[\|[^|\]]+\|\]/", $strContent, $arrKeywords))
				{
					foreach ($arrKeywords[0] as $strKeyword)
					{
						$strKeyword = preg_replace("/^\[\|/", "", $strKeyword);
						$strKeyword = preg_replace("/\|\]$/", "", $strKeyword);
						$arrKeywordsParsed[$intLanguage][$strKeyword] = NULL;
					}
				}
			}
			if (count($arrKeywordsParsed))
			{
				// loop through all keywords and load from/to database
				foreach ($arrKeywordsParsed as $intLanguage => $arrKeywordsList)
				{
					$arrKeywordResults[$intLanguage] = $query->app_keywords__select(array(
						"language_id" => $intLanguage,
						"institution_id" => array(1, $objUserSession->institution_id),
						"content" => array_keys($arrKeywordsList),
						array(
							"app_name" => array($strTemplateStyle, ""),
							"_IS_NULL" => "app_name"
						)
					));
					// add the missing keywords
					$arrMissingKeywords = array_diff(array_keys($arrKeywordsList), array_keys(array_stack("content", $arrKeywordResults[$intLanguage])));
					if (count($arrMissingKeywords))
					{
						foreach ($arrMissingKeywords as $intItr => $strMissingKeyword)
						{
							$arrAppKeywordInsert = array(
								"primary_app_keyword_id" => 0,
								"institution_id" => 1,
								"language_id" => $intLanguage,
								"content" => $strMissingKeyword
							);
							$query->app_keywords__insert($arrAppKeywordInsert);
						}
					}
				}
				if (isset($arrKeywordResults[$intLanguage]))
				{
					$arrKeywordResult = $arrKeywordResults[$intLanguage];
					$arrKeywordFoundHash = array_hash("content", "app_name", "institution_id", $arrKeywordResult);
					//dumper($arrKeywordFoundHash,1,1);
					// Assign the keywords to the result content
					foreach ($arrLanguageResults as $arrResultItem)
					{
						if (preg_match_all("/\[\|([^\|\]]+)\|\]/", $arrResultItem["strMatchedContent"], $arrMatchedKeywords))
						{
							$arrMatchedKeywords = array_flip($arrMatchedKeywords[1]);
							// loop through each keyword and replace the content with the matched results
							foreach ($arrMatchedKeywords as $strKeyword => $intItr)
							{
								// if items from the users current app template are available set as piroity
								if (
									!empty($strTemplateStyle)
									&& isset($arrKeywordFoundHash[$strKeyword])
									&& count($arrKeywordFoundHash[$strKeyword]) > 1
									&& isset($arrKeywordFoundHash[$strKeyword][$strTemplateStyle])
								) {
									$arrPriotiyApp = $arrKeywordFoundHash[$strKeyword][$strTemplateStyle];
									unset($arrKeywordFoundHash[$strKeyword][$strTemplateStyle]);
									$arrKeywordFoundHash[$strKeyword][$strTemplateStyle] = $arrPriotiyApp;
								}
								foreach ($arrKeywordFoundHash[$strKeyword] as $strTemplateStyle => $arrKeywordInstitutions)
								{
									ksort($arrKeywordInstitutions, SORT_NUMERIC);
									foreach ($arrKeywordInstitutions as $intInstitution => $objKeywordItem)
									{
										$intResourceKey = $arrResultItem["intRowKey"];
										$arrResultsFound[$intResourceKey]["strMatchedContent"] = preg_replace("/\[\|" . preg_quote($strKeyword, "/") . "\|\]/", $objKeywordItem->content, $arrResultsFound[$intResourceKey]["strMatchedContent"]);
									}
								}
							}
						}
					}
				}
			}
			// Write the content to the page
			foreach ($arrResultsFound as $arrResultItem)
			{
				// Remove remaining keyword markers
				$arrResultItem["strMatchedContent"] = preg_replace("/\[\|([^\|\]]+)\|\]/", '$1', $arrResultItem["strMatchedContent"]);
				$strRegexPattern = "/" . preg_quote($arrResultItem["strMarker"], "/") . "/";
				$strOutput = preg_replace($strRegexPattern, $arrResultItem["strMatchedContent"], $strOutput);
			}
		}
		return $strOutput;
	}

	public function text_process($strInput)
	{
		global $arrAppDetails;
		global $arrTextualContentData;
		$query = new QueryGen();
		$strOutput = $strInput;
		$objRequest = new Zend_Controller_Request_Http();
		$strRegex = "/___:\['textual_content;r:[0-9]+;i:([0-9]+);([^']*)']:___/";
		preg_match("/^(\/?[^\/]*\/?[^\/]*)/", $_SERVER["REQUEST_URI"], $arrMatched);
		$strURI = reset($arrMatched);
		// fix the uri
		if (preg_match("/^\/[^\/]*\/$/", $strURI))
			$strURI .= "index";
		else if ($strURI == "/")
			$strURI .= "index/index";
		preg_match("/^\/([^\/]+)\/([^\/]+)/", $strURI, $arrMatched);
		$strController = isset($arrMatched[1]) ? $arrMatched[1] : "index";
		$strAction = isset($arrMatched[2]) ? $arrMatched[2] : "index";
		// choose the session to observe
		$strSessionName = "user_session_data";
		$arrControllerSessions = array(
			"hebrewschools" => "hebrewschools"
		);
		if (isset($arrControllerSessions[$strController]))
			$strSessionName = $arrControllerSessions[$strController];
		$objUserSession = new Zend_Session_Namespace($strSessionName);

		if (!isset($objUserSession->template_style))
		{
			preg_match("/[^a-z\-_]tstyle\/([^\/]+)/", $_SERVER["REQUEST_URI"], $arrMatched);
			$strTemplateStyle = end($arrMatched);
		}
		else
			$strTemplateStyle = $objUserSession->template_style;
		if (!$objUserSession)
		{
			$objUserSession = new stdClass;
			$objUserSession->language_id = 0;
			$objUserSession->permission = "Not Logged In";
			$objUserSession->institution_id = 1;
		}
		if (isset($arrAppDetails[$strTemplateStyle]['terminology']))
			$strTerminology = $arrAppDetails[$strTemplateStyle]['terminology'] == 'School' ? 'School' : $arrAppDetails[$strTemplateStyle]['terminology'];
		else
			$strTerminology = 'School';
		// search all place holders on the page
		if (preg_match_all($strRegex, $strOutput, $arrResultsFound, PREG_SET_ORDER))
		{
			// aggregate all results with $arrResultsFound
			$arrResourceIdItems = $arrContentHash = array();
			// loop through the place holders on the page to be indexed
			foreach ($arrResultsFound as $intKey => $arrItemData)
			{
				$arrResultsFound[$intKey] = array();
				$arrResultsFound[$intKey]["strMarker"] = $arrItemData[0];
				// keep in mind the index of an item is subject to change
				$arrResultsFound[$intKey]["strExtraData"] = $arrItemData[2];
				$arrResultsFound[$intKey]["intRowKey"] = $intKey;

				// match the place holder with the dataset
				$arrContextItem = $arrTextualContentData[$arrResultsFound[$intKey]["intRowKey"]];
				$arrResultsFound[$intKey]["strInitialText"] = $arrContextItem["strCurrentText"];
				$arrResultsFound[$intKey]["strResourceID"] = $arrContextItem["strResourceID"];

				if (isset($arrContextItem["strResourceID"]) && $arrContextItem["strResourceID"])
					$arrResourceIdItems[] = $arrContextItem["strResourceID"];
				else
					$arrContentHash[$arrContextItem["strCurrentText"]][$intKey] = 1;
			}
			$arrResultsFoundContentHash = array_hash("content", $arrResultsFound);
			// if no resource id was provided search using content itself
			if (count($arrContentHash))
			{
				$arrTextItemsContent = array_keys($arrContentHash);
				// Search the database for the conent
				$arrTextSelectParams = array(
					"resource_name" => $strURI,
					"institution_id" => array($objUserSession->institution_id,1),
					"content" => $arrTextItemsContent,
					"primary_app_text_id" => 0,
					array(
						"app_name" => array($strTemplateStyle, ""),
						"_IS_NULL" => array(
							"app_name"
						)
					)
				);
				if ($objUserSession)
				{
					$arrTextSelectParams["permission"] = $objUserSession->permission;
					$arrTextSelectParams["institution_id"][] = $objUserSession->institution_id;
				}
				// Text search
				$arrTextSearchResults = array_hash("app_text_id", $query->app_text__select($arrTextSelectParams));
				// aggregate results found with $arrResultsFound
				foreach ($arrTextSearchResults as $intKey => $objTextResult)
				{
					if (!isset($arrContentHash[$objTextResult->content]))
						continue;
					foreach ($arrContentHash[$objTextResult->content] as $intKey => $boolValue)
					{
						$arrResultsFound[$intKey]["objOriginalMatch"] = $objTextResult;
						$arrResultsFound[$intKey]["intOriginalTextId"] = $objTextResult->app_text_id;
					}
				}
			}
			// search for content using a resource id if provided
			else if (count($arrResourceIdItems))
			{
				$arrTextSelectParams = array(
					"permission" => $objUserSession->permission,
					"institution_id" => array($objUserSession->institution_id,1),
					"resource_id" => $arrResourceIdItems,
					"primary_app_text_id" => 0,
					array(
						"app_name" => array($strTemplateStyle, ""),
						"_IS_NULL" => true
					)
				);
				if ($objUserSession)
					$arrTextSelectParams["institution_id"][] = $objUserSession->institution_id;
				$arrTextSearchResults = array_hash("app_text_id", $query->app_text__select($arrTextSelectParams));
				// aggregate results found with $arrResultsFound
				foreach ($arrTextSearchResults as $intKey => $objTextResult)
				{
					foreach ($arrContentHash[$objTextResult->content] as $intKey => $boolValue)
					{
						$arrResultsFound[$intKey]["objOriginalMatch"] = $objTextResult;
						$arrResultsFound[$intKey]["intOriginalTextId"] = $objTextResult->app_text_id;
					}
				}
			}

			// Find the missing items and add them
			$arrMissingText = array_diff($arrTextItemsContent, array_keys(array_stack("content", $arrTextSearchResults)));
			foreach ($arrMissingText as $intItr => $strMissingText)
			{
				$arrAppTextInsert = array(
					"primary_app_text_id" => 0,
					"order_found" => $intItr,
					"institution_id" => 1,
					"language_id" => 0,
					"permission" => $objUserSession->permission,
					"resource_name" => $strURI,
					"controller" => $strController,
					"action" => $strAction,
					"content" => $strMissingText,
					"priority" => 0,
					"app_name" => $strTemplateStyle
				);
				$query->app_text__insert($arrAppTextInsert);
			}

/*
			// Create a list of all ids that were first created when the content was found

			// Searching for the relatvent language
			if (count($arrOriginalLanguageIds))
			{
				// Find translations
				$arrTranslationsFound = $query->app_text__select(array(
					"primary_app_text_id" => $arrOriginalLanguageIds,
					"language_id" => array($objUserSession->language_id, 1),
					"institution_id" => array(1, $objUserSession->institution_id),
					//"_VERBOSE" => 4
				));
				//dumper($arrTranslationsFound,1,1);
				// keys are in order of priotiy
				$arrTranslationsFoundHash = array_bubble_hash("language_id", "institution_id", "priority", $arrTranslationsFound);
				// use this to find the record for the translation
				$arrOriginalTextResourceHash = array_hash("intOriginalTextId", $arrResultsFound);

				// Loop through the matched content and assign the hiest
				// priority items to their associated results
				ksort($arrTranslationsFoundHash, SORT_NUMERIC);
				foreach ($arrTranslationsFoundHash as $intLanguage => $arrInstitutions)
				{
					ksort($arrInstitutions, SORT_NUMERIC);
					foreach ($arrInstitutions as $intInstitution => $arrPriorities)
					{
						krsort($arrPriorities, SORT_NUMERIC);
						foreach ($arrPriorities as $intPriority => $arrContent)
						{
							foreach ($arrContent as $objContent)
							{
								if (isset($arrOriginalTextResourceHash[$objContent->primary_app_text_id]))
								{
									$intResourceKey = $arrOriginalTextResourceHash[$objContent->primary_app_text_id]["intRowKey"];
									$arrResultsFound[$intResourceKey]["objAlternateContent"] = $objContent;
								}
							}
						}
					}
				}
			}
			 *
			 */
			// Process the content and isolate keywords
			//dumper($arrResultItem,1,1);
			$arrKeywordsParsed = array();
			foreach ($arrResultsFound as $intKey => $arrResultItem)
			{
				if (isset($arrResultItem["objAlternateContent"]))
					$strContent = $arrResultItem["objAlternateContent"]->content;
				else if (isset($arrResultItem["objOriginalMatch"]))
					$strContent = $arrResultItem["objOriginalMatch"]->content;
				else
					$strContent = $arrResultItem["strInitialText"];
				$arrResultsFound[$intKey]["strMatchedContent"] = $strContent;
				$intLanguage = 1;
				if (isset($arrResultItem["objAlternateContent"]))
					$intLanguage = $arrResultItem["objAlternateContent"]->language_id;
				$arrResultsFound[$intKey]["intMatchedLanguage"] = $intLanguage;
				if (preg_match_all("/\[\|[^|\]]+\|\]/", $strContent, $arrKeywords))
				{
					foreach ($arrKeywords[0] as $strKeyword)
					{
						$strKeyword = preg_replace("/^\[\|/", "", $strKeyword);
						$strKeyword = preg_replace("/\|\]$/", "", $strKeyword);
						$arrKeywordsParsed[$intLanguage][$strKeyword] = NULL;
					}
				}
			}
			if (count($arrKeywordsParsed))
			{
				// loop through all keywords and load from/to database
				foreach ($arrKeywordsParsed as $intLanguage => $arrKeywordsList)
				{
					$arrKeywordResults[$intLanguage] = $query->app_keywords__select(array(
						"language_id" => $intLanguage,
						'app_keyword_type' => 'School',
						//"institution_id" => array(1, $objUserSession->institution_id),
						'institution_id' => array($objUserSession->institution_id,1),
						"content" => array_keys($arrKeywordsList),
						array(
							"app_name" => array($strTemplateStyle, ""),
							"_IS_NULL" => "app_name"
						)
					));
					// insert the missing keywords
					$arrMissingKeywords = array_diff(array_keys($arrKeywordsList), array_keys(array_stack("content", $arrKeywordResults[$intLanguage])));
					if (count($arrMissingKeywords))
					{
						foreach ($arrMissingKeywords as $intItr => $strMissingKeyword)
						{
							$arrAppKeywordInsert = array(
								"primary_app_keyword_id" => 0,
								"institution_id" => 1,
								"language_id" => $intLanguage,
								"content" => $strMissingKeyword
							);
							$query->app_keywords__insert($arrAppKeywordInsert);
						}
					}
					// check to search for other keywords
					$arrKeywordIds = first(array_extract2('app_keyword_id', $arrKeywordResults));
					$arrNewKeywords = array_hash('primary_app_keyword_id', $query->app_keywords__select(array(
						'app_keyword_type' => $strTerminology,
						"institution_id" => array($objUserSession->institution_id,1),
						'primary_app_keyword_id' => $arrKeywordIds
					)));
				}
				// Split the results up by language to assign keywords
				$arrResultLanguages = array_bubble_hash("intMatchedLanguage", $arrResultsFound);
				foreach ($arrResultLanguages as $intLanguage => $arrLanguageResults)
				{
					// Were there keywords found under this language
					if (isset($arrKeywordResults[$intLanguage]))
					{
						$arrKeywordResult = $arrKeywordResults[$intLanguage];
						$arrKeywordFoundHash = array_hash("content", "app_name", "institution_id", $arrKeywordResult);
						//dumper($arrKeywordFoundHash,1,1);
						// Assign the keywords to the result content
						foreach ($arrLanguageResults as $arrResultItem)
						{
							if (preg_match_all("/\[\|([^\|\]]+)\|\]/", $arrResultItem["strInitialText"], $arrMatchedKeywords))
							{
								$arrMatchedKeywords = array_flip($arrMatchedKeywords[1]);
								// loop through each keyword and replace the content with the matched results
								foreach ($arrMatchedKeywords as $strKeyword => $intItr)
								{
									// if items from the users current app template are available set as piroity
									if (
										!empty($strTemplateStyle)
										&& isset($arrKeywordFoundHash[$strKeyword])
										&& count($arrKeywordFoundHash[$strKeyword]) > 1
										&& isset($arrKeywordFoundHash[$strKeyword][$strTemplateStyle])
									) {
										$arrPriotiyApp = $arrKeywordFoundHash[$strKeyword][$strTemplateStyle];
										unset($arrKeywordFoundHash[$strKeyword][$strTemplateStyle]);
										$arrKeywordFoundHash[$strKeyword][$strTemplateStyle] = $arrPriotiyApp;
									}
									if (isset($arrKeywordFoundHash[$strKeyword]))
									{
										foreach ($arrKeywordFoundHash[$strKeyword] as $strTemplateStyle => $arrKeywordInstitutions)
										{
											ksort($arrKeywordInstitutions, SORT_NUMERIC);
											foreach ($arrKeywordInstitutions as $intInstitution => $objKeywordItem)
											{
												$intResourceKey = $arrResultItem["intRowKey"];
												$intKeywordKey = $objKeywordItem->app_keyword_id;
												$strReplaceKeyword = $strKeyword;
												if (isset($arrNewKeywords[$intKeywordKey])) {
													$objMachedKeyword = $arrNewKeywords[$intKeywordKey];
													$strReplaceKeyword = $objMachedKeyword->content;
												}
												$arrResultsFound[$intResourceKey]["strMatchedContent"] = preg_replace("/\[\|" . preg_quote($strKeyword, "/") . "\|\]/", $strReplaceKeyword, $arrResultsFound[$intResourceKey]["strMatchedContent"]);
											}
										}
									}
								}
							}
						}
					}
				}
			}
			// Write the content to the page
			foreach ($arrResultsFound as $arrResultItem)
			{
				// Remove remaining keyword markers
				$arrResultItem["strMatchedContent"] = preg_replace("/\[\|([^\|\]]+)\|\]/", '$1', $arrResultItem["strMatchedContent"]);
				$strRegexPattern = "/" . preg_quote($arrResultItem["strMarker"], "/") . "/";
				$strOutput = preg_replace($strRegexPattern, $arrResultItem["strMatchedContent"], $strOutput);
			}
		}
		return $strOutput;
	}


}
?>
