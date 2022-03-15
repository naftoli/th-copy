<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);
ini_set('max_execution_time', 300);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';

if ($admin_user['auth'] != 'super') {
    echo "No Permission to be here.";
    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth']);
$schools = $as->getSchools();

$gender = $_REQUEST['type'];

//$girlSchools = [269,54,162,45,30,2,7,112,81,613,192,50,37,265,42,61,40];

require 'functions.php';

$prizes = getUserPrizes();
foreach ($schools as $school_id => $school) {
    $children = getChildren($school_id, $gender);
    $sheet = createSpreadSheet($children);
//    createFile("$school_id.txt", $sheet);
}

echo json_encode([
    'success' => true
]);