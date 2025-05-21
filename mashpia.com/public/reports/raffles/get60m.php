<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once '../../header.php';

if ($admin_user['auth'] != 'super') {
    die('Unauthorized');
}

// load the required classes
require_once $_SERVER["DOCUMENT_ROOT"].'/class.adminSchools.php';
require_once $_SERVER["DOCUMENT_ROOT"].'/class.schoolsUsers.php';
require_once $_SERVER["DOCUMENT_ROOT"].'/class.globalSettings.php';
require_once $_SERVER["DOCUMENT_ROOT"].'/raffles/shared/classes/Raffle.php';
use raffles\weekly\Raffle as Raffle;

$year = GlobalSettings::getCurrentYear();

$schools = [];
$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth']);
$schools = $as->getSchools();

$schoolUsers = [];
foreach ($schools as $school_id => $school) {
    $su = new SchoolsUsers($school_id);
    $schoolUsers[$school_id] = $su->getUsers(false, false);
}

$userInfo = [];
foreach ($schoolUsers as $school_id => $users) {
    foreach ($users as $user_id => $user) {
        $userInfo[$user_id] = $user;
    }
}

if (!isset($_GET['raffle_id'])) {
    $raffle_id = 444;
} else {
    $raffle_id = $_GET['raffle_id'];
}

$eligibleUsers = [];
$raffle = Raffle::load($raffle_id);
$required = $raffle->required_days_of_tasks();
$overriden = $raffle->get_raffle_eligable_user_ids();
$eligible = $raffle->get_eligable_user_ids();
foreach ($eligible as $user_id => $user) {
    $eligibleUsers[$raffle->raffle_id][$user_id] = [
        'raffle_name' => $raffle->name,
        'school_name' => $schools[$user['school_id']],
        'class_grade' => $userInfo[$user_id]['class_grade'],
        'class_sub' => $userInfo[$user_id]['class_sub'],
        'user_serial' => $userInfo[$user_id]['user_serial'],
        'first' => $userInfo[$user_id]['first'],
        'last' => $userInfo[$user_id]['last'],
    ];
}

echo json_encode($eligibleUsers);