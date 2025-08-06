<?php
$admin_auth = ['school'];
require_once '../../header.php';

if ($admin_user['auth'] != 'super') {
    echo "Not authorized";
    exit;
}

require_once '../../class.adminSchools.php';
$adminSchools = new adminSchools($admin_user['admin_id'], $admin_user['auth'], true, true);
$schools = $adminSchools->getSchools();

require_once '../../class.globalSettings.php';
$year = GlobalSettings::getCurrentYear();

$years = [];
for ($i = $year; $i >= 5780; $i--) {
    $years[] = $i;
}

echo json_encode([
    'years' => $years,
    'schools' => $schools
]);