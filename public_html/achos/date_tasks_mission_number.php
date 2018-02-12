<?php
include ("db.php");

// user id = 5532 Mushka Berkowitz
// school_type_id  = 3

$date_tasks_missions = array();
if (isset($_POST["mission_number"])) {
	include("classes/date_tasks_mission.php");
	$sql = "SELECT * FROM date_tasks_missions WHERE school_type_id=3 AND mission_number=" . $_POST["mission_number"] . " AND track_id=2 AND level=10";
	//echo $sql . "<br />";
	$query = mysql_query($sql);
	while ($row = mysql_fetch_assoc($query)) {
		$date_tasks_mission = new date_tasks_mission($row);
		array_push($date_tasks_missions, $date_tasks_mission);
	}
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">

<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	</head>
	
	<body>
		<form method="post" action="date_tasks_mission_number.php">
			<input type="text" name="mission_number">
			<input type="submit" value="GO">
		</form>
		
		<br />
		
		<? for ($dno = 0; $dno < count($date_tasks_missions); $dno++) : ?>
			<?=$date_tasks_missions[$dno]->mission_number;?> <?=jdtogregorian($date_tasks_missions[$dno]->start_date);?> <?=jdtogregorian($date_tasks_missions[$dno]->end_date);?> <br />
		<? endfor; ?>
		
	</body>
</html>	