<?php
require $_SERVER['DOCUMENT_ROOT'] . '/db.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.points.php';

$school = $_REQUEST['school_id'];
$user = $_REQUEST['user_id'];
$card = $_REQUEST['card'];

$msg = Points::scanMiles($school, $user, $card);
echo json_encode($msg);
?> 