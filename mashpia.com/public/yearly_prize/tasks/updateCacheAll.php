<?php
ini_set('display_errors', 1);
ini_set('max_execution_time', 600);
require_once( dirname(__FILE__) . "/../../db.php" );
require_once( dirname(__FILE__) . "/../classes/TotalWeeklyTasks.php" );

// create the object
$totalWeeklyTasks = new TotalWeeklyTasks(0, unixtojd());
// generate the week_dates
$totalWeeklyTasks->get_week_dates();

$limit = isset($_GET['limit']) ? $_GET['limit'] : 0;
$users_query = mysql_query(
    "SELECT user_id, first, last FROM users WHERE user_registered IS NOT NULL ORDER BY last, first limit $limit, 500"
);

$user_count = mysql_num_rows( $users_query );
while ( $user = mysql_fetch_assoc( $users_query ) ) {
    $totalWeeklyTasks->user_id = $user['user_id'];
    $total = $totalWeeklyTasks->total_weeks_with_task( true );
    echo $user['last'] . ", " . $user['first'] . " - " . $total . "\n";
}
echo "done.";