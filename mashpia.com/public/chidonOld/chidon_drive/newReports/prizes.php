<?php
$admin_auth = ['school'];
require_once __DIR__ . '/../../../db.php';

if ($admin_user['auth'] != 'super') {
    echo "No permission.";
    exit;
}

$info = [];
$sql = "select * from chidon_prizes";
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
            <th>Already Purchased</th>
        </tr>
        <?php
        foreach ($info as $prize) {
            echo "<tr><td>" . $prize['prize_name'] . "</td><td>" . $prize['size'] . "</td><td>" . $prize['color'] .
                "</td><td>" . $prize['quantity'] . "</td><td>" . $prize['purchased'] . "</td></tr>";
        }
        ?>
    </table>
</body>
</html>
