<?php
ini_set('display_errors', 1);
ini_set('max_execution_time', 300);
require_once( dirname(__FILE__) . "/../../db.php" );
require_once( dirname(__FILE__) . "/../classes/TotalWeeklyTasks.php" );

// create the object
$totalWeeklyTasks = new TotalWeeklyTasks(0, unixtojd());
// generate the week_dates
$totalWeeklyTasks->get_week_dates();

$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 100;
$offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
$sql = "SELECT user_id, first, last FROM users WHERE user_registered IS NOT NULL ORDER BY last, first limit $limit offset $offset";
$users_query = mysql_query($sql) or die(json_encode(["errors" => "invalid params"]));

$message = "";
while ( $user = mysql_fetch_assoc( $users_query ) ) {
    $totalWeeklyTasks->user_id = $user['user_id'];
    $totalWeeklyTasks->update_all_weeks();
    $message .= $user['last'] . ", " . $user['first'] . "\n";
}
$user_count = mysql_num_rows($users_query);
$message .=  "done $user_count users.\n";
$done = $user_count !== $limit;

echo json_encode(["message" => $message, "done" => $done]);
