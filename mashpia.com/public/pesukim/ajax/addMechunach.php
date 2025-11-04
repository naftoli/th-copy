<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/pesukim/class.pesukim.php';

$p = new Pesukim($_POST['user_id']);
$res = $p->addMechunach($_POST);

echo json_encode(['success' => $res]);