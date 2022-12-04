<?php
$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';

if (!$admin_user || $admin_user['auth'] != 'super') {
    echo "Permission denied.";
    exit;
}

require $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

$info = [];
$stmt = $MASHPIA_DB->prepare("
    SELECT u.first, u.last, tc.paid, IFNULL( SUM(subsidy_amount), 0 ) as total_raised 
    FROM users u 
    JOIN th_chidon tc USING (user_id) 
    LEFT JOIN chidon_user_subsidies cs ON (tc.user_id = cs.user_id and tc.year = cs.chidon_year)
    WHERE tc.year = :year 
    GROUP BY tc.user_id 
    ORDER BY u.last, u.first
");
if ( $stmt->execute([':year' => $year]) ) $info = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf8" />
        <style>
            tr, th, td {
                font-family:Arial, Helvetica, sans-serif;
                font-size: 14px;
                padding: 5px;
            }
        </style>
    </head>
    <body>
        <table>
            <tr>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Raised</th>
                <th>Registration Paid</th>
                <th>Total</th>
                <?php if ($year < 5783) : ?>
                    <th>Rohr</th>
                <?php endif; ?>
            </tr>
            <?php
            $totals['raised'] = 0;
            $totals['paid'] = 0;
            $totals['rohr'] = 0;
            foreach ($info as $row) {
                $first = $row['first'];
                $last = $row['last'];
                $raised = floatval($row['total_raised']);
                $paid = floatval($row['paid']);
                $rohr = 0;
                if (($raised + $paid) >= 275) {
                    $rohr = 350 - ($raised + $paid);
                    if ($rohr < 0) $rohr = 0;
                }
                $totals['raised'] += $raised;
                $totals['paid'] += $paid;
                $totals['rohr'] += $rohr;
                echo "<tr><td>" . $first . "</td><td>" . $last . "</td><td>" . $raised . "</td><td>" . $paid . "</td><td>" . ($raised + $paid);
                if ($year < 5783) echo "</td><td>" . $rohr;
                echo "</td></tr>";
            }
            echo "<tr><th colspan='2'>Totals:</th><th>" . number_format($totals['raised']) . "</th><th>" . number_format($totals['paid']) . 
                "</th><th>" . number_format($totals['raised'] + $totals['paid']);
            if ($year < 5783) echo "</th><th>" . number_format($totals['rohr']);
            echo "</th></tr>";
            ?>
        </table>
    </body>
</html>