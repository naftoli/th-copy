<?php
ini_set('display_errors',1);
ini_set('memory_limit','256M');
ini_set('max_execution_time',300);
require '../db.php';

/*
$prizes = array();
foreach ($data as $prize => $school) {
    $prizes[] = $prize;
}
*/
$users = array();
$sql = "select user_id from users where user_registered > 0";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $users[] = $row['user_id'];
}

$prizes = array(421 => 15);

$give = array();
foreach ($prizes as $prize => $qty) {
    do {
        $random = rand(0, count($users));
        $give[] = $users[$random];
        unset($users[$random]);
        $users = array_values($users);
    } while (--$qty > 0);
}

$i = 0;
foreach ($prizes as $prize => $qty) {
    do {
        $sql = "insert into auction_user_prizes
                set auction_id = 70,
                prize_id = " . $prize . ",
                user_id = " . $give[$i++] . ",
                quantity = 1";
        mysql_query($sql);
    } while (--$qty > 0);
}
/*
$points = array();
$sql = "select prize_id, prize_points from prizes_auction where prize_id in (" . implode(',', $prizes) . ")";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $points[$row['prize_id']] = $row['prize_points'];
}

// calculate balances
$sql = "select * from auctions where auction_id = 70";
$result = mysql_query($sql);
$auction = mysql_fetch_assoc($result);

$users = array();
$sql = "select user_id from users where user_registered > 0";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $users[] = $row['user_id'];
}

$points = array();
foreach ($users as $user) {
    $p = auctionPoints($user, $auction, false);
    $points[$user] = $p["cur"];
}

$used = array();
    $sql = "select * from auction_user_prizes aup
            join prizes_auction pa using (prize_id) 
            where auction_id = 70";
    $result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    if (isset($used[$row['user_id']])) {
        $used[$row['user_id']] += $row['prize_points'] * $row['quantity'];
    } else {
        $used[$row['user_id']] = $row['prize_points'] * $row['quantity'];
    }
}

$balances = array();
foreach ($users as $user) {
    $bal = $points[$user];
    if (isset($used[$user])) {
        $bal -= $used[$user];
    }
    $balances[$user] = $bal;
}

/*
$info = array();
$sql = "select * from auction_70";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $info[$row['school_id']][$row['user_id']] = $row['balance'];
}

foreach ($prizes as $prize) {
    $pointsNeeded = $points[$prize];
    foreach ($balances as $user => $bal) {
        $num = 0;
        $cur = $bal;
        while ($cur >= $pointsNeeded && $num < 21) {
            $num++;
            $cur -= $pointsNeeded;
        }
        if ($num) {
            
            $sql = "delete from auction_user_prizes
                    where auction_id = 70
                    and user_id = " . $user . "
                    and prize_id = " . $prize;
            mysql_query($sql) or die(mysql_error());
            
            $sql = "insert into auction_user_prizes
                    set auction_id = 70,
                    user_id = " . $user . ",
                    prize_id = " . $prize . ",
                    quantity = " . $num;
            //echo $sql . "<br />";
            mysql_query($sql) or die(mysql_error());
        }
    }
}
*/
echo 'done';
?>