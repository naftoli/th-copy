<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';

if ($admin_user['auth'] != 'super') {
    echo "No Permission.";
    exit;
}

//***************** LOAD CURRENT YEAR **********************/
require_once $_SERVER['DOCUMENT_ROOT']. '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

// find parents with duplicates so that we don't process holds as of now
$duplicates = [];
$sql = "select admin_id, count(*) as total 
        from th_chidon_parent_purchases 
        where authorize_id > 1 
        group by admin_id 
        having total > 1";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $duplicates[] = $row['admin_id'];
}

// find parents with holds and their trans id numbers
$parents = [];
$sql = "SELECT 
            tcpp.*, a.first, a.last 
        FROM
            th_chidon_parent_purchases tcpp
        JOIN
            admins a USING (admin_id) 
        WHERE
            authorize_id > 1";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    // skip parents with duplicated
    if (in_array($row['admin_id'], $duplicates)) continue;
    $parents[] = $row;
}

// find how much was raised
foreach ($parents as $idx => $parent) {
    $sql = "SELECT 
                SUM(donation_amount) as total 
            FROM
                mashpiadb.chidon_donations
            WHERE
                for_family_id = " . $parent['admin_id'] . " AND chidon_year = " . $year;
    $result = mysql_query($sql);
    if (mysql_num_rows($result) > 0) {
        $row = mysql_fetch_assoc($result);
        $total = $row['total'];
        $parents[$idx]['raised'] = $total;
    }
}

$children = [];
foreach ($parents as $parent) {
    $admin_id = $parent['admin_id'];
    $sql = "select u.user_id, u.first, tc.paid 
            from th_chidon tc 
            join users u using (user_id) 
            where tc.year = " . $year . " 
            and tc.parent_id = " . $admin_id;
    $result = mysql_query($sql);
    while ($row = mysql_fetch_assoc($result)) {
        $children[$admin_id][] = $row;
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Information Report</title>
    <style>
        tr, th, td {
            font-family: Arial, "Helvetica Neue", Helvetica, sans-serif;
            font-size: 14px;
            padding: 5px;
        }
    </style>
</head>
<body>
    <h1>Transactions Information Report</h1>
    <table>
        <tr>
            <th>Parent ID</th>
            <th>Parent Name</th>
            <th>Transaction ID</th>
            <th>Transaction Type</th>
            <th>Transaction Amount</th>
            <th>Registrations</th>
            <th>Total Registration Charges</th>
            <th>Extra Celebration Box</th>
            <th>Extra Celebration Box Shipping</th>
            <th>Sweaters</th>
            <th>Shipping Charges</th>
            <th>Total Non-Registration Charges</th>
            <th>ChidonDrive Raised</th>
            <th>Rohr Subsidy</th>
            <th>ChidonDrive Subsidy</th>
            <th>Balance</th>
            <th>Type of Action</th>
            <th>Amount</th>
        </tr>
        <?php
        foreach ($parents as $parent) {
            echo "<tr><td>" . $parent['admin_id'] . "</td><td>" . $parent['first'] . ' ' . $parent['last'] . "</td><td>" .
                $parent['authorize_id'] . "</td><td>" . $parent['authorize_trans_type'] . "</td><td>" . $parent['amount'] .
                "</td><td>";
            $reg_total = 0;
            foreach ($children[$parent['admin_id']] as $child) {
                $reg_total += intval($child['paid']);
                echo $child['user_id'] . ': ' . $child['first'] . " - Registration Charge: " . $child['paid'] . "<br />";
            }
            $non_reg_total = 0;
            echo "</td><td>" . $reg_total . "</td><td>";
            if (intval($parent['celeb_box_add'])) {
                $non_reg_total += 20;
                echo "$20";
            }
            echo "</td><td>";
            if (intval($parent['celeb_box_add_ship'])) {
                $non_reg_total += 10;
                echo "$10";
            }
            echo "</td><td>";
            // figure out sweaters and sweater shipping
            $sweaters = [];
            $shipping = [];
            $types = ['mother', 'father', 'bubby', 'zaidy'];
            foreach ($types as $type) {
                if ($parent["sweater_$type"]) $sweaters[] = $type;
                if (intval($parent["sweater_{$type}_ship"])) $shipping[] = $type;
            }
            foreach ($sweaters as $type) {
                $non_reg_total += 25;
                echo $type . " - $25<br />";
            }
            echo "</td><td>";
            foreach ($shipping as $type) {
                $non_reg_total += 10;
                echo $type . " - $10<br />";
            }
            echo "</td><td>" . $non_reg_total . "</td><td>" . $parent['raised'] . "</td><td>";

            $rohr = 0;
            if (floatval($parent['raised']) >= 270) $rohr = 100;
            $subsidy = (floatval($parent['raised']) + $rohr) / 2;
            $balance = $reg_total - $subsidy;
            if ($balance < 0) $balance = 0;

            echo $rohr . "</td><td>" . $subsidy . "</td><td>" . $balance . "</td><td>";

            if ($parent['authorize_trans_type'] == 'charge') {
                if ($balance == 0) echo "Refund" . "</td><td>" . $reg_total;
                else if ($subsidy) echo "Refund" . "</td><td>" . $subsidy;
                else echo "No Refund" . "</td><td>";
            } else if ($parent['authorize_trans_type'] == 'hold') {
                if ($balance == 0) echo "Remove From Hold" . "</td><td>" . $reg_total;
                else if ($subsidy) echo "Remove From Hold" . "</td><td>" . $subsidy;
                else echo "Charge Entire Hold" . "</td><td>";
            }
            echo "</td></tr>";
        }
        ?>
    </table>
</body>
</html>