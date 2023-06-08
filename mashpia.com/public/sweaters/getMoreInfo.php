<?php

ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';

if ($admin_user['auth'] != 'super') {
    echo "No access";
    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth']);
$schools = $as->getSchools();

// get all ranks
$ranks = [];
$sql = "SELECT * FROM ranks";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $ranks[$row['rank_ord']] = $row['rank_name'];
}

echo json_encode([
    'success'   => true,
    'schools'   => $schools,
    'ranks'     => $ranks
]);