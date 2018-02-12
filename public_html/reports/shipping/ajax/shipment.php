<?php
/***************** DEBUGGING **********************/
if ($_GET['debug']) {
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
    $debug = true; // set debug to true
}

/***************** AUTHENTICATION **********************/
$admin_auth = array('school'); 
require_once($_SERVER["DOCUMENT_ROOT"].'/header.php');
// load the shipment class
require_once(dirname(__FILE__)."/../classes/Shipment.php");

use shipping\Shipment; // get rid of the namespacing...

/***************** POST DATA **********************/
$status = ['success' => false, 'error' => "Under Development"];

$shipment_id = isset($_POST['shipment_id']) ? mysql_real_escape_string($_POST['shipment_id']) : false;

$name = mysql_real_escape_string($_POST['name']);
$school_id = mysql_real_escape_string($_POST['school_id']);
$date_shipped = isset($_POST['date_shipped']) ?  mysql_real_escape_string($_POST['date_shipped']) : false;
$description = isset($_POST['description']) ? mysql_real_escape_string($_POST['description']) : false;
$delivered = isset($_POST['delivered']) ? $_POST['delivered'] == "true" : false;
$archived = isset($_POST['archived']) ? $_POST['archived'] == "true" : false;

if(!$name && !$shipment_id) {
    $status['error'] = "Invalid Paramaters";
    echo json_encode($status); die();
}

$props = ['name' => $name, 'school_id' => $school_id];

if($description) $props['description'] = $description;
if($date_shipped) $props["date_shipped"] = new DateTime($date_shipped);
if($delivered) $props['delivered'] = $delivered;
if($archived) $props['archived'] = $archived;

if(!$shipment_id){ // if a shipment_id was not passed in
    $result = Shipment::create($props); // create a shipment row in the database
    
    if($result instanceof Shipment){ // make sure it worked....
        $status['success'] = true;  $status['error'] = "";
    } else { // else return the string that was returned
        $status['success'] = false; $status['error'] = $result;
    }
} else {
    $shipment = Shipment::load($shipment_id);
    $status['success'] = $shipment->update($props);
    $status['post'] = $_POST;
    $status['props'] = $props;
    $status['error'] = "Could not update shipment at this time. Please try again later";
}
// return the JSON response....
echo json_encode($status);
