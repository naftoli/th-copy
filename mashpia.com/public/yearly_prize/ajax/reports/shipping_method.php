<?php
/***************** DEBUGGING SETTINGS **********************/
if ($_GET['debug']) {
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
    $debug = true;
}

/***************** AUTHENTICATION **********************/
$admin_auth = array('school'); 
require_once($_SERVER["DOCUMENT_ROOT"].'/header.php');

// get the params
$type = $_POST['shipping_method'];
$school_id = $_POST['school_id'];

if(!$type || !$school_id){
    echo json_encode(["success" => false, "post" => $_POST]); die();
}

$sql = "UPDATE schools SET yearly_prize_shipping_method='$type' WHERE school_id='$school_id';";

//echo json_encode(["success" => false, "post" => $_POST, "sql" => $sql]); die();

$result = mysql_query($sql) ? true : false;

echo json_encode(["success" => $result]);