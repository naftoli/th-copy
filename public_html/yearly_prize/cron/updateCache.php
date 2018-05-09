<?php
require_once( dirname(__FILE__) . "/../../db.php" );
require_once( dirname(__FILE__) . "/../classes/TotalWeeklyTasks.php" );
// get the dates for the last week.
$current_parsha_query = mysql_query(
    "SELECT * FROM parshos WHERE end <= " . unixtojd() . " ORDER BY end DESC LIMIT 1;"
);
$current_parsha = mysql_fetch_assoc( $current_parsha_query );

echo "Updating Cache for Parshas " . $current_parsha['name'] . " ( registered users only ):\n\n";

// create the object
$totalWeeklyTasks = new TotalWeeklyTasks(0, $current_parsha['end']);
// set the start date
$totalWeeklyTasks->start_date = $current_parsha['start'];
// generate the week_dates
$totalWeeklyTasks->get_week_dates();

$users_query = mysql_query(
    "SELECT user_id, first, last FROM users WHERE user_registered IS NOT NULL ORDER BY last, first"
);

$user_count = mysql_num_rows( $users_query );
$time_start = microtime(true);

while( $user = mysql_fetch_assoc( $users_query ) ){
    $totalWeeklyTasks->user_id = $user['user_id'];
    $total = $totalWeeklyTasks->total_weeks_with_task( true ); // run for the current week (the only week)

    echo $user['last'] . ", " . $user['first'] . " - " . $total . "\n";
}
$time_end = microtime(true);

echo "\n\nUpdated $user_count users in " . ($time_end - $time_start)/60 . " Minutes\n\n";