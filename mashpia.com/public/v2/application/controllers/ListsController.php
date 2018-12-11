<?php
class ListsController extends Zend_Controller_Action
{
	private $_user_session_data;
	private $objPermission; // permission instance
	private $boolVerbose = 0;

	function preDispatch()
	{
		$query = new QueryGen();
		$arrParams = $this->_request->getParams();
		unset($arrParams["controller"], $arrParams["action"], $arrParams["module"]);
		$strParam = preg_replace("/[&=]+/", "/", http_build_query($arrParams));
		/*
		// Load thie session array
		$this->_user_session_data = new Zend_Session_Namespace('user_session_data');
		if (
			!$this->_user_session_data->user_id
			|| !$this->_user_session_data->permission_id
			|| !$this->_user_session_data->permission
			|| !$this->_user_session_data->institution_id
		)
			$this->_redirect('logout/index/' . $strParam);
		$this->objPermission = first($query->permissions__select(array(
			"user_id" => $this->_user_session_data->user_id,
			"permission_id" => $this->_user_session_data->permission_id,
			"permission" => $this->_user_session_data->permission,
			"institution_id" => $this->_user_session_data->institution_id
		)));
		if (!$this->objPermission)
			$this->_redirect('logout/index/' . $strParam);
		*/
	}

	function standard1Action()
	{
		$this->view->strTitle = ucfirst(trim($this->_request->getParam("title")));
		if (!$this->view->strTitle)
			$this->view->strTitle = "List";
		$this->view->strTitle = $this->view->strTitle;
		$this->view->strObjSrc = $this->_request->getParam("objsrc");
		$this->view->boolLineNumbers = $this->_request->getParam("ln") == "1" ? 1 : 0;
		$this->view->strHierarchyCol = $this->_request->getParam("hierarchy");
		$this->view->arrFields = preg_split("/ *; */", $this->_request->getParam("fields"));
		$this->view->strDataModel = $this->_request->getParam("datamodel");
	}

	function standard2Action()
	{
		$arrResult = array();
		$objSourceParams = json_decode(stripslashes(urldecode($this->_request->getParam("params"))));
		if (!is_object($objSourceParams))
		{
			$this->view->arrSourceParams = array("error" => text("Sorry, there was an error") . ": CL-S2101-DF322F");
		}
		else if (!isset($objSourceParams->data_source))
		{
			$this->view->arrSourceParams = array("error" => text("Sorry, there was an error") . ": CL-S2102-GDGE23");
		}
		else
			$this->view->arrSourceParams = (array) $objSourceParams;
	}

	function standard3Action()
	{
		/*

required:
	params: could be empty but a list of params that get passed to the query
	default_table: the table that is being displayed / edited
	data_source: the source where the script should postback to
	tables: provide an array of the tables with arrays of params reqiored for each column

options:
	create new: enables creation of new records as well as deleting. provide an array for the default data set for the newly created record
	header_text: provide a bubble of text at the top of the scene

table params: - the key defines the column name if it is being displayed
	_params: provide extra params to be merged in for the current table query

table special columns
	_ln: true | flase. display line numbers the left

table column params:
	key: true | false. indicate that this column should be match in the database as the key
	required: true | false. indicate that this column is required
	pertinent: true | false. if the feild is left blank the row is ommited from processing
	unique: true | false. indicate that this column is unique
	data: define which column on the database to be dislpayed
	input: image | plaintext | text | checkbox | checkbox2 | hidden
	no_header_render: dont render this column in the header
	no_header: true | false. hide the header
	name: override the default name of an input
	value: override the value of a column
	hierarchy_offset: deine the number the hierarchy should start from
	hierarchy: true | false. enables the move up ability and maintains the current order of items on the screen
	width: define the width of the column
	prefix: add a value to the start of the content
	postfix: add a value to the end of the content
	bgcolor: change the background color of a cell



		 */
		$arrResult = array();
		$arrParams = $this->_request->getParams();
		$objSourceParams = json_decode(stripslashes(urldecode($arrParams["params"])));
		unset($arrParams["params"]);
		$this->view->arrPassedInParams = $arrParams;
		if (!is_object($objSourceParams))
		{
			$this->view->arrSourceParams = array("error" => text("Sorry, there was an error") . ": CL-S2101-DF322F");
		}
		else if (!isset($objSourceParams->data_source))
		{
			$this->view->arrSourceParams = array("error" => text("Sorry, there was an error") . ": CL-S2102-GDGE23");
		}

		$arrSourceParams = (array) $objSourceParams;
		$arrSourceParams = array_merge($arrSourceParams, $arrParams);
		$this->view->arrSourceParams = $arrSourceParams;
	}
}
?>