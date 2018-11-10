<?
class rank_updater {
	
	function update_rank_two($user_id, $debug = false) {
		$response = false;
		// ***** Get the number of medals aquired ***** //
		$sql1 = "SELECT u.user_id, count(*) AS medals_aquired ";
		$sql1 = $sql1 . "FROM users AS u ";
		$sql1 = $sql1 . "JOIN medal_marks AS mm USING (user_id) ";

		if ($user_id > 0)
			$sql1 = $sql1 . "WHERE user_id=" . $user_id . " ";

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
						//$today_julian = gregoriantojd(date("m"), date("d"), date("Y")); causing major bugs b/c date works on UTC and jd works on EST
						$today_julian = unixtojd();
						$insert_sql = "INSERT INTO rank_marks ";
						$insert_sql = $insert_sql . "SET rank_ord=" . $rank_ord . ", ";
						$insert_sql = $insert_sql . "user_id=" . $user_id . ", ";
						$insert_sql = $insert_sql . "date_promoted=" . $today_julian . ", ";
						$insert_sql = $insert_sql . "ranks_updated=1";
						$insert_query = mysql_query($insert_sql);
						
						$this->updateWP($rank_ord, $user_id);
						
						$response = ['rank_ord' => $rank_ord ];
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
		return $response;
	}

	function updateWP( $rank, $user ) {
		$info = $this->getInfo( $rank, $user );
		require_once($_SERVER['DOCUMENT_ROOT']."/blog/wp-load.php");
		$this->import_promotion($info);
		@mysql_select_db("mashpiadb");
	}
	
	function getInfo( $rank, $user ) {
		@mysql_select_db("mashpiadb");
		$sql = "select first, last, gender, school_name from users 
				join schools using (school_id) 
				where user_id = " . $user;
		$result = mysql_query($sql);
		$row = mysql_fetch_assoc($result);
		$name = $row['first'] . ' ' . $row['last'];
		$school = $row['school_name'];
		$gender = $row['gender'];
		if (strtolower($gender) == 'm') $gender = "boy";
		else if (strtolower($gender) == 'f') $gender = "girl";
		
		$sql = "select rank_name from ranks where rank_ord = " . $rank;
		$result = mysql_query($sql);
		$row = mysql_fetch_assoc($result);
		$rankName = $row['rank_name'];
		@mysql_select_db("wp");
		
		return array(
			'user'		=>	$user, 
			'name'		=>	$name, 
			'gender'	=>	$gender, 
			'school'	=>	$school, 
			'rankName'	=>	$rankName
		);
	}
	
	function import_promotion($info) {				
		$post = array(
			'post_name'		=>	$info['name'], 
			'post_title'	=>	$info['name'], 
			'post_content'	=>	'', 
			'post_status'	=>	'publish', 
			'post_type'		=>	'promotion', 
			'post_author'	=>	1 
		);
		
		$id = wp_insert_post( $post );
		if ($id) {
			add_post_meta( $id, 'user_id', $info['user'] );
			add_post_meta( $id, 'school', $info['school'] );
			add_post_meta( $id, 'gender', $info['gender'] );
			add_post_meta( $id, 'rank', $info['rankName'] );
			/*
			$term = get_term_by( 'name', $info['school'], 'school' );
			if ( $term ) wp_set_object_terms( $id, (int)$term->term_id, 'school' );
			$term2 = get_term_by( 'name', $info['gender'], 'gender' );
			if ( $term2 ) wp_set_object_terms( $id, (int)$term2->term_id, 'gender' );
			$term3 = get_term_by( 'name', $info['rankName'], 'rank' );
			if ( $term3 ) wp_set_object_terms( $id, (int)$term3->term_id, 'rank' );
			*/
		}
	}
}
?>