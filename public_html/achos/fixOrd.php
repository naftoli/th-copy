<?
require_once 'db.php';
$tasks = array();
$sql = "select date_task_id from date_tasks where date_tasks_mission_id >= 49";
$result = mysql_query( $sql );
while ($row = mysql_fetch_assoc( $result )) {
	$tasks[] = $row['date_task_id'];
}

$ord = 1;
foreach ($tasks as $id) {
	$sql = "update date_tasks set ord = " . $ord++ . " where date_task_id = " . $id;
	//echo $sql;
	mysql_query( $sql );
}