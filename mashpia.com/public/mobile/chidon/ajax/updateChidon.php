<?php
require '../../../db.php';

$year = mysql_real_escape_string($_POST['year']);
$user = mysql_real_escape_string($_POST['user']);
$size = mysql_real_escape_string($_POST['size']);
$grade = mysql_real_escape_string($_POST['grade']);
$book = mysql_real_escape_string($_POST['book']);
$sandwich = mysql_real_escape_string($_POST['sandwich']);
$allergies = mysql_real_escape_string($_POST['allergies']);
$permission = mysql_real_escape_string($_POST['permission']);
$host = mysql_real_escape_string($_POST['host']);
$hostAddr1 = mysql_real_escape_string($_POST['hostAddr1']);
$hostAddr2 = mysql_real_escape_string($_POST['hostAddr2']);
$betStreets = mysql_real_escape_string($_POST['betStreets']);
$hostPhone = mysql_real_escape_string($_POST['hostPhone']);
$shoeSize = mysql_real_escape_string($_POST['shoe']);
$lang = mysql_real_escape_string($_POST['lang']);

$sql_chidon = "UPDATE th_chidon SET "
     ."size = '" . $size . "', "
     ."grade = '" . $grade . "', "
     ."book = '" . $book . "', " 
     ."sandwich = '" . $sandwich . "', "
     ."allergies = '" . $allergies . "', "
     ."shoe_size = '" . $shoeSize . "', "
     ."host = '" . $host . "', "
     ."host_address1 = '" . $hostAddr1 . "', "
     ."host_address2 = '" . $hostAddr2 . "', "
     ."between_streets = '" . $betStreets . "', " 
     ."host_number = '" . $hostPhone . "', "
     ."test_lang = '" . $lang . "'";
if (intval($permission) == 1) {
     $sql_chidon .= ", walk_day = 0, walk_night = 0";
} else if (intval($permission) == 2) {
    $sql_chidon .= ", walk_day = 1, walk_night = 0";
} else if (intval($permission) == 3) {
    $sql_chidon .= ", walk_night = 1, walk_day = 1";
}
$sql_chidon .= " WHERE user_id = " . intval($user) . " AND year = " . intval($year);

//echo $sql;
if (mysql_query($sql_chidon)) echo 0;
else echo mysql_error();
?>