<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

require $_SERVER['DOCUMENT_ROOT'] . '/db.php';
require $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require $_SERVER['DOCUMENT_ROOT'] . '/chidonTests/class.chidonTests.php';
//require $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonRegYear();

require '../coupons/class.couponCode.php';

function getRegInfo() {
    global $reg, $year, $users;

    $sqlReg = "select u.user_id, u.user_serial, u.first, u.last, u.school_id, u.class_id, c.class_grade, tc.th_chidon_id, 
                tc.paid, tc.date_paid, tc.payment_request, tc.parent_id, tc.khk_reg, tc.test_type, tc.reward_type, tc.to_fundraise_5782    
            from users u 
            join th_chidon tc using (user_id)  
            join classes c on c.class_id = u.class_id 
            where tc.date_paid > 0 
            and tc.year = " . $year . "
            order by date_paid, last, first";
    $resReg = mysql_query($sqlReg);
    while ($rowReg = mysql_fetch_assoc($resReg)) {
        $reg[] = $rowReg;
        $users[] = [
            'user_id'   => $rowReg['user_id'],
            'class_id'  => $rowReg['class_id'],
            'school_id' => $rowReg['school_id'],
            'test_type' => $rowReg['test_type'],
            'reward_type'   => $rowReg['reward_type'],
            'th_chidon_id'  => $rowReg['th_chidon_id']
        ];
    }
}

function getTracks() {
    global $tracks, $users;

    $ct = new ChidonTests();
    $types = $ct->getTypes();

    foreach ($users as $user) {
        $info = $ct->getHighestTrackPassed($user);
        $highest = $info['highest_track'];
        $reward_type = $user['reward_type'];
        if ($reward_type !== 'highest track passed') {
            $key1 = array_search($highest, array_keys($types));
            $key2 = array_search($reward_type, array_keys($types));
            if ($key2 > $key1) $highest = $reward_type;
        }
        $tracks[$user['user_id']] = isset($types[$highest]) ? $types[$highest] : 'none';
    }
}

function getShippingInfo() {
    global $shipping;

    $sql = "select admin_id, first, last, chidon_shipping_paid 
            from admins 
            where chidon_shipping_paid > 0";
    $result = mysql_query($sql);
    while ($row = mysql_fetch_assoc($result)) {
        $shipping[] = $row;
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

function getRaised() {
    global $raised, $year;

    $sql = "
        SELECT 
            user_id, SUM(subsidy_amount) as total 
        FROM
            chidon_user_subsidies
        WHERE
            chidon_year = $year
        GROUP BY user_id";
    $result = mysql_query($sql);
    while ($row = mysql_fetch_assoc($result)) {
        $raised[$row['user_id']] = $row['total'];
    }
}

function get50Percent($user_id, $grade) {
    global $half, $tracks;

    $track = $tracks[$user_id];
    if (in_array($track, ['Havonah', 'Iyun']) && parseInt($grade) == 8) $track = 'Khk Trip';

    $amounts = [
        'Yesod'     => [70, 50, 35],
        'Yediah'    => [180, 125, 100, 90],
        'Havonah'   => [370, 250, 200, 185],
        'Iyun'      => [370, 250, 200, 185],
        'Khk Trip'  => [500, 400, 325, 250]
    ];
    $values = $amounts[$track];
    return $values[count($values)-1];
}

function getCoupons($user_serial) {
    global $coupons, $MASHPIA_DB, $year;

    $c = new CouponCode($MASHPIA_DB, $year);
    $coupon = $c->checkForUsedUserCode($user_serial);
    return $coupon;
}

$reg = [];
$users = [];
$tracks = [];
$shipping = [];
$extra_purchases = [];
$addresses = [];
$half = [];
$coupons = [];
$raised = [];

getRegInfo();
getTracks();
getShippingInfo();
getExtraPurchases();
getAddresses();
getRaised();
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
        <caption>Registered Report</caption>
        <tr>
            <th>Parent ID</th>
            <th>User ID</th>
            <th>Serial Number</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Grade</th>
            <th>Highest Track Passed</th>
            <th>KHK Trip</th>
            <th>Subsidy</th>
            <th>50% Amount</th>
            <th>Chidon Drive</th>
            <th>Coupon</th>
            <th>Paid</th>
            <th>Date Paid</th>
            <th>Amount to Fundraise</th>
        </tr>
        <?php
        foreach ($reg as $row) {
            echo "<tr><td>" . $row['parent_id'] . "</td><td>" . $row['user_id'] . "</td><td>" . $row['user_serial'] .
                "</td><td>" . $row['first'] . "</td><td>" . $row['last'] . "</td><td>" . $row['class_grade'] . "</td><td>" .
                $tracks[$row['user_id']] . "</td><td>";
            echo $row['khk_trip'] ? 'yes' : 'no';
            echo "</td><td>" . $row['payment_request'] . "</td><td>" . get50Percent($row['user_id'], $row['class_grade']) .
                "</td><td>" . $raised[$row['user_id']] . "</td><td>" . getCoupons($row['user_serial']) . "</td><td>" .
                $row['paid'] . "</td><td>" . $row['date_paid'] . "</td><td>" . $row['to_fundraise_5782'] . "</td></tr>";
        }
        ?>
    </table>
    <br /><br />
    <table>
        <caption>Shipping Paid</caption>
        <tr>
            <th>Parent ID</th>
            <th>First</th>
            <th>Last</th>
            <th>Amount Paid</th>
        </tr>
        <?php
        foreach ($shipping as $row) {
            echo "<tr><td>" . $row['admin_id'] . "</td><td>" . $row['first'] . "</td><td>" . $row['last'] .
                "</td><td>" . $row['chidon_shipping_paid'] . "</td></tr>";
        }
        ?>
    </table>
    <br /><br />
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
