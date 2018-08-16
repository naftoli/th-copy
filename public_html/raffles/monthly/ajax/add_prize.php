<?php
/***************** AUTHENTICATION **********************/
$admin_auth = array('school'); 
require_once($_SERVER["DOCUMENT_ROOT"].'/header.php');

if ($admin_user['auth'] != 'super') {
     echo json_encode(["success" => false, "error" => "Invalid Permissions"]); die();
}

$raffle_id = mysql_real_escape_string($_POST['raffle_id']);
$prize_id = mysql_real_escape_string($_POST['prize_id']);
$school_id = mysql_real_escape_string($_POST['school_id']);

if( !$raffle_id || !$prize_id || !$school_id ){
    echo json_encode(["success" => false, "error" => "Invalid Paramaters"]); die();
}

$prize_sql = "INSERT INTO raffles_monthly (raffle_id, prize_id, school_id) VALUES ($raffle_id, $prize_id, $school_id);";

$success = !!mysql_query($prize_sql);

echo json_encode([
    "success"   => $success
]);
