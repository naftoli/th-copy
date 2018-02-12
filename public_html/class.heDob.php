<?php
class HeDob {
	private $user_id;
	private $dob;
	private $addOne;
	private $debug;
	
	public function __construct( $user_id, $debug = false ) {
		$this->user_id = $user_id;
		$sql = 'select dob, dob_he_offset from users where user_id = ' . $user_id;
		$result = mysql_query($sql);
		$row = mysql_fetch_assoc($result);
		$this->dob = $row['dob'];
		$this->addOne = $row['dob_he_offset'];
		$this->debug = $debug;
	}
	
	public function setHeDob() {
		$date = new DateTime( $this->dob );
		if ($this->addOne) $date->add(new DateInterval('P1D'));
		$newDate = $date->format( 'Y-m-d' );
		$arrDOB = explode('-', $newDate); 
			
		// find out hebrew birthday
		if (intval($arrDOB[0]) <= 2017 && intval($arrDOB[0]) >= 2003) {
			$jd = gregoriantojd($arrDOB[1], $arrDOB[2], $arrDOB[0]);
			$jDate = jdtojewish($jd);
			$arrJDate = explode("/", $jDate);
			$hMonth = $arrJDate[0];
			$hDay = $arrJDate[1];
			$hYear = $arrJDate[2];
			
			//find out if user born in leap year
			if (((7 * $arrJDate[2] + 1) % 19) < 7) {
				$bornInLeap = 1;
			} else {
				$bornInLeap = 0;
			}
			
			$user_id = $this->user_id;
			//find out if we are inserting or updating
			$sql = "select * from he_dob where user_id = " . $user_id;
			$result = mysql_query($sql);
			if (mysql_num_rows($result) > 0) {
				$sql = "update he_dob 
						set he_mm = $hMonth, 
						he_dd = $hDay, 
						he_yy = $hYear, 
						born_in_leap = $bornInLeap, 
						wp_synced = 0 
						where user_id = " . $user_id;
			} else {
				$sql = "insert into he_dob values($user_id, $hMonth, $hDay, $hYear, $bornInLeap, 0)";
			}
			if ( $this->debug ) echo $sql . "<br />";
			mysql_query($sql);
		
			$this->syncToWp();
		} else {
			echo $arrDOB[0] . '-' . $arrDOB[1] . '-' . $arrDOB[2] . "<br />";
		}
	}
	
	private function syncToWp() {
		require_once 'class.globalSettings.php';
		$year = GlobalSettings::getBirthdayYear();
		$sql = "select s.school_name, u.first, u.last, u.gender, d.*
				from he_dob d
				join users u using (user_id)
				join schools s using (school_id)
				where d.user_id = " . $this->user_id;
		$result = mysql_query($sql);
		$child = mysql_fetch_assoc($result);
		
		//figure out date of birthday
		if (intval($child['he_mm']) > 0 && (intval($child['he_dd']) > 0 && intval($child['he_dd']) < 31) ) {			
			// find out todays hebrew date
			$todayHe = jdtojewish(unixtojd());
			$arrHe = explode('/', $todayHe);
			// if birthday is between now and the new hebrew year, decrease the year to current year
			if ($arrHe[2] < $year && $arrHe[0] <= intval($child['he_mm']) && $arrHe[1] < intval($child['he_dd'])) $year--;
			
			$child['age'] = $year - intval($child['he_yy']);
			//echo $child['he_mm'] . ':' . $child['he_dd'] . ':' . $year . "<br />";
			$jd = floor(jewishtojd($child['he_mm'], $child['he_dd'], $year));
			$dob = jdtogregorian($jd);
			//echo $dob . "<br />";
			$arrDob = explode('/', $dob);
			// add 0 padding to single digit mm and dd
			if (strlen($arrDob[0]) == 1) $arrDob[0] = '0' . $arrDob[0];
			if (strlen($arrDob[1]) == 1) $arrDob[1] = '0' . $arrDob[1];
			$postDate = $arrDob[2] . '-' . $arrDob[0] . '-' . $arrDob[1];
			//if ( $this->debug ) echo $this->user_id . ' : ' . $child['first'] . ' ' . $child['last'] . ' : ' . $postDate . "<br />";
			
			$arrPost = array(
				'info' => $child,
				'post' => array(
					'post_date'     =>  $postDate,
					'post_title'	=>	ucwords($child['first'] . ' ' . $child['last']), 
					'post_content'	=>	'', 
					'post_status'	=>	'future', 
					'post_type'		=>	'birthday', 
					'post_author'	=>	1 
				),
			);
			//echo "<pre>"; print_r($arrPost); echo "</pre>";
			
			// change db to wp
			mysql_select_db('wp');

			require_once dirname(__FILE__)."/blog/wp-blog-header.php";
			
			//$id = wp_insert_post( $arrPost['post'] );
			//if ($id) {
			//	//echo $id . "<br />";
			//	// delete old birthday posts
			//	$oldIDs = array();
			//	$sql = "select * from wp_posts 
			//			where post_title = '" . $arrPost['post']['post_title'] . "'
			//			and post_type = 'birthday'
			//			and ID != " . $id;
			//	$result = mysql_query($sql);
			//	while ($row = mysql_fetch_assoc($result)) {
			//		$oldIDs[]  = $row['ID'];
			//	}
			//	//echo "<pre>"; print_r($oldIDs); echo "</pre>";
			//	foreach ($oldIDs as $postID) {
			//		wp_delete_post( $postID, true );
			//	}
			//	
			//	$gender = strtolower($arrPost['info']['gender']);
			//	if ($gender == 'm') $gender = 'boy';
			//	else if ($gender == 'f') $gender = 'girl';
			//	add_post_meta( $id, 'user_id', $arrPost['info']['user_id'] );
			//	add_post_meta( $id, 'school', $arrPost['info']['school_name'] );
			//	add_post_meta( $id, 'gender', $gender );
			//	add_post_meta( $id, 'age', $arrPost['info']['age'] );
			//	add_post_meta( $id, 'registered', 1 );
			//	$sqlUpdate = "update mashpiadb.he_dob set wp_synced = 1 where user_id = " . $this->user_id;
			//	mysql_query($sqlUpdate);
			//}
			// change db back to mashpiadb
			mysql_select_db('mashpiadb');
		}
	}
}
