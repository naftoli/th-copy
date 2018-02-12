<?php
/***************** AUTHENTICATION **********************/
$admin_auth = array('school'); 
require_once($_SERVER["DOCUMENT_ROOT"].'/header.php');

if ($admin_user['auth'] != 'super') {
     echo json_encode(["success" => false, "error" => "Invalid Permissions"]); die();
}

$serial_number = mysql_real_escape_string($_POST['serial_number']);
$raffle_id = mysql_real_escape_string($_POST['raffle_id']);
$prize_id = mysql_real_escape_string($_POST['prize_id']);
$school_id = mysql_real_escape_string($_POST['school_id']);

if(!$serial_number || !$raffle_id || !$prize_id || !$school_id){
    echo json_encode(["success" => false, "error" => "Invalid Paramaters"]); die();
}

$user_query = mysql_query("SELECT user_id, first, last FROM users WHERE user_serial = $serial_number AND school_id = $school_id LIMIT 1;");

if(!mysql_num_rows($user_query)){
    echo json_encode(["success" => false, "error" => "Chayol Not Found. Please Check Serial Number"]); die();
}

$user = mysql_fetch_assoc($user_query);
$user_id = $user['user_id'];

$winner_sql = "INSERT INTO raffle_winners (raffle_id, prize_id, user_id) VALUES ($raffle_id, $prize_id, $user_id);";

$success = !!mysql_query($winner_sql);

echo json_encode([
    "success"   => $success,
    "error"     => "Could Not Save Winner. Please Contact Support",
    "name"      => $user['first'] . " " . $user['last']
]);
