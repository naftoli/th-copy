<?php
ini_set('display_errors',1);
ini_set('max_execution_time', 600);
require_once 'db.php';

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
	//echo $sql . "<br />";
	$query = mysql_query($sql) or die( $sql . "<br />" . mysql_error() );
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
		//if ($user_id == 50689) echo $sql;
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
$users = array();
$sql = "select user_id from users where user_registered > 0 limit 6000, 2000";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $users[] = $row['user_id'];
}

$updated = 0;
foreach ($users as $user_id) {
    $info = array();
    $sql = "select * from date_tasks_marks dtm
            join date_tasks dt using (date_task_id) 
            where dtm.mark_date >= 2457935
            and dtm.user_id = " . $user_id . "
            group by dt.date_tasks_mission_id";
    $result = mysql_query( $sql ) or die( mysql_error() );
    while ($row = mysql_fetch_assoc( $result )) {
        $info[] = $row;
    }
    
    foreach ($info as $row) {
        $user_id = $row['user_id'];
        $mark_date = $row['mark_date'];
        $date_tasks_mission_id = $row['date_tasks_mission_id'];
        if ($date_tasks_mission_id) {
            if (check_tasks( $user_id, $date_tasks_mission_id )) {
                // check that mission needs to be inserted
                $sql4 = "select * from date_tasks_mission_marks where user_id = " . $user_id . " and date_tasks_mission_id = " . $date_tasks_mission_id;
                $result4 = mysql_query( $sql4 ) or die( mysql_error() );
                if (mysql_num_rows( $result4 ) == 0) {
                    $sql5 = "select * from date_tasks_missions where date_tasks_mission_id = " . $date_tasks_mission_id;
                    $result5 = mysql_query( $sql5 ) or die( mysql_error() );
                    if ($row5 = mysql_fetch_assoc( $result5 )) {
                        $insert_sql = "INSERT IGNORE INTO date_tasks_mission_marks
                                        SET user_id=" . $user_id . ",
                                        date_tasks_mission_id=" . $date_tasks_mission_id . ",
                                        subject_id=" . $row5['subject_id'] . ",
                                        mission_value=" . $row5['mission_value'] . ",
                                        mission_name='" . mysql_real_escape_string($row5['mission_name']) . "',
                                        mark_date=" . $mark_date . ",
                                        mark_override=0";
                        //echo $insert_sql . "<br />";
                        $insert_query = mysql_query($insert_sql) or die( mysql_error() );
                        if ($insert_query) $updated++;
                    }
                }
            }
        }
    }
}
echo "Updated: " . $updated;