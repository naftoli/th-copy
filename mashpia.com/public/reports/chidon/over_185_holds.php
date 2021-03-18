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
            authorize_id > 1
        ORDER BY
            authorize_trans_type, admin_id";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    // skip parents with duplicates
    if (in_array($row['admin_id'], $duplicates)) continue;
    $parents[] = $row;
}

$parentsToRemove = [];
$children = [];
foreach ($parents as $parent) {
    $admin_id = $parent['admin_id'];
    $sql = "select u.user_id, u.first, tc.paid 
            from th_chidon tc 
            join users u using (user_id) 
            where tc.year = " . $year . " 
            and tc.parent_id = " . $admin_id;
    $result = mysql_query($sql);
    $paid185 = 0;
    while ($row = mysql_fetch_assoc($result)) {
        if ($row['paid'] == 185) $paid185++;
        $children[$admin_id][] = $row;
    }
    // if all kids paid 185 remove from lists
    if ($paid185 == mysql_num_rows($result)) {
        $parentsToRemove[] = $admin_id;
    }
}

// remove from list
foreach ($parents as $idx => $parent) {
    if (in_array($parent['admin_id'], $parentsToRemove)) {
        unset($parents[$idx]);
    }
}
foreach ($children as $admin_id => $details) {
    if (in_array($admin_id, $parentsToRemove)) {
        unset($children[$admin_id]);
    }
}

// get chidondrive info
foreach ($children as $admin_id => $more) {
    foreach ($more as $idx => $child) {
        $sql = "SELECT 
                    SUM(subsidy_amount) AS total
                FROM
                    mashpiadb.chidon_user_subsidies
                WHERE
                    user_id = " . $child['user_id'] . " 
                        AND chidon_donation_id IN (SELECT 
                            chidon_donation_id
                        FROM
                            chidon_donations
                        WHERE
                            chidon_year = " . $year . " AND for_family_id = " . $admin_id . ")";
        $result = mysql_query($sql);
        $row = mysql_fetch_assoc($result);
        $children[$admin_id][$idx]['raised'] = $row['total'];
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
        input.refund {
            width: 60px;
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
        <th>Total ChidonDrive Subsidy</th>
        <th>50% Subsidy for Registration</th>
        <th>Balance</th>
        <th>Difference</th>
        <th>Amount Refunded</th>
        <th></th>
    </tr>
    <?php
    foreach ($parents as $parent) {
        echo "<tr><td>" . $parent['admin_id'] . "</td><td>" . $parent['first'] . ' ' . $parent['last'] . "</td><td>" .
            $parent['authorize_id'] . "</td><td>" . $parent['authorize_trans_type'] . "</td><td>" . $parent['amount'] .
            "</td><td>";
        $reg_total = 0;
        foreach ($children[$parent['admin_id']] as $child) {
            $reg_total += intval($child['paid']);
            echo $child['first'] . ": " . $child['paid'] . "<br />";
        }
        $non_reg_total = 0;
        echo "</td><td>" . $reg_total . "</td><td>";
        if (intval($parent['celeb_box_add'])) {
            $non_reg_total += 20;
            echo "20";
        }
        echo "</td><td>";
        if (intval($parent['celeb_box_add_ship'])) {
            $non_reg_total += 10;
            echo "10";
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
            echo $type . " - 25<br />";
        }
        echo "</td><td>";
        foreach ($shipping as $type) {
            $non_reg_total += 10;
            echo $type . " - 10<br />";
        }
        echo "</td><td>" . $non_reg_total . "</td><td>";
        $subsidy = [];
        foreach ($children[$parent['admin_id']] as $child) {
            $subsidy[$child['user_id']] = floatval($child['raised']);
            echo $child['first'] . ": " . $child['raised'] . "<br />";
        }
        echo "</td><td>";
        foreach ($children[$parent['admin_id']] as $child) {
            if (floatval($child['raised']) >= 270) {
                $subsidy[$child['user_id']] += 100;
                echo $child['first'] . ": 100<br />";
            } else
                echo $child['first'] . ": 0<br />";
        }
        echo "</td><td>";
        foreach ($children[$parent['admin_id']] as $child) {
            echo $child['first'] . ": " . $subsidy[$child['user_id']] . "<br />";
        }
        echo "</td><td>";
        foreach ($children[$parent['admin_id']] as $child) {
            echo $child['first'] . ": " . floatval($subsidy[$child['user_id']] / 2) . "<br />";
        }
        echo "</td><td>";
        // balance is registration charge minus 50% subsidy per child plus non registration charges
        $balance = 0;
        foreach ($children[$parent['admin_id']] as $child) {
            $regAfterSubsidy = intval($child['paid']) - floatval($subsidy[$child['user_id']] / 2);
            if ($regAfterSubsidy < 0) $regAfterSubsidy = 0;
            $balance += $regAfterSubsidy;
        }
        $balance += $non_reg_total;
        $difference = floatval($parent['amount']) - floatval($balance);
        echo $balance . "</td><td>" . $difference . "</td><td>";
        echo "<input type='text' name='toRefund' class='toRefund' data-id='$parent[admin_id]'";
        if ($parent['refund']) echo " value='" . $parent['refund'] . "'";
        echo " /></td><td>
                <input type='button' name='refund' class='refund' value='Save' /></td></tr>";
    }
    ?>
</table>
</body>
<script
    src="https://code.jquery.com/jquery-1.12.4.min.js"
    integrity="sha256-ZosEbRLbNQzLpnKIkEdrPv7lOy9C27hHQ+Xp8a4MxAQ="
    crossorigin="anonymous"></script>
<script>
    $(function() {
        $(".refund").click( function(e) {
            e.preventDefault()
            const info = $(this).parent().parent().find('.toRefund')
            const admin = $(info).data('id')
            const amount = $(info).val()
            $.post('ajax/refund.php', { admin: admin, amount: amount }, function(result) {
                const res = JSON.parse(result)
                if (res.success) alert('Success.')
                else alert('Error.')
            })
        })
    })
</script>
</html>