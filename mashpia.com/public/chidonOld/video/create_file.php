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

require_once $_SERVER['DOCUMENT_ROOT'] . '/chidon_shipping/class.chidonShipping.php';
$cs = new ChidonShipping($year);

$gender = $_REQUEST['type'];
require 'functions.php';

$prizes = getUserPrizes();
$marks = getMarks();
$final_marks = getFinalMarks();
//$children = getChildren(0, $gender);
//$sheet = createSpreadSheet($children);
//$file_name = "schools.txt";
//createFile($file_name, $sheet);
//$allChildren = [];
foreach ($schools as $school_id => $school) {
    $children = getChildren($school_id, $gender);
    if (! empty($children)) {
//        if (in_array($school_id, [54,106,255])) {
//            // for OT do both, by school and by grade
//            if ($school_id == 255) {
//                $sheet = createSpreadSheet($children);
//                $file_name = $school . ".tsv";
//                createFile($file_name, $sheet);
//            }
//            // sort children by grade and create sheet for each grade
//            $sorted = [];
//            foreach ($children as $child) {
//                $sorted[$child['class_grade']][] = $child;
//            }
//            foreach ($sorted as $grade => $details) {
//                $sheet = createSpreadSheet($details);
//                $file_name = $school . " Grade " . $grade . ".tsv";
//                createFile($file_name, $sheet);
//            }
//        } else {
            $sheet = createSpreadSheet($children);
            $file_name = $school . ".tsv";
            createFile($file_name, $sheet);
//            $allChildren += $children; // for images
//        }
    }
}

downloadFile();
//createImages($allChildren);

//// loop through dir to get files
//$dir = getcwd();
//$list = scandir($dir);
//$files = extractFiles($list);
////$list2 = scandir($dir . '/images');
////$images = extractFiles($list2);
//
//$filename = "Chidon.zip";
//createZip($files, $filename);
//
//header('Content-Description: File Transfer');
//header('Content-Type: application/octet-stream');
//header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
//header('Expires: 0');
//header('Cache-Control: must-revalidate');
//header('Pragma: public');
//header('Content-Length: ' . filesize($filename));
//flush(); // Flush system output buffer
//readfile($filename);
//unlink($filename);