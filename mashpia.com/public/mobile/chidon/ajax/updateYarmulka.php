<?php
require $_SERVER['DOCUMENT_ROOT'] . '/db.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonRegYear();

$user_id = mysql_real_escape_string($_POST['user']);
$size = mysql_real_escape_string($_POST['size']);

$sql = "update th_chidon set yarmulka = '" . $size . "' where user_id = " . $user_id . " and year = " . $year;
if (mysql_query($sql)) {
    echo json_encode([
        'success'   => true
    ]);
} else {
    echo json_encode([
        'success'   => false
    ]);
}