<?php
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

if ($admin_user['auth'] != 'super') {
    echo "No Permission.";
    exit;
}

$info = [];
$sql = "select * from family_prepaid_balances where year = :year";
$stmt = $MASHPIA_DB->prepare($sql);
$res = $stmt->execute([
    ':year' => $year
]);
if ($res) {
    $info = $stmt->fetchAll();
}

// get admin emails
$emails = [];
$sql = "select admin_email from admins where admin_id = :admin";
$stmt = $MASHPIA_DB->prepare($sql);
foreach ($admins as $admin_id) {
    $stmt->execute([
        ':admin' => $admin_id
    ]);
    $tmp = $stmt->fetch();
    $emails[$admin_id] = $tmp['admin_email'];
}

// get family pot
$family_pot = [];
$sql = "SELECT * FROM registration_charges WHERE year = :year and type = 'RRFAM'";
$stmt = $MASHPIA_DB->prepare($sql);
$stmt->execute([
    ':year' => $year
]);
while ($row = $stmt->fetch()) {
    $family_pot[$row['admin_id']][] = $row;
}

$data['info'] = $info;
$data['emails'] = $emails;
$data['family_pot'] = $family_pot;

echo json_encode($data);