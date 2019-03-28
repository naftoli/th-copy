<?php
class MController extends Zend_Controller_Action
{
	function preDispatch()
	{
		//print 123;exit;
		header('Location: ' . WEB_ROOT . 'mobile/index/tstyle/chabadhebrewschool');
		exit;
	}
	public function indexAction() {
		exit;
	}
}
?>