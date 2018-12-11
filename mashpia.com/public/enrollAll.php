<?php
require 'db.php';

$campaigns = array(
    'chabad' => array(
        1,4,12,13,16,21,27,40,41,42,45,90,100
    ),
    'frum' => array(
        1,4,93,92,16,21,27,94,41,42,45,90,100
    )
);

$users = array();
$classes = array();
$userLevels = array();
$sql = "select user_id, class_id, dob, school_type_id from users where user_registered > 0";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $users[] = $row;
    if ($row['class_id']) {
        if (array_key_exists($row['class_id'], $classes)) {
            $level = $classes[$row['class_id']];
        } else {
            $year = "select class_grade from classes where class_id = " . $row['class_id'];
            $year_res = mysql_query($year);
            $row_year = mysql_fetch_row($year_res);
            $y = $row_year[0];
            switch ($y) {
                case 'Pre1a':
                    $level = 6;
                    break;
                case '1':
                    $level = 7;
                    break;
                case '2':
                    $level = 8;
                    break;
                case '3':
                    $level = 9;
                    break;
                case '4':
                    $level = 10;
                    break;
                case '5':
                    $level = 11;
                    break;
                case '6':
                    $level = 12;
                    break;
                case '7':
                    $level = 13;
                    break;
                case '8':
                    $level = 14;
                    break;
                default:
                    $level = 6;
                    break;
            }
            $classes[$row['class_id']] = $level;
        }
    } else {
        //figure out age and set level by age
        $d1 = new DateTime();
        $d2 = new DateTime($row['dob']);
        $age = $d2->diff($d1);
        $level = $age->format('%y');
        if ($level < 6) $level = 6;
        if ($level > 14) $level = 14;		
    }
    $userLevels[$row['user_id']] = $level;
}
echo "<pre>"; print_r($userLevels); echo "</pre>"; exit;