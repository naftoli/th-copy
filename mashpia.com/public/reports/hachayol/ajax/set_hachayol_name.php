<?php $debug = false;
// enable debuging
if ($_GET['debug']) {
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
    $debug = true; // set debug to true
    echo "<pre>";
}

/***************** AUTHENTICATION **********************/
$admin_auth = array('school'); 
require_once($_SERVER["DOCUMENT_ROOT"].'/header.php');

if ($admin_user['auth'] != 'super') {
    echo json_encode([
        "success"   => false,
        "error"     => "Invalid Permissions"
    ]);
    die();
}

$school_id      = mysql_real_escape_string($_POST['school_id']);
$hachayol_name  = mysql_real_escape_string($_POST['hachayol_name']);

if(!$school_id || !$hachayol_name){
    echo json_encode([
        "success"   => false,
        "error"     => "Invalid Paramaters"
    ]);
    die();
}

$sql = "UPDATE schools SET hachayol_name = '$hachayol_name' WHERE school_id = '$school_id'";

echo json_encode([
    "success"   => !!mysql_query($sql),
    "error"     => "Server Error HA-NAME-SET: Please contact Support."
]);
die();