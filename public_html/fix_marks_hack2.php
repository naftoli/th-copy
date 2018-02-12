<?php
ini_set('display_errors',1);
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

$info = array();
$sql = "select * from date_tasks_marks 
        where done_qty = 3 
        and mark_date >= 2457935";
$result = mysql_query( $sql ) or die( mysql_error() );
while ($row = mysql_fetch_assoc( $result )) {
    $info[] = $row;
}

foreach ($info as $row) {
    $user_id = $row['user_id'];
    $mark_date = $row['mark_date'];
    $date_task_id = $row['date_task_id'];
    $sql2 = "select * from mashpiadb_sf01220566.date_tasks_marks
            where date_task_id = " . $date_task_id . "
            and user_id = " . $user_id . "
            and mark_date = " . $mark_date;
    $result2 = mysql_query( $sql2 ) or die( mysql_error() );
    if ($row2 = mysql_fetch_assoc( $result2 )) {
        if ($row2['done_qty']) {
            $sql3 = "update date_tasks_marks
                    set done_qty = " . $row2['done_qty'] . "
                    where date_task_id = " . $date_task_id . "
                    and user_id = " . $user_id . "
                    and mark_date = " . $mark_date;
        } else {
            $sql3 = "delete from date_tasks_marks
                    where date_task_id = " . $date_task_id . "
                    and user_id = " . $user_id . "
                    and mark_date = " . $mark_date; 
        }
        //echo $sql3 . "<br />";
        mysql_query( $sql3 ) or die( mysql_error() );
        
        $sqlMission = "select date_tasks_mission_id from date_tasks where date_task_id = " . $date_task_id;
        $resMission = mysql_query( $sqlMission );
        $rowMission = mysql_fetch_assoc( $resMission );
        $date_tasks_mission_id = $rowMission['date_tasks_mission_id'];
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
                }
            }
        }
    }
}
echo "done.";