<?php
/***************** DEBUGGING SETTINGS **********************/
if ($_GET['debug']) {
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
    $debug = true;
    //echo "<h2>Debug log:</h2>";
    //echo "<pre>";
}
/***************** AUTHENTICATION **********************/
$admin_auth = array('school'); 
require_once($_SERVER["DOCUMENT_ROOT"].'/header.php');

/***************** IMPORTS **********************/
require_once(dirname(__FILE__)."/../../classes/Staff.php");

$staff = Staff::load($_POST['staff_id']);

if(!$staff){
    echo json_encode(["success" => false, "error" => "Error: Could not find staff member"]); die(); // show the error and die
}

if(!$staff->destroy()){
    echo json_encode(["success" => false, "error" => "Error: Could not delete staff member"]); die(); // show the error and die
}

echo json_encode(["success" => true]);

