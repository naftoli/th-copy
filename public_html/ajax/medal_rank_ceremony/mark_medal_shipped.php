<?php
/*  Please note that this script relies on code in the reports directory.
 *  Apecifically /reports/shipping/functions/get_medals.php to update the database.
 *  It is not meant to be anything but another endpoint with which to interface with that code.
 *  If a change needs to be made that is not regarding input sanitization please do it there
 */

/***************** DEBUGGING **********************/
if ($_GET['debug']) {
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
    $debug = true; // set debug to true
}
/***************** AUTHENTICATION **********************/
$admin_auth = array('school'); 
require_once($_SERVER["DOCUMENT_ROOT"].'/header.php');

require_once($_SERVER["DOCUMENT_ROOT"]."/reports/shipping/functions/get_medals.php");

// sanatize the inputs
$shipped = $_POST['shipped'] == "true" ? true : false;
$params = explode(":", $_POST['params']);

echo json_encode(["success" => mark_medal($shipped, $params[0], $params[1], $params[2], $params[3])]);