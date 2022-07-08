<?php
class mission_updater 
{
	
	function __construct($setupDB = false)
	{
		if ($setupDB) {
			require '/home/mashpia/includes/globals.php';
			$link = mysql_connect('localhost', $global_db_user, $global_db_pass) or trigger_error_server('Failed to connect to mysql', E_USER_ERROR);
			mysql_query('SET NAMES utf8');
			mysql_query('SET CHARACTER_SET utf8');
			mysql_select_db('mashpiadb') or trigger_error_server('Failed to select db', E_USER_ERROR);
		}
	}
	
	function mission_update($user_id, $date_tasks_mission_id) 
	{
		$marked = false;

        // check if there's any mandatory tasks in this mission id
        $sql = "select * from date_tasks where mandatory_qty = 1 and date_tasks_mission_id = " . $date_tasks_mission_id;
        $result = mysql_query($sql);
        if (mysql_num_rows($result) > 0) {

            // ***** See if there are any tasks that have not been completed yet ***** //
            $sql = "SELECT * ";
            $sql = $sql . "FROM date_tasks AS dt ";
            $sql = $sql . "LEFT JOIN date_tasks_marks AS dtm ON (dtm.user_id=" . $user_id . " AND dtm.date_task_id=dt.date_task_id) ";
            $sql = $sql . "WHERE dt.date_tasks_mission_id=" . $date_tasks_mission_id . " ";
            $sql = $sql . "AND dt.quantity IS NULL ";
            $sql = $sql . "AND dtm.date_task_id IS NULL ";
            $sql = $sql . "AND dt.mandatory_qty=1 ";

            $completed = true;
            $query = mysql_query($sql);
            $num_rows = mysql_num_rows($query);
            if ($num_rows > 0) $completed = false;
            // ***** See if there are any tasks that have not been completed yet ***** //

            if ($completed) {
                // ***** See if there are any tasks that have a mark that is below the required amount ***** //
                $sql = "SELECT * ";
                $sql = $sql . "FROM date_tasks AS dt ";
                $sql = $sql . "LEFT JOIN date_tasks_marks AS dtm ON (dtm.user_id=" . $user_id . " AND dtm.date_task_id=dt.date_task_id) ";
                $sql = $sql . "WHERE dt.date_tasks_mission_id=" . $date_tasks_mission_id . " ";
                $sql = $sql . "AND dt.mandatory_qty = 1 ";
                $sql = $sql . "AND dt.quantity IS NOT NULL ";
                $sql = $sql . "AND (dtm.date_task_id IS NULL OR dtm.done_qty < dt.quantity) ";
                // echo $sql;
                $query = mysql_query($sql);
                $num_rows = mysql_num_rows($query);

                if ($num_rows > 0) {
                    $completed = false;
                }
                // ***** See if there are any tasks that have a mark that is below the required amount ***** //
            }

            // check if mission mark exists
            $missionMarked = false;
            $sql = "select * from date_tasks_mission_marks where user_id = " . $user_id . " and date_tasks_mission_id = " . $date_tasks_mission_id;
            // echo $sql;
            $result = mysql_query($sql);
            if (mysql_num_rows($result) > 0) {
                $missionMarked = true;
            }

            if ($completed && !$missionMarked) {
                $sql = "SELECT * FROM date_tasks_missions WHERE date_tasks_mission_id=" . $date_tasks_mission_id;
                $query = mysql_query($sql);
                $row = mysql_fetch_assoc($query);

                $insert = "INSERT INTO date_tasks_mission_marks SET ";
                $insert = $insert . "user_id=" . $user_id . ", ";
                $insert = $insert . "date_tasks_mission_id=" . $date_tasks_mission_id . ", ";
                $insert = $insert . "subject_id=" . $row['subject_id'] . ", ";
                $insert = $insert . "mission_value=" . $row['mission_value'] . ", ";
                $insert = $insert . "mission_name='" . mysql_real_escape_string($row['mission_name']) . "', ";
                $insert = $insert . "mark_date=" . $row['end_date'] . ", ";
                $insert = $insert . "mark_override=0, ";
                $insert = $insert . "missions_updated=0 ";
                $insert_query = mysql_query($insert);

                if ($insert_query)
                    $marked = true;
            } else if ($missionMarked && !$completed) {
                $sql = "delete from date_tasks_mission_marks where user_id = " . $user_id . " and date_tasks_mission_id = " . $date_tasks_mission_id;
                //echo $sql;
                mysql_query($sql);
            }
        }
		
		return $marked;
	}
} 
?>