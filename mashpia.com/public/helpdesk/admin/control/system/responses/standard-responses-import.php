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
$mswUploadDropzone = array(
  'ajax' => 'srimport-upload',
  'multiple' => 'false',
  'max-files' => 1,
  'drag' => 'false'
);

$title = $msg_adheader60;

include(PATH . 'templates/header.php');
include(PATH . 'templates/system/responses/responses-import.php');
include(PATH . 'templates/footer.php');

?>