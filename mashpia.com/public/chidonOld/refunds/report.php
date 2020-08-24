<?php
$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';

if ($admin_user['auth'] != 'super') {
    echo "No permissions to be here.";
    exit;
}

require $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
$year = 5780;

$stmt = $MASHPIA_DB->prepare("
    SELECT * FROM chidon_refunds WHERE year = :year
");
$res = $stmt->execute([':year' => $year]);
if ($res) {
    $rows = $stmt->fetchAll();
    $totals['donations'] = 0;
    $totals['refunds'] = 0;
    $totals['num_donation_50'] = 0;
    $totals['gave50_all'] = 0;
    $totals['gave50_some'] = 0;
    $totals['gave50_none'] = 0;
    foreach ($rows as $row) {
        $totals['donations'] += floatval($row['donation']);
        $totals['refunds'] += floatval($row['refund']);
        $totals['num_donation_50'] += $row['num_donation_50'];
        $num_children = $row['num_children'];
        $num_50 = intval($row['num_donation_50']);
        if ($num_50 == $num_children) $totals['gave50_all']++;
        else if ($num_50 > 0) $totals['gave50_some']++;
        else $totals['gave50_none']++;
    }
} else {
    $rows = [];
}
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Chidon Refund Report</title>
        <meta charset="utf8" />
        <style>
            body, table, tr, th, td {
                font-size: 14px;
                font-family: Arial, "Helvetica Neue", Helvetica, sans-serif;
            }
            tr, th, td {
                padding: 5px;
                border: 1px solid black;
            }
        </style>
    </head>
    <body>
        <h1>Chidon Refund Report</h1>
        <?php
        if (empty($rows)) echo "No refunds have been requested yet.";
        else {
        ?>
        <h3>
            Total Refund Requested (Needed in Bank): $<?= number_format($totals['refunds'], 2); ?><br />
            Total Donations: $<?= number_format($totals['donations'], 2); ?><br />
            Total number of kids gave $50: <?= $totals['num_donation_50'] ?><br />
            Total parents gave $50 for all kids: <?= $totals['gave50_all'] ?><br />
            Total parents gave $50 for some kids: <?= $totals['gave50_some'] ?><br />
            Total parents DID NOT GIVE $50 for ANY kids: <?= $totals['gave50_none'] ?><br />
        </h3>
        <table>
            <tr>
                <th>Parent ID</th>
                <th>Refund Requested</th>
                <th>Donation given</th>
                <th>Number of Kids in Shabbaton</th>
                <th>Number of Kids giving $50</th>
                <th>Submitted</th>
            </tr>
            <?php
            foreach ($rows as $row) {
                echo "<tr><td>" . $row['admin_id'] . "</td><td>" . $row['refund'] . "</td><td>" . $row['donation'] .
                    "</td><td>" . $row['num_children'] . "</td><td>" . $row['num_donation_50'] . "</td><td>" .
                    $row['created'] . "</td></tr>";
            }
            ?>
        </table>
        <?php } ?>
    </body>
</html>
