<?php
//ini_set('display_errors', 1);
//ini_set('error_reporting', E_ALL);

require_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.points.php';

$input = json_decode(file_get_contents('php://input'), true);
$user = mysql_real_escape_string($input['user']);
$card = mysql_real_escape_string($input['card']);

// get school id from user
$res = mysql_query("select school_id, class_id from users where user_id = $user");
$row = mysql_fetch_assoc($res);
$school = $row['school_id'];
$class = $row['class_id'];

$msg = Points::scanMiles($school, $class, $user, $card);
echo $msg;
$scanned = json_decode($msg, true);
if ($scanned['success']) Points::updateScanned($card);
?>