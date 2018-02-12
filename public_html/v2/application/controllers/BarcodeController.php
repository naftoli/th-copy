<?php
class BarcodeController extends Zend_Controller_Action
{
	public function qrAction() {
		$strBarcode = $this->_request->getParam("code");
		include SERVER_ROOT . "modules/phpqrcode/qrlib.php";
		QRcode::png($strBarcode, NULL, QR_ECLEVEL_L, 4, 0);
		exit;
	}
}
?>