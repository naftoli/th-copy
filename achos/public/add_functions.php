<?php
include ("db.php");

$function_name = $_GET['function_name'];
$parameters = $_GET['parameters'];
$parameters = explode(",", $parameters);

echo $function_name($parameters);

function add_date_tasks_marks($parameters) {
	$user_id = $parameters[0];
	$date_task_ids = explode(":", $parameters[1]);
	$mark_dates = explode(":", $parameters[2]);
	
	//print_r($date_task_ids);
	$added = 0;
	$numTasks = count($date_task_ids);
	for ($dtmno = 0; $dtmno < $numTasks; $dtmno++) {
		$date_task_id = $date_task_ids[$dtmno];
		$mark_date = $mark_dates[$dtmno];
		
		$query = add($user_id, $date_task_id, $mark_date);
		if ($query) {
			$added++;
		}
	}
	
	//checkMission($user_id, $date_task_id);
	
	if ($added == $numTasks) {
		return json_encode("1");
	} else {
		return json_encode("0");
	}
}

function add($user_id, $date_task_id, $mark_date) {
	$sql = "INSERT INTO date_tasks_marks SET date_task_id=" . $date_task_id . ", user_id=" . $user_id . ", mark_date=" . $mark_date . ", done_qty=1";
	$query = mysql_query($sql);
	return $query;
}
/*
function checkMission($user_id, $date_task_id) {
	require_once 'class.missionMarks.php';
    $mm = new MissionMarks($user_id, $date_task_id);
    $mm->checkMissionCompletion();
}
*/
function add_mark($parameters) 
{
	$user_id = $parameters[0];
	$date_task_id = $parameters[1];
	$mark_date = $parameters[2];
	$user_mark = $parameters[3];
	
	$sql = "select * from date_tasks_marks where date_task_id = " . $date_task_id . " and user_id = " . $user_id;
	$result = mysql_query($sql);
	if (mysql_num_rows($result) > 0) {
		$sql = "update date_tasks_marks set mark_date = " . $mark_date . ", done_qty = " . $user_mark . 
			" where user_id = " . $user_id . " and date_task_id = " . $date_task_id;
	} else {
		$sql = "insert into date_tasks_marks set date_task_id = " . $date_task_id . ", user_id = " . $user_id . 
			", mark_date = " . $mark_date . ", done_qty = " . $user_mark;
	}
	//echo $sql;
	$result = mysql_query($sql);
	if ($result) {
		//checkMission($user_id, $date_task_id);
		return json_encode("1");
	} else {
		return json_encode("0");
	}
	
	/*
	$inserted_or_updated = false;
	
	// ***** DATE TASK INFORMATION ***** //
	$sql = "SELECT * FROM date_tasks WHERE date_task_id=" . $date_task_id;
	$query = mysql_query($sql);
	$row = mysql_fetch_assoc($query);
		
	$date_tasks_mission_id = mysql_real_escape_string($row['date_tasks_mission_id']);
	$mark_description = mysql_real_escape_string($row['description']);
	$mark_points = $row['points'];
	$mark_quantity = $row['quantity'];
	// ***** DATE TASK INFORMATION ***** //
	
	//figure out if entered quantity is double or triple required amount - if yes double / triple the points earned
	$multiply = (int)($user_mark / $mark_quantity);
	if ($multiply) {
		$mark_points *= $mulitply;
	}
	
	$sql = "SELECT * FROM date_tasks_marks WHERE date_task_id=" . $date_task_id . " AND user_id=" . $user_id;
	$query = mysql_query($sql);
	$row = mysql_fetch_assoc($query);

	if ($row) 
	{
		$sql = "UPDATE date_tasks_marks SET done_qty=" . $user_mark . ", mark_points = " . $mark_points . " WHERE date_task_id=" . $date_task_id . " AND user_id=" . $user_id;
		$query = mysql_query($sql);		
		if ($query) 
			$inserted_or_updated = true;		
	}
	else 
	{
		$sql = "INSERT INTO date_tasks_marks SET date_task_id=" . $date_task_id . ", user_id=" . $user_id . ", mark_date=" . $mark_date . ", done_qty=" . $user_mark . ", mark_description='" . $mark_description . "', mark_points=" . $mark_points . ", mark_quantity=" . $mark_quantity;
		$query = mysql_query($sql);			
		if ($query) 
			$inserted_or_updated = true;	
	}	
	
	if ($inserted_or_updated == true)
	{
		require_once 'class.missionMarks.php';
        $mm = new MissionMarks($user_id, $date_task_id);
        $mm->checkMissionCompletion();

	    require_once("classes/medal_updater.php");
        $medal_updater = new medal_updater();
        $medal_updater->update_medal_two($user_id);
		
		return json_encode("1");
	}
	else
	{
		return json_encode("0");
	}
	 * 
	 */		
}

// ***** WEEKLY, SHABBOS, NO LABEL TASKS ***** //
function add_task_mark($parameters, $updateMedals = true) {
	$user_id = $parameters[0];
	$date_task_id = $parameters[1];
	$mark_date = $parameters[2];
		
	$query = add($user_id, $date_task_id, $mark_date);
	if ($query) {
		//checkMission($user_id, $date_task_id);
		return json_encode("1");
	} else {
		return json_encode("0");
	}
	
	/*
	$sql = "SELECT dt.*, dtm.start_date, dtm.end_date, dtm.subject_id ";
	$sql = $sql . "FROM date_tasks AS dt ";
	$sql = $sql . "JOIN date_tasks_missions AS dtm USING (date_tasks_mission_id)  ";
	$sql = $sql . "WHERE date_task_id=" . $date_task_id;
	
	$query = mysql_query($sql);
	$row = mysql_fetch_assoc($query);
	$date_tasks_mission_id = $row["date_tasks_mission_id"];
	$mark_description = $row["name"];
	$mandatory = $row['mandatory_qty'];
	$subject_id = $row['subject_id'];
    $start = $row['start_date'];

	if ($mark_date == 0) 
		$mark_date = $row["end_date"];
	
	$insert_sql = "INSERT INTO date_tasks_marks SET date_task_id=" . $date_task_id . ", user_id=" . $user_id . ", mark_date=" . $mark_date . ", done_qty=1, mark_description='" . mysql_real_escape_string($mark_description) . "', mark_points=" . $row["points"];
	//echo $insert_sql . "<br />";
	$insert_query = mysql_query($insert_sql);
	
	if ($insert_query) {	
		if ($mandatory && $updateMedals) {	
		    require_once 'class.missionMarks.php';
	        $mm = new MissionMarks($user_id, $date_task_id);
	        $mm->checkMissionCompletion();
            require_once("classes/medal_updater.php");
            $medal_updater = new medal_updater();
            $medal_updater->update_medal_two($user_id);
		}
		return json_encode("1");
	}
	else {
		return json_encode("0");
	}
	 * 
	 */
	
}
// ***** WEEKLY, SHABBOS, NO LABEL TASKS ***** //

// ***** DAILY TASKS ***** //
function add_daily_task_mark($parameters, $updateMedals = true) 
{	
	$user_id = $parameters[0];
	$date_task_id = $parameters[1];
	$mark_date = $parameters[2];
	
	$query = add($user_id, $date_task_id, $mark_date);
	if ($query) {
		//checkMission($user_id, $date_task_id);
		return json_encode("1");
	} else {
		return json_encode("0");
	}
	
	/*	
	$sql = "SELECT * FROM date_tasks WHERE date_task_id=" . $date_task_id;
	$query = mysql_query($sql);
	$row = mysql_fetch_assoc($query);
	$points = $row['points'];
	$mandatory = $row['mandatory_qty'];
	$needed = $row['needed'];
	$mark_description = $row["name"];
	$date_tasks_mission_id = $row['date_tasks_mission_id'];
	
	$sql = "INSERT INTO date_tasks_marks SET date_task_id=" . $date_task_id . ", user_id=" . $user_id . ", mark_date=" . $mark_date . ", mark_description='" . mysql_real_escape_string($mark_description) . "', mark_points=" . $points . ",done_qty=1";
	//echo $sql;
	$query = mysql_query($sql);
	
	if ($query) {		
		if ($mandatory && $updateMedals) {
			require_once 'class.missionMarks.php';
	        $mm = new MissionMarks($user_id, $date_task_id);
	        $mm->checkMissionCompletion();
            require_once("classes/medal_updater.php");
            $medal_updater = new medal_updater();
            $medal_updater->update_medal_two($user_id);
		}
		return json_encode("1");		
	}
	else 
	{
		return json_encode("0");
	}
	 * 
	 */
}
// ***** DAILY TASKS ***** //
?>