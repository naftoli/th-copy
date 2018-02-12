<?
require ("../db.php");

$sql = "
SELECT *
FROM date_tasks AS dt
LEFT JOIN date_tasks_missions AS dtm
USING ( date_tasks_mission_id )
WHERE dtm.subject_id IS NULL";

$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$id = $row['date_tasks_mission_id'];
	
	$ins = "insert into date_tasks_missions values ($id, null, null, null, null, 'unavailable', null, null, null, null, null, null, 1)";
	echo $ins . "<br />";
}
?>