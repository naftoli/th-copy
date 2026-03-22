<?php
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';

if ($admin_user['auth'] != 'super') {
    die('Access denied');
}

$year = GlobalSettings::getChidonYear();

$stmt = $MASHPIA_DB->prepare("
    SELECT 
        *, cup.he_name as heName 
    FROM
        chidon_user_prizes cup
            JOIN
        chidon_prizes cp USING (prize_id)
            JOIN
        users u USING (user_id)
            JOIN
        schools s USING (school_id)
            JOIN
        classes c ON c.class_id = u.class_id
    WHERE
        cup.he_name != '' AND cup.year = :year
            AND user_id NOT IN (SELECT 
                user_id
            FROM
                registration_charges
            WHERE
                year = :year
                    AND type IN ('RRYSD' , 'RRYDA', 'RRHVN'))
    ORDER BY school_name , class_grade , class_sub , last , first
");
$res = $stmt->execute(['year' => $year]);
if (!$res) {
    die('Failed to fetch data');
}
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
$total = count($rows);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Missing Personalized Payment</title>
    <style>
      table {
        font-family: Arial, sans-serif;
        font-size: 14px;
      }

      tr, th, td {
        border-bottom: #f0f0f0 1px solid;
        padding: 10px;
      }
    </style>
</head>
<body>
    <h1>Missing Personalized Payment</h1>
    <p>Total Missing: <?= $total ?></p>
    <table>
        <tr>
            <th></th>
            <th>User ID</th>
            <th>Serial Number</th>
            <th>School</th>
            <th>Grade</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Hebrew Name</th>
            <th>Prize Chosen</th>
        </tr>
        <?php foreach ($rows as $i => $row) : ?>
            <tr>
                <td><?= $i + 1 ?></td>
                <td><?= $row['user_id'] ?></td>
                <td><?= $row['user_serial'] ?></td>
                <td><?= $row['school_name'] ?></td>
                <td><?= $row['class_grade'] . (empty($row['class_sub']) ? '' : '-' . $row['class_sub']) ?></td>
                <td><?= $row['first'] ?></td>
                <td><?= $row['last'] ?></td>
                <td><?= $row['heName'] ?></td>
                <td><?= $row['prize_name'] ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
