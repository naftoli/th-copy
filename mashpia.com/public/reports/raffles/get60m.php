<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once '../../header.php';

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
    foreach ($users as $user) {
        $userInfo[$user['user_id']] = $user;
    }
}

$raffle_ids = [444, 445, 446];

$userRaffles = [];
foreach ($raffle_ids as $raffle_id) {
    $raffle = Raffle::load($raffle_id);
    $eligible = $raffle->get_raffle_eligable_user_ids();
    foreach ($eligible as $user_id => $user) {
        if (isset($userInfo[$user_id])) {
            $userRaffles[$user_id][] = $raffle_id;
        }
    }
}

// create info array
$info = [];
foreach ($schoolUsers as $school_id => $users) {
    foreach ($users as $user) {
        $user_id = $user['user_id'];
        if (isset($userRaffles[$user_id])) {
            $user = $userInfo[$user_id];
            $school_id = $user['school_id'];
            $school_name = $schools[$school_id];
            $grade = $user['class_grade'];
            $sub = $user['class_sub'];
            $serial = $user['user_serial'];
            $first = $user['first'];
            $last = $user['last'];
            $info[$school_name][$grade][$sub][$last][$first][$user_id] = [
                'raffles' => $userRaffles[$user_id],
                'school_id' => $school_id,
                'school_name' => $school_name,
                'grade' => $grade,
                'sub' => $sub,
                'serial' => $serial,
                'first' => $first,
                'last' => $last,
            ];
        }
    }
}

// return the info and schools
$data['info'] = $info;
$data['schools'] = $schools;
echo json_encode($data);