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

$admins = [];
$info = [];
$sql = "select * from family_prepaid_balances where year = :year";
$stmt = $MASHPIA_DB->prepare($sql);
$res = $stmt->execute([
    ':year' => $year
]);
if ($res) {
    while ($row = $stmt->fetch()) {
        $admins[] = $row['admin_id'];
        $info[$row['admin_id']] = $row;
    }
}

$emails = [];
$family_pot = [];

$sqlCharges = "SELECT * FROM registration_charges rc 
            LEFT JOIN transactions t ON rc.trans_id = t.trans_id
            WHERE rc.year = :year and rc.type = 'RRFAM' 
            AND rc.admin_id = :admin";
$stmtCharges = $MASHPIA_DB->prepare($sqlCharges);

$sqlEmails = "select admin_email from admins where admin_id = :admin";
$stmtEmails = $MASHPIA_DB->prepare($sqlEmails);

foreach ($admins as $admin_id) {
    $stmtCharges->execute([
        ':year' => $year,
        ':admin' => $admin_id
    ]);
    while ($row = $stmtCharges->fetch()) {
        $family_pot[$admin_id][] = $row;
    }
    $stmtEmails->execute([
        ':admin' => $admin_id
    ]);
    $row = $stmtEmails->fetch();
    $emails[$admin_id] = $row['admin_email'];
}

$data['info'] = $info;
$data['emails'] = $emails;
$data['family_pot'] = $family_pot;

echo json_encode($data);