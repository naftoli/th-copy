<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$school_id = $_GET['school'] ?? 0;
if ($school_id == 0) {
    $response = [
        'success' => false,
        'data' => 'No school ID provided'
    ];
    echo json_encode($response);
    exit;
}

require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../class.shabbosMevorchim.php';
require_once __DIR__ . '/../../class.globalSettings.php';
$year = GlobalSettings::getCurrentYear();
$sm = new ShabbosMevorchim($year);

// find latest shabbos mevorchim
$sm_dates = calculateSM($year);
$today = unixtojd();
foreach ($sm_dates as $sm_date) {
    if ($sm_date <= $today) {
        $latest_sm = $sm_date;
    }
}
$sm->setStudentResults( $school_id, $latest_sm, true );
$quotas = $sm->getStudentResults();
$done = $sm->getStudentDoneResults();
$users = $sm->getUsers();
// get the class names
// $classes = $sm->getClasses();

$user_names = [];
foreach ($quotas[$latest_sm] as $class => $info) {
    foreach ($info as $user_id => $details) {
        foreach ($details as $key => $quota) {
            if ($done[$latest_sm][$class][$user_id][$key] >= $quota) {
                $user_names[] = [
                    'name' => $users[$user_id],
                    'chapter' => $done[$latest_sm][$class][$user_id][$key],
                    'quota' => $quota
                ];
            }
        }
    }
}

$response = [
    'success' => true,
    'data' => $user_names
];

echo json_encode($response);