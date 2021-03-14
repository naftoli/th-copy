<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

require __DIR__ . '/../../../db.php';
require __DIR__ . '/../../../class.globalSettings.php';
require __DIR__ . '/../encrypt.php';

$year = GlobalSettings::getChidonYear();
$admin = mysql_real_escape_string( $_POST['admin'] );
$admin_id = encrypt_decrypt('decrypt', $admin);

$parent = [];
$sql = "select * from th_chidon_parent_purchases where admin_id = " . $admin_id;
$result = mysql_query($sql);
if (mysql_num_rows($result)) {
    $parent = mysql_fetch_assoc($result);
}

$children = [];
$sql = "select th_chidon_id, user_id, paid, yarmulka from th_chidon where parent_id = " . $admin_id . " and year = " . $year;
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $children[$row['user_id']] = $row;
}

echo json_encode([
    'parent'    => $parent,
    'children'  => $children
]);