<?php
//ini_set('display_errors', 1);
//ini_set('error_reporting', E_ALL);

require $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require $_SERVER['DOCUMENT_ROOT'] . '/mobile/chidon/ajax/encrypt.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonRegYear();

$info = json_decode(file_get_contents('php://input'), true);

// find all children for this admin
$stmt = $MASHPIA_DB->prepare("select id from admin_auths where admin_id = :admin");
$stmt->execute([':admin' => $info['admin']]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
$children = [];
foreach ($rows as $row) {
    $children[] = $row['id'];
}

// check if there's any shipping fee already charged for any children of this admin
$stmt = $MASHPIA_DB->prepare("
    select * from registration_charges where type in ('RRSUSA', 'RRSCAN', 'RRSINT') and year = :year 
    and user_id in (" . implode(',', $children) . ")
    ");
$stmt->execute([':year' => $year]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if ($row && !empty($row)) echo 1;
else echo 0;