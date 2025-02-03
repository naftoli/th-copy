<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

if ($admin_user['auth'] != 'super') {
    echo "No Permission";
    exit;
}

for ($i = 0; $i < 3; $i++) {
    $first_name = $faker->firstName;
    $last_name = 'chidon_test_' . $i;
    $gender = $i % 2 == 0 ? 'M' : 'F';
    $lang = 'en';
    $lang_id = 1;
    $school_type_id = 2;
    $school_id = $i % 2 == 0 ? 61 : 269;
    $class_id = $classes[$school_id][$gender];
    $user_start = 2455448;
    $dob = '2018-01-01';
    echo $i . ": First Name: " . $first_name . ", Last Name: " . $last_name . ", Gender: " . $gender . ", School ID: " . 
        $school_id . ", Class ID: " . $class_id . ", User Start: " . $user_start . ", DOB: " . $dob . "<br />";
}