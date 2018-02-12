<?php $debug = false;
// enable debuging
if (isset($_POST['debug']) && $_POST['debug'] == "true") {
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
    $debug = true; // set debug to true
}

function return_json_error($error_msg, $details = false) {
    echo json_encode([
        "success"   => false,
        "error"     => $error_msg,
        "details"   => $details
    ]);
    die();
}

/***************** AUTHENTICATION **********************/
$admin_auth = array('school'); 
require_once($_SERVER["DOCUMENT_ROOT"].'/header.php');

// get the params
$level      = mysql_real_escape_string($_POST['level']);
$type       = mysql_real_escape_string($_POST['type']);
$checked    = isset($_POST['checked']) ? ($_POST['checked'] == "true" ? "1" : "0") : false;
$id         = false;

// set the ID based on the type
if($level == "school"){
    $id  = mysql_real_escape_string($_POST['school_id']);
} elseif($level == "class") {
    $id  = mysql_real_escape_string($_POST['class_id']);
} elseif($level == "user") {
    $id  = mysql_real_escape_string($_POST['user_id']);
}

// server side validation
if(!$level || !$type || $checked === false || !$id){
    return_json_error("Server Error: C-TASKS-001: Invalid Request");
}

if($level == "school"){
    $sql = "UPDATE schools s LEFT JOIN classes c ON s.school_id = c.school_id "
        ."LEFT JOIN users u ON s.school_id = u.school_id "
        ."SET s.$type='$checked', c.$type='$checked', u.$type='$checked' WHERE s.school_id = '$id'";
} elseif($level == "class"){
    $sql = "UPDATE classes c LEFT JOIN users u USING (class_id) SET c.$type='$checked', u.$type='$checked' WHERE ".$level."_id = '$id'";
} elseif($level == "user") {
    $sql = "UPDATE users SET $type='$checked' WHERE user_id = '$id'";
}


if($debug){
    echo json_encode(['post' => $_POST, 'sql' => $sql]);
    die();
}

echo json_encode([
    "success"   => !!mysql_query($sql),
    "error"     => "Server Error: C-TASKS-002: Please contact Support."
]);
die();