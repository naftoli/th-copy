<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/db.php'); // connect to the database

//**************** GET THE ADMIN ****************//
require_once(dirname(__FILE__)."/../reg/ajax/encrypt.php");
$admin_id = encrypt_decrypt('decrypt', $_COOKIE['admin']);

if(!$admin_id){
    echo json_encode(["success" => false]); die();
}

$admin_query = mysql_query("SELECT username, first, last, admin_email, admin_phone_mobile FROM admins WHERE admin_id='$admin_id' LIMIT 1");
if (mysql_num_rows($admin_query) === 0) {
    echo json_encode(["success" => false]); die();
}

$admin = mysql_fetch_assoc($admin_query);

//**************** GET THE USER IF PROVIDED ****************//
$user = new stdClass; // converts to {} in JSOIN
$user_id = isset($_POST['user_id']) ? mysql_real_escape_string($_POST['user_id']) : false;

if($user_id) {
    $user_query = mysql_query("SELECT first, last, school_name FROM users JOIN schools USING (school_id) WHERE user_id = '$user_id'");
    $user = mysql_fetch_assoc($user_query);
}

echo json_encode(["success" => true, "admin" => $admin, "user" => $user]);