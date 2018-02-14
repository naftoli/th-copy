<?php $debug = false;
// enable debuging
if (isset($_GET['debug']) || (isset($_POST['debug']) && $_POST['debug'] == "true")) {
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
    $debug = true; // set debug to true
}

/***************** AUTHENTICATION **********************/
$admin_auth = array('school'); 
require_once($_SERVER["DOCUMENT_ROOT"].'/header.php');

function render_json_error($error_message, $details = false){
    echo json_encode([
        "success"   => false,
        "error"     => $error_message,
        "details"   => $details
    ]);
    die();
}

function clean_post_param($param_name){
    return mysql_real_escape_string($_POST[$param_name]);
}