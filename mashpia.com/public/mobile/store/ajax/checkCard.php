<?php
require $_SERVER['DOCUMENT_ROOT'] . '/db.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.points.php';

$input = json_decode(file_get_contents('php://input'), true);
$user = $input['user'];
$admin = $input['admin'];
$card = $input['card'];

$p = new Points($user);
$msg = $p->scanMiles($card);
echo json_encode($msg);
?>