<?php
ini_set('display_errors',1);
ini_set('memory_limit','256M');
//ini_set('max_execution_time',300);
require '../db.php';

$sql = "select * from auctions where auction_id = 70";
$result = mysql_query($sql);
$auction = mysql_fetch_assoc($result);
/*
$users = array();
$sql = "select u.user_id, u.school_id from users u
        join auction_user_prizes aup using (user_id) 
        where u.user_registered > 0
        and aup.auction_id = 70
        and school_id in (269,61,176,50,60,39,21,84,33,21)";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $users[$row['school_id']][] = $row['user_id'];
}

$info = array();
foreach ($users as $school => $ids) {
    foreach ($ids as $id) {
        $total = 0;
        $sql = "select pa.prize_points, aup.quantity, aup.user_id 
                from auction_user_prizes aup
                join auction_prizes ap using (prize_id)
                join prizes_auction pa using (prize_id)
                where aup.auction_id = 70
                and aup.user_id = " . $id;
        $result = mysql_query($sql);
        while ($row = mysql_fetch_assoc($result)) {
            $total += $row['prize_points'] * $row['quantity'];
        }
        $info[$school][$id] = $total;
    }
}

foreach ($info as $school => $other) {
    foreach ($other as $user => $total) {
        $sql = "insert into auction_70
                set user_id = " . $user . ",
                school_id = " . $school . ",
                points_used = " . $total;
        //echo $sql . "<br />";
        mysql_query($sql);
    }
}
echo 'done';
exit;
//echo "<pre>"; print_r($info); echo "</pre>"; exit;
*/
$users = array();
$sql = "select user_id from auction_70";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $users[] = $row['user_id'];
}

$points = array();
foreach ($users as $user) {
    $p = auctionPoints($user, $auction, true, true);
    $points[$user] = $p["cur"];
}

//echo "<pre>"; print_r($points); echo "</pre>"; exit;

foreach ($points as $user => $num) {
    $sql = "update auction_70
            set balance = points_used - " . $num . "
            where user_id = " . $user;
    mysql_query($sql);
}
echo 'done';
?>