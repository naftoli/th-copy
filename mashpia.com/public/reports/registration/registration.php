<?php
$admin_auth = array();
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';

if ($admin_user['auth'] != 'super') {
    echo "No Permission.";
    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getRegistrationYear();

require_once $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth']);
$schools = $as->getSchools();

$stmt = $MASHPIA_DB->prepare("
    SELECT * FROM registration_charges 
    WHERE year = :year 
    AND type = 'chayolei'
");
$stmt->execute([':year' => $year]);
$rows = $stmt->fetchAll();

$totals = [];
foreach ($rows as $row) {
    if (isset($totals[$row['school_id']])) $totals[$row['school_id']] += intval($row['amount']);
    else $totals[$row['school_id']] = intval($row['amount']);
}

// find out total for any coupons that were used
$stmt = $MASHPIA_DB->prepare("
    SELECT 
        school_id, IFNULL(SUM(value), 0) AS coupon
    FROM
        registration_charges rc
            LEFT JOIN
        coupon_codes cc ON rc.coupon = cc.code
    WHERE
        rc.year = :year AND rc.type = 'chayolei'
    GROUP BY school_id
");
$stmt->execute([':year' => $year]);
$temp = $stmt->fetchAll();
foreach ($temp as $row) {
    $coupons[$row['school_id']] = $row['coupon'];
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?=$year?> Soldier Registration</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        table { width: 100%; }
        th, td { border: 1px solid #888; padding: 4px 8px; }
        body, tr, th, td {
            font-family: Arial, "Helvetica Neue", Helvetica, sans-serif;
        }
        tr, th, td {
            font-size: 14px;
        }
    </style>
</head>
<body>
<h1><?=$year?> Soldier Registration</h1>
<h2>Totals</h2>
<table>
   <tr>
       <th>Base Name</th>
       <th>Soldiers Registered</th>
       <th>Fee per Soldier</th>
       <th>Total Fee</th>
       <th>Total Paid</th>
       <th>Total Coupons</th>
       <th>Balance</th>
   </tr>
    <?php
    foreach ($totals as $school_id => $total) {
        $stmt = $MASHPIA_DB->prepare("
            SELECT count(u.user_id) as total_users, school_type, child_fee  
            FROM schools s 
            JOIN users u using (school_id) 
            WHERE u.school_id = :id 
            AND u.user_registered > 0 
            GROUP BY u.school_id
        ");
        $stmt->execute([':id' => $school_id]);
        $row = $stmt->fetch();
        // figure out soldier fee
        $fee = GlobalSettings::calculateChildFee($row['school_type'], $row['child_fee'], true);
        echo "<tr><td>" . $schools[$school_id] . "</td><td>" . $row['total_users'] . "</td><td>" . $fee .
            "</td><td>" . ($fee * intval($row['total_users'])) . "</td><td>" . $total . "</td><td>" . $coupons[$school_id] .
            "</td><td>" . (($fee * intval($row['total_users'])) - $total - $coupons[$school_id]) . "</td></tr>";
    }
    ?>
</table>
<h2>Details</h2>
<table>
    <tr>
        <th>User ID</th>
        <th>Base Number</th>
        <th>Base Name</th>
        <th>Grade</th>
        <th>Soldier</th>
        <th>Registered</th>
        <th>Fee</th>
        <th>Paid</th>
        <th>Coupon Amount</th>
        <th>Balance</th>
    </tr>
</table>
</body>
</html>