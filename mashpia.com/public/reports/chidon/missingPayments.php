<?php
ini_set('display_errors');
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';

if ($admin_user['auth'] != 'super') {
    echo "No Permission.";
    exit;
}

require $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

require_once $_SERVER["DOCUMENT_ROOT"].'/class.adminSchools.php';
$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
$schools = $as->getSchools();

$info = [];
$sql = "select * from th_chidon_parent_purchases";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $info[$row['admin_id']][] = $row;
}

// take out parents that have a charge
$remove = [];
foreach ($info as $admin_id => $details) {
    foreach ($details as $row) {
        if ($row['authorize_id'] > 1) {
            if (!in_array($admin_id, $remove)) {
                $remove[] = $admin_id;
                continue 2;
            }
        }
    }
}

foreach ($remove as $admin_id) {
    unset($info[$admin_id]);
}