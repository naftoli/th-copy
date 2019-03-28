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
require_once(dirname(__FILE__)."/../classes/Shipment.php");
/***************** POST DATA **********************/
$status = ['success' => false, 'error' => "Under Development"];
$school_id = mysql_real_escape_string(isset($_POST['school_id']) ? $_POST['school_id'] : $_GET['school_id']); // get the school id from the get or post paramaters....
/***************** POST DATA VALIDATION **********************/
if(!$school_id){
    echo json_encode(['success' => false, 'error' => "Invalid Paramaters"]); die();
}

/***************** SQL **********************/
$shipments = shipping\Shipment::getSchoolShipments($school_id);

$status['success'] = is_array($shipments);
$status['error'] = "ERROR: Internal Data Error. Please contact support";
$status['shipments'] = $shipments;

echo json_encode($status);
