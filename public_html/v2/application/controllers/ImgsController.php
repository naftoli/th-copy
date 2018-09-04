<?php
class ImgsController extends Zend_Controller_Action
{
    function init()
	{}

	function preDispatch()
	{}

	public function indexAction()
	{
		// 404
		exit;
	}

	public function textAction()
	{
		header('Content-Type: image/png');
		$intFont = $this->_request->getParam('size');
		if (!$intFont)
			$intFont = 12;
		$intWidth = $this->_request->getParam('w');
		$intHeight = $this->_request->getParam('h');
		$strText = urldecode($this->_request->getParam('text'));
		$strColor = $this->_request->getParam('color');
		$strFont = $this->_request->getParam('font');
		if (!$strFont)
			$strFont = "impact.ttf";
		$strFontFile = SERVER_ROOT . $strFont;
		if (!$strColor)
			$strColor = '0,0,0';
		$arrColor = explode(",", $strColor);
		$im = imagecreatetruecolor($intWidth, $intHeight);
		$black = imagecolorallocate($im, $arrColor[0], $arrColor[1], $arrColor[2]);
		imagealphablending($im, false);
		$colorTransparent = imagecolorallocatealpha($im, 0, 0, 0, 127);
		imagefill($im, 0, 0, $colorTransparent);
		imagesavealpha($im, true);
		imagettftext($im, $intFont, 0, 1, 1+$intFont, $black, $strFontFile, $strText);
		imagepng($im);
		imagedestroy($im);
		exit;
	}

	public function vAction()
	{
		$strBaseURL = WEB_ROOT . "images/imgsrepo/";
		$strImage = $this->_request->getParam("src");
		if (preg_match("/^[0-9a-z]{7}_[0-9]+\.(jpg|gif|jpeg|png)$/i", $strImage, $arrImage))
		{

		}
		else if (!preg_match("/^(?:[0-9]{7}_)?[0-9]+\.(jpg|gif|jpeg|png)$/i", $strImage, $arrImage))
		{
			if (preg_match("/^[0-9]+$/i", $strImage))
				return header("Location: " . WEB_ROOT . "images/show-image/image_id/" . $strImage);
			print "Sorry, there was an error: CI-V101-FW2F3F";
			exit;
		}

		// Check if the file exists
		$strImagePath = SERVER_ROOT . "images/imgsrepo/" . $strImage;
		if (!file_exists($strImagePath))
		{
			$strImagePath = SERVER_ROOT . "images/uploads/" . $strImage;
		}
		/*
		if (!file_exists($strImagePath))
		{
			$strImagePath = SERVER_ROOT2 . "images/imgsrepo/" . $strImage;
		}
		if (!file_exists($strImagePath))
		{
			$strImagePath = SERVER_ROOT2 . "uploads/" . $strImage;
		}
		*/
		header('Content-type: ' . $arrImage[1]);
		header('Content-Disposition: inline');
		header("Pragma: public");
		$intImageExpires = 86400 * 1; // days
		header("Cache-Control: maxage=" . $intImageExpires);
		header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $intImageExpires) . ' GMT');
		if (
			$this->_request->getParam('w')
			&& $this->_request->getParam('w') < 1000
			&& $this->_request->getParam('h')
			&& $this->_request->getParam('h') < 1000
		) {
			$objSimpleImage = new SimpleImage();
			$objSimpleImage->load($strImagePath);
			if ($this->_request->getParam('p') == 'h') // preserve horizontal
				$objSimpleImage->thumbnailph($this->_request->getParam('w'),$this->_request->getParam('h'));
			else
				$objSimpleImage->thumbnail($this->_request->getParam('w'),$this->_request->getParam('h'));
			$objSimpleImage->output();
		} else if ($this->_request->getParam('w')) {
			$objSimpleImage = new SimpleImage();
			$objSimpleImage->load($strImagePath);
			$objSimpleImage->resizeToWidth($this->_request->getParam('w'));
			$objSimpleImage->output();
		} else {
			$strContent = file_get_contents($strImagePath);
			print $strContent;
		}
		exit;
	}

	public function smalluploaderAction ()
	{
		Zend_Session::start();
		$this->view->strCurrentImage = $this->_request->getParam("current_image");
		$this->view->strResultID = $this->_request->getParam("resultid");
		$this->view->intCustomSize = $this->_request->getParam("size");
	}

	public function htmluploaderAction ()
	{
		Zend_Session::start();
		$this->view->strCurrentImage = $this->_request->getParam("current_image");
		$this->view->strResultID = $this->_request->getParam("resultid");
		$this->view->intCustomSize = $this->_request->getParam("size");
	}
	public function processuploadAction ()
	{
		if ($this->_request->getPost('sid'))
			Zend_Session::setId($this->_request->getPost('sid'));
		// If a file is available, upload it
	 	if (!empty($_FILES['Filedata']['name']) && $_FILES['Filedata']['size'] > 0)
		{
			$strFileName = $_FILES['Filedata']['name'];
			$strFileSize = $_FILES['Filedata']['size'];

			// Retrieve the location where we're uploading the image to from the configuration file
			$strUploadPath = SERVER_ROOT . "images/imgsrepo/temp/";
			$strRepoPath = SERVER_ROOT . "images/imgsrepo/";
			if (!file_exists($strUploadPath))
			{
				print "Sorry, there was an error: CI-PU101-DSF7DS";
				exit;
			}
			// Start the HTTP adapter to receive the file
			$objAdapter = new Zend_File_Transfer_Adapter_Http();
			$objAdapter->setDestination($strUploadPath);
			if (!$objAdapter->receive($strFileName))
			{
				print "Sorry, there was an error: CI-PU102-SD7D78";
				exit;
			}

			$this->getHelper('viewRenderer')->setNoRender();

			if (preg_match("/([a-z]+)$/i", $strFileName, $arrMatchedExtention))
			{
				$strExtention = $arrMatchedExtention[1];
			}
			else
			{
				print "The file extension was not recognized.";
				exit;
			}

			$user_session_data = new Zend_Session_Namespace('user_session_data');
			$objImgs = new Imgs();
			$strRand = rand(1000000,9999999);
			$intImgID = $objImgs->_imgs_insert(array(
				"img_category" => "",
				"img_type" => $strExtention,
				"user_id" => $user_session_data->user_id,
				"img_name" => 'Pending'
			));
			$objImgs->_imgs_update(array(
				"where" => array(
					"img_id" => $intImgID
				),
				"values" => array(
					"img_name" => $strRand . "_" . $intImgID. "." . $strExtention
				)
			));

			rename($strUploadPath . $strFileName, $strRepoPath . $strRand . "_" . $intImgID . "." . $strExtention);
			print $strRand . "_" . $intImgID. "." . $strExtention;
			exit;
	   	} else {
			print json_encode(["error" => "no file", "files_uploaded" => $_FILES]);
			exit;
		}
	}
	public function processupload2Action ()
	{
		if ($this->_request->getPost('sid'))
			Zend_Session::setId($this->_request->getPost('sid'));
		// If a file is available, upload it
	 	if (!empty($_FILES['file']['name']) && $_FILES['file']['size'] > 0)
		{
			$strFileName = $_FILES['file']['name'];
			$strFileSize = $_FILES['file']['size'];

			// Retrieve the location where we're uploading the image to from the configuration file
			$strUploadPath = SERVER_ROOT . "images/imgsrepo/temp/";
			$strRepoPath = SERVER_ROOT . "images/imgsrepo/";
			if (!file_exists($strUploadPath))
			{
				print text("Sorry, there was an error") . ": CI-PU101-DSF7DS";
				exit;
			}
			// Start the HTTP adapter to receive the file
			$objAdapter = new Zend_File_Transfer_Adapter_Http();
			$objAdapter->setDestination($strUploadPath);
			if (!$objAdapter->receive($strFileName))
			{
				print text("Sorry, there was an error") . ": CI-PU102-SD7D78";
				exit;
			}

			$this->getHelper('viewRenderer')->setNoRender();

			if (preg_match("/([a-z]+)$/i", $strFileName, $arrMatchedExtention))
			{
				$strExtention = $arrMatchedExtention[1];
			}
			else
			{
				print "The file extension was not recognized.";
				exit;
			}

			$user_session_data = new Zend_Session_Namespace('user_session_data');
			$objImgs = new Imgs();
			$strRand = rand(1000000,9999999);
			$intImgID = $objImgs->_imgs_insert(array(
				"img_category" => "",
				"img_type" => $strExtention,
				"user_id" => $user_session_data->user_id,
				"img_name" => 'Pending'
			));
			$objImgs->_imgs_update(array(
				"where" => array(
					"img_id" => $intImgID
				),
				"values" => array(
					"img_name" => $strRand . "_" . $intImgID. "." . $strExtention
				)
			));

			rename($strUploadPath . $strFileName, $strRepoPath . $strRand . "_" . $intImgID . "." . $strExtention);
			print $strRand . "_" . $intImgID. "." . $strExtention;
			exit;
	   	}
	}

	public function rotateAction () {
		// Check if the file exists
		$user_session_data = new Zend_Session_Namespace('user_session_data');
		$strImage = $this->_request->getParam('src');
		if (empty($strImage)) {
			print "Sorry, there was an error: CI-R101-gna93j";
			exit;
		}
		$strImagePath = SERVER_ROOT . "images/imgsrepo/" . $strImage;
		//print $strImagePath;
		//exit;
		//print file_exists($strImagePath)?1:0;exit;
		if (!file_exists($strImagePath))
		{
			$strImagePath = SERVER_ROOT . "uploads/" . $strImage;
		}
		if (!file_exists($strImagePath))
		{
			$strImagePath = WEB_ROOT2 . "images/imgsrepo/" . $strImage;
		}
		if (!file_exists($strImagePath))
		{
			print "Sorry, there was an error: CI-R102-bgjjs2";
			exit;
		}
		$filename = $strImagePath;
		$rotang = 90; // Rotation angle
		$source = @imagecreatefrompng($filename);
		$strType = 'png';
		if (!$source) {
			$source = @imagecreatefromgif($filename);
			$strType = 'gif';
		}
		if (!$source) {
			$source = @imagecreatefromjpeg($filename);
			$strType = 'jpeg';
		}
		imagealphablending($source, false);
		imagesavealpha($source, true);

		$rotation = imagerotate($source, $rotang, imageColorAllocateAlpha($source, 255, 255, 255, 0));
		imagealphablending($rotation, false);
		imagesavealpha($rotation, true);
		$new_filename = substr(md5($filename.'_'.rand(0,1000000)), 0, 7) . '_' . $user_session_data->user_id . '.' . $strType;
		while (file_exists(SERVER_ROOT  . "images/imgsrepo/" .  $new_filename)) {
			$new_filename = substr(md5($filename.'_'.rand(0,1000000)), 0, 7) . '_' . $user_session_data->user_id . '.' . $strType;
		}
		if ($strType == 'jpeg')
			imagejpeg($rotation, SERVER_ROOT  . "images/imgsrepo/" .  $new_filename);
		else if ($strType == 'gif')
			imagegif($rotation, SERVER_ROOT  . "images/imgsrepo/" .  $new_filename);
		else if ($strType == 'png')
			imagepng($rotation, SERVER_ROOT  . "images/imgsrepo/" .  $new_filename);
		imagedestroy($source);
		imagedestroy($rotation);
		print json_encode(array(
			'success' => 'true',
			'filename' => $new_filename
		));
		exit;
	}
}
?>