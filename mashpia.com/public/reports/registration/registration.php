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

$details = [];
$stmt = $MASHPIA_DB->prepare("
    SELECT * FROM user_registration  
    WHERE year = :year 
");
$stmt->execute([':year' => $year]);
$rows = $stmt->fetchAll();

$totals = [];
foreach ($rows as $row) {
    if (isset($totals[$row['school_id']])) $totals[$row['school_id']] += floatval($row['paid']);
    else $totals[$row['school_id']] = floatval($row['paid']);
    $details[$row['school_id']][] = $row;
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

$types = [
    1 => 'Tuition',
    2 => 'Guaranteed',
    3 => 'Regular'
];

$school_info = [];
$stmt = $MASHPIA_DB->query("
    SELECT school_id, reg_type, COUNT(*) as eligible 
    FROM schools s 
    LEFT JOIN users u using (school_id) 
    WHERE u.chayolei_eligible = 1 
    GROUP BY s.school_id
");
$temp = $stmt->fetchAll();
foreach ($temp as $row) {
    $school_info[$row['school_id']] = [
        'type'      => $types[$row['reg_type']],
        'eligible'  => $row['eligible']
    ];
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Soldier Registration</title>
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
<h1>Soldier Registration <?=$year?></h1>
<h2>Base Totals</h2>
<table id="table" class="table table-striped table-condensed">
    <thead>
        <tr>
           <th>Base Type</th>
           <th>Base Name</th>
           <th>Eligible Soldiers</th>
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
    $t['soldiers'] = 0;
    $t['total_fee'] = 0;
    $t['discounts'] = 0;
    $t['owing'] = 0;
    $t['paid'] = 0;
    $t['balance'] = 0;
    foreach ($totals as $school_id => $total) {
        // if there's no name for the base just skip
        if (! isset($schools[$school_id])) continue;
        $stmt = $MASHPIA_DB->prepare("
            SELECT reg_type, child_fee     
            FROM schools  
            WHERE school_id = :id 
        ");
        $stmt->execute([':id' => $school_id]);
        $row = $stmt->fetch();
        // figure out soldier fee
//        $early_bird = new DateTime($row['user_registered']) <=  GlobalSettings::earlyBird();
//        echo "School ID: " . $school_id . " Reg Type: " . $row['school_type'] . "<br />";
        $fee = GlobalSettings::calculateChildFee($row['reg_type'], $row['child_fee'], false, true);
        echo "<tr><td>" . $school_info[$school_id]['type'] . "</td><td><a href='#$school_id'>" . $schools[$school_id] .
            "</a></td><td>" . $school_info[$school_id]['eligible'] . "</td><td>" . count($details[$school_id]) . "</td><td>" . $fee .
            "</td><td>" . ($fee * count($details[$school_id])) . "</td><td>" . $discounts[$school_id] . "</td><td>" .
            ($fee * count($details[$school_id]) - floatval($discounts[$school_id])) . "</td><td>" . $total . "</td>";
        $style = '';
        $balance = (($fee * count($details[$school_id]) - floatval($discounts[$school_id])) - floatval($total));
        if ($balance > 0) {
            $style = "background-color: red";
        }
        echo "<td style='$style'>" . $balance . "</td></tr>";
        $t['soldiers'] += count($details[$school_id]);
        $t['total_fee'] += ($fee * count($details[$school_id]));
        $t['discounts'] += $discounts[$school_id];
        $t['owing'] += ($fee * count($details[$school_id]) - floatval($discounts[$school_id]));
        $t['paid'] += $total;
        $t['balance'] += $balance;
    }
    ?>
    </tbody>
    <tfoot>
    <?php
    echo "<tr><th>Totals:</th><th></th><th></th><th>" . $t['soldiers'] . "</th><th></th><th>" . $t['total_fee'] .
        "</th><th>" . $t['discounts'] . "</th><th>" . $t['owing'] . "</th><th>" . $t['paid'] . "</th><th>" .
        $t['balance'] . "</th></tr>";
    ?>
    </tfoot>
</table>
<h2>Soldier Details</h2>
<table id="details" class="table table-striped table-condensed">
    <thead>
        <tr>
            <th>User ID</th>
            <th>Serial Number</th>
            <th>Base Number</th>
            <th>Base Name</th>
            <th>Grade</th>
            <th>Soldier</th>
            <th>Registered</th>
            <th>Fee</th>
            <th>Discount</th>
            <th>Owes</th>
            <th>Paid</th>
            <th>Balance</th>
        </tr>
    </thead>
    <tbody>
    <?php
    $stmt = $MASHPIA_DB->prepare("
        SELECT 
            *
        FROM
            users u
                JOIN
            schools s USING (school_id)
                JOIN
            classes c ON c.class_id = u.class_id
        WHERE
            u.user_id = :id
    ");
    foreach ($details as $school_id => $students) {
        foreach ($students as $idx => $student) {
            $stmt->execute([':id' => $student['user_id']]);
            $row = $stmt->fetch();
            if (! $row['school_name']) continue;

            $early = true;
            $registered = $student['reg_date'];
            if ($registered) {
                $early_bird = GlobalSettings::earlyBird();
                $reg_date = new DateTime($registered);
                if ($reg_date > $early_bird) $early = false;
            }
            $fee = GlobalSettings::calculateChildFee($row['school_type'], $row['child_fee'], false, $early);

            echo "<tr><td>" . $student['user_id'] . "</td><td>" . $row['user_serial'] . "</td><td>" . $row['school_number'] . "</td>";
            if ($idx == 0) echo "<td id='$school_id'>";
            else echo "<td>";
            echo $row['school_name'] . "</td><td>" . ($row['class_grade'] . ($row['class_sub'] ? '-' . $row['class_sub'] : 0)) .
                "</td><td>" . $row['first'] . ' ' . $row['last'] . "</td><td>" . $row['user_registered'] . "</td><td>" .
                $fee . "</td><td>" . $student['discount'] . "</td><td>" . ($fee - $student['discount']) . "</td><td>" .
                $student['paid'] . "</td>";
            $style = '';
            $balance = (($fee - $student['discount']) - $student['amount']);
            if ($balance > 0) {
                $style = "background-color: red";
            }
            echo "<td style='$style'>";
            echo $balance . "</td></tr>";
        }
    }
    ?>
    </tbody>
</table>
</body>
<script type="text/javascript" src="https://cdn.datatables.net/v/bs-3.3.7/jqc-1.12.4/dt-1.10.13/cr-1.3.2/fc-3.2.2/fh-3.1.2/r-2.1.1/sc-1.4.2/se-1.2.0/datatables.min.js"></script>
<script>
    $(function() {
        $('#table').DataTable({
            paging : false,
            "order": [[ 1, 'asc' ]]
        });
        $('#details').DataTable({
            paging : false,
            "order": [[ 2, 'asc' ]]
        });
    })
</script>
</html>