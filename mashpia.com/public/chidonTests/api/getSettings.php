<?php
//ini_set('display_errors', 1);
//ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require $_SERVER['DOCUMENT_ROOT'] . '/chidonTests/class.chidonTests.php';
$ct = new ChidonTests();

$input = json_decode(file_get_contents('php://input'), true);
$school_id = $input['school_id'];
$class_id = $input['class_id'];
$user_id = $input['user_id'];

$settings = $ct->getSettings($school_id, $class_id, $user_id);
echo json_encode($settings);