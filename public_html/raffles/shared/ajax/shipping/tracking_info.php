<?php
// enable debuging
if ($_GET['debug']) {
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
    $debug = true; // set debug to true
}

/***************** AUTHENTICATION **********************/
$admin_auth = array('school'); 
require_once($_SERVER["DOCUMENT_ROOT"].'/header.php');
// only superusers can use this page. Non superusers get redirected to the page that they can use
if ($admin_user['auth'] != 'super') {
    echo json_encode(["success" => false, "error" => "You do not have permission do perform this action"]); die();
}

/***************** UPDATE/CREATE TRACKING NUMBERS **********************/
if($_POST['action'] == "update"){
    // make sure there is a tracking number
    if(!$_POST["tracking_number"]){
        echo json_encode(["success" => false, "error" => "Please enter a tracking number"]); die();
    }
    // parse the params
    $tracking_number = htmlspecialchars(mysql_real_escape_string($_POST["tracking_number"])); // Prevent SQL injection and HTML injection
    $tracking_number_id = mysql_real_escape_string($_POST["tracking_number_id"]);
    $school_id = mysql_real_escape_string($_POST["school_id"]);
    
    // generate the correct SQL
    if($tracking_number_id){
        $sql = "UPDATE tracking_numbers SET tracking_number='$tracking_number' WHERE tracking_number_id=$tracking_number_id;";
    } else {
        $created_date = date("Y-m-d H:i:s"); // log the date that it was created into the description
        $sql = "INSERT INTO tracking_numbers (school_id, tracking_number, description) VALUES ($school_id, '$tracking_number', 'Raffle Prize Shipment - $created_date')";
    }
    
    $result = mysql_query($sql);
    
    echo json_encode(["success" => !!$result, "error" => "Server Error. Please contact support"]); die();
    
}

if($_POST['action'] == "delivered"){
    $tracking_number_id = mysql_real_escape_string($_POST["tracking_number_id"]);
    $delivered_at = date("Y-m-d H:i:s"); // get the current time as a mysql timestamp;
    // make sure an ID was passed in
    if(!$tracking_number_id){
        echo json_encode(["success" => false, "error" => "Inavlid Request"]); die();
    }
    
    $sql = "UPDATE tracking_numbers SET delivered_at='$delivered_at' WHERE tracking_number_id = '$tracking_number_id';";
    
    $result = mysql_query($sql);
    
    echo json_encode(["success" => !!$result, "error" => "Server Error. Please contact support"]); die();
}

echo json_encode(["success" => false, "error" => "Inavlid Request"]); die();
