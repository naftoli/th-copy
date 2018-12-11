<?php
// DBS connection.....
require_once( $_SERVER['DOCUMENT_ROOT'].'/db.php' );
require_once(dirname(__FILE__)."/functions/header.php");

// Authentication scheme
require_once( $_SERVER['DOCUMENT_ROOT'].'/mobile/reg/ajax/encrypt.php' );
$login = encrypt_decrypt('decrypt', $_POST['login']);
if(!$login) render_json_error("Invalid Login");

$user_query = mysql_query("SELECT * FROM th_chidon_staff WHERE staff_id = '$login' LIMIT 1;"); // get the users info

if(!$user_query || mysql_num_rows($user_query) == 0)  render_json_error("Invalid Login");

$user = mysql_fetch_assoc($user_query);
echo json_encode([
    "success" => true,
    "user" => $user
]);