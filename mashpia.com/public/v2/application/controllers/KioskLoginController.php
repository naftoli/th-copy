<?php
class KioskLoginController extends Zend_Controller_Action
{	
	public function indexAction()
	{
		$objUser = new Users();
		
		if (isset($_SESSION['kiosk_user_session_data']))
			unset($_SESSION['kiosk_user_session_data']);
		
		$this->view->tstyle = $this->_request->getParam("tstyle");
		
		// Check if the form has been posted
		if ($this->_request->isPost())
		{
			$intBarCode = $this->_request->getPost('user_code');
			if (
				preg_match("/^[0-9]{20}$/", $intBarCode)
				&& $objUser->KioskAuthenticate($intBarCode)
			)
				$arrResults = array(
					"success" => "true"
				);
			else
				$arrResults = array(
					"success" => "false"
				);
			print json_encode($arrResults);
			exit;
		}
	}
	
	public function resetAction()
	{
		unset($_SESSION['kiosk_user_session_data']);
		$this->_redirect('kiosk-login');
	}
}
?>