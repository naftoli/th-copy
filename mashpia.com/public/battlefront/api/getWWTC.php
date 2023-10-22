<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER["DOCUMENT_ROOT"] . '/header.php';

// make sure only super admins can access
if ($admin_user['auth'] != 'super') {
    echo "No permission.";
    exit;
}

// imports
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.shabbosMevorchim.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';

require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getRegistrationYear();

$sm = calculateSM($year);
$months = array(
    0 => 'Tishrei',
    1 => 'Cheshvon',
    2 => 'Kislev',
    3 => 'Teves',
    4 => 'Shvat',
    5 => 'Adar I',
    6 => 'Adar II',
    7 => 'Nissan',
    8 => 'Iyar',
    9 => 'Sivan',
    10 => 'Tamuz',
    11 => 'Av',
    12 => 'Elul'
);

// if plain yr change adar 1 to adar and remove adar 2
if ($sm[6] == $sm[7]) {
    unset($sm[6]);
    $months[5] = 'Adar';
}

// get most recent shabbos mevorchim
$now = unixtojd();
foreach ($sm as $idx => $date) {
    if ($date > $now) break;
}
if ($idx != 0) $date = $sm[--$idx];

$sm = new ShabbosMevorchim();
$sm->setReportDates($date);
$sm->setArmyResults();

$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
$ids = $as->getSchools();

foreach ($ids as $id => $name) {
    // generate the report for just this school
    $sm->setSchool( $id );
    $sm->setSchoolResults( $id );
    $sm->setClassResults();
}
echo json_encode($sm->perfectPlatoons);