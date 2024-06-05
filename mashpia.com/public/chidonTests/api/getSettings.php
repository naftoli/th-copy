<?php
//ini_set('display_errors', 1);
//ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require $_SERVER['DOCUMENT_ROOT'] . '/chidonTests/class.chidonTests.php';
$ct = new ChidonTests();

$input = json_decode(file_get_contents('php://input'), true);
$year = $input['year'];
$school_id = $input['school_id'];
$class_id = $input['class_id'];
$user_id = $input['user_id'];

$settings = $ct->getSettings($school_id, $class_id, $user_id, $year);
// if settings are empty and year is current, get from previous year
if (empty($settings) && $year == GlobalSettings::getChidonRegYear()) {
    $settings = $ct->getSettings($school_id, $class_id, $user_id, $year - 1);
}
echo json_encode($settings);