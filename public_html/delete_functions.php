<?php
include ( dirname(__FILE__) . "/db.php" );
require_once( dirname(__FILE__) . '/yearly_prize/classes/TotalWeeklyTasks.php' );

$function_name = $_GET['function_name'];
$parameters = $_GET['parameters'];
$parameters = explode(",", $parameters);

echo $function_name($parameters);

function delete_date_tasks_marks($parameters) {
	$user_id = $parameters[0];
	$date_task_ids = explode(":", $parameters[1]);
	$mark_dates = explode(":", $parameters[2]);
	
	//$subjects = array();
	for ($dtmno = 0; $dtmno < count($date_task_ids); $dtmno++) {
		$date_task_id = $date_task_ids[$dtmno];
		$mark_date = $mark_dates[$dtmno];
		
		if (!empty($date_task_id)) {
			$sql = "SELECT date_tasks_mission_id, mandatory_qty FROM date_tasks WHERE date_task_id=" . $date_task_id;
			$query = mysql_query($sql);
			$row = mysql_fetch_assoc($query);
			$date_tasks_mission_id = $row["date_tasks_mission_id"];
			$mandatory_qty = $row["mandatory_qty"];
				
			$delete_sql1 = "DELETE FROM date_tasks_marks WHERE date_task_id=" . $date_task_id . " AND user_id=" . $user_id . " AND  mark_date=" . $mark_date;
			$delete_query1 = mysql_query($delete_sql1);
			
			if ($delete_query1 && $mandatory_qty > 0) {
				$delete_sql2 = "DELETE FROM date_tasks_mission_marks WHERE user_id=" . $user_id . " AND date_tasks_mission_id=" . $date_tasks_mission_id;		
				$delete_query2 = mysql_query($delete_sql2);	
	
				//$sql2 = "SELECT subject_id FROM date_tasks_missions WHERE date_tasks_mission_id=" . $date_tasks_mission_id;
				//$query2 = mysql_query($sql2);
				//$row2 = mysql_fetch_assoc($query2);
				//if (!in_array($row2['subject_id'], $subjects)) $subjects[] = $row2['subject_id'];
			}
		}
	}
	/*
	require_once("classes/mission_marks_updater.php");
	require_once 'classes/medal_updater.php';
	require_once 'classes/rank_updater.php';
	
	$mm = new mission_marks_updater();
	$medal_updater = new medal_updater();
	$rank_updater = new rank_updater();
	
	foreach ($subjects as $subject) {
        $mm->mission_marks_update_by_subject_id($user_id, $subject);
    }
    
    $medal_updater->update_medal_two($user_id);
    $rank_updater->update_rank_two($user_id);
    */
}

function delete_task_mark($parameters) {
	$user_id = $parameters[0];
	$date_task_id = $parameters[1];
	$mark_date = $parameters[2];

	$sql = "SELECT date_tasks_mission_id, mandatory_qty FROM date_tasks WHERE date_task_id=" . $date_task_id;
	$query = mysql_query($sql);
	$row = mysql_fetch_assoc($query);
	$date_tasks_mission_id = $row["date_tasks_mission_id"];
	$mandatory = $row["mandatory_qty"];
	
	//if ($mark_date > 0)
		//$sql = "DELETE FROM date_tasks_marks WHERE date_task_id=" . $date_task_id . " AND user_id=" . $user_id . " AND mark_date=" . $mark_date;	
	//else
	$sql = "DELETE FROM date_tasks_marks WHERE date_task_id=" . $date_task_id . " AND user_id=" . $user_id;	
	$query = mysql_query($sql);
	
	if ($query) {
		if ($mandatory) {
			$delete_sql = "DELETE FROM date_tasks_mission_marks WHERE user_id=" . $user_id . " AND date_tasks_mission_id=" . $date_tasks_mission_id;		
			$delete_query = mysql_query($delete_sql);
			/*
			if ($delete_query) {
			
				require_once("classes/rank_updater.php");
				require_once("classes/medal_updater.php");
				
				$medal_updater = new medal_updater();
				$medal_updater->update_medal_two($user_id);
						
				$rank_updater = new rank_updater();
				$rank_updater->update_rank_two($user_id);
				
				return 1;
			}
			*/
        }
        // update the user for the yearly gift
        TotalWeeklyTasks::updateUser( $user_id, $mark_date );
        
		return 1;
	}
	else {
		return 0;
	}
}

function delete_daily_task_mark($parameters) {
	$user_id = $parameters[0];
	$date_task_id = $parameters[1];
	$mark_date = $parameters[2];

	$sql = "SELECT date_tasks_mission_id, mandatory_qty, needed FROM date_tasks WHERE date_task_id=" . $date_task_id;
	$query = mysql_query($sql);
	$row = mysql_fetch_assoc($query);
	$date_tasks_mission_id = $row["date_tasks_mission_id"];
	$mandatory = $row['mandatory_qty'];
	$needed = $row['needed'];
	
	$sql = "DELETE FROM date_tasks_marks WHERE date_task_id=" . $date_task_id . " AND user_id=" . $user_id . " AND mark_date=" . $mark_date;	
	$query = mysql_query($sql);
	
	// check if mission needs to be deleted
	$sql = "select count(*) as total from date_tasks_marks where date_task_id = " . $date_task_id . " and user_id = " . $user_id;
	$result = mysql_query($sql);
	$row = mysql_fetch_assoc($result);
	
	if ($mandatory && $row['total'] < $needed) {
		$sql = "DELETE FROM date_tasks_mission_marks WHERE user_id=" . $user_id . " AND date_tasks_mission_id=" . $date_tasks_mission_id;
		$query = mysql_query($sql);
	}
	/*
	require_once("classes/rank_updater.php");
	require_once("classes/medal_updater.php");
	
	$medal_updater = new medal_updater();
	$medal_updater->update_medal_two($user_id);
			
	$rank_updater = new rank_updater();
	$rank_updater->update_rank_two($user_id);
	*/
}

function delete_daily_task_mark2($parameters) {
	$user_id = $parameters[0];
	$date_task_id = $parameters[1];
	$mark_date = $parameters[2];

	$sql = "SELECT date_tasks_mission_id, mandatory_qty FROM date_tasks WHERE date_task_id=" . $date_task_id;
	$query = mysql_query($sql);
	$row = mysql_fetch_assoc($query);
	$date_tasks_mission_id = $row["date_tasks_mission_id"];
	
	$sql = "DELETE FROM date_tasks_marks WHERE date_task_id=" . $date_task_id . " AND user_id=" . $user_id . " AND mark_date=" . $mark_date;	
	$query = mysql_query($sql);
	
	$sql = "DELETE FROM date_tasks_mission_marks WHERE user_id=" . $user_id . " AND date_tasks_mission_id=" . $date_tasks_mission_id;
	$query = mysql_query($sql);
	
	if ($query) {
        // update the user for the yearly gift
        TotalWeeklyTasks::updateUser( $user_id, $mark_date );
        echo 0;
    } else {
        echo 1;
    }
}

function delete_mark($parameters) {
	$user_id = $parameters[0];
	$date_task_id = $parameters[1];
	$mark_date = $parameters[2];

	$sql = "SELECT * FROM date_tasks_marks WHERE date_task_id=" . $date_task_id . " AND user_id=" . $user_id . " AND mark_date=" . $mark_date;
	$result = mysql_query($sql);
	$num = mysql_num_rows($result);
	
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
}
?>