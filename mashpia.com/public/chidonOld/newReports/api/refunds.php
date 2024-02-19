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
$sql = "select * from family_prepaid_balances where year = :year and used > 0";
$stmt = $MASHPIA_DB->prepare($sql);
$res = $stmt->execute([
    ':year' => $year
]);
if ($res) {
    $info = $stmt->fetchAll();
}

// get personal balance from th_chidon database
$sql = "select user_id, prepaid_credit, prepaid_credit_old 
        from th_chidon 
        where year = :year 
        and user_id in (
            select id from admin_auths where admin_id = :admin and auth = 'user')";
$stmt = $MASHPIA_DB->prepare($sql);

$admins = [];
$prepaid = [];
foreach ($info as $row) {
    $admins[] = $row['admin_id'];
    $stmt->execute([
        ':year' => $year,
        ':admin' => $row['admin_id']
    ]);
    $total1 = 0;
    $total2 = 0;
    $tmp = $stmt->fetchAll();
    foreach ($tmp as $t) {
        $total1 += floatval($t['prepaid_credit_old']);
        $total2 += floatval($t['prepaid_credit']);
    }
    $prepaid[$row['admin_id']] = [
        'total1' => $total1,
        'total2' => $total2
    ];
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

$data['info'] = $info;
$data['prepaid'] = $prepaid;
$data['emails'] = $emails;

echo json_encode($data);