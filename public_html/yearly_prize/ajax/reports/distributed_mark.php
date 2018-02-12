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
$type = $_POST['type'];
$id = $_POST['id'];
$marked = $_POST['mark'] == "true" ? true : false;

if(!$marked){
    $sql = "UPDATE yearly_prize_shipping SET distributed=0 WHERE type='$type' AND id='$id';";
} else {
    $sql = "UPDATE yearly_prize_shipping SET distributed=1 WHERE type='$type' AND id='$id';";
}

$result = mysql_query($sql) ? true : false;

echo json_encode(["success" => $result, "sql" => $sql]);