<?php
require $_SERVER['DOCUMENT_ROOT'] . '/db.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonRegYear();

$user_id = mysql_real_escape_string($_POST['user']);
$prize_id = mysql_real_escape_string($_POST['prize']);
$he_name = mysql_real_escape_string($_POST['he_name']);

$sql = "update chidon_user_prizes 
        set he_name = \"" . $he_name . "\" 
        where user_id = " . $user_id . " 
        and prize_id = " . $prize_id . " 
        and year = " . $year;
if (mysql_query($sql)) {
    echo json_encode([
        'success'   => true
    ]);
} else {
    echo json_encode([
        'success'   => false
    ]);
}