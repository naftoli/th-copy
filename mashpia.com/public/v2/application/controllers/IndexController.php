<?php
class IndexController extends Zend_Controller_Action
{

	function testemailAction()
	{
		print mail("andyware@gmail.com", "test", 'message', 'From: admin@mashpia.com\r\n');
		exit;
	}

    function init()
    {}

	function cardstestAction()
	{}

    function preDispatch()
    {}

    public function indexAction()
    {
		$this->view->tstyle = $this->_request->getParam("tstyle");
		$this->view->portal = $this->_request->getParam("portal");
		if ($this->_request->getParam("timeout") == "true") {
			$this->view->message = "Your session timed out, please re-login.";
		}
	}

	public function widgetAction()
	{

	}

	public function moreinfoAction()
	{

	}

	public function contactAction()
	{
		global $arrAppDetails;
		$strTemplateStyle = $this->view->tstyle = $this->_request->getParam("tstyle");
		$arrDetails = $arrAppDetails[$strTemplateStyle];
		$this->_user_session_data = new Zend_Session_Namespace('user_session_data');
		$this->view->tstyle = $this->_request->getParam("tstyle");
		if ($this->_request->isPost())
		{
			$arrResult = array();
			$arrResult['success'] = 'false';
			$arrPost = $this->_request->getPost();
			if (
				!empty($arrPost['email'])
				&& (
					!preg_match('/^[^@]+@[^@]+$/', $arrPost['email'])
					//|| !preg_match('/\.[.]+$/', $arrPost['email'])
				)
			) {
				$arrResult['error']['email'] = "The doesn't appear to be a valid email address.";
			}
			if (!isset($arrResult['error']))
			{
				$strHeaders = 'From: system@mashpia.com' . "\r\n";
				$strTo = join(', ', $arrDetails['admin_emails']);
				mail($strTo, 'Mashpia V2 Contact Us Form', 'Name: ' . $arrPost['name'] . "\r\nEmail: " . $arrPost['email'] . "\r\nReason: " . $arrPost['reason'] . "\r\nMessage: " . $arrPost['message'], $strHeaders);
				$arrResult['success'] = 'true';
				if (!empty($arrPost['email']))
				{
					$strHeaders = 'From: Customer Service <support@mashpia.com>' . "\r\n";
					mail($arrPost['email'], "Your message has been receieved", "This is to confirm that your message was received. We will be sure to reply promptly if necessary.\r\n\r\nThanks,\r\nCustomer Service", $strHeaders);
				}
			}
			print json_encode($arrResult);
			exit;
		}
	}
}
?>