<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';

$year = GlobalSettings::getChidonRegYear();
$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth']);
$schools = $as->getSchools();

function getParents() {
    global $parents, $year, $schools;

    $sql = "select parent_id from th_chidon where date_paid > 0 and school_id in (" . implode(',', array_keys($schools)) . ") 
            and year = " . $year;
    $result = mysql_query($sql);
    while ($row = mysql_fetch_assoc($result)) {
        $parents[] = $row['parent_id'];
    }
}

function getExtraPurchases() {
    global $extra_purchases, $year;

    $sqlExtra = "select p.*, a.admin_id, a.first, a.last 
            from extra_purchases p 
            join admins a using (admin_id) 
            where year = " . $year;
    $resExtra = mysql_query($sqlExtra);
    while ($rowExtra = mysql_fetch_assoc($resExtra)) {
        $extra_purchases[] = $rowExtra;
    }
}

function getAddresses() {
    global $addresses;

    $sql = "select * from purchase_addresses";
    $result = mysql_query($sql);
    while ($row = mysql_fetch_assoc($result)) {
        $addresses[$row['purchase_id']] = $row;
    }
}

$parents = [];
$extra_purchases = [];
$addresses = [];

getParents();
getExtraPurchases();
getAddresses();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf8" />
    <title>Chidon Report</title>
    <style>
        tr, th, td {
            font-family: Arial, "Helvetica Neue", Helvetica, sans-serif;
            font-size: 12px;
            padding: 5px;
            border-bottom: 1px solid grey;
        }
    </style>
</head>
<body>
<h1>Chidon Report</h1>
<table>
    <caption>Extra Purchases</caption>
    <tr>
        <th>Parent ID</th>
        <th>First Name</th>
        <th>Last Name</th>
        <th>Item Purchased</th>
        <th>Amount purchased</th>
        <th>Sweater Type</th>
        <th>Sweater Size</th>
        <th>Needs Shipping</th>
        <th>Shipping Paid</th>
        <th>Address</th>
    </tr>
    <?php
    foreach ($extra_purchases as $purchase) {
        if (! in_array($purchase['admin_id'], $parents)) continue;
        echo "<tr><td>" . $purchase['admin_id'] . "</td><td>" . $purchase['first'] . "</td><td>" . $purchase['last'] .
            "</td><td>" . $purchase['item'] . "</td><td>" . $purchase['amount'] . "</td><td>" . $purchase['type_of_sweater'] .
            "</td><td>" . $purchase['size'] . "</td><td>";
        if (intval($purchase['shipping_amount'])) echo 'yes';
        else echo 'no';
        echo "</td><td>" . $purchase['shipping_amount'] . "</td>";
        if (intval($purchase['shipping_amount'])) {
            $ship_info = $addresses[$purchase['purchase_id']];
            echo "<td>" . $ship_info['address'] . "<br />" . $ship_info['city'] . ' ' . $ship_info['state'] . ' ' .
                $ship_info['zip'] . "<br />" . $ship_info['country'] . "</td>";
        } else {
            echo "<td></td>";
        }
        echo "</tr>";
    }
    ?>
</table>
</body>
</html>
