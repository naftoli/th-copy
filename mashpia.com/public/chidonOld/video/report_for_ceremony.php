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

$grade_sheets = [];
$school_sheets = [];
foreach ($schools as $school_id => $school) {
    $children = getChildren($school_id, $gender);
    // echo "<pre>"; print_r($children); echo "</pre>"; 
    // continue;
    if (! empty($children)) {
       if (in_array($school_id, [7,54,106,255])) {
            // sort children by grade and create sheet for each grade
            $sorted = [];
            foreach ($children as $child) {
               $sorted[$child['class_grade']][] = $child;
            }
            foreach ($sorted as $grade => $details) {
               $grade_sheets[$school_id][$grade] = createSpreadSheet($details, 'ht', false, true);
            }
       } else {
            $school_sheets[$school_id] = createSpreadSheet($children, 'ht', false, true);
       }
    }
}

echo "<pre>"; print_r($grade_sheets); echo "</pre>";
echo "<pre>"; print_r($school_sheets); echo "</pre>";
?>
<DOCTYPE html>
<html>
    <head>
        <meta charset="utf8" />
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Chidon Ceremony Report</title>
        <style>
            .child-sheet {
                page-break-after: always;
            }
            .child-sheet table {
                width: 100%;
                border-collapse: collapse;
            }
            .child-sheet table th,
            .child-sheet table td {
                padding: 10px;
            }
            .child-sheet table td {
                border-bottom: 1px solid #e0e0e0;
            }
        </style>
    </head>
    <body>
        <h1>Chidon Ceremony Report</h1>
        <?php
        if (isset($grade_sheets)) {
            foreach ($grade_sheets as $school_id => $grades) {
                foreach ($grades as $grade => $sheet) {
                    foreach ($sheet as $child) {
                        $user_id = $child[0];
                        $name = $child[1];
                        $grade = $child[2];
                        $reward_track = $child[3];
                        $award_track = $child[4];
                        $trip = $child[5];
                        $prizes = [$child[6], $child[7], $child[8], $child[9], $child[10], $child[11]];
                        echo "<div class='child-sheet'>";
                        echo "<h4>Name: " . $name . " Grade: " . $grade . "</h4>";                       
                        echo "</div>";
                    }
                }
            }
        } else {
            foreach ($school_sheets as $school_id => $sheet) {
                foreach ($sheet as $child) {
                    $user_id = $child[0];
                    $name = $child[1];
                    $grade = $child[2];
                    $reward_track = $child[3];
                    $award_track = $child[4];
                    $trip = $child[5];
                    $prizes = [$child[6], $child[7], $child[8], $child[9], $child[10], $child[11]];
                    echo "<div class='child-sheet'>";
                    echo "<h4>Name: " . $name . " Grade: " . $grade . "</h4>";                       
                    echo "</div>";
                }
            }
        }
        ?>
    </body>
</html>