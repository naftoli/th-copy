<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);
ini_set('max_execution_time', 780);

$admin_auth = ['school'];
require_once '../../header.php';

if ($admin_user['auth'] != 'super') {
    die('Unauthorized');
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.schoolsUsers.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/raffles/shared/classes/Raffle.php';
use raffles\weekly\Raffle as Raffle;

// get all users
$schools = [];
$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth']);
$schools = $as->getSchools();

$schoolUsers = [];
foreach ($schools as $school_id => $school) {
    $su = new SchoolsUsers($school_id);
    $schoolUsers[$school_id] = $su->getUsers(false, false);
}

$stmt = $MASHPIA_DB->prepare("
    INSERT INTO raffle_eligibility SET raffle_id = ?, user_id = ?, eligible = 1
    ON DUPLICATE KEY UPDATE eligible = 1
");

$raffle_id = $_GET['raffle_id'] ?? 444;

foreach ($schoolUsers as $school_id => $users) {
    $raffle = Raffle::load($raffle_id);
    $marked_eligible = $raffle->get_raffle_eligable_user_ids();
    foreach ($users as $user) {
        if (isset($marked_eligible[$user['user_id']])) {
            continue;
        }
        // check user eligiblity
        $total = $raffle->checkMonthly($user['user_id']);
        if ($total >= 60) { 
            $res = $stmt->execute([$raffle_id, $user['user_id']]);
            if ($res === false) {
                $stmt->debugDumpParams();
                die('Failed to mark user as eligible');
            }
        }
    }
}
echo 'Done';