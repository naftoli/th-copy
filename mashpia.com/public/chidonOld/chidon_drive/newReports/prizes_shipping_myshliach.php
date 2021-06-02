<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';

$super = $admin_user['auth'] == 'super';

$year = GlobalSettings::getChidonYear();

$shipment_number = isset($_GET['num']) ? intval($_GET['num']) : 1;

$school_id = 61;

// maps shipment number to prize id's
$shipments = [
    1   =>  [
        7, 8, 9, 10, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 30, 31, 32, 34, 35,
        38, 39, 40, 44, 45, 48, 50, 53, 54, 59, 60, 62, 63
    ],
    4   =>  [
        29, 36, 37
    ]
];

$children = [];
$parents = [];
$sql = "select tc.*, u.first as uFirst, u.last as uLast, u.gender, a.* 
        from th_chidon tc 
        join users u using (user_id) 
        join admins a on a.admin_id = tc.parent_id 
        where tc.paid > 0 
        and tc.year = $year 
        and u.school_id = $school_id 
        order by u.first";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $children[$row['parent_id']][] = $row;
    $parents[$row['parent_id']] = $row;
}
$grandTotals = [];
$prizeInfo = [];
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf8">
    <title>Prizes Shipping Report</title>
    <link href="/admin_styles.css" rel="stylesheet" type="text/css"/>
    <style>
        tr, th, td {
            font-family: Arial, "Helvetica Neue", Helvetica, sans-serif;
            font-size: 14px;
            padding: 10px;
            border-bottom: 1px solid grey;
        }
        .warning {
            background-color: yellow;
        }
    </style>
</head>
<body>
<?php include($_SERVER["DOCUMENT_ROOT"].'/admin_header.php'); ?>
<h1>Prizes Shipping Report</h1>
<?php
foreach ($parents as $parent_id => $parent) {
    $name = $parent['first'] . ' ' . $parent['last'];
    $address = $parent['admin_address1'] . "<br />" . ($parent['admin_address2'] ? $parent['admin_address2'] . "<br />" : '') .
        $parent['admin_city'] . ', ' . $parent['admin_state'] . ' ' . $parent['admin_postal'] . "<br />" . $parent['admin_country'];
    ?>
    <h2><?= $name ?></h2>
    <p style="font-size: 18px;"><?= $address ?></p>
    <table>
        <tr>
            <th>Chidon ID</th>
            <th>Name</th>
            <th>Gifts / Prizes</th>
        </tr>
        <?php
        $totals = [];
        foreach ($children[$parent_id] as $child) {
            echo "<tr><td>" . $child['th_chidon_id'] . "</td><td>" .
                $child['uFirst'] . ' ' . $child['uLast'] . "</td><td>";
            // get yarmulka / bracelet
            if (!intval($child['shabbaton_maven'])) {
                if ($shipment_number == 1) {
                    if ($child['gender'] == 'M') {
                        $yarmulka = intval($child['yarmulka']);
                        if (!$yarmulka) {
                            $yarmulka = in_array($child['yarmulka'], [4, 5]) ? 4 : 5;
                        }
                        echo "Yarmulka Size: " . $yarmulka . "<br />";
                        // totals
                        if (!isset($totals['yarmulka'][$yarmulka])) $totals['yarmulka'][$yarmulka] = 0;
                        $totals['yarmulka'][$yarmulka]++;
                        if (!isset($grandTotals['yarmulka'][$yarmulka])) $grandTotals['yarmulka'][$yarmulka] = 0;
                        $grandTotals['yarmulka'][$yarmulka]++;
                    } else {
                        echo "Chidon Bracelet<br />";
                        // totals
                        if (!isset($totals['bracelet'])) $totals['bracelet'] = 0;
                        $totals['bracelet']++;
                        if (!isset($grandTotals['bracelet'])) $grandTotals['bracelet'] = 0;
                        $grandTotals['bracelet']++;
                    }
                }
                // get prizes
                if (intval($child['shabbaton_expert']) || intval($child['shabbaton_trophy'])) {
                    $prizes = [];
                    $sql = "select * from chidon_user_prizes cup 
                                    join chidon_prizes cp using (prize_id) 
                                    where cup.user_id = " . $child['user_id'];
                    $result = mysql_query($sql);
                    while ($row = mysql_fetch_assoc($result)) {
                        $prizes[] = $row;
                    }
                    foreach ($prizes as $prize) {
                        if (in_array($prize['prize_id'], $shipments[$shipment_number])) {
                            $he_name = false;
                            if ($prize['prize_id'] == 36 || ($prize['prize_id'] >= 44 && $prize['prize_id'] <= 60)) {
                                $he_name = true;
                                echo "<span class='warning'>";
                            }
                            echo $prize['prize_name'];
                            if ($prize['color']) echo " - Color: " . $prize['color'];
                            if ($prize['size']) echo "; Size: " . $prize['size'];
                            if ($he_name) echo "; He Name: " . $prize['he_name'] . "</span>";
                            echo "<br />";

                            // totals
                            if (!isset($totals['prizes'][$prize['prize_id']])) $totals['prizes'][$prize['prize_id']] = 0;
                            $totals['prizes'][$prize['prize_id']]++;
                            if (!isset($grandTotals['prizes'][$prize['prize_id']])) $grandTotals['prizes'][$prize['prize_id']] = 0;
                            $grandTotals['prizes'][$prize['prize_id']]++;

                            // set prize information
                            if (!isset($prizeInfo[$prize['prize_id']])) {
                                $prizeInfo[$prize['prize_id']] = [
                                    'name' => $prize['prize_name'],
                                    'color' => $prize['color'],
                                    'size' => $prize['size']
                                ];
                            }
                        }
                    }
                }
            }
            echo "</td></tr>";
        }
        ?>
    </table>
    <div style="page-break-after: always"></div>
    <?php
}
if ($super) {
    ?>
    <h2>Grand Totals</h2>
    <table>
        <tr>
            <th>Item</th>
            <th>Color</th>
            <th>Size</th>
            <th>Total</th>
        </tr>
        <?php
        echo "<tr><td>Chidon Bracelet</td><td></td><td></td><td>" . $grandTotals['bracelet'] . "</td></tr>";
        foreach ($grandTotals['yarmulka'] as $size => $total) {
            echo "<tr><td>Yarmulka</td><td></td><td>$size</td><td>$total</td></tr>";
        }
        ksort($grandTotals['prizes']);
        foreach ($grandTotals['prizes'] as $prize_id => $total) {
            echo "<tr><td>" . $prizeInfo[$prize_id]['name'] . "</td><td>" . $prizeInfo[$prize_id]['color']
                . "</td><td>" . $prizeInfo[$prize_id]['size'] . "</td><td>" . $total . "</td></tr>";
        }
        ?>
    </table>
<?php } ?>
</body>
</html>

