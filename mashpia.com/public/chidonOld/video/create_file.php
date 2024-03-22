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
    }
}

downloadFile();