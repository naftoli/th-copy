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
		$yy = intval( $arrDOB[0] );
		// make sure birthday is within correct range
		if ( $yy < date('Y') && $yy > (date('Y') - 15)  ) {
			$jd = gregoriantojd($arrDOB[1], $arrDOB[2], $arrDOB[0]);
			$jDate = jdtojewish($jd);
			$arrJDate = explode("/", $jDate);
			if ( $this->debug ) {
				echo $this->user_id;
				echo "<pre>"; print_r( $arrJDate ); echo "</pre>";
				return;
			}
			$hMonth = $arrJDate[0];
			$hDay = $arrJDate[1];
			$hYear = $arrJDate[2];

			//find out if user born in leap year
			if (((7 * $arrJDate[2] + 1) % 19) < 7) {
				$bornInLeap = 1;
			} else {
				$bornInLeap = 0;
			}

			// update users table
			// $dobHe = jdtojewish( $jd, true, CAL_JEWISH_ADD_GERESHAYIM );
			// $dobHe = iconv ('WINDOWS-1255', 'UTF-8', $dobHe);
			// $sqlHe = "update users set dob_he = '" . $dob_he . "' where user_id = " . $this->user_id;
			// mysql_query( $sqlHe );
			
			$user_id = $this->user_id;
			//find out if we are inserting or updating
			$sql = "SELECT * FROM he_dob WHERE user_id = " . $user_id;
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
			// if ( $this->debug ) echo $sql . "<br />";
      		mysql_query($sql);
		}
	}
}
