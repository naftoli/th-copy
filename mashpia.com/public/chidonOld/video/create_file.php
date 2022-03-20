<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);
ini_set('max_execution_time', 300);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';

if ($admin_user['auth'] != 'super') {
    echo "No Permission to be here.";
    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth']);
$schools = $as->getSchools();

require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

$gender = $_REQUEST['type'];
require 'functions.php';

$prizes = getUserPrizes();
$marks = getMarks();
$final_marks = getFinalMarks();
//$children = getChildren(0, $gender);
//$sheet = createSpreadSheet($children);
//$file_name = "schools.txt";
//createFile($file_name, $sheet);
$allChildren = [];
foreach ($schools as $school_id => $school) {
    $children = getChildren($school_id, $gender);
    if (! empty($children)) {
        $sheet = createSpreadSheet($children);
        $file_name = $school . ".tsv";
        createFile($file_name, $sheet);
        $allChildren += $children; // for images
    }
}
createImages($allChildren);

// loop through dir to get files
$dir = getcwd();
$list = scandir($dir);
$list2 = scandir($dir . '/images');
$files = extractFiles($list);
$images = extractFiles($list2);

$filename = "Chidon.zip";
createZip($files, $images, $filename);

header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($filename));
flush(); // Flush system output buffer
readfile($filename);
unlink($filename);