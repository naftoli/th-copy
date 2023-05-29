<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';

require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

$raised = [];
$sql = "select * from family_raised where year = $year order by admin_id";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $raised[$row['admin_id']] = $row['amount'];
}

$sweaters = [];
$sql = "select fs.*, u.first from family_sweaters fs join users u using (user_id) where year = $year order by admin_id";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $sweaters[$row['admin_id']][] = $row;
}

$admins = [];
$sql = "select * from admins where admin_id in (" . implode(',', array_keys($raised)) . ") order by admin_id";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $admins[$row['admin_id']] = $row;
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf8" />
        <title>Sweater Report</title>
        <link href="../../admin_styles.css" rel="stylesheet" type="text/css">
        <style>
            tr, th, td {
              padding: 10px;
              font-size: 14px;
              border-bottom: 1px solid grey;
            }
        </style>
    </head>
    <body>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/admin_header.php'); ?>
    <h1>Sweater Report</h1>
    <table>
        <tr>
            <th>Family ID</th>
            <th>Parent</th>
            <th>Raised</th>
            <th>Sweaters</th>
        </tr>
        <?php
        $totals = [];
        $totals['amount'] = 0;
        $totals['sweaters'] = 0;
        foreach ($admins as $admin_id => $admin_info) {
            $total = $raised[$admin_id];
            $sweater_info = $sweaters[$admin_id];
            echo "<tr><td>" . $admin_id . "</td><td>" . ($admin_info['first'] . ' ' . $admin_info['last']) . "</td><td>" .
                $total . "</td><td>";
            foreach ($sweater_info as $sweater) {
                $name = $sweater['first'];
                $color = $sweater['color'];
                $size = $sweater['size'];
                $rank = $sweater['rank'];
                echo "$name ($color, $size, $rank)<br />";
                // totals
                if (isset($totals['sweater_info'][$size][$color])) {
                    $totals['sweater_info'][$size][$color]++;
                } else {
                    $totals['sweater_info'][$size][$color] = 1;
                }
            }
            echo "</td></tr>";
            $totals['amount'] += $total;
            $totals['sweaters'] += count($sweater_info);
        }
        ?>
    </table>
    <h2>Totals</h2>
    <p>Total amount raised: <?= $totals['amount'] ?></p>
    <p>Total sweaters: <?= $totals['sweaters'] ?></p>
    <h2>Details</h2>
    <table>
        <tr>
            <th>Sweater Size</th>
            <th>Sweater Color</th>
            <th>Total</th>
        </tr>
        <?php
        foreach ($totals['sweater_info'] as $size => $colors) {
            foreach ($colors as $color => $total) {
                echo "<tr><td>$size</td><td>$color</td><td>$total</td></tr>";
                if (isset($grandTotals[$size][$color])) {
                    $grandTotals[$size][$color] += $total;
                } else {
                    $grandTotals[$size][$color] = $total;
                }
            }
        }
        ?>
    </table>
    </body>
</html>
