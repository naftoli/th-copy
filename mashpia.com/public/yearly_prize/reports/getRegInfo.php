<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = array('school');
require_once $_SERVER["DOCUMENT_ROOT"].'/header.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/class.globalSettings.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.reg.php';
$year = GlobalSettings::getRegistrationYear();

$school_id = $_POST['school_id'];
$start = $_POST['start'];
$end = $_POST['end'];

if ($school_id > 0) {
    $reg = new Reg($school_id);
    $students[$school_id] = $reg->getChildren();
} else {
    require_once $_SERVER['DOCUMENT_ROOT'].'/class.adminSchools.php';
    $as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
    $schools = $as->getSchools();
    $students = [];
    foreach ($schools as $school_id => $school_name) {
        $reg = new Reg($school_id);
        $students[$school_id] = $reg->getChildren();
    }
}

// order the students by grade, name
foreach ($students as $school_id => $details) {
    usort($students[$school_id], function($a, $b) {
        if ($a['class_id'] == $b['class_id']) {
            if ($a['last'] == $b['last']) {
                return strcmp(strtolower($a['first']), strtolower($b['first']));
            }
            return strcmp(strtolower($a['last']), strtolower($b['last']));
        }
        return $a['class_id'] - $b['class_id'];
    });
}

echo json_encode($students);