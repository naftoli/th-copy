<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getRegistrationYear();

// get all info about admins and children registered in myshliach
$admins = [];
$children = [];
$stmt = $MASHPIA_DB->prepare("
    SELECT 
        a.*, u.user_id, u.first as user_first, u.last as user_last
    FROM
        admins a 
            JOIN
        admin_auths aa USING (admin_id) 
            JOIN
        users u ON u.user_id = aa.id 
            JOIN 
        user_registration ur ON ur.user_id = u.user_id 
    WHERE
        u.user_registered > 0
            AND u.school_id = 61 AND ur.year = :year
");
$stmt->execute([
    'year'  => $year,
]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $row) {
    $admins[$row['admin_id']] = $row;
    $children[$row['admin_id']][$row['user_id']] = $row['user_first'] . ' ' . $row['user_last'];
}

$chayolei_charges = [];
$shipping_charges = [];
$stmt = $MASHPIA_DB->prepare("
    SELECT 
        * 
    FROM
        registration_charges  
    WHERE
        year = :year 
            AND (type = 'THE' OR type LIKE 'THMS%')
");
$stmt->execute([
    'year'  => $year,
]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $row) {
    if ($row['type'] == 'THE') {
        $chayolei_charges[$row['user_id']][] = $row;
    } else {
        $shipping_charges[$row['admin_id']][] = $row;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf8"/>
  <title>MyShliach Hachayol Shipping Charges Report</title>
  <style>
    tr, th, td {
      font-size: 14px;
      padding: 5px;
      font-family: Arial, "Helvetica Neue", Helvetica, sans-serif;
    }
  </style>
</head>
<body>
<table>
  <tr>
    <th>Family ID</th>
    <th>Family Name</th>
    <th>Address</th>
    <th>Number of Registered Children</th>
    <th>Children Registered</th>
    <th>Registration amount paid</th>
    <th>Shipping Fee Paid</th>
  </tr>
    <?php
    foreach ($children as $admin_id => $details) {
        $admin = $admins[$admin_id];
        $name = $admin['first'] . ' ' . $admin['last'];
        $address = $admin['admin_address1'] . " " . $admin['admin_address2'] . "<br />" . $admin['admin_city'] .
            ", " . $admin['admin_state'] . "<br />" . $admin['admin_postal'] . "<br />" . $admin['admin_country'];
        echo "<tr><td>" . $admin_id . "</td><td>" . $name . "</td><td>" . $address . "</td><td>" . count($details) . "</td><td>";
        foreach ($details as $child_name) {
            echo $child_name . "<br />";
        }
        echo "</td><td>";
        foreach ($details as $user_id => $child_name) {
            if (isset($chayolei_charges[$user_id])) {
                foreach ($chayolei_charges[$user_id] as $charge) {
                    echo $child_name . ": " . $charge['amount'];
                    if (intval($charge['discount']) > 0) echo ' (discount: ' . $charge['discount'] . ')';
                    echo "<br />";
                }
            }
        }
        echo "</td><td>";
        $paid = 0;
        if (isset($shipping_charges[$admin_id])) {
            foreach ($shipping_charges[$admin_id] as $charge) {
                if ($charge['type'] != 'THE') {
                    $paid += $charge['amount'];
                }
            }
        }
        if (!$paid) echo "NOT PAID";
        else echo $paid;
        echo "</td></tr>";
    }
    ?>
</table>
</body>
</html>
