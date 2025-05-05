<?php
require_once "../db.php";
require_once "../raffles/shared/classes/Raffle.php";
use raffles\weekly\Raffle as Raffle; // use the raffle from its namespace

function alreadyWon($user, $type) {
    $year = GlobalSettings::getCurrentYear();
    $sql = "select * from raffle_winners where user_id = $user and raffle_id in (
            select raffle_id from raffles where type = '$type' and year = $year
        )";
    $result = mysql_query($sql);
    return mysql_num_rows($result) > 0;
}

$school_id = 0;
$raffles = [435];
foreach ($raffles as $raffle_id) {
    echo "<h3>Loading raffle $raffle_id</h3>";
    $raffle = Raffle::load($raffle_id);
    $users = $raffle->get_eligable_user_ids(false, false, true); // no specific user but do show the log
    foreach ($users as $school => $user_ids) {
        echo "<p>Choosing random users from school $school</p>";
        for ($i = 0; $i < 5; $i++) {
            echo "<pre>"; print_r($user_ids); echo "</pre>";
            $user = $user_ids[array_rand($user_ids)];
            if (alreadyWon($user['user_id'], $raffle->type)) {
                echo "<p>User " . $user['user_id'] . " already won</p>";
                continue;
            }
            echo "<p>" . $user['user_id'] . "</p>";
        }
    }
}