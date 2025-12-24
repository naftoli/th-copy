<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);
ini_set('max_execution_time', 300); // update max execution time to 5 min

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getCurrentYear();

// make sure it's hq
if ($admin_user['auth'] != 'super') {
    echo 'You are not authorized to view this page.';
    exit;
}

//******************** SCRIPT START HERE ************************//

$info = file_get_contents("php://input");
$info = json_decode($info, true);

$school_id = $info['school_id'];
$end_date = $info['end_date'];

require_once 'future/class.futureMedals.php';
$fm = new FutureMedals($year, $end_date, [$school_id]);
$future_medals = $fm->getFutureMedals();  // array of user_id => num_medals

echo json_encode($future_medals);