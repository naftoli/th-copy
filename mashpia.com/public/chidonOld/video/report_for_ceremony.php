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
$chidon_prizes = getChidonPrizes();
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
            body {
                font-family: Arial, Helvetica, sans-serif;
                font-size: 14px;
            }
            .child-sheet {
                page-break-after: always;
            }
            .child-sheet img {
                width: 150px;
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
                        $sweater = $child[6];
                        $yarmulka = $child[7];
                        echo "<div class='child-sheet'>";
                        echo "<h4>Name: " . $name . " Grade: " . $grade . "</h4>";                       
                        echo "<p>Sweater: " . $sweater . " Sweater</p>";
                        if (is_numeric($yarmulka)) {
                            echo "<p>Gift: Yarmulka size " . $yarmulka . "</p>";
                        } else {
                            echo "<p>Gift: Jewelry Gift</p>";
                        }
                        echo "<p>Prizes: <br />";
                        foreach ($prizes[$user_id] as $prize) {
                            $prize_id = $prize['id'];
                            $he_name = $prize['name'];
                            $prize_name = $chidon_prizes[$prize_id]['name'];
                            if ($chidon_prizes[$prize_id]['size']) {
                                $prize_name .= " " . $chidon_prizes[$prize_id]['size'];
                            }
                            if ($chidon_prizes[$prize_id]['color']) {
                                $prize_name .= " " . $chidon_prizes[$prize_id]['color'];
                            }
                            if ($he_name) {
                                $prize_name .= " - " . $he_name;
                            }
                            $prize_img = $chidon_prizes[$prize_id]['img'];
                            echo "Prize: " . $prize_name . " <br /><img src='" . $prize_img . "' alt='" . $prize_name . "' />";
                        }
                        echo "</p>";
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
                    $sweater = $child[6];
                    $yarmulka = $child[7];
                    echo "<div class='child-sheet'>";
                    echo "<h4>Name: " . $name . " Grade: " . $grade . "</h4>";
                    echo "<p>Sweater: " . $sweater . " Sweater</p>";
                    if (is_numeric($yarmulka)) {
                        echo "<p>Gift: Yarmulka size " . $yarmulka . "</p>";
                    } else {
                        echo "<p>Gift: Jewelry Gift</p>";
                    }
                    echo "<p>Prizes: <br />";
                    foreach ($prizes[$user_id] as $prize) {
                        $prize_id = $prize['id'];
                        $he_name = $prize['name'];
                        $prize_name = $chidon_prizes[$prize_id]['name'];
                        if ($chidon_prizes[$prize_id]['size']) {
                            $prize_name .= " " . $chidon_prizes[$prize_id]['size'];
                        }
                        if ($chidon_prizes[$prize_id]['color']) {
                            $prize_name .= " " . $chidon_prizes[$prize_id]['color'];
                        }
                        if ($he_name) {
                            $prize_name .= " - " . $he_name;
                        }
                        $prize_img = $chidon_prizes[$prize_id]['img'];
                        echo "Prize: " . $prize_name . " <br /><img src='" . $prize_img . "' alt='" . $prize_name . "' />";
                    }
                    echo "</p>";
                    echo "</div>";
                }
            }
        }
        ?>
    </body>
</html>