<?php
class PortalController extends Zend_Controller_Action
{
	public function chabadchildrenAction()
	{
		$arrGet = $this->_request->getParams();
		$strUrl = "http://v2.mashpia.com/index/index/tstyle/chabadhebrewschool/portal/true";
		if (isset($arrGet['kiosk']))
			$strUrl = "http://v2.mashpia.com/hebrewschools/index/tstyle/chabadhebrewschool/portal/true";
		$this->_redirect($strUrl);
	}
}
?>
