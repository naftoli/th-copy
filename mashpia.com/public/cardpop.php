<?php
require $_SERVER['DOCUMENT_ROOT'] . '/db.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.points.php';

$user = $_REQUEST['user_id'];
$card = $_REQUEST['card'];

$p = new Points($user);
$msg = $p->scanMiles($card);
echo json_encode($msg);
?> 