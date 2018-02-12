<?php
include ("db.php");

$sql = "SELECT dtmm.user_id, dtmm.date_tasks_mission_id, dtm.end_date ";
$sql = $sql . "FROM date_tasks_mission_marks AS dtmm ";
$sql = $sql . "JOIN date_tasks_missions AS dtm USING (date_tasks_mission_id) ";
$sql = $sql . "WHERE (dtmm.mark_date=2010 OR dtmm.mark_date=2011)";
$query = mysql_query($sql);
while ($row = mysql_fetch_assoc($query))
{
	$update_sql = "UPDATE date_tasks_mission_marks SET mark_date=" . $row['end_date'] . " WHERE user_id=" . $row['user_id'] . " AND date_tasks_mission_id=" . $row['date_tasks_mission_id'];
	$update_query = mysql_query($update_sql);		
	if (!update_query)
		echo "UPDATE NOT PERFORMED<br />";
}
?>