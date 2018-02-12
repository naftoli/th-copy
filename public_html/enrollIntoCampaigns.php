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
$levels = array();
$classes = array();
$sql = "select * from users where user_registered > 0";
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
    $levels[$row['user_id']] = $level;
}

$updated = array();
foreach ($users as $row) {
    $user = $row['user_id'];
    $typeID = $row['school_type_id'];
    $level = $levels[$user];
    
    // figure out type
    switch ($typeID) {
        case 2:
        case 3:
            $type = 'chabad';
            break;
        case 12:
        case 13:
            $type = 'frum';
            break;
        default:
            $type = 'chabad';
            break;                
    }
    foreach ($campaigns[$type] as $campaign) {
        $sql = "select * from user_tracks where user_id = " . $user . " and subject_id = " . $campaign;
        $result = mysql_query($sql);
        if (mysql_num_rows($result) > 0) {
            $row = mysql_fetch_assoc($result);
            if (!$row['enrolled']) {
                $sql = "update user_tracks set enrolled = 1 where user_id = " . $user . " and subject_id = " . $campaign;
                mysql_query($sql) or die(mysql_error());
            }
        } else {
            $track = 1;
            if ($campaign == 1) {
                if ($type == 'chabad') {
                    $track = 5;
                } else if ($type == 'frum') {
                    $track = 3;
                }
            } 
            $sql = "insert into user_tracks
                    set user_id = " . $user . ",
                    subject_id = " . $campaign . ",
                    track_id = " . $track . ",
                    level = " . $level . ",
                    enrolled = 1";
            mysql_query($sql) or die(mysql_error());
        }
        $updated[$user][] = $campaign;
    }
}
echo "<pre>"; print_r($updated); echo "</pre>";
?>