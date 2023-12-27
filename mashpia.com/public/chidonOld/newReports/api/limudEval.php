<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';

$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth'], true, true);
$schools = $as->getSchools();
$year = intval(GlobalSettings::getChidonYear());

if (count($schools) == 1) {
    $school_id = key($schools);
    $sql = "select * from classes where class_era = 0 and class_grade in ('4', '5', '6', '7', '8') and school_id = " . $school_id;
    $result = mysql_query($sql);
    while ($row = mysql_fetch_assoc($result)) {
        $grades[$row['class_id']] = $row['class_grade'] . ($row['class_sub'] ? '-' . $row['class_sub'] : '');
    }
}

echo json_encode([
    'success'   => true,
    'schools'   => $schools,
    'years'     => [$year, $year - 1],
]);