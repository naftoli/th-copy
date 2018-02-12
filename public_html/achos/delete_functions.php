<?php
include ("db.php");

$function_name = $_GET['function_name'];
$parameters = $_GET['parameters'];
$parameters = explode(",", $parameters);

echo $function_name($parameters);

function delete_date_tasks_marks($parameters) {
	$user_id = $parameters[0];
	$date_task_ids = explode(":", $parameters[1]);
	$mark_dates = explode(":", $parameters[2]);
	
	$deleted = 0;
	$numTasks = count($date_task_ids);
	for ($dtmno = 0; $dtmno < $numTasks; $dtmno++) {
		$date_task_id = $date_task_ids[$dtmno];
		$mark_date = $mark_dates[$dtmno];
		
		$query = delete($user_id, $date_task_id, $mark_date);
		if ($query) {
			$deleted++;
		}
	}
	
	//checkMission($user_id, $date_task_id);
	
	if ($deleted == $numTasks) {
		return json_encode("1");
	} else {
		return json_encode("0");
	}
}

function delete($user_id, $date_task_id, $mark_date) {
	$sql = "DELETE FROM date_tasks_marks WHERE date_task_id=" . $date_task_id . " AND user_id=" . $user_id . " and mark_date = " . $mark_date;
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
function delete_mark($parameters) {
	$user_id = $parameters[0];
	$date_task_id = $parameters[1];
	$mark_date = $parameters[2];
	
	$sql = "SELECT * FROM date_tasks_marks WHERE date_task_id=" . $date_task_id . " AND user_id=" . $user_id;
	$result = mysql_query($sql);
	$num = mysql_num_rows($result);
	
	$deleted = false;
	if ($num) {
		$sql = "delete from date_tasks_marks where date_task_id = " . $date_task_id . " and user_id = " . $user_id;
		$result = mysql_query($sql);
		
		if ($result) {
			$deleted = true;
			//checkMission($user_id, $date_task_id);
		}
	}
	
	if ($deleted) {
		return json_encode("1");
	} else {
		return json_encode("0");
	}
		
	/*
	if ($num > 0) {
		$sql = "DELETE FROM date_tasks_marks WHERE date_task_id=" . $date_task_id . " AND user_id=" . $user_id . " AND mark_date=" . $mark_date;
		$query = mysql_query($sql);
		$deleted = false;
		
		if ($query) { 
			//find out if task was mandatory
			$sql = "select * from date_tasks where date_task_id = " . $date_task_id;
			$result = mysql_query($sql);
			$row = mysql_fetch_assoc($result);
			$id = $row['date_tasks_mission_id'];
			$mandatory = $row['mandatory_qty'];
			
			//if yes, delete mission from mission marks
			if ($mandatory == 1) {
				$sql = "delete from date_tasks_mission_marks where date_tasks_mission_id = " . $id . " and user_id = " . $user_id;
				if (mysql_query($sql)) {
					$deleted = true;
                    require_once 'class.missionMarks.php';
			        $mm = new MissionMarks($user_id, $date_task_id);
			        $mm->checkMissionCompletion();
					
		            require_once("classes/medal_updater.php");
		            $medal_updater = new medal_updater();
		            $medal_updater->update_medal_two($user_id);
				} else {
					$deleted = false;
				}
			} else {
				$deleted = true;
			}
		}
		
		if ($deleted)
			return json_encode("1");
		else
			return json_encode("0");
	}
	else {
		return json_encode("1");
	}
	 * 
	 */
}

function delete_task_mark($parameters, $updateMedals = true) {
	$user_id = $parameters[0];
	$date_task_id = $parameters[1];
	$mark_date = $parameters[2];
	
	$query = delete($user_id, $date_task_id, $mark_date);
	if ($query) {
		//checkMission($user_id, $date_task_id);
		return json_encode("1");
	} else {
		return json_encode("0");
	}
	
	/*
	$sql = "SELECT date_tasks_mission_id, mandatory_qty FROM date_tasks WHERE date_task_id=" . $date_task_id;
	$query = mysql_query($sql);
	$row = mysql_fetch_assoc($query);
	$date_tasks_mission_id = $row["date_tasks_mission_id"];
	$mandatory = $row["mandatory_qty"];
	
	if ($mark_date > 0)
		$sql = "DELETE FROM date_tasks_marks WHERE date_task_id=" . $date_task_id . " AND user_id=" . $user_id . " AND mark_date=" . $mark_date;	
	else
		$sql = "DELETE FROM date_tasks_marks WHERE date_task_id=" . $date_task_id . " AND user_id=" . $user_id;	
		
	$query = mysql_query($sql);
	
	if ($query) {
		if ($mandatory) {
			$delete_sql = "DELETE FROM date_tasks_mission_marks WHERE user_id=" . $user_id . " AND date_tasks_mission_id=" . $date_tasks_mission_id;		
			$delete_query = mysql_query($delete_sql);		
			if ($delete_query && $updateMedals) {
				require_once 'class.missionMarks.php';
		        $mm = new MissionMarks($user_id, $date_task_id);
		        $mm->checkMissionCompletion();
 
	            require_once("classes/medal_updater.php");
	            $medal_updater = new medal_updater();
	            $medal_updater->update_medal_two($user_id);

			}
		}
		return json_encode("1");
	}
	else {
		return json_encode("0");
	}
	 * 
	 */
}

function delete_daily_task_mark($parameters, $updateMedals = true) {
	$user_id = $parameters[0];
	$date_task_id = $parameters[1];
	$mark_date = $parameters[2];
	
	$query = delete($user_id, $date_task_id, $mark_date);
	if ($query) {
		//checkMission($user_id, $date_task_id);
		return json_encode("1");
	} else {
		return json_encode("0");
	}
	
	/*
	$sql = "SELECT date_tasks_mission_id, mandatory_qty FROM date_tasks WHERE date_task_id=" . $date_task_id;
	$query = mysql_query($sql);
	$row = mysql_fetch_assoc($query);
	$date_tasks_mission_id = $row["date_tasks_mission_id"];
	
	$sql = "DELETE FROM date_tasks_marks WHERE date_task_id=" . $date_task_id . " AND user_id=" . $user_id . " AND mark_date=" . $mark_date;	
	$query = mysql_query($sql);
	
	$success = true;
	if ($query) {
	    if ($row['mandatory_qty'] && $updateMedals) {
	    	require_once 'class.missionMarks.php';
	        $mm = new MissionMarks($user_id, $date_task_id);
	        $mm->checkMissionCompletion();
			
            require_once("classes/medal_updater.php");
            $medal_updater = new medal_updater();
            $medal_updater->update_medal_two($user_id);
	    }
	} else {
		$success = false;
	}
	if ($success) {
		return json_encode("1");
	} else {
		return json_encode("0");
	}
	 * 
	 */
}
?>