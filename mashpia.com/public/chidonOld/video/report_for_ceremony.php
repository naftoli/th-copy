<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);
ini_set('max_execution_time', 300);

/*
It should be able to print as one page per child of all the items they should be receiving for chidon this year

The top of the page should say the child's name in English and their class. 

Including: 
- recruitment prizes
- sweater (size, color)
- yarmulka/jewelry gift
- celebration boxes (if shipped to school)
- parent/grandparent sweater (if shipped to school)
- their prizes (if it has a personalized name, it should say the name)
- their awards (if its a Plaque/blue trophy it should say the name)
*/

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';

require_once $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth']);
$schools = $as->getSchools();

require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

require_once $_SERVER['DOCUMENT_ROOT'] . '/chidon_shipping/class.chidonShipping.php';
$cs = new ChidonShipping($year);

require_once $_SERVER['DOCUMENT_ROOT'] . '/chidonTests/class.chidonTests.php';
$ct = new ChidonTests();

$gender = isset($_REQUEST['gender']) ? $_REQUEST['gender'] : 'F';
require 'functions.php';
$prizes = getUserPrizes();
$marks = getMarks();
$final_marks = getFinalMarks();

foreach ($schools as $school_id => $school) {
    $children = getChildren($school_id, $gender);
    // echo "<pre>"; print_r($children); echo "</pre>"; 
    // continue;
    if (! empty($children)) {
       if (in_array($school_id, [7,54,106,255])) {
            // continue; // for now
           // for OT do both, by school and by grade
            if ($school_id == 255) {
               $ot_school_sheet = createSpreadSheet($children);
            }
            // sort children by grade and create sheet for each grade
            $sorted = [];
            foreach ($children as $child) {
               $sorted[$child['class_grade']][] = $child;
            }
            foreach ($sorted as $grade => $details) {
               $grade_sheet = createSpreadSheet($details);
            }
       } else {
            $school_sheet = createSpreadSheet($children);
       }
    }
}

echo "<pre>"; print_r($ot_school_sheet); echo "</pre>";
echo "<pre>"; print_r($grade_sheet); echo "</pre>";
echo "<pre>"; print_r($school_sheet); echo "</pre>";