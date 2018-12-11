<?php
ini_set('display_errors', 1);
require '../db.php';

$auction_id = 77;
$prizeInfo = array();
$prizeIDs = array();
$sql = "select * from prizes_auction pa 
        join auction_prizes ap using (prize_id) 
        where ap.auction_id = " . $auction_id . "
        order by prize_name";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $prizeInfo[] = $row;
    $prizeIDs[] = $row['prize_id'];
}

$schoolList = array();
$schoolTicketsInfo = array();
foreach ($prizeIDs as $id) {
    $sql = "select school_id, count(user_id) as total from auction_user_prizes 
            join users u using (user_id) 
            where auction_id = " . $auction_id . "  
            and prize_id = " . $id . " 
            and user_id not in (
            select user_id from auction_winners where auction_id > 70) 
            and u.school_id is not null 
            group by u.school_id ";
    $result = mysql_query($sql);
    while ($row = mysql_fetch_assoc($result)) {
        $schoolTicketsInfo[$id][$row['school_id']] = $row['total'];
        if (!in_array($row['school_id'], $schoolList)) $schoolList[] = $row['school_id'];
    }
}
ksort($schoolTicketsInfo);

$schoolInfo = array();
$sql = "select school_id, school_name from schools where school_id in (" . implode(',', $schoolList) . ")";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $schoolInfo[$row['school_id']] = $row['school_name'];
}
if (isset($_GET['debug'])) {
    echo "<pre>"; print_r($schoolTicketsInfo); echo "</pre>"; exit;
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf8" />
        <style>
            tr, th, td {
                padding: 5px;
                font-size: 12px;
                font-family: sans-serif;
            }
        </style>
    </head>
    
    <body>
        <table>
            <tr>
                <th>Prize ID</th>
                <th>Prize Name</th>
                <th>Prize Points</th>
                <th>Prize Qty</th>
                <?php
                foreach ($schoolInfo as $id => $school) {
                    echo "<th>" . $school . " (" . $id . ")</th>";
                }
                ?>
            </tr>
            <?php
            foreach ($prizeInfo as $row) {
                echo "<tr><td>" . $row['prize_id'] . "</td><td>" . $row['prize_name'] . "</td><td>" . $row['prize_points'] . "</td><td>" . $row['available'] . "</td>";
                foreach ($schoolInfo as $id => $school) {
                    if (isset($schoolTicketsInfo[$row['prize_id']][$id])) {
                        echo "<td>" . $schoolTicketsInfo[$row['prize_id']][$id] . "</td>";
                    } else {
                        echo "<td>0</td>";
                    }
                }
            }
            ?>
        </table>
    </body>
</html>