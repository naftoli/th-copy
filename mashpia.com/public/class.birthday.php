<?
require_once 'db.php';
require_once 'class.NewTasks.php';

abstract class Birthday {
	private $user_id;
	private $errors = array();
	private $year;
	private $enablePrev = false;
	private $grid_ids;

	/* abstract */ protected $lang_id;
	/* abstract */ protected $description;
	/* abstract */ protected $category;
	/* abstract */ protected $mandTasks = array();
	/* abstract */ protected $optTasks = array();
	/* abstract */ protected $qtyTasks = array();
	/* abstract */ protected $boysAgeTasks = array();
	abstract protected function missionName($yomHoledes, $yearsOld);

	public function __construct( $user_id ) {
		$this->user_id = $user_id;

		// initialize grid ids to more than the needed tasks
		for ($i = 1001; $i <= 1015; $i++) {
			$this->grid_ids[] = $i;
		}

		// dynamically get the current year ...
		// get the current jewish date from the julian date from the unix timestamp,
		// then split that by the date seperator ("/") and get the year (the third item from index 0)
		$this->year = explode("/", jdtojewish(unixtojd()))[2];
	}

	public function enablePrevious() {
		$this->enablePrev = true;
	}

	/**
	 * creates birthday tasks if not yet created for the users birthday and links the user to that mission
	 * 
	 * @param bool|string $clear_existing records from the birthdays table for this user bwefore creating new ones
	 * the default maintains the previous behaivour of only clearing it before creating english missions as that ran first
	 */
	public function setBirthday($clear_existing = "english_only") {
		if ($clear_existing === "english_only") {
			$clear_existing = $this->lang_id === 1;
		}
		$t = new NewTasks( $this->user_id, $this->lang_id );

		if ( !$t->setUserInfo() ) {
			$this->errors[] = $this->user_id . ' not found' . "<br />";
			return false;
		}
		//get birthday date
		$user = $t->getUserInfo();
		$dob = $user['dob'];
		/*
		if ( !empty( $user['dob_he'] ) && strpos( $user['dob_he'], '"' ) ) {
			//echo $user['dob_he'] . "<br />";
			return;
		}
		*/
		if ( empty( $dob ) ) {
			$this->errors[] = $user['first'] . ' ' . $user['last'] . ' is missing a dob.' . "<br />";
			return false;
		}
		$arrDOB = explode('-', $dob);
		//check that dob makes sense
		$yy = $arrDOB[0];
		$mm = $arrDOB[1];
		$dd = $arrDOB[2];
		if ($yy > date('Y') || $yy < (date('Y') - 15) || $mm == 0 || $dd == 0) {
			//echo $user['user_id'] . "<br />";
			//print_r( $arrDOB );
			//echo "<br /><br />";
			$this->errors[] = "Invalid dob: " . implode(',', $arrDOB);
		}
		//check if dob_he should be one day further
		if ($user['dob_he_offset']) {
			//add one to dob
			$date = new DateTime( $dob );
			$date->add( new DateInterval( 'P1D' ) );
			$newDate = $date->format( 'Y-m-d' );
			$arrDOB = explode('-', $newDate);
		}
		$jd = gregoriantojd($arrDOB[1], $arrDOB[2], $arrDOB[0]);
		$jewish = jdtojewish($jd, true, CAL_JEWISH_ADD_GERESHAYIM + CAL_JEWISH_ADD_ALAFIM_GERESH);
		$j = iconv('WINDOWS-1255', 'UTF-8', $jewish);
		//if ( empty( $user['dob_he'] ) || ( !empty( $user['dob_he'] ) && !strpos( $user['dob_he'], '"' ) ) )

		//find out if user born in leap year
		$jDate = jdtojewish($jd);
		$arrJDate = explode("/", $jDate);
		$hMonth = $arrJDate[0];
		$hDay = $arrJDate[1];
		if (((7 * $arrJDate[2] + 1) % 19) < 7) {
			$bornInLeap = true;
		} else {
			$bornInLeap = false;
		}

		// check if the birthday is before the current date (so it is in the past and we need to add the birthday for next year)
		if ( !$this->enablePrev ) {
			$jNow = jdtojewish(unixtojd()); // get the current jewish date from the unix timestamp
			$arrJNow = explode('/', $jNow); // split the year up into an array like so [m, d, y]
			// if the month is before/equal to today and the date is before/equal to today
			if ($arrJDate[0] <= $arrJNow[0] && $arrJDate[1] <= $arrJNow[1]) $this->year++; // then jump to next year
		}

		//find out if birthday year is leap year
		$leap = ((7 * $this->year + 1) % 19) < 7;

		//if born in regular year and current year is leap year,
		//and month is adar, then month needs to be changed to adar II
		if (!$bornInLeap && $leap && $hMonth == 6) {
			$hMonth++;
		}

		$date = jewishtojd($hMonth, $hDay, $this->year);
		$t->setDates( $date, $date );
		//get hebrew date of birthday for mission name
		$he_date = jdtojewish( $date, true, CAL_JEWISH_ADD_GERESHAYIM + CAL_JEWISH_ADD_ALAFIM_GERESH );
		$yomHoledes = iconv( 'WINDOWS-1255', 'UTF-8', $he_date );
		$year = $this->year - $arrJDate[2];
		$missionName = $this->missionName($yomHoledes, $year);
		$mission = mysql_real_escape_string( $missionName );

		// if ( $date > 2459089 ) { // Aug 20, 2020
		//	 @mail(
		//		 "bugs@tzivoshashem.org", "Error: Invalid Birthday Dates",
		//		 json_encode([
		//			 "date" => $date,
		//			 "jewishtojd" => [ $hMonth, $hDay, $this->year ],
		//			 "user_id" => $this->user_id,
		//			 "mission" => $mission,
		//			 "server" => $_SERVER,
		//			 "request" => $_REQUEST
		//		 ])
		//	 );
		//	 return false;
		// }

		if ( !$t->createMission( $mission, $this->description ) ) {
			$this->errors[] = $this->user_id . " is not signed up to a class and is not signed up to yoma depagra / yom tov";
			return false;
		}
		if ( $t->needToCreateTasks() ) {
			$points = 0.5;
			$t->setCategory($this->category);
			$grid_id_idx = 0;

			foreach( $this->mandTasks as $task ) {
				if ( !$t->createTask( $task, $points, 1, null, $this->grid_ids[$grid_id_idx++] ) ) {
					$this->errors[] = "problem creating task " . mysql_error();
				}
			}

			foreach( $this->optTasks as $task ) {
				if ( !$t->createTask( $task, $points, 0, null, $this->grid_ids[$grid_id_idx++] ) ) {
					$this->errors[] = "problem creating task " . mysql_error();
				}
			}

			foreach( $this->qtyTasks as $qty => $task ) {
				if ( !$t->createTask( $task, $points, 1, $qty, $this->grid_ids[$grid_id_idx++] ) ) {
					$this->errors[] = "problem creating task " . mysql_error();
				}
			}

			foreach( $this->boysAgeTasks as $age => $task ) {
				if ( $year >= $age && $user['gender'] == 'M' ){
					if ( !$t->createTask( $task, $points, 0, null, $this->grid_ids[$grid_id_idx++] ) ) {
						$this->errors[] = "problm creating task " . mysql_error();
					}
				}
			}
		}

		if ($clear_existing) {
			$sql = "delete from birthdays where user_id = " . $this->user_id;
			mysql_query( $sql );
		};

		//add user_id and mission_id to birthday database
		$mission_id = $t->getMissionID();
		$sql = "insert ignore into birthdays values( $this->user_id, $mission_id )";
		mysql_query( $sql );
	}

	public function getErrors() {
		if ( count( $this->errors ) > 0 ) {
			return $this->errors;
		} else {
			return false;
		}
	}
}
