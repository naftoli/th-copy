<?php
/***************** DEBUGGING SETTINGS **********************/
if ($_GET['debug']) {
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
    $debug = true;
}

/***************** AUTHENTICATION **********************/
$admin_auth = array('school'); 
require_once($_SERVER["DOCUMENT_ROOT"].'/header.php');
// make sure that params was passed in
if (!isset($_POST["params"])){
    echo json_encode(["error" => "400: invalid request", "request" => $_POST]);
    die();
}

$table_name = 'user_yearly_gift'; // this allows us to change the table name easily :-)

// handle paramaters user_id:start_date:end_date:mark
$params = explode(":", $_POST["params"]); // separate the post requests condenced params
// use ms() as defined in db.php to avoid SQL injection
$user_id = ms($params[0]);
$start_date = ms($params[1]);
$end_date = ms($params[2]);
$marked = ms($params[3]);

// check if it already exists in the database
$sql = "SELECT * FROM $table_name WHERE user_id = $user_id AND start_date = $start_date AND end_date = $end_date";
$query = mq($sql);
//
if (mysql_num_rows($query) > 0){ // if we do, just update the row
    $sql = "UPDATE $table_name SET marked=$marked WHERE user_id=$user_id AND start_date = $start_date AND end_date = $end_date";
} else { // if we do not have the data, then insert the new row
    $sql = "INSERT INTO $table_name (user_id, start_date, end_date, marked) VALUES ($user_id, $start_date, $end_date, $marked)";
}

$result = mysql_query($sql);
if (!$result) { // check if the query failed
    echo json_encode([success => false, error => "Could not save mark. Please contact support."]); die(); // the result was returned. kill the script now
} else {
    echo json_encode([success => true]); die(); // all is well, kill the script now
}