<?php
ini_set('display_errors', 1);
ini_set('max_execution_time', 300);

require '../db.php';
require '../class.points.php';

$users = array();
$sql = "select user_id from auction_user_prizes
        where auction_id = 77
        and prize_id = 392
        and user_id not in (
            select user_id from auction_winners where auction_id > 70
        )
        group by user_id";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $users[] = $row['user_id'];
}

$users2 = array();
$sql = "select user_id from users 
        where user_registered > 0
        and user_id not in (
        select user_id from auction_user_prizes 
        where auction_id = 77)
        and user_id not in (
        select user_id from auction_winners where auction_id > 70)";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $users2[] = $row['user_id'];
}

foreach ($users2 as $id) {
    // check if user has at least 500 points
    $p = new Points( $id );
    $points = $p->getTotalThisYear();
    if ($points >= 500) $users[] = $id;
}

//echo "<pre>"; print_r($users);

$max = count($users);
$winner = rand(0, --$max);
echo $users[$winner];