<?
require_once 'db.php';
$sql = "select * from date_tasks where date_tasks_mission_id = 48 order by date_task_id desc limit 1";
$result = mysql_query($sql);
$row = mysql_fetch_assoc($result);
$taskID = $row['date_task_id'];

mysql_query("set autocommit = 0");
mysql_query("begin");

$success = false;
$sql = "delete from date_tasks where date_task_id > " . $taskID;
if (mysql_query($sql)) {
	
	$sql = "delete from user_tasks where task_id > " . $taskID;
	if (mysql_query($sql)) {
	
		$sql = "delete from date_tasks_marks where date_task_id > " . $taskID;
		if (mysql_query($sql)) {
			
			$sql = "delete from date_tasks_mission_marks where date_tasks_mission_id > 48";
			if (mysql_query($sql)) {
	
				$sql = "alter table date_tasks auto_increment = " . ++$taskID;
				if (mysql_query($sql)) {
					$success = true;
				}
			}
		}
	}
}

if ($success) {
	mysql_query("commit");
	echo "Commited";
} else {
	echo mysql_error();
	mysql_query("rollback");
	echo "Rolled Back";	
}
mysql_query("set autocommit = 1");
mysql_close();
?>