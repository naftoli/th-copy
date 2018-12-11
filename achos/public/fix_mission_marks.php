<?
require_once('db.php');

$deleted = array();

$sql = "select user_id from users where user_registered > 0";
$result = mysql_query($sql);

while ($row = mysql_fetch_assoc($result)) {	
	$sql2 = "SELECT date_task_id, count( date_task_id ) AS total, needed, date_tasks_mission_id
			FROM date_tasks
			JOIN date_tasks_marks
			USING ( date_task_id )
			WHERE user_id = " . $row['user_id'] . "
			GROUP BY date_task_id
			ORDER BY total DESC";

	$result2 = mysql_query($sql2);
	while ($row2 = mysql_fetch_assoc($result2)) {
		if ($row2['total'] < $row2['needed']) {
			$id = $row2['date_tasks_mission_id'];
			$sql3 = "delete from date_tasks_mission_marks where user_id = " . $row['user_id'] . " and date_tasks_mission_id = " . $id;
			echo $sql3 . "<br />";
			continue;
			/*
			if (mysql_query($sql3)) {
				$deleted[] = $id;
			}
			*/
		}
	}
	/*
	echo "Deleted for " . $row['user_id'] . ":<br />";
	foreach ($deleted as $id) echo $id . "<br />";
	echo "<br /><br />";
	*/
}
?>