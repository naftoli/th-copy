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
        }
        // update the user for the yearly gift
        TotalWeeklyTasks::updateUser( $user_id, $mark_date );

        return json_encode("1");
	}
	else return json_encode("0");
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

	// day schools work differently
    $daySchool = false;
    $sql_type = "select school_type_id from users where user_id = " . $user_id;
	$result_type = mysql_query($sql_type);
	$row_type = mysql_fetch_assoc($result_type);
    if (in_array($row_type['school_type_id'], [4, 5])) {
        $daySchool = true;
    }

    if ($daySchool && $mandatory) {
        $totalMissions = $row['total']; // total times the task is marked is the total amount of mission the child should have
        if ($totalMissions > 0) {
            $sql = "UPDATE date_tasks_mission_marks SET mission_count = $totalMissions WHERE user_id=" . $user_id . " AND date_tasks_mission_id=" . $date_tasks_mission_id;
            $query = mysql_query($sql);
        } else {
            $sql = "DELETE FROM date_tasks_mission_marks WHERE user_id=" . $user_id . " AND date_tasks_mission_id=" . $date_tasks_mission_id;
            $query = mysql_query($sql);
        }
    }

    // update the users information in the user_yearly_gift table
    TotalWeeklyTasks::updateUser( $user_id, $mark_date );

    if ($query) return json_encode("1");
    else return json_encode("0");
}

function delete_daily_task_mark2($parameters) {
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

    // day schools work differently
    $daySchool = false;
    $sql_type = "select school_type_id from users where user_id = " . $user_id;
    $result_type = mysql_query($sql_type);
    $row_type = mysql_fetch_assoc($result_type);
    if (in_array($row_type['school_type_id'], [4, 5])) {
        $daySchool = true;
    }

    if ($daySchool && $mandatory) {
        $totalMissions = $row['total']; // total times the task is marked is the total amount of mission the child should have
        if ($totalMissions > 0) {
            $sql = "UPDATE date_tasks_mission_marks SET mission_count = $totalMissions WHERE user_id=" . $user_id . " AND date_tasks_mission_id=" . $date_tasks_mission_id;
            $query = mysql_query($sql);
        } else {
            $sql = "DELETE FROM date_tasks_mission_marks WHERE user_id=" . $user_id . " AND date_tasks_mission_id=" . $date_tasks_mission_id;
            $query = mysql_query($sql);
        }
    }
	
	if ($query) {
        // update the users information in the user_yearly_gift table
        TotalWeeklyTasks::updateUser( $user_id, $mark_date );
        return json_encode("1");
    }
	else return json_encode("0");
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