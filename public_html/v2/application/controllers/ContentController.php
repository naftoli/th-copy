<?php
class ContentController extends Zend_Controller_Action
{
	private $_user_session_data;
	private $objPermission;

	function init()
	{
		/* ?? $ajaxContext = $this->_helper->getHelper('AjaxContext');
		$ajaxContext->addActionContext('validate-order', 'html')->initContext(); */
	}

	function preDispatch()
	{
		$this->_user_session_data = new Zend_Session_Namespace('user_session_data');
		//$arrParams = $this->_request->getParams();
		//$utilities = new Utilities();
		//$this->objPermission = $utilities->dispatch_helper($arrParams);
	}

	public function contentmanagerAction()
	{
		$query = new QueryGen();

	}

	public function urgenttranslationsAction()
	{
		$query = new QueryGen();
		$objLists = new Lists();

		$arrResult = array();
		$arrPost = $this->_request->getPost();
		$arrParams = unserialize(stripslashes($this->_request->getPost("arrParams")));
		if ($this->_request->getParam("display_view") == "true")
			return;
		$arrData = unserialize(stripslashes($this->_request->getPost("arrData")));
		if (!is_array($arrParams)) {
			print json_encode(array(
				"error" => text("Sorry, there was an error") . ": CU-BIU102-FFS223A"
			));
			exit;
		}
		if (!isset($arrParams["app_text_language_id"]))
		{
			print json_encode(array(
				"error" => text("Sorry, there was an error") . ": CU-BIU101-441422"
			));
			exit;
		}
		$objAppTextLanguage = first($query->app_text_languages__select(array(
			"app_text_language_id" => $arrParams["app_text_language_id"]
		)));
		$arrConfigData = array(
			"params" => $arrParams,
			"default_table" => "app_text",
			"title" => "Urgent Translations",
			"data_source" => "/content/urgenttranslations",
			"header_text" => "
				Words with the [|Content|] wrapper indicate that system should
				treat those words as variables in other contexts. For example,
				[|Schools|] can be replaced with [|Camps|]). Please maintain the
				variables as much as possible as this will enrich our platform.
			",
			"onsave" => "refresh",
			"tables" => array(
				"app_text" => array(
					"_params" => array(
						"language_id" => 0,
						"_ORDER" => "permission, resource_name, order_found+0 ASC",
						"_LIMIT" => 4,
						"_NOT" => array(
							"_IN" => array(
								"_TABLE" => "app_text",
								"_DEPENDENT" => "primary_app_text_id",
								"_INDEPENDENT" => "app_text_id",
								"language_id" => $arrParams["app_text_language_id"]
							)
						),
						//"_VERBOSE" => 2
					),
					"Location" => array(
						"data" => "resource_name",
						"input" => "plaintext",
						"width" => "60%",
						"no_header_render" => true,
						"prefix" => "Location: "
					),
					"Permission" => array(
						"data" => "permission",
						"input" => "plaintext",
						"width" => "35%",
						"no_header_render" => true
					),
					"Tranlate From English To " . $objAppTextLanguage->app_text_language => array(
						"data" => "content",
						"input" => "plaintext",
						"width" => "98%",
						"bgcolor" => "white",
						"prefix" => "<b>Translate \"</b>",
						"postfix" => "<b>\" to " . $objAppTextLanguage->app_text_language . "</b>"
					),
					"To " . $objAppTextLanguage->app_text_language => array(
						"input" => "text",
						"inject" => "content",
						"name" => "content",
						"width" => "98%",
						"no_header_render" => true,
						"bgcolor" => "white",
						"pertinent" => true
					),
					"primary_app_text_id" => array(
						"data" => "app_text_id",
						"name" => "primary_app_text_id",
						"input" => "hidden"
					),
					"language_id" => array(
						"name" => "language_id",
						"value" => $arrParams["app_text_language_id"],
						"input" => "hidden"
					),
					"institution_id" => array(
						"name" => "institution_id",
						"value" => $this->_user_session_data->institution_id,
						"input" => "hidden"
					),
					"permission" => array(
						"data" => "permission",
						"input" => "hidden"
					),
					"inner_item" => array(
						"data" => "inner_item",
						"input" => "hidden"
					),
					"app_name" => array(
						"data" => "app_name",
						"input" => "hidden"
					),
					"resource_name" => array(
						"data" => "resource_name",
						"input" => "hidden"
					),
					"resource_id" => array(
						"data" => "resource_id",
						"input" => "hidden"
					),
					"order_found" => array(
						"data" => "order_found",
						"input" => "hidden"
					),
					"action" => array(
						"data" => "action",
						"input" => "hidden"
					),
					"controller" => array(
						"data" => "controller",
						"input" => "hidden"
					)
				)
			)
		);
		$arrLoadedData = $objLists->load_data($arrConfigData, $arrPost);
		print json_encode($arrLoadedData);
		exit;
	}

	public function navigationAction()
	{

	}

	public function managelanguagesAction()
	{
		$query = new QueryGen();
		$objLists = new Lists();

		$arrResult = array();
		$arrPost = $this->_request->getPost();
		$arrParams = unserialize(stripslashes($this->_request->getPost("arrParams")));
		if ($this->_request->getParam("display_view") == "true")
			return;
		$arrData = unserialize(stripslashes($this->_request->getPost("arrData")));
		if (!is_array($arrParams)) {
			print json_encode(array(
				"error" => text("Sorry, there was an error") . ": CU-BIU101-FFS223A"
			));
			exit;
		}
		$arrConfigData = array(
			"params" => @$arrParams["params"],
			"default_table" => "app_text_languages",
			"title" => "Manage Languages",
			"data_source" => "/content/managelanguages",
			"create_new" => array(
				"model" => array(
					"is_active" => 0
				)
			),
			"header_text" => "English is the default and therefore can not be disabled or renamed.",
			"tables" => array(
				"app_text_languages" => array(
					"_params" => array(
						"_NOT" => array(
							"app_text_language" => "English"
						),
						"_ORDER" => "hierarchy"
					),
					"_ln" => true,
					"_hierarchy" => array(
						"data" => "hierarchy",
						"input" => "hidden",
						"hierarchy" => true,
						"hierarchy_offset" => 1
					),
					"app_text_language_id" => array(
						"data" => "app_text_language_id",
						"input" => "hidden",
						"key" => true
					),
					"Language Name" => array(
						"data" => "app_text_language",
						"input" => "text",
						"width" => 400,
						"required" => true,
						"unique" => true
					),
					"Status" => array(
						"data" => "is_active",
						"input" => "checkbox2",
						"width" => 100,
						"no_header" => true,
						"value" => 1
					)
				)
			)
		);
		$arrLoadedData = $objLists->load_data($arrConfigData, $arrPost);
		print json_encode($arrLoadedData);
		exit;
	}

	public function terminologyAction()
	{
		$query = new QueryGen();
		$objLists = new Lists();

		$arrResult = array();
		$arrPost = $this->_request->getPost();
		$arrParams = unserialize(stripslashes($this->_request->getPost("arrParams")));
		if ($this->_request->getParam("display_view") == "true")
			return;
		$arrData = unserialize(stripslashes($this->_request->getPost("arrData")));
		if (!is_array($arrParams)) {
			print json_encode(array(
				"error" => text("Sorry, there was an error") . ": CU-BIU101-FFS223A"
			));
			exit;
		}
		$arrConfigData = array(
			"params" => @$arrParams["params"],
			"default_table" => "app_keyword_types",
			"title" => "Terminology Types",
			"data_source" => "/content/terminology",
			"create_new" => array(
				"model" => array(

				)
			),
			"header_text" => "School is the default and therefore can not be created.",
			"tables" => array(
				"app_keyword_types" => array(
					"_params" => array(
						"_NOT" => array(
							"keyword_type" => "School"
						)
					),
					"_ln" => true,
					"app_keyword_type_id" => array(
						"data" => "app_keyword_type_id",
						"input" => "hidden",
						"key" => true
					),
					"Type" => array(
						"data" => "keyword_type",
						"input" => "text",
						"width" => 400,
						"required" => true,
						"unique" => true
					)
				)
			)
		);
		$arrLoadedData = $objLists->load_data($arrConfigData, $arrPost);
		print json_encode($arrLoadedData);
		exit;
	}

	public function keywordtypesAction()
	{
		$query = new QueryGen();
		$objLists = new Lists();

		$arrResult = array();
		$arrPost = $this->_request->getPost();
		$arrParams = unserialize(stripslashes($this->_request->getPost("arrParams")));
		if ($this->_request->getParam("display_view") == "true")
			return;
		$arrData = unserialize(stripslashes($this->_request->getPost("arrData")));
		if (!is_array($arrParams)) {
			print json_encode(array(
				"error" => text("Sorry, there was an error") . ": CU-BIU101-FFS223A"
			));
			exit;
		}
		$arrConfigData = array(
			"params" => @$arrParams["params"],
			"default_table" => "app_text_languages",
			"title" => "Manage Languages",
			"data_source" => "/content/managelanguages",
			"create_new" => array(
				"model" => array(
					"is_active" => 0
				)
			),
			"header_text" => "English is the default and therefore can not be disabled or renamed.",
			"tables" => array(
				"app_text_languages" => array(
					"_params" => array(
						"_NOT" => array(
							"app_text_language" => "English"
						),
						"_ORDER" => "hierarchy"
					),
					"_ln" => true,
					"_hierarchy" => array(
						"data" => "hierarchy",
						"input" => "hidden",
						"hierarchy" => true,
						"hierarchy_offset" => 1
					),
					"app_text_language_id" => array(
						"data" => "app_text_language_id",
						"input" => "hidden",
						"key" => true
					),
					"Language Name" => array(
						"data" => "app_text_language",
						"input" => "text",
						"width" => 400,
						"required" => true,
						"unique" => true
					),
					"Status" => array(
						"data" => "is_active",
						"input" => "checkbox2",
						"width" => 100,
						"no_header" => true,
						"value" => 1
					)
				)
			)
		);
		$arrLoadedData = $objLists->load_data($arrConfigData, $arrPost);
		print json_encode($arrLoadedData);
		exit;
	}
}
?>