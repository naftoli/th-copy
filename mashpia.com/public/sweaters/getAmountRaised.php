<?php
//ini_set('display_errors', 1);
//ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';

if ($admin_user['auth'] != 'super') {
    echo json_encode([
        'success' => false,
        'message' => 'No access'
    ]);
    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

$raised = [];
$sql = "select * from family_raised where year = $year order by admin_id";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $raised[$row['admin_id']] = $row['amount'];
}

echo json_encode([
    'success'   => true,
    'raised'    => $raised
]);