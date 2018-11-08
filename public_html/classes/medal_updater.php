<?
class medal_updater {

	function update_medal_two( $user_id ) {
		$user_id = intval( $user_id );
		// ***** Get the number of finished missions ***** //
		$sql1 = "SELECT s.subject_id, u.user_id, s.subject_name, SUM( mission_count ) AS finished_missions ";
		$sql1 .= "FROM users AS u ";
		$sql1 .= "JOIN user_tracks AS ut USING (user_id) ";
		$sql1 .= "JOIN date_tasks_mission_marks AS dtmm ON (u.user_id=dtmm.user_id AND ut.subject_id=dtmm.subject_id) ";
		$sql1 .= "JOIN subjects AS s ON (ut.subject_id=s.subject_id) ";
		$sql1 .= "WHERE u.school_id > 0 ";
		$sql1 .= "AND u.school_type_id > 0 ";
		//$sql1 .= "AND ut.enrolled=1 ";
		if ($user_id > 0)
			$sql1 .= "AND u.user_id= $user_id ";
		$sql1 .= "GROUP BY ut.subject_id ";

		$query1 = mysql_query($sql1);
		// ***** Get the number of finished missions ***** //
		
		//if ($row1) {
		while ($row1 = mysql_fetch_assoc($query1)) {
			$finished_missions = $row1['finished_missions'];
			// ***** Get the number of medals required to achieve medal mark ***** //
			$missions_required = 0;
			$sql2 = "SELECT * FROM medals_subjects WHERE subject_id=" . $row1['subject_id'] . " ORDER BY medal_ord ";
			//if ($user_id == 51629) echo $sql2 . "<br />";
			$query2 = mysql_query($sql2);
			while ($row2 = mysql_fetch_assoc($query2)) {
				$missions_required = $missions_required + $row2['missions_required'];			
				
				// ***** If the number has been met then add the medal ***** //
				if ($finished_missions >= $missions_required) {
					$sql3 = "SELECT count(*) AS number_of_medals FROM medal_marks WHERE medal_ord=" . $row2['medal_ord'] . " AND subject_id=" . $row1['subject_id'] . " AND user_id=" . $row1['user_id'];
					$query3 = mysql_query($sql3);
					$row3 = mysql_fetch_assoc($query3);
					//echo $sql3;
						
					if ($row3['number_of_medals'] == 0) {
						//$medals_counter++;
						//$today_julian = gregoriantojd(date("m"), date("d"), date("Y")); causing major bugs b/c date works on UTC and jd works on EST
						$today_julian = unixtojd();
						
						$insert_sql  = "INSERT INTO medal_marks ";
						$insert_sql .= "SET medal_ord=" . $row2['medal_ord'] . ", ";
						$insert_sql .= "subject_id=" . $row1['subject_id'] . ", ";
						$insert_sql .= "user_id=" . $row1['user_id'] . ", ";
						$insert_sql .= "date_awarded=" . $today_julian . ", ";
						$insert_sql .= "medals_updated=1";

						$insert_query = mysql_query($insert_sql);
						return ['medal_ord' => $row2['medal_ord'], 'subject' => $row1['subject_name'], 'subject_id' => $row1['subject_id']];
					}										
				}
				// ***** If the number has NOT been met then delete the medal if it exists ***** //
				else {
					$delete_sql = "DELETE FROM medal_marks WHERE medal_ord=" . $row2['medal_ord'] . " AND subject_id=" . $row1['subject_id'] . " AND user_id=" . $row1['user_id'];
					//echo $row1['user_id'] . ": " . $delete_sql . "<br />";
					$delete_query = mysql_query($delete_sql) or die(mysql_error());				
				}				
			}
		
		}
	
	}
} 
?>