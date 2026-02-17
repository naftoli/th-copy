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

$stmt = $MASHPIA_DB->prepare("
    SELECT 
        a.*, amount 
    FROM
        registration_charges rc 
    JOIN admins a USING (admin_id) 
    WHERE
        type LIKE '%RRS%' AND year = :year 
        AND refunded = 0 
        AND school_id IN (269, 61) 
    GROUP BY admin_id ORDER BY admin_id
");
$stmt->execute(['year' => $year]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>AK/MS Report</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid black;
            padding: 5px;
        }
    </style>
</head>
<body>
    <h1>AK/MS Report</h1>
    <table>
        <tr>
            <th>Admin ID</th>
            <th>Amount</th>
        </tr>
        <? foreach ($rows as $row) : ?>
            <tr>
                <td><?= $row['admin_id'] ?></td>
                <td><?= $row['amount'] ?></td>
            </tr>
        <? endforeach; ?>
    </table>
</body>
</html>