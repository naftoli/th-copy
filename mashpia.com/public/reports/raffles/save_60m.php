<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);
ini_set('max_execution_time', 600);

$admin_auth = ['school'];
require_once '../../header.php';

if ($admin_user['auth'] != 'super') {
    die('Unauthorized');
}

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

$MASHPIA_DB->beginTransaction();
foreach ($schoolUsers as $school_id => $users) {
    foreach ([444, 445, 446] as $raffle_id) {
        $raffle = Raffle::load($raffle_id);
        $marked_eligible = $raffle->get_raffle_eligable_user_ids();
        foreach ($users as $user_id => $user) {
            if (! isset($marked_eligible[$user_id])) {
                // check user eligiblity
                $total = $raffle->checkMonthly($user_id);
                if ($total >= 60) { 
                    $res = $stmt->execute([$raffle_id, $user_id]);
                    if ($res === false) {
                        $MASHPIA_DB->rollBack();
                        $stmt->debugDumpParams();
                        die('Failed to mark user as eligible');
                    }
                }
            }
        }
    }
}
$MASHPIA_DB->commit();
echo 'Done';