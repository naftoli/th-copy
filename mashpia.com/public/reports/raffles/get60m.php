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
    $schoolUsers[$school_id] = $su->getUsers();
}

// create array with user_id as key and user info as value
$userInfo = [];
foreach ($schoolUsers as $school_id => $users) {
    foreach ($users as $user) {
        $user['school_name'] = $schools[$school_id];
        $userInfo[$user['user_id']] = $user;
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
$daysLeft = $raffle->end_date - unixtojd();
$overriden = $raffle->get_raffle_eligable_user_ids();
$eligibleUsers = $raffle->get_eligable_user_ids(false, true, false, false, 0, true);
foreach ($eligibleUsers as $user_id => $user) {
    $userInfo[$user_id]['raffle'] = $raffle->name;
    $eligibleUsers[$user_id] = $userInfo[$user_id];
}

echo json_encode($eligibleUsers);