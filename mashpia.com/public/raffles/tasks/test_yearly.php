<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER["DOCUMENT_ROOT"].'/header.php';

require_once $_SERVER["DOCUMENT_ROOT"].'/class.adminSchools.php';
require_once $_SERVER["DOCUMENT_ROOT"].'/class.schoolsUsers.php';

$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
$schools = $as->getSchools();

require_once $_SERVER['DOCUMENT_ROOT'] . '/raffles/yearly/classes/YearlyRaffle.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/raffles/shared/classes/Raffle.php';

use raffles\weekly\Raffle as Raffle; // use the raffle from its namespace
use raffles\yearly\YearlyRaffle as YearlyRaffle;

$schoolsUsers = [];
// for each school get its users
foreach ( $schools as $id => $school ) {
    $s = new SchoolsUsers( $id );
    $schoolsUsers[$id] = $s->getUsers(false, false);
}

$raffle = Raffle::load(347);
$yearly = new YearlyRaffle();

foreach ($schoolsUsers as $school_id => $users) {
    $eligibility = $yearly->getAndCacheEligibility($school_id);
    echo "<pre>"; print_r($eligibility); echo "</pre>";
//        $total = $yearly->set_user_eligibility( $user['user_id'] )[ $user['user_id'] ];
//         echo "User ID: " . $user['user_id'] . " Total: " . $total . "<br>";

}

