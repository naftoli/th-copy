<?php
include("db.php");

$months = array("Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec");

$group_id = $_GET["group_id"];	
$group_name = $_GET["group_name"];	
			
$sql = "SELECT gtd.group_task_date_id, gtd.camp_task_id, gtd.task_date, ct.task_name, ct.points, cm.mission_name FROM group_task_dates AS gtd JOIN camp_tasks AS ct USING (camp_task_id) JOIN camp_missions AS cm USING (camp_mission_id) WHERE group_id=" . $group_id . " AND completed=1 ORDER BY task_date";
$query = mysql_query($sql);
?>

<H1><?=$group_name;?></H1>

<TABLE>
	<TH>DATE</TH>
	<TH>MISSION</TH>
	<TH>TASK</TH>
	<TH>POINTS</TH>
<?
while ($row = mysql_fetch_assoc($query)) {
	$greg_date = jdtogregorian($row["task_date"]);
	$date_array = split("/", $greg_date); 
	$month = $months[$date_array[0] - 1];
	$day = $date_array[1];
	$year = $date_array[2];

	//echo "<TR><TD>" . $month . " " . $day . ", " . $year . "</TD><TD>" . $row["mission_name"] . "</TD><TD>" . $row["task_name"] . "</TD><TD>" . $row["points"] . "</TD></TR>";	
	//echo "<TR><TD>" . $row["task_date"] . " " . $month . " " . $day . ", " . $year . "</TD><TD>" . $row["mission_name"] . "</TD><TD>" . $row["task_name"] . "</TD><TD>" . $row["points"] . "</TD></TR>";
	echo "<TR><TD>" . $row["camp_task_id"] . " " . $month . " " . $day . ", " . $year . "</TD><TD>" . $row["mission_name"] . "</TD><TD>" . $row["task_name"] . "</TD><TD>" . $row["points"] . "</TD></TR>";
}
?>
</TABLE>
