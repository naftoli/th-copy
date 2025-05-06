<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

require_once "../db.php";
require_once '../class.globalSettings.php';
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
//    echo "<pre>"; print_r($users); echo "</pre>";
    foreach ($users as $school => $info) {
        echo "<p>Choosing random users from school $school</p>";
        $user_ids = array_keys($info);
        echo "<p>Pool of user IDs:</p>";
        echo '<pre>';
        print_r($user_ids);
        echo '</pre>';
        for ($i = 0; $i < 3; $i++) {
            echo "<p>Random user $i</p>";
            $key = array_rand($user_ids);
            $user_id = $user_ids[$key];
            if (alreadyWon($user_id, $raffle->type) && count($user_ids) > 1) {
                echo "<p>" . $user_id . " already won</p>";
                unset($user_ids[$key]); // remove the user from the pool
                $i--;
                continue;
            }
            echo "<p>" . $user_id . "</p>";
        }
    }
}