<?php
require $_SERVER['DOCUMENT_ROOT'] . '/db.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.points.php';

$input = json_decode(file_get_contents('php://input'), true);
$user = mysql_real_escape_string($input['user']);
$admin = mysql_real_escape_string($input['admin']);
$card = mysql_real_escape_string($input['card']);

// get school id from user
$res = mysql_query("select school_id from users where user_id = $user");
$row = mysql_fetch_assoc($res);
$school = $row['school_id'];

$msg = Points::scanMiles($school, $user, $card);
echo json_encode($msg);
?>