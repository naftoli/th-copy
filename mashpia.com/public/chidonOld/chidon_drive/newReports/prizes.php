<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once __DIR__ . '/../../../header.php';
require_once __DIR__ . '/../../../class.globalSettings.php';
$year = GlobalSettings::getChidonRegYear();

if ($admin_user['auth'] != 'super') {
    echo "No permission.";
    exit;
}

$info = [];
$sql = "SELECT 
            p.prize_id,
            prize_name,
            size,
            color,
            quantity,
            COUNT(*) AS purchased
        FROM
            chidon_prizes p
                JOIN
            chidon_user_prizes USING (prize_id)
        WHERE
            p.year = $year
        GROUP BY prize_id";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $info[] = $row;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf8" />
    <title>Prizes Report</title>
    <style>
        tr, th, td {
            font-family: Arial, "Helvetica Neue", Helvetica, sans-serif;
            font-size: 14px;
            padding: 10px;
        }
    </style>
</head>
<body>
    <h1>Prizes Report</h1>
    <table>
        <tr>
            <td>Prize Name</td>
            <th>Size</th>
            <th>Color</th>
            <th>Quantity</th>
            <th>Purchased</th>
            <th>Amount Left</th>
        </tr>
        <?php
        foreach ($info as $prize) {
            echo "<tr><td>" . $prize['prize_name'] . "</td><td>" . $prize['size'] . "</td><td>" . $prize['color'] .
                "</td><td>" . $prize['quantity'] . "</td><td>" . $prize['purchased'] . "</td><td>" .
                ($prize['quantity'] - $prize['purchased']) . "</td></tr>";
        }
        ?>
    </table>
</body>
</html>
