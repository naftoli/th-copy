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

$info = [];
$sql = "select tc.th_chidon_id, u.first, u.last, tc.paid, tcpp.authorize_id, tcpp.authorize_trans_type, tcpp.authorize_desc 
        from th_chidon tc 
        join users u using (user_id) 
        join th_chidon_parent_purchases tcpp on tc.parent_id = tcpp.admin_id 
        where year = " . $year;
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $info[] = $row;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf8" />
    <title>Chidon Registrations Report</title>
    <style>
        tr, th, td {
            font-family: Arial, "Helvetica Neue", Helvetica, sans-serif;
            font-size: 14px;
            padding: 10px;
        }
    </style>
</head>
<body>
<h1>Chidon Registrations Report</h1>
<table>
    <tr>
        <th>Chidon ID</th>
        <th>First Name</th>
        <th>Last Name</th>
        <th>Paid</th>
        <th>Transaction ID</th>
        <th>Type</th>
        <th>Transaction Desc</th>
    </tr>
    <?php
    foreach ($info as $row) {
        echo "<tr><td>" . $row['th_chidon_id'] . "</td><td>" . $row['first'] . "</td><td>" . $row['last'] . "</td><td>" .
            $row['paid'] . "</td><td>" . $row['authorize_id'] . "</td><td>" . $row['authorize_trans_type'] . "</td><td>" .
            $row['authorize_desc'] . "</td></tr>";
    }
    ?>
</table>
</body>
</html>
