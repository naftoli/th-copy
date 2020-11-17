<?php
require_once( dirname(__FILE__) . "/../../db.php" );
require_once( dirname(__FILE__) . "/../classes/TotalWeeklyTasks.php" );

// create the object
$totalWeeklyTasks = new TotalWeeklyTasks(0, unixtojd());
// set the start date
$totalWeeklyTasks->start_date = 2459099;
// generate the week_dates
$totalWeeklyTasks->get_week_dates();

$users_query = mysql_query(
    "SELECT user_id, first, last FROM users WHERE user_registered IS NOT NULL ORDER BY last, first"
);

$user_count = mysql_num_rows( $users_query );
while( $user = mysql_fetch_assoc( $users_query ) ){
    $totalWeeklyTasks->user_id = $user['user_id'];
    $total = $totalWeeklyTasks->total_weeks_with_task( true );
    echo $user['last'] . ", " . $user['first'] . " - " . $total . "\n";
}
echo "done.";