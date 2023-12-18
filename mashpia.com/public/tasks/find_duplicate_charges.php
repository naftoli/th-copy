<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

$sql = "select rc.*, u.user_id, u.first, u.last, u.user_serial  
        from registration_charges rc 
        join users u using (user_id) 
        where year = :year";
$stmt = $MASHPIA_DB->prepare($sql);
$stmt->execute(['year' => $year]);

$charges = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $charges[$row['user_id']][$row['type']][] = $row;
}

$extras = [];
$others = [];
foreach ($charges as $user_id => $details) {
    foreach ($details as $type => $more) {
        foreach ($more as $idx => $row) {
            if ($idx > 0) {
                if ($type == 'LDE') $extras[] = $row;
                else $others[] = $row;
            }
        }
    }
}
//echo count($extras) . "<br />" . count($others) . "<br />";
//echo "<pre>"; print_r($others); echo "</pre>";

$stmt = $MASHPIA_DB->prepare("delete from registration_charges where registration_charge_id = :id");
foreach ($extras as $row) {
    $stmt->execute(['id' => $row['registration_charge_id']]);
}
//echo "done";
?>
<!DOCTYPE html>
<html>
<head>
    <title>Duplicate Charges</title>
    <style>
        tr, th, td {
          font-family: "Arial", sans-serif;
          padding: 5px;
        }
    </style>
</head>
<body>
    <h1>Find Duplicate Charges</h1>
    <table>
        <tr>
            <th>Student ID</th>
            <th>Serial Number</th>
            <th>Student Name</th>
            <th>Charge Type</th>
            <th>Amount</th>
            <th>Charge Date</th>
        </tr>
        <?php foreach ($others as $row) { ?>
        <tr>
            <td><?php echo $row['user_id']; ?></td>
            <td><?php echo $row['user_serial']; ?></td>
            <td><?php echo $row['first'] . ' ' . $row['last']; ?></td>
            <td><?php echo $row['type']; ?></td>
            <td><?php echo $row['amount']; ?></td>
            <td><?php echo $row['date']; ?></td>
        </tr>
        <?php } ?>
</html>
