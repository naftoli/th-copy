<?
class medal_updater {
	
	function __construct()
	{
	}

	function update_medal_two($user_id) {
		// ***** Get the number of finished missions ***** //
		$sql1 = "SELECT count(*) AS finished_missions ";
		$sql1 .= "FROM date_tasks_mission_marks ";
		$sql1 .= "WHERE user_id=" . $user_id;
		
		$startSql = "select start_date from reg_year 
					join users u using (heb_year) 
					where u.user_id = " . $user_id;
		$startRes = mysql_query($sql);
		if (mysql_num_rows($startRes) > 0) {
			$startRow = mysql_fetch_assoc($startRes);
			$sql1 .= " and mark_date > " . $startRow['start_date'];
		}
				
		$query1 = mysql_query($sql1);
		// ***** Get the number of finished missions ***** //
		
		//if ($row1) {
		if (mysql_num_rows($query1) > 0) {
			while ($row1 = mysql_fetch_assoc($query1)) {
				$finished_missions = $row1['finished_missions'];
	
				// ***** Get the number of medals required to achieve medal mark ***** //		
				$missions_required = 0;
				$sql2 = "SELECT * FROM medals_subjects WHERE subject_id = 1 ORDER BY medal_ord ";
				//echo $sql2 . "<br />";
				$query2 = mysql_query($sql2);
				while ($row2 = mysql_fetch_assoc($query2)) {
					$missions_required += $row2['missions_required'];
								
					// ***** If the number has been met then add the medal ***** //
					if ($finished_missions >= $missions_required) {
						$sql3 = "SELECT count(*) AS number_of_medals FROM medal_marks WHERE medal_ord=" . $row2['medal_ord'] . " AND subject_id=1 AND user_id=" . $user_id;
						$query3 = mysql_query($sql3);
						$row3 = mysql_fetch_assoc($query3);
						//echo $sql3;
							
						if ($row3['number_of_medals'] == 0) {
							//$medals_counter++;
							$today_julian = gregoriantojd(date("m"), date("d"), date("Y"));
							$insert_sql = "INSERT INTO medal_marks ";
							$insert_sql = $insert_sql . "SET medal_ord=" . $row2['medal_ord'] . ", ";
							$insert_sql = $insert_sql . "subject_id=1, ";
							$insert_sql = $insert_sql . "user_id=" . $user_id . ", ";
							$insert_sql = $insert_sql . "date_awarded=" . $today_julian . ", ";
							$insert_sql = $insert_sql . "medals_updated=1";
							$insert_query = mysql_query($insert_sql);						
						}										
					}
					// ***** If the number has NOT been met then delete the medal if it exists ***** //
					else {
						$delete_sql = "DELETE FROM medal_marks WHERE medal_ord=" . $row2['medal_ord'] . " AND subject_id=1 AND user_id=" . $user_id;
						//echo $row1['user_id'] . ": " . $delete_sql . "<br />";
						$delete_query = mysql_query($delete_sql) or die(mysql_error());				
					}				
				}
			
			}
		}	
	}	
		
} 
?>