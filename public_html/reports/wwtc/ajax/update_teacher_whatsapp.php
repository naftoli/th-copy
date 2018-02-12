<?php
$debug = false; // default debugging is false
if ($_POST['debug']) {
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
    $debug = true; // set debug to true
}
/***************** AUTHENTICATION **********************/
$admin_auth = array('school'); 
require_once($_SERVER["DOCUMENT_ROOT"].'/header.php');

/***************** PARAMATERS *****************/
$class_id   = mysql_real_escape_string($_POST['class_id']);
$gender     = isset($_POST['gender']) && $_POST['gender'] ? mysql_real_escape_string($_POST['gender']) : false;
$whatsapp   = isset($_POST['whatsapp']) ? mysql_real_escape_string($_POST['whatsapp']) : false;

if(!$gender && ($whatsapp != "0" && !$whatsapp)){
    echo json_encode(["success" => false, "error" => "Inavlid Paramaters", "post" => [$whatsapp, $_POST]]); die();
}

$updates  = [];


if($gender) $updates[] = "class_gender='$gender'";
if($whatsapp !== false)  $updates[] = "whatsapp='$whatsapp'";

$update_sql = "UPDATE classes SET ".implode(", ", $updates) . " WHERE class_id = $class_id;";

echo json_encode(["success" => !!mysql_query($update_sql), "sql" => $update_sql]);