<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . "/header.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/class.globalSettings.php";
$year = GlobalSettings::getChidonYear();

if ($admin_user['auth'] != 'super') {
    echo "No Permission.";
    exit;
}

$info = [];
$sql = "select * from chidon_missing_items where year = " . $year;
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $info[] = $row;
}

echo "<pre>";
foreach ($info as $row) {
    $items = json_decode($row['items'], false, 512, JSON_HEX_APOS | JSON_HEX_QUOT);
    print_r($items);
}
echo "</pre>";