<?
class mission_updater 
{
	
	function __construct()
	{
	}
	
	function mission_update($user_id, $date_tasks_mission_id) 
	{
		$marked = false;
		$completed = true;
		
		// ***** See if there are any tasks that have not been completed yet ***** //
		$sql = "SELECT * ";
		$sql = $sql . "FROM date_tasks AS dt ";
		$sql = $sql . "LEFT JOIN date_tasks_marks AS dtm ON (dtm.user_id=" . $user_id . " AND dtm.date_task_id=dt.date_task_id) ";
		$sql = $sql . "WHERE dt.date_tasks_mission_id=" . $date_tasks_mission_id . " ";
		$sql = $sql . "AND dt.quantity IS NULL ";
		$sql = $sql . "AND dtm.date_task_id IS NULL ";
		$sql = $sql . "AND dt.mandatory_qty=1 ";
		
		$query = mysql_query($sql);
		$num_rows = mysql_num_rows($query);
		
		if ($num_rows > 0)
			$completed = false;
		// ***** See if there are any tasks that have not been completed yet ***** //
		
		if ($completed == true)
		{
			// ***** See if there are any tasks that have a mark that is below the required amount ***** //
			$sql = "SELECT * ";
			$sql = $sql . "FROM date_tasks AS dt ";
			$sql = $sql . "LEFT JOIN date_tasks_marks AS dtm ON (dtm.user_id=" . $user_id . " AND dtm.date_task_id=dt.date_task_id) ";
			$sql = $sql . "WHERE dt.date_tasks_mission_id=" . $date_tasks_mission_id . " ";
			$sql = $sql . "AND dt.quantity IS NOT NULL ";
			$sql = $sql . "AND (dtm.date_task_id IS NULL OR dtm.done_qty < dt.quantity) ";
			
			$query = mysql_query($sql);
			$num_rows = mysql_num_rows($query);
			
			if ($num_rows > 0)
				$completed = false;
			// ***** See if there are any tasks that have a mark that is below the required amount ***** //
		}
		
		if ($completed == true)
		{	
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
		}
		
		return $marked;
		
	}
		
} 
?>