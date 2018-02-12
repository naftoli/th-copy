<?php

/**********************************************************
  Logout Controller
  Allows registered users to login to their accounts
  *********************************************************/

class LogoutController extends Zend_Controller_Action
{
	    function init()
	    {}

		function preDispatch()
		{
			// Get the session object
			$this->_user_session_data = new Zend_Session_Namespace('user_session_data');
		}

	    public function indexAction()
	    {
			include "config.phtml";
			$this->view->original = $this->_user_session_data->original;
			$this->view->user_id = $this->_user_session_data->user_id;
			$strPath = $sn;
			$strTStyle = $this->_request->getParam("tstyle");
			$user = new Zend_Session_Namespace("user_session_data");
			if ($user->referer_url)
			{
				$strPath = $user->referer_url;
			}
			else if ($strTStyle == 'schoolstemplate1')
			{
				$this->view->strPath = "http://mashpia.com/admin.php";
			}
			else if ($strTStyle)
			{
				$strPath .= "/index/index/tstyle/" . $strTStyle . "/";
			}
			
			if ($user->permission == 'Teacher') {
				$this->view->strPath = "http://mashpia.com/logout.php";
			} else {
				$this->view->strPath = "http://mashpia.com/admin.php";
			}
			
			unset($_SESSION["user_session_data"]);
			//$this->view->strPath = $strPath;
			
	    	//$this->_redirect("/".$strPath);
	    	//exit;

      	}

		/**
		 * This action logs out the user from current session and redirects him/her
		 * to www.mashpia.com where he is already logged in
		 */
		public function returnAction()
		{
			unset($_SESSION);
			$this->_redirect("http://www.mashpia.com");
		}
}

?>