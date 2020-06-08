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
    JOIN chidon_user_subsidies cs ON (cs.user_id = tc.user_id and tc.year = cs.chidon_year)
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
                <th>Rohr</th>
            </tr>
            <?php
            foreach ($info as $row) {
                $first = $row['first'];
                $last = $row['last'];
                $raised = floatval($row['total_raised']);
                $paid = floatval($row['paid']);
                $rohr = 0;
                if (($raised + $paid) >= 225) {
                    $rohr = 350 - ($raised + $paid);
                    if ($rohr < 0) $rohr = 0;
                }
                echo "<tr><td>" . $first . "</td><td>" . $last . "</td><td>" . $raised . "</td><td>" . $paid . "</td><td>" . ($raised + $paid) . 
                    "</td><td>" . $rohr . "</td></tr>";
            }
            ?>
        </table>
    </body>
</html>