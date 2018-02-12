<?php
if ($_GET['debug']) {
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
    $debug = true; // set debug to true
    echo "<pre>";
}

/***************** AUTHENTICATION **********************/
$admin_auth = array('school'); 
require_once($_SERVER["DOCUMENT_ROOT"].'/header.php');

$tracking_number = mysql_real_escape_string($_POST['tracking_number']);
$provider = mysql_real_escape_string($_POST['provider']);
$shipment_id = mysql_real_escape_string($_POST['shipment_id']);
$tracking_number_id = isset($_POST['tracking_number_id']) ? mysql_real_escape_string($_POST['tracking_number_id']) : false;

if($tracking_number_id){
    $sql = "UPDATE tracking_numbers SET tracking_number='$tracking_number', provider='$provider' WHERE tracking_number_id='$tracking_number_id'";
} else {
    $sql = "INSERT INTO tracking_numbers (shipment_id, tracking_number, provider) VALUES ('$shipment_id', '$tracking_number', '$provider')";
}

echo json_encode(["success" => !!mysql_query($sql), "error" => "Sorry, it seems that we are having an issue with tracking numbers at the moment. Please try again later"]);
