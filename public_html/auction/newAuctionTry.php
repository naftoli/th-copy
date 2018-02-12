<?php
ini_set('display_errors', 1);
ini_set('memory_limit','256M');
ini_set('max_execution_time', 300);
require '../db.php';

$auction_id = 70;

// top level 
$top = array(
    194,
    393,
    392
);

//set 1 ticket for all registered users
$tickets = array();
$sql = "select user_id from users where user_registered > 0";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $tickets[] = $row['user_id'];
}

$winners = array();
foreach ($top as $prize) {
    $num = count($tickets);
    $index = rand(0, ($num-1));
    $winner = $tickets[$index];
    $winners[$prize] = $tickets[$index];
    unset($tickets[$index]);
    $tickets = array_values($tickets);
}

$prizes = array();
$sql = "select * from auction_prizes
        join prizes_auction using (prize_id) 
        where auction_id = " . $auction_id . "
        and prize_id not in (" . implode(',', $top) . ")
        order by prize_points desc";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    if (!in_array($row['prize_id'], $prizes))
     $prizes[$row['prize_id']] = $row['available'];
}

$tickets = array();
$sql = "select * from auction_user_prizes where auction_id = " . $auction_id;
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $num = $row['quantity'];
    $prize = $row['prize_id'];
    for ($i = 0; $i < $num; $i++) {
        $tickets[$prize][] = $row['user_id'];
    }
}
//echo "<pre>";
//print_r($tickets);
//print_r($prizes);
//echo "</pre>";
//exit;

$winners = array();
$missing = array();
$usersWon = array();
foreach ($prizes as $prize => $available) {
    if (isset($tickets[$prize])) {
        do {
            $loop = true;
            while ($loop) {
                if (count($tickets[$prize])) {
                    $numTickets = count($tickets[$prize]);
                    $index = rand(0, ($numTickets - 1));
                    $winner = $tickets[$prize][$index];
                    if (in_array($winner, $usersWon)) {
                        $keys = array_keys($tickets[$prize], $winner);
                        foreach ($keys as $key) {
                            unset($tickets[$prize][$key]);
                        }
                        $tickets[$prize] = array_values($tickets[$prize]);
                    } else {
                        $loop = false;
                        $winners[$prize][] = $winner;
                        $usersWon[] = $winner;
                        $keys = array_keys($tickets[$prize], $winner);
                        foreach ($keys as $key) {
                            unset($tickets[$prize][$key]);
                        }
                        $tickets[$prize] = array_values($tickets[$prize]);
                    }
                } else {
                    $missing[] = $prize;
                    $loop = false;
                }
            } 
        } while (--$available > 0);
    } else {
        $missing[] = $prize;
    }
}

echo "<pre>";
echo "Winners:";
//print_r($winners);
echo "Missing:";
print_r($missing);
echo "</pre>";

foreach ($winners as $prize => $users) {
    foreach ($users as $user) {
        $sql = "insert into auction_winners
                set auction_id = 70,
                prize_id = " . $prize . ",
                user_id = " . $user . ",
                quantity = 1";
        mysql_query($sql);
    }
}