<?php
require_once "../db.php";
require_once "../raffles/shared/classes/Raffle.php";
use raffles\weekly\Raffle as Raffle; // use the raffle from its namespace

$school_id = 40;
$raffles = [423, 424, 425];
foreach ($raffles as $raffle_id) {
    echo "<h3>Loading raffle $raffle_id</h3>";
    $raffle = Raffle::load($raffle_id);
    $users = $raffle->get_eligable_user_ids(false, false, true, false, $school_id);
    foreach ($users as $school => $user_ids) {
        for ($i = 0; $i < 5; $i++) {
            echo "<p>Choosing random user from school $school</p>";
            $user = $user_ids[array_rand($user_ids)];
            echo "<p>" . $user['user_id'] . "</p>";
        }
    }
}