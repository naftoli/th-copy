<?php
require '../db.php';

$schools = array(63,112,11,9,81,42,9,58,9,105);

$info = array();
$sql = "select user_id, school_id, gender from users where user_registered > 0 and school_id in (" . implode(',', $schools) . ")";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    if ($row['school_id'] == 110 && $row['gender'] != 'M') continue;
    $info[$row['school_id']][] = $row['user_id'];
}

$winners = array();
foreach ($schools as $school) {
    //do {
        $max = count($info[$school]);
        $random = rand(0,$max);
        $winners[$school][] = $info[$school][$random];
        unset($info[$school][$random]);
    //} while (--$num > 0);
}

echo "<pre>"; print_r($winners); echo "</pre>";
foreach ($winners as $school => $students) {
    foreach ($students as $user) {
        $sql = "insert into auction_winners
                set auction_id = 72,
                user_id = " . $user . ", 
                prize_id = 1,
                quantity = 1,
                display_order = 0";
        mysql_query($sql);
    }
}