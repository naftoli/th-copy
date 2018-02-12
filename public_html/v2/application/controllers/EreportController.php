<?php
class EreportController extends Zend_Controller_Action
{
	private $_user_session_data;
	private $objPermission; // permission instance

	function preDispatch()
	{
		$query = new QueryGen();
		$arrParams = $this->_request->getParams();
		unset($arrParams["controller"], $arrParams["action"], $arrParams["module"]);
		$strParam = preg_replace("/[&=]+/", "/", http_build_query($arrParams));

		// Load thie session array
		$this->_user_session_data = new Zend_Session_Namespace('user_session_data');
		$this->objPermission = FALSE;
		
		/*
		if (!(
			!$this->_user_session_data->user_id
			|| !$this->_user_session_data->permission_id
			|| !$this->_user_session_data->permission
			|| !$this->_user_session_data->institution_id
		)) {
			$this->objPermission = first($query->permissions__select(array(
				"user_id" => $this->_user_session_data->user_id,
				"permission_id" => $this->_user_session_data->permission_id,
				"permission" => $this->_user_session_data->permission,
				"institution_id" => $this->_user_session_data->institution_id
			)));
		}
		*/
	}

	// Post url: /ereport/ajaxerror
	// Post array: {
	//	'code' : 'CR-AE101-lfkmef',
	//	'sequence' => JS_TIMESTAMP,
	//	'location' : '/ereport/ajaxerror',
	//	'message' : '...',
	//	'other' : '...'
	// }
	public function ajaxerrorAction()
	{
		$query = new QueryGen();
		$arrPost = $this->_request->getPost();
		$query->error_reports__insert(array(
			'user_id' => $this->_user_session_data->user_id,
			'institution_id' => $this->_user_session_data->institution_id,
			'permission_id' => empty($this->objPermission) ? NULL : $this->objPermission->permission_id,
			'sequence' => isset($arrPost['sequence']) ? $arrPost['sequence'] : '',
			'code' => isset($arrPost['code']) ? $arrPost['code'] : '',
			'location' => isset($arrPost['location']) ? $arrPost['location'] : '',
			'message' => isset($arrPost['message']) ? $arrPost['message'] : '',
			'other' => isset($arrPost['other']) ? $arrPost['other'] : ''
		));
		print json_encode(array(
			'success' => 'true'
		));
		exit;
	}

}
?>