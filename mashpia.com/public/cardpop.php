<?php
require $_SERVER['DOCUMENT_ROOT'] . '/db.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.points.php';

$school = mysql_real_escape_string($_REQUEST['school_id']);
$user = mysql_real_escape_string($_REQUEST['user_id']);
$card = mysql_real_escape_string($_REQUEST['card']);

$msg = Points::scanMiles($school, $user, $card);
echo json_encode($msg);
?> 