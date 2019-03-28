<?php
require 'db.php';

$users = array();
$sql = "select user_id from users where user_registered > 0";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $users[] = $row['user_id'];
}

$totals = array();
for ($i = 1; $i < 15; $i++) {
    $totals[$i] = 0;
}

foreach ($users as $id) {
    $sql = "select max(rank_ord) as total from rank_marks where user_id = " . $id;
    $result = mysql_query($sql);
    if (mysql_num_rows($result) > 0) {
        $row = mysql_fetch_assoc($result);
        $totals[$row['total']]++;
    } else {
        echo "User : " . $id . " has no rank.<br />";
    }
}

echo "<pre>";
//print_r($totals);
echo "</pre>";

$ranks = array();
$sql = "select rank_ord, rank_name from ranks";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $ranks[$row['rank_ord']] = $row['rank_name'];
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf8" />
        <style>
            body {
                font-family: sans-serif;
            }
            tr, th, td {
                font-size: 12px;
                padding: 5px;
            }
        </style>
    </head>
    <body>
        <p>Totals By Rank (as of today)</p>
        <table>
            <tr>
                <th>Rank</th>
                <th>Total</th>
            </tr>
            <?php
            $gtotal = 0;
            foreach ($totals as $rank => $total) {
                $gtotal += $total;
                echo "<tr><td>" . $ranks[$rank] . "</td><td>" . $total . "</td></tr>";
            }
            ?>
        </table>
        <?//=$gtotal?>
    </body>
</html>
