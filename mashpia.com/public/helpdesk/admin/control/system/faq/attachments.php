<?php

if (!defined('PARENT')) {
  die('Permission denied');
}

// Access..
if (!in_array($cmd, $userAccess) && $MSTEAM->id != '1') {
  $HEADERS->err403(true);
}

// Uploader class
include(REL_PATH . 'control/classes/class.upload.php');
$MSUPL = new msUpload();

// Upload dropzone..
define('DROPZONE_LOADER', 1);
$mswUploadDropzone2 = array(
  'ajax' => 'faqimport-upload',
  'multiple' => (isset($_GET['edit']) ? 'false' : 'true'),
  'max-files' => (isset($_GET['edit']) ? '1' : '99999'),
  'max-size' => $MSUPL->getMaxSize(),
  'drag' => 'false',
  'div' => 'one'
);

$title  = (isset($_GET['edit']) ? $msg_attachments12 : $msg_attachments2);

include(PATH . 'templates/header.php');
include(PATH . 'templates/system/faq/faq-attachments.php');
include(PATH . 'templates/footer.php');

?>