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

// get family pot
$family_pot = [];
$sql = "SELECT * FROM registration_charges 
        LEFT JOIN transactions ON registration_charges.trans_id = transactions.trans_id
        WHERE year = :year and type = 'RRFAM'";
$stmt = $MASHPIA_DB->prepare($sql);
$stmt->execute([
    ':year' => $year
]);
while ($row = $stmt->fetch()) {
    $admins[] = $row['admin_id'];
    if (isset($row['response']) && $row['response'] != null) {
        $response = json_decode($row['response'], true);
        $row['trans_id'] = $response['transactionResponse']['transId'];
    }
    $family_pot[$row['admin_id']][] = $row;
}

// get admin emails
$emails = [];
foreach ($admins as $admin_id) {
    $sql = "select admin_email from admins where admin_id = :admin";
    $stmt = $MASHPIA_DB->prepare($sql);
    $stmt->execute([
        ':admin' => $admin_id
    ]);
    $row = $stmt->fetch();
    $emails[$admin_id] = $row['admin_email'];
}

$data['info'] = $info;
$data['emails'] = $emails;
$data['family_pot'] = $family_pot;

echo json_encode($data);