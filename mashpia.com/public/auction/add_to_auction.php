<?php
require '../db.php';

$auction_id = 70;
$users = array();
$sql = "select user_id from users u
        join classes c using (class_id) 
        where u.user_registered > 0";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $users[] = $row['user_id'];
}

$missing = array(
    381 => 31,
    421 => 1,
    279 => 99
);

$winners = array();
$num = count($users);
for ($i = 0; $i < 131; $i++) {
    $winners[] = rand(0, $num);
}

$i = 0;
$qrys = array();
foreach ($missing as $prize => $qty) {
    do {
        $qrys[] = "insert into auction_winners
                    set auction_id = " . $auction_id . ",
                    prize_id = " . $prize . ",
                    user_id = " . $users[$winners[$i++]] . ",
                    quantity = 1";
    } while (--$qty > 0);
}

$errors = array();
foreach ($qrys as $qry) {
    if (!mysql_query($qry)) {
        $errors[] = $qry;
    }
}
echo 'done.';

echo "<pre>";
print_r($errors);
echo "</pre>";