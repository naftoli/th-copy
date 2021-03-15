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
$sql = "select tc.*, a.first, a.last from th_chidon_parent_purchases tc 
        join admins a using (admin_id)
        order by admin_id, purchase_date";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $transactions[$row['admin_id']][] = $row;
}

$children = [];
foreach ($transactions as $admin_id => $details) {
    $sql = "select tc.user_id, tc.paid, tc.date_paid, u.first, u.last  
            from th_chidon tc 
            join users u using (user_id) 
            where parent_id = " . $admin_id . " and year = " . $year;
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
        tr {
            margin-bottom: 1px solid black;
        }
        td {
            vertical-align: top;
        }
    </style>
</head>
<body>
<h1>Transactions Report</h1>
<table>
    <tr>
        <th>Admin ID</th>
        <th>Name</th>
        <th>Date</th>
        <th>Amount</th>
        <th>Type</th>
        <th>Desc</th>
        <th>Children Registrations</th>
    </tr>
    <?php
    foreach ($transactions as $admin_id => $more) {
        foreach ($more as $transaction) {
            echo "<tr><td>" . $admin_id . "</td><td>" . $transaction['first'] . ' ' . $transaction['last'] .
                "</td><td>" . $transaction['purchase_date'] . "</td><td>" .
                $transaction['amount'] . "</td><td>" . $transaction['authorize_trans_type'] . "</td><td>" .
                $transaction['authorize_desc'] . "</td><td><table>";
            foreach ($children[$admin_id] as $child) {
                echo "<tr><td>" . $child['user_id'] . "</td><td>" . $child['first'] . "</td><td>" . $child['paid'] .
                        "</td><td>" . $child['date_paid'] . "</td></tr>";
            }
            echo "</table></td></tr>";
        }
    }
    ?>
</table>
</body>
</html>