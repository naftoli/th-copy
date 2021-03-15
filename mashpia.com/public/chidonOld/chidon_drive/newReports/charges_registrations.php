<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once __DIR__ . '/../../../header.php';
require_once __DIR__ . '/../../../class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

if ($admin_user['auth'] != 'super') {
    echo "No permission.";
    exit;
}

$transactions = [];
$sql = "select * from th_chidon_parent_purchases order by admin_id";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $transactions[$row['admin_id']][] = $row;
}

$children = [];
foreach ($transactions as $admin_id => $details) {
    $sql = "select user_id, paid, date_paid from th_chidon where parent_id = " . $admin_id . " and year = " . $year;
    $result = mysql_query($sql);
    while ($row = mysql_fetch_assoc($result)) {
        $children[$admin_id][] = $row;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf8" />
    <title>Transactions Report</title>
    <style>
        tr, th, td {
            font-family: Arial, "Helvetica Neue", Helvetica, sans-serif;
            font-size: 14px;
            padding: 10px;
        }
    </style>
</head>
<body>
<h1>Transactions Report</h1>
<table>
    <tr>
        <th>Admin ID</th>
        <th>Amount</th>
        <th>Type</th>
        <th>Desc</th>
        <th>Date</th>
        <th>Children</th>
    </tr>
    <?php
    foreach ($transactions as $admin_id => $more) {
        foreach ($more as $transaction) {
            echo "<tr><td>" . $admin_id . "</td><td>" . $transaction['amount'] . "</td><td>" .
                $transaction['authorize_trans_type'] . "</td><td>" . $transaction['authorize_desc'] . "</td><td>" .
                $transaction['purchase_date'] . "</td><td><table>";
            foreach ($children[$admin_id] as $child) {
                echo "<tr><td>" . $child['user_id'] . "</td><td>" . $child['paid'] . "</td><td>" . $child['date_paid'] . "</td></tr>";
            }
            echo "</table></td></tr>";
        }
    }
    ?>
</table>
</body>
</html>