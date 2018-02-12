<?php
//**************** GET THE ADMIN ****************//
$admin_auth = array('school'); 
require_once($_SERVER["DOCUMENT_ROOT"].'/header.php');
/***************** IMPORTS **********************/
require_once $_SERVER["DOCUMENT_ROOT"].'/class.adminSchools.php';

/***************** LOAD SCHOOLS **********************/
$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
$schools = $as->getSchools();

$admin_id = $admin_user['admin_id'];

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
} elseif (count($schools) == 1){
    $admin['school_name'] = current($schools);
}

$admin['account_type'] = $admin_user['auth'] == "super" ? "Headquarters" : "Base Commander" ;

echo json_encode(["success" => true, "admin" => $admin, "user" => $user]);