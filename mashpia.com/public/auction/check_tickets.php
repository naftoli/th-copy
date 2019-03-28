<?php
ini_set('max_execution_time', 600);
ini_set('display_errors', 1);
require '../db.php';

echo "<pre>";
$auction = array();
$sql = "select * from auctions where auction_id = 70";
$result = mysql_query($sql);
$auction = mysql_fetch_assoc($result);

$users = array();
$sql = "select user_id from users where user_registered > 0";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $users[] = $row['user_id'];
}
//print_r($users); exit;

$info = array();
foreach ($users as $user) {
    $sql = "select *  
            from auction_user_prizes 
            where auction_id = 70
            and user_id = " . $user;
    $result = mysql_query($sql);
    if (! mysql_num_rows($result)) {
        $info[] = $user;
    }
}

$points = array();
foreach ($info as $user) {
    $p = auctionPoints($user, $auction);
    $points[$user] = $p['cur'];
}
print_r($points);

print_r($info);
echo "</pre>";