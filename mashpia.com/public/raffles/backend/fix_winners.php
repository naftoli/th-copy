<?php
$admin_auth = ['school'];
require_once '../../header.php';

$winners = [];
$sql = "SELECT 
            *
        FROM
            mashpia_backup.raffle_winners
        WHERE
            raffle_id IN (306 , 307, 308)";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $winners[] = "insert into raffle_winners 
                set raffle_id = " . $row['raffle_id'] . ", 
                prize_id = " . $row['prize_id'] . ", 
                user_id = " . $row['user_id'];
}

foreach ($winners as $sql) {
    mysql_query($sql);
}
echo "done.";