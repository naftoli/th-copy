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
        $userInfo[$user['user_id']] = $user;
    }
}

$eligableUsers = [];
// get raffles that are monthly
$raffles = Raffle::loadAll("where year = $year and type = 'monthly' and name like '%60M%'");
foreach ($raffles as $raffle) {
    $required = $raffle->required_days_of_tasks();
    $daysLeft = $raffle->end_date - unixtojd();
    $overriden = $raffle->get_raffle_eligable_user_ids();
    $eligibleUsers[$raffle->raffle_id] = $raffle->get_eligable_user_ids(false, true, false, false, 0, true);
    foreach ($eligibleUsers[$raffle->raffle_id] as $user_id => $user) {
        $eligibleUsers[$raffle->raffle_id][$user_id] = $userInfo[$user_id];
    }
}

echo json_encode($eligableUsers);