<?php
/***************** DEBUGGING **********************/
//if ($_GET['debug']) {
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
    $debug = true; // set debug to true
//}
/***************** AUTHENTICATION **********************/
$admin_auth = array('school'); 
require_once($_SERVER["DOCUMENT_ROOT"].'/header.php');
require_once(dirname(__FILE__)."/../classes/Shipment.php");

/***************** POST DATA **********************/
$status = ['success' => false, 'error' => "Under Development"];
$shipment_id = mysql_real_escape_string(isset($_POST['shipment_id']) ? $_POST['shipment_id'] : $_GET['shipment_id']); // get the school id from the get or post paramaters....
$ajax = mysql_real_escape_string(isset($_POST['ajax']) ? $_POST['ajax'] : $_GET['ajax']); // get the AJAX info....

/***************** POST DATA VALIDATION **********************/
if(!$shipment_id){
    echo json_encode(['success' => false, 'error' => "Invalid Paramaters"]); die();
}

/***************** SQL **********************/
$shipment = shipping\Shipment::load($shipment_id);
$status['success'] = $shipment->add_or_move_item($ajax);
$status['error'] = "ERROR: Internal Data Error. Please contact support";

echo json_encode($status);