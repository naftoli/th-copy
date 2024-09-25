<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once '../header.php';

require_once '../class.adminSchools.php';
require_once '../class.schoolsUsers.php';
require_once '../class.globalSettings.php';

$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth']);
$schools = $as->getSchools();

$year = isset($_GET['year']) ? $_GET['year'] : GlobalSettings::getRegistrationYear();
$schoolsUsers = array();
$totals = array();
//ksort($schools);
if ($admin_user['auth'] == 'super') {
    $s = new SchoolsUsers(0);
    $s->setYear($year);
    $schoolsUsers[0] = $s->getUsers(true, true);
} else {
    foreach ($schools as $id => $school) {
        $s = new SchoolsUsers($id);
        $s->setYear($year);
        $schoolsUsers[$id] = $s->getUsers(true, true);
    }
}

echo json_encode([
    'success'   => true,
    'data'      => $schoolsUsers,
    'schools'   => $schools
]);