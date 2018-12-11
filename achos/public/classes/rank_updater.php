<?
class rank_updater 
{
	function __construct()
	{
	}
	/*
	function update_rank($user_id = 0) {
		$updated = false;
		
		// ***** Get the number of medals aquired ***** //
		$sql1 = "SELECT u.user_id, count(*) AS medals_aquired ";
		$sql1 = $sql1 . "FROM users AS u ";
		$sql1 = $sql1 . "JOIN medal_marks AS mm ON (mm.user_id=u.user_id) ";
		
		if ($user_id > 0) 
			$sql1 = $sql1 . "WHERE u.user_id=" . $user_id . " ";
			
		$sql1 = $sql1 . "GROUP BY u.user_id ";	
		
		$query1 = mysql_query($sql1);
		// ***** Get the number of medals aquired ***** //
		
		while ($row1 = mysql_fetch_assoc($query1)) {
			$medals_aquired = $row1['medals_aquired'];
			//echo 'medals_aquired:' . $medals_aquired  . '<br />';
			
			
			// ***** Get the number of medals required for each rank ***** //
			$sql2 = "SELECT * FROM ranks ORDER BY rank_ord";
			$query2 = mysql_query($sql2);
			while ($row2 = mysql_fetch_assoc($query2)) {
				$rank_ord = $row2['rank_ord'];
				$medals_required = $row2['medals_required'];
				//echo 'medals_required:' . $medals_required . '<br />';
				
				// ***** If the number of medals required has been reached the add the rank ***** //
				if ($medals_aquired >= $medals_required) {
					$sql3 = "SELECT count(*) AS rank_exists FROM rank_marks WHERE rank_ord=" . $rank_ord . " AND user_id=" . $user_id;
					$query3 = mysql_query($sql3);
					$row3 = mysql_fetch_assoc($query3);
						
					if ($row3['rank_exists'] == 0) {
						$today_julian = gregoriantojd(date("m"), date("d"), date("Y"));
						$insert_sql = "INSERT INTO rank_marks SET rank_ord=" . $rank_ord . ", user_id=" . $user_id . ", date_promoted=" . $today_julian;
						$insert_query = mysql_query($insert_sql);
						if ($insert_query) 
							$updated = true;
					}
				}
				// ***** If the number of medals required has NOT been reached the delete the rank ***** //
				else {
					$delete_sql = "DELETE FROM rank_marks WHERE rank_ord=" . $rank_ord . " AND user_id=" . $user_id;
					$delete_query = mysql_query($delete_sql);	
				}
					
			}
			// ***** Get the number of medals required for each rank ***** //
		}
		return $updated;	
	}	
	*/
	function update_rank_two($user_id) {
		// ***** Get the number of medals aquired ***** //
		$sql1 = "SELECT u.user_id, count(*) AS medals_aquired ";
		$sql1 = $sql1 . "FROM users AS u ";
		$sql1 = $sql1 . "JOIN medal_marks AS mm USING (user_id) ";
		if ($user_id > 0) {
			$sql1 = $sql1 . "WHERE user_id=" . $user_id . " ";
		}
		$sql1 = $sql1 . "GROUP BY u.user_id ";	
		$query1 = mysql_query($sql1);
		// ***** Get the number of medals aquired ***** //
		
		while ($row1 = mysql_fetch_assoc($query1)) {
			$medals_aquired = $row1['medals_aquired'];
			
			
			// ***** Get the number of medals required for each rank ***** //
			$sql2 = "SELECT * FROM ranks ORDER BY rank_ord";
			$query2 = mysql_query($sql2);
			while ($row2 = mysql_fetch_assoc($query2)) {
				$rank_ord = $row2['rank_ord'];
				$medals_required = $row2['medals_required'];
					
				// ***** If the number of medals required has been reached the add the rank ***** //
				if ($medals_aquired >= $medals_required) {
					$sql3 = "SELECT count(*) AS rank_exists FROM rank_marks WHERE rank_ord=" . $rank_ord . " AND user_id=" . $user_id;
					$query3 = mysql_query($sql3);
					$row3 = mysql_fetch_assoc($query3);
						
					if ($row3['rank_exists'] == 0) {
						$today_julian = gregoriantojd(date("m"), date("d"), date("Y"));
						$insert_sql = "INSERT INTO rank_marks ";
						$insert_sql = $insert_sql . "SET rank_ord=" . $rank_ord . ", ";
						$insert_sql = $insert_sql . "user_id=" . $user_id . ", ";
						$insert_sql = $insert_sql . "date_promoted=" . $today_julian . ", ";
						$insert_sql = $insert_sql . "ranks_updated=1";
						$insert_query = mysql_query($insert_sql);	
					}
				}
				// ***** If the number of medals required has NOT been reached the delete the rank ***** //
				else {
					$delete_sql = "DELETE FROM rank_marks WHERE rank_ord=" . $rank_ord . " AND user_id=" . $user_id;
					$delete_query = mysql_query($delete_sql);	
				}
					
			}
			// ***** Get the number of medals required for each rank ***** //
		}
			
	}	
	
} 
?>