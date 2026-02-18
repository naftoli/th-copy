<?php
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

if ($admin_user['auth'] != 'super') {
    echo 'You are not authorized to view this page.';
    exit;
}

$paid_for_shipping = [];
$stmt = $MASHPIA_DB->prepare("
    SELECT 
        a.admin_id, rc.amount, rc.date  
    FROM
        registration_charges rc
            JOIN
        admin_auths aa ON aa.id = rc.user_id
            JOIN
        admins a ON a.admin_id = aa.admin_id
    WHERE
        year = :year 
            AND rc.school_id IN (61 , 269)
            AND (type LIKE 'THAK%' OR type LIKE 'THMS%')
            AND rc.refunded = 0
    GROUP BY admin_id
    ORDER BY admin_id
");
$stmt->execute(['year' => $year]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $row) {
    $paid_for_shipping[$row['admin_id']] = [
        'amount' => $row['amount'],
        'date' => $row['date']
    ];
}

$stmt = $MASHPIA_DB->prepare("
    SELECT 
        a.*
    FROM
        users u
            JOIN
        admin_auths aa ON aa.id = u.user_id
            JOIN
        admins a ON a.admin_id = aa.admin_id
    WHERE
        u.school_id IN (61 , 269)
            AND user_registered IS NOT NULL
    GROUP BY admin_id
    ORDER BY admin_id
");
$stmt->execute();
$admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>AK/MS Report</title>
    <style>
        tr, th, td {
            font-size: 14px;
            padding: 10px;
            border-bottom: 1px solid #ccc;
            font-family: Arial;
        }
        th {
            background-color: #f0f0f0;
        }
    </style>
</head>
<body>
    <h1>AK/MS Report</h1>
    <table>
        <tr>
            <th>Admin ID</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Paid for Shipping</th>
            <th>Amount Paid</th>
            <th>Date Paid</th>
        </tr>
        <? foreach ($admins as $row) : ?>
            <tr>
                <td><?= $row['admin_id'] ?></td>
                <td><?= $row['first'] ?></td>
                <td><?= $row['last'] ?></td>
                <td><?= isset($paid_for_shipping[$row['admin_id']]) ? 'Yes' : 'No' ?></td>
                <td><?= isset($paid_for_shipping[$row['admin_id']]) ? $paid_for_shipping[$row['admin_id']]['amount'] : '' ?></td>
                <td><?= isset($paid_for_shipping[$row['admin_id']]) ? $paid_for_shipping[$row['admin_id']]['date'] : '' ?></td>
            </tr>
        <? endforeach; ?>
    </table>
</body>
</html>