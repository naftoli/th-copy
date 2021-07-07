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
    if (isset($totals[$row['school_id']])) $totals[$row['school_id']] += floatval($row['amount']);
    else $totals[$row['school_id']] = floatval($row['amount']);
}

// find out total for any discounts that were used
$stmt = $MASHPIA_DB->prepare("
    SELECT 
        school_id, IFNULL(SUM(discount), 0) AS discount
    FROM
        registration_charges rc
    WHERE
        rc.year = :year AND rc.type = 'chayolei'
    GROUP BY school_id
");
$stmt->execute([':year' => $year]);
$temp = $stmt->fetchAll();
foreach ($temp as $row) {
    $discounts[$row['school_id']] = $row['discount'];
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?=$year?> Soldier Registration</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs-3.3.7/jqc-1.12.4/dt-1.10.13/cr-1.3.2/fc-3.2.2/fh-3.1.2/r-2.1.1/sc-1.4.2/se-1.2.0/datatables.min.css"/>
    <style>
        body {
            padding-left: 20px;
            padding-right: 20px;
        }
    </style>
</head>
<body>
<h1><?=$year?> Soldier Registration</h1>
<h2>Base Totals</h2>
<table id="table" class="table table-striped table-condensed">
    <thead>
        <tr>
           <th>Base Name</th>
           <th>Soldiers Registered</th>
           <th>Fee per Soldier</th>
           <th>Total Fee</th>
           <th>Total Discounts</th>
           <th>Total Owing</th>
           <th>Total Paid</th>
           <th>Balance</th>
        </tr>
    </thead>
    <tbody>
    <?php
    $totals['soldiers'] = 0;
    $totals['fee'] = 0;
    $totals['total_fee'] = 0;
    $totals['discounts'] = 0;
    $totals['owing'] = 0;
    $totals['paid'] = 0;
    $totals['balance'] = 0;
    foreach ($totals as $school_id => $total) {
        // if there's no name for the base just skip
        if (! isset($schools[$school_id])) continue;
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
//        $early_bird = new DateTime($row['user_registered']) <=  GlobalSettings::earlyBird();
        $fee = GlobalSettings::calculateChildFee($row['school_type'], $row['child_fee'], true, true);
        echo "<tr><td>" . $schools[$school_id] . "</td><td>" . $row['total_users'] . "</td><td>" . $fee .
            "</td><td>" . ($fee * intval($row['total_users'])) . "</td><td>" . $discounts[$school_id] . "</td><td>" .
            ($fee * intval($row['total_users']) - floatval($discounts[$school_id])) . "</td><td>" . $total . "</td>";
        $style = '';
        $balance = (($fee * intval($row['total_users']) - floatval($discounts[$school_id])) - floatval($total));
        if ($balance > 0) {
            $style = "background-color: red";
        }
        echo "<td style='$style'>" . $balance . "</td></tr>";
        $totals['soldiers'] += $row['total_users'];
        $totals['fee'] += $fee;
        $totals['total_fee'] += ($fee * intval($row['total_users']));
        $totals['discounts'] += $discounts[$school_id];
        $totals['owing'] += ($fee * intval($row['total_users']) - floatval($discounts[$school_id]));
        $totals['paid'] += $total;
        $totals['balance'] += $balance;
    }
    ?>
    </tbody>
    <tfoot>
    <?php
    echo "<tr><th>Totals:</th><th>" . $totals['soldiers'] . "</th><th>" . $totals['fee'] . "</th><th>" . $totals['total_fee'] .
        "</th><th>" . $totals['discounts'] . "</th><th>" . $totals['owing'] . "</th><th>" . $totals['paid'] . "</th><th>" .
        $totals['balance'] . "</th></tr>";
    ?>
    </tfoot>
</table>
<h2>Details</h2>
<table>
    <thead>
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
    </thead>
</table>
</body>
<script type="text/javascript" src="https://cdn.datatables.net/v/bs-3.3.7/jqc-1.12.4/dt-1.10.13/cr-1.3.2/fc-3.2.2/fh-3.1.2/r-2.1.1/sc-1.4.2/se-1.2.0/datatables.min.js"></script>
<script>
    $('#table').DataTable({
        paging : false,
        "order": [[ 0, 'asc' ]]
    });
</script>
</html>