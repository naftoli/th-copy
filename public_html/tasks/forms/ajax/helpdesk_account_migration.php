<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
/***************** AUTHENTICATION **********************/
$admin_auth = array('school'); 
require_once($_SERVER["DOCUMENT_ROOT"].'/header.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/helpdesk/control/connect.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/helpdesk/control/functions.php');

function return_json_error($error_msg, $details = false) {
    echo json_encode([
        "success"   => false,
        "error"     => $error_msg,
        "details"   => $details
    ]);
    die();
}

// only superusers can use this page
if ($admin_user['auth'] != 'super') {
    return_json_error("Sorry you don't have the privilege(s) necessary to view this page.");
}

$admin_id = isset($_POST['admin_id']) ? $_POST['admin_id'] : false;
$action = $_POST['action'];

if(!$action){
    return_json_error("Invalid Request");
}

if($action == "refresh_admin"){
    if(!$admin_id){return_json_error("Invalid Request");}
    refresh_admin($admin_id); // see lines 68 - 
} else if($action == "refresh_all_admins"){
    refresh_all_admins();
} elseif ($action == "create_admins"){
    create_admins();
} else {
    return_json_error("Invalid Request");
}

// break up code into smaller functions...
include(dirname(__FILE__)."/../functions/helpdesk_account_migration.php"); // load in the functions used above