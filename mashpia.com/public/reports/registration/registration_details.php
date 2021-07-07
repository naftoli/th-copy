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
    SELECT * FROM registration_charges 
    WHERE year = :year 
    AND type = 'chayolei'
");
$stmt->execute([':year' => $year]);
$rows = $stmt->fetchAll();

$totals = [];
foreach ($rows as $row) {
    $details[$row['school_id']][] = $row;
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
<h2>Details</h2>
<table id="details" class="table table-striped table-condensed">
    <thead>
    <tr>
        <th>User ID</th>
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
            $fee = GlobalSettings::calculateChildFee($student['school_type'], $student['child_fee'], true, true);
            echo "<tr><td>" . $student['user_id'] . "</td><td>" . $row['school_number'] . "</td>";
            if ($idx == 0) echo "<td id='$school_id'>";
            else echo "<td>";
            echo $row['school_name'] . "</td><td>" . ($row['class_grade'] . ($row['class_sub'] ? '-' . $row['class_sub'] : 0)) .
                "</td><td>" . $row['first'] . ' ' . $row['last'] . "</td><td>" . $row['user_registered'] . "</td><td>" .
                $fee . "</td><td>" . $student['discount'] . "</td><td>" . ($fee - $student['discount']) . "</td><td>" .
                $student['amount'] . "</td><td>";
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
        $('#details').DataTable({
            paging : false,
            "order": [[ 2, 'asc' ]]
        });
    })
</script>
</html>