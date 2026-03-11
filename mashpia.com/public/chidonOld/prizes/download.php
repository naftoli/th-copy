<?php
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';

if ($admin_user['auth'] != 'super') {
    die('Access denied');
}

function createZip($files, $zip_path) {
  $zip = new ZipArchive;
  $success = $zip->open($zip_path, ZipArchive::CREATE);
  if ($success !== true) {
      exit("cannot open <$zip_path>\n");
  }
  // get the root directory
  $root_dir = $_SERVER['DOCUMENT_ROOT'];
  foreach ($files as $file) {
    $zip->addFile(($root_dir . $file['filename']), ('Prize_' . $file['prize_id'] . '.png'));
  }
  $zip->close();

  // download the zip file
  downloadZip($zip_path);
}

function downloadZip($zip_path) {
  while (ob_get_level()) {
      ob_end_clean();
  }
  header('Content-Description: File Transfer');
  header('Content-Type: application/octet-stream');
  header('Content-Disposition: attachment; filename="' . basename($zip_path) . '"');
  header('Expires: 0');
  header('Cache-Control: must-revalidate');
  header('Pragma: public');
  header('Content-Length: ' . filesize($zip_path));
  flush();
  readfile($zip_path);
  unlink($zip_path);
}

$prizes = json_decode($_POST['prizes'], true);
createZip($prizes, 'prizes.zip');