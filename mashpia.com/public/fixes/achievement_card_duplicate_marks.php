<?php
require '../db.php';

$rows = [];
$sql = "SELECT 
            user_point_id,
            user_id,
            achievement_card_id,
            COUNT(*) AS total
        FROM
            pointsDB.user_points
        WHERE
            created > '2023-06-01'
                AND achievement_card_id > 0
        GROUP BY achievement_card_id
        HAVING total > 1";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $rows[] = $row;
}

$toDelete = [];
foreach ($rows as $row) {
    $sql = "SELECT 
                user_point_id
            FROM
                pointsDB.user_points
            WHERE
                achievement_card_id = " . $row['achievement_card_id'] . " 
                    AND user_id = " . $row['user_id'];
    $result = mysql_query($sql);
    $i = 0;
    while ($r = mysql_fetch_assoc($result)) {
        if (++$i > 1) {
            $toDelete[] = $r['user_point_id'];
        }
    }
}

$qrys = [];
foreach ($toDelete as $user_point_id) {
    $qry = "delete from pointsDB.user_points where user_point_id = " . $user_point_id;
    $qrys[] = $qry;
}

$deleted = 0;
foreach ($qrys as $qry) {
    echo $qry . "<br />";
//    if (mysql_query($qry)) $deleted++;
}

echo "Deleted: " . $deleted;