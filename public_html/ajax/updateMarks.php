<?php
require_once '../db.php';

//require_once("../classes/mission_marks_updater.php");
require_once '../classes/medal_updater.php';
require_once '../classes/rank_updater.php';

//$mm = new mission_marks_updater();
$medal_updater = new medal_updater();
$rank_updater = new rank_updater();

$action = mysql_real_escape_string($_POST['action']);
$parameters = $_POST['data'];

if ($action == 'delete') {
    $user_id = $parameters[0];
	$date_task_ids = explode(":", $parameters[1]);
	$mark_dates = explode(":", $parameters[2]);
	
	$subjects = array();
	for ($dtmno = 0; $dtmno < count($date_task_ids); $dtmno++) {
		$date_task_id = $date_task_ids[$dtmno];
		$mark_date = $mark_dates[$dtmno];
			
		$sql = "SELECT date_tasks_mission_id, mandatory_qty, daily_task FROM date_tasks WHERE date_task_id=" . $date_task_id;
		$query = mysql_query($sql);
		$row = mysql_fetch_assoc($query);
		$date_tasks_mission_id = $row["date_tasks_mission_id"];
		$mandatory_qty = $row["mandatory_qty"];
        $daily_task = $row["daily_task"];
			
		$delete_sql1 = "DELETE FROM date_tasks_marks WHERE date_task_id=" . $date_task_id . " AND user_id=" . $user_id;
        if ($daily_task) $delete_sql1 .= " AND  mark_date=" . $mark_date;
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
	
//	foreach ($subjects as $subject) {
//        $mm->mission_marks_update_by_subject_id($user_id, $subject);
//    }
    
    $medal_updater->update_medal_two($user_id);
    $rank_updater->update_rank_two($user_id);
    
    echo json_encode(true);
    
} else if ($action == 'add') {
    $user_id = $parameters[0];
	$date_task_ids = explode(":", $parameters[1]);
	$mark_dates = explode(":", $parameters[2]);
	
	//print_r($date_task_ids);
	$subjects = array();
	for ($dtmno = 0; $dtmno < count($date_task_ids); $dtmno++) {

		$date_task_id = $date_task_ids[$dtmno];
		$mark_date = $mark_dates[$dtmno];
		//echo $date_task_id . "<br />" . $mark_date; exit;
		
		$sql = "SELECT dt.*, dtm.end_date, dtm.subject_id FROM date_tasks AS dt JOIN date_tasks_missions AS dtm USING (date_tasks_mission_id)  WHERE date_task_id=" . $date_task_id;			
		$result = mysql_query($sql);
		$row = mysql_fetch_assoc($result);
		$daily_task = $row['daily_task'];
		//if (!in_array($row['subject_id'], $subjects)) $subjects[] = $row['subject_id'];
		//print_r($row);
		
		if ($daily_task) {
			$parameters = array($user_id, $date_task_id, $mark_date);
			add_daily_task_mark($parameters);
		}
		else {
			$parameters = array($user_id, $date_task_id, $mark_date);
			add_task_mark($parameters);
		}			
	}
    
    //foreach ($subjects as $subject) {
    //    $mm->mission_marks_update_by_subject_id($user_id, $subject);
    //}
    
    $medal_updater->update_medal_two($user_id);
    $rank_updater->update_rank_two($user_id);
    
    echo json_encode(true);
}

function add_task_mark($parameters) {
	$user_id = $parameters[0];
	$date_task_id = $parameters[1];
	$mark_date = $parameters[2];
	
	$sql = "SELECT dt.*, dtm.start_date, dtm.end_date, dtm.subject_id ";
	$sql = $sql . "FROM date_tasks AS dt ";
	$sql = $sql . "JOIN date_tasks_missions AS dtm USING (date_tasks_mission_id)  ";
	$sql = $sql . "WHERE date_task_id=" . $date_task_id;
	
	$query = mysql_query($sql);
	$row = mysql_fetch_assoc($query);
	$date_tasks_mission_id = $row["date_tasks_mission_id"];
	//$mark_description = $row["name"];
	$mandatory = $row['mandatory_qty'];
	$subject_id = $row['subject_id'];
	$grid_id = $row['grid_id'];
	$points = $row['points'];

	if ($mark_date == 0) 
		$mark_date = $row["end_date"];
		
	// need to make sure marks in system aren't greater than or less than current mission dates
	// this will make sure user cannot switch langs and marks same mission twice
	if ($mark_date > $row['end_date']) {
		$mark_date = $row['end_date'];		
	}
	if ($mark_date < $row['start_date']) {
		$mark_date = $row['start_date'];
	}
    
    if ($grid_id) {
		$start_date = $row['start_date'];
		$end_date = $row['end_date'];
		$sql = "select * from date_tasks_marks dtm
				join date_tasks dt using (date_task_id) 
				where grid_id = " . $grid_id . "
				and user_id = " . $user_id . "
				and mark_date >= " . $start_date . "
				and mark_date <= " . $end_date;
	} else {
		$sql = "select * from date_tasks_marks  
				where date_task_id = " . $date_task_id . "
				and user_id = " . $user_id;
	}
	$result = mysql_query($sql);
	if (mysql_num_rows($result) > 0) {
		return; // it's already been marked
	}

	$insert_sql = "INSERT INTO date_tasks_marks SET date_task_id=" . $date_task_id . ", user_id=" . $user_id . ", mark_date=" . $mark_date . ", done_qty=1, mark_description='" . mysql_real_escape_string($mark_description) . "', mark_points=" . $row["points"];
	//echo $insert_sql . "<br />";
	$insert_query = mysql_query($insert_sql);
	
	if ($insert_query) {	
		if ($mandatory > 0){
			check_mission_completion($user_id, $subject_id, $date_tasks_mission_id, $mark_date);
		}
	}
    
    // if it's a tanya mark update the yud alef nissan tables
    if (unixtojd() > 2458101 && isset($grid_id) && $grid_id == 21013) {
        
        require_once '../class.globalSettings.php';
        $year = GlobalSettings::getChidonYear();
        
        // get campaigns for current year
        $sql = "select * from line_campaigns where year = " . $year;
        $result = mysql_query( $sql );
        while ($row = mysql_fetch_assoc( $result )) {
            if (strtolower($row['type']) == 'tanya') $tanyaCampaign = $row['id'];
            else if (strtolower($row['type']) == 'mishna') $mishnaCampaign = $row['id'];
        }
        
        if ($grid_id == 21013) $campaign = $tanyaCampaign;
        //else if ($grid_id == 333) $campaign = $mishnaCampaign;			
        
        $sql = "select lines_learned as t from lines_learned where campaign_id = " . $campaign . " and user_id = " . $user_id;
        $result = mysql_query($sql);
        if (mysql_num_rows($result) > 0) {
            $row = mysql_fetch_assoc($result);
            $learned = $row['t'];
            if ($learned < $user_mark) {
                $sql = "update lines_learned
                        set lines_learned = " . $user_mark . ",
                        entered_by_parent = 1 
                        where campaign_id = " . $campaign . "
                        and user_id = " . $user_id;
                mysql_query($sql);
            }
        } else {
            $sql = "select school_id, class_id from users where user_id = " . $user_id;
            $result = mysql_query($sql);
            $row = mysql_fetch_assoc($result);
            $sql = "insert into lines_learned set
                    campaign_id = " . $campaign . ",
                    user_id = " . $user_id . ",
                    lines_learned = " . $user_mark . ",
                    school_id = " . $row['school_id'] . ",
                    class_id = " . $row['class_id'] . ",
                    entered_by_parent = 1";
            mysql_query($sql);
        }
    }
}

function check_mission_completion($user_id, $subject_id, $date_tasks_mission_id, $mark_date) {	
	$sql = "SELECT dt.date_task_id, dt.quantity, dtm.done_qty, dtm.date_task_id, dt.name ";
	$sql = $sql . "FROM date_tasks AS dt ";
	$sql = $sql . "LEFT JOIN date_tasks_marks AS dtm ON (dt.date_task_id=dtm.date_task_id AND dtm.user_id=" . $user_id . ") ";
	$sql = $sql . "WHERE dt.date_tasks_mission_id=" . $date_tasks_mission_id . " ";
	$sql = $sql . "AND dt.mandatory_qty > 0 ";
	$sql = $sql . "AND (dtm.date_task_id IS NULL OR dtm.done_qty < dt.quantity)";
	
	$query = mysql_query($sql);
	$num_rows = mysql_num_rows($query);
	
	if ($num_rows == 0) {
		$sql = "SELECT dtm.subject_id, dtm.mission_value, dtm.mission_name FROM date_tasks_missions AS dtm WHERE date_tasks_mission_id=" . $date_tasks_mission_id;
		$query = mysql_query($sql);
		$row = mysql_fetch_assoc($query);
		
		$insert_sql = "INSERT INTO date_tasks_mission_marks SET user_id=" . $user_id . ", date_tasks_mission_id=" . $date_tasks_mission_id . ", subject_id=" . $row['subject_id'] . ", mission_value=" . $row['mission_value'] . ", mission_name='" . mysql_real_escape_string($row['mission_name']) . "', mark_date=" . $mark_date . ", mark_override=0";				
		$insert_query = mysql_query($insert_sql);
	}
}

function add_daily_task_mark($parameters) 
{	
	$user_id = $parameters[0];
	$date_task_id = $parameters[1];
	$mark_date = $parameters[2];
	
	$sql = "SELECT dt.*, dtm.start_date, dtm.end_date
			FROM date_tasks dt 
			join date_tasks_missions dtm using (date_tasks_mission_id) 
			WHERE date_task_id=" . $date_task_id;
	$query = mysql_query($sql);
	$row = mysql_fetch_assoc($query);
	$points = $row['points'];
	$mandatory = $row['mandatory_qty'];
	$needed = $row['needed'];
	//$mark_description = $row["name"];
	$date_tasks_mission_id = $row['date_tasks_mission_id'];
	$grid_id = $row['grid_id'];
	
	// need to make sure marks in system aren't greater than or less than current mission dates
	// this will make sure user cannot switch langs and marks same mission twice
	if ($mark_date > $row['end_date']) {
		$mark_date = $row['end_date'];		
	}
	if ($mark_date < $row['start_date']) {
		$mark_date = $row['start_date'];
	}
	
	// make sure mark doesn't already exist either in current lang or in different lang / level
	if ($grid_id) {
		$sql = "select * from date_tasks_marks dtm
				join date_tasks dt using (date_task_id) 
				where grid_id = " . $grid_id . "
				and user_id = " . $user_id . "
				and mark_date = " . $mark_date;
	} else {
		$sql = "select * from date_tasks_marks  
				where date_task_id = " . $date_task_id . "
				and user_id = " . $user_id . "
				and mark_date = " . $mark_date;
	}
	$result = mysql_query($sql);
	if (mysql_num_rows($result) > 0) {
		return; // it's already been marked
	}
	
	$sql = "INSERT INTO date_tasks_marks SET date_task_id=" . $date_task_id . ", user_id=" . $user_id . ", mark_date=" . $mark_date . ", mark_description='" . mysql_real_escape_string($mark_description) . "', mark_points=" . $points . ",done_qty=1";
	//echo $sql;
	$query = mysql_query($sql);
	
	if ($query) 
	{		
		
		// ***** If the task is mandatory then we need to see if all of the daily tasks have been completed ***** //
		if ($mandatory) 
		{
				
			$sql = "SELECT * FROM date_tasks_marks WHERE user_id=" . $user_id . " AND date_task_id=" . $date_task_id;
			$query = mysql_query($sql);
			$num_rows = mysql_num_rows($query);
			//echo "done - " . $num_rows . " needed - " . $needed; exit;
			
			// ***** If all of the daily tasks have been completed then we need to see if the mission has been completed ***** //
			if ($num_rows >= $needed) 
			{	
				$done = check_tasks($user_id, $date_tasks_mission_id);
				//echo "done - " . $num_rows . " needed - " . $needed; exit;
				
				if ($done) 
				{
					$sql = "SELECT dtm.subject_id, dtm.mission_value, dtm.mission_name FROM date_tasks_missions AS dtm WHERE date_tasks_mission_id=" . $date_tasks_mission_id;
					$query = mysql_query($sql);
					$row = mysql_fetch_assoc($query);
				
					$insert_sql = "INSERT INTO date_tasks_mission_marks SET user_id=" . $user_id . ", date_tasks_mission_id=" . $date_tasks_mission_id . ", subject_id=" . $row['subject_id'] . ", mission_value=" . $row['mission_value'] . ", mission_name='" . mysql_real_escape_string($row['mission_name']) . "', mark_date=" . $mark_date . ", mark_override=0";
					$insert_query = mysql_query($insert_sql);
				}
				
			}
			// ***** If all of the daily tasks have been completed then we need to see if the mission has been completed ***** //
			
		}
		// ***** If the task is mandatory then we need to see if all of the daily tasks have been completed ***** //		
	}
}

function check_tasks($user_id, $date_tasks_mission_id) {
	$done = true;
	
	// ***** Check to see if all non daily tasks were all completed ***** //
	$sql = "SELECT dt.date_task_id, dt.quantity, dtm.done_qty, dtm.date_task_id, dt.name ";
	$sql = $sql . "FROM date_tasks AS dt ";
	$sql = $sql . "LEFT JOIN date_tasks_marks AS dtm ON (dt.date_task_id=dtm.date_task_id AND dtm.user_id=" . $user_id . ") ";
	$sql = $sql . "WHERE dt.date_tasks_mission_id=" . $date_tasks_mission_id . " ";
	$sql = $sql . "AND dt.mandatory_qty=1 ";
	$sql = $sql . "AND dt.daily_task=0 ";
	$sql = $sql . "AND (dtm.date_task_id IS NULL OR dtm.done_qty < dt.quantity) ";
	$query = mysql_query($sql);
	$num_rows = mysql_num_rows($query);
	// ***** Check to see if all non daily tasks were all completed ***** //
	
	if ($num_rows > 0) 
	{
		$done = false;
	}
	else 
	{
		// ***** Check to see if all daily tasks were all completed ***** //
		$sql = "SELECT * ";
		$sql = $sql . "FROM date_tasks AS dt ";
		$sql = $sql . "WHERE dt.date_tasks_mission_id=" . $date_tasks_mission_id . " ";
		$sql = $sql . "AND daily_task=1 ";
		$sql = $sql . "AND mandatory_qty=1 ";
		//echo $sql;
		$query = mysql_query($sql);
	
		while ($row = mysql_fetch_assoc($query)) {
			$sql2 = "SELECT * FROM date_tasks_marks WHERE user_id=" . $user_id . " AND date_task_id=" . $row['date_task_id'];
			$query2 = mysql_query($sql2);
			$num_rows2 = mysql_num_rows($query2);

			if ($num_rows2 < $row['needed']) {
				$done = false;
				break;
			}
		}
		// ***** Check to see if all daily tasks were all completed ***** //
	}
	
	return $done;
}
