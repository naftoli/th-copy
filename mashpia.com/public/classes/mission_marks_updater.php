<?php 
class mission_marks_updater {
		
	function __construct()
	{
	}
	
	function mission_marks_update($user_id, $output = false) 
	{
		require_once("user_track.php");
		require_once("date_tasks_mission.php");
				
		$sql = "SELECT * ";
		$sql = $sql . "FROM user_tracks ";
		$sql = $sql . "WHERE track_id IS NOT NULL AND level IS NOT NULL ";
		if ($user_id > 0)
			$sql = $sql . "AND user_id=" . $user_id . " ";
		$sql = $sql . "ORDER BY user_id, subject_id, track_id, level";
		
		$query = mysql_query($sql);
		
		while ($row = mysql_fetch_assoc($query)) 
		{
			$user_track = new user_track($row);
			$user_track->set_school_type_id();
			//$user_track->check_missions($output);	
			//$user_track->update_missions();
			/*
			if ($updated == false)
				$updated = $user_track->update_mission();
			else
				$updated = $user_track->update_mission();
			*/
		}
	}
	
	function mission_marks_update_by_subject_id($user_id, $subject_id) 
	{
		require_once("user_track.php");
		require_once("date_tasks_mission.php");
		
		$sql = "SELECT * ";
		$sql = $sql . "FROM user_tracks ";
		$sql = $sql . "WHERE user_id=" . $user_id . " ";
		$sql = $sql . "AND subject_id=" . $subject_id . " ";
		$sql = $sql . "AND track_id IS NOT NULL ";
		$sql = $sql . "AND level IS NOT NULL ";
		$sql = $sql . "ORDER BY user_id, subject_id, track_id, level";
		$query = mysql_query($sql);
		while ($row = mysql_fetch_assoc($query)) 
		{
			$user_track = new user_track($row);
			$user_track->set_school_type_id();
			//$user_track->check_missions();
		}
	}
} 
?>