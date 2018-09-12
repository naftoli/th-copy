<?
class Birthday {
    private $users;
    private $mandTasks;
    private $optTasks;
    private $qtyTasks;
    private $ageTasks;
    private $errors;
	private $year;
    
    public function __construct( $user_id = 0 ) {
        if ( $user_id > 0 ) {
            $this->users = array( $user_id );
        } else {
            $this->setUsers();
        }
        $this->mandTasks = array(
            'I said my new kapital.', 
            'I made a farbrengen to thank Hashem for reaching this special day.', 
            'I made a new hachlata. What is your hachlata? ________________________'
        );
        $this->optTasks = array(
            'I davened with extra kavana.',  
            'I gave extra tzedoka before Shacharis (and Mincha. If my birthday was on Shabbos, I gave extra before and after.)', 
            'I learnt my new kapital.',
            'I made a Cheshbon Hanefesh to go over which areas of my life I am doing well in and in which areas I need to improve.', 
            'I made a Shehechiyanu on a new fruit.', 
            'I learnt extra Torah. What did you learn? ________________________', 
            'I had extra Ahavas Yisroel. How did you have extra Ahavas Yisroel? ________________________' 
        );
            
        $this->qtyTasks = array(
            '1-150' =>  'I said extra tehillim. How many extra kapitelach did you say?'
        );
            
        $this->ageTasks = array(
            '10' => 'I learnt part of a Maamer by heart and reviewed it (on my birthday or on the Shabbos after). Which Maamer did you learn? __________________', 
            '13' => 'On the Shabbos before my birthday (and if my birthday fell out on a day the Torah is read, on that day) I was called up for an Aliya.'
        );
        
        $this->errors = array();
        
		require_once 'db.php';
        require_once 'class.NewTasks.php';
		//require_once 'class.globalSettings.php';
		
		//$this->year = GlobalSettings::getBirthdayYear();
		// dynamically get the current year
		$this->year = explode("/", jdtojewish(unixtojd()))[2]; // get the current jewish date from the julian date from the unix timestamp, split that by the date seperator ("/") and get the year (the third item from index 0)
    }
	
	public function setYear($year) {
		$this->year = $year;
	}
    
    private function setUsers() {
        $this->users = [];
    }

    public function setBirthday() {
        foreach ( $this->users as $user_id ) {
            $t = new NewTasks( $user_id );
    
            if ( $t->setUserInfo() ) {
                //get birthday date
                $user = $t->getUserInfo();
                $dob = $user['dob'];
				/*
                if ( !empty( $user['dob_he'] ) && strpos( $user['dob_he'], '"' ) ) {
                	//echo $user['dob_he'] . "<br />"; 
                    continue;
                }
				 */
                $gender = $user['gender'];
                if ( !empty( $dob ) ) {
                    $arrDOB = explode('-', $dob);
                    //check that dob makes sense
                    $yy = $arrDOB[0];
					$mm = $arrDOB[1];
					$dd = $arrDOB[2];
                    if ($yy > date('Y') || $yy < (date('Y') - 15) || $mm == 0 || $dd == 0) {
                        //echo $user['user_id'] . "<br />";    
                        //print_r( $arrDOB );
                        //echo "<br /><br />";    
                        $this->errors[$user_id][] = "Invalid dob: " . implode(',', $arrDOB);
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
	                $this->setHeDob( $j, $user_id );
					
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
					
					// check if the birhday is before the current date (so it is in the past and we need to add the birthday for next year)
					$jNow = jdtojewish(unixtojd()); // get the current jewish date from the unix timestamp
					$arrJNow = explode('/', $jNow); // split the year up into an array like so [m, d, y]
					// if the month is before/equal to today and the date is before/equal to today
					if ($arrJDate[0] <= $arrJNow[0] && $arrJDate[1] <= $arrJNow[1]) $this->year++; // then jump to next year
					
					//find out if current year is leap year
					if (((7 * $this->year + 1) % 19) < 7) {
						$leap = true;
					} else {
						$leap = false;
					}
					
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
					switch ($year) {
						case 1:
							$ext = "st Birthday!";
							break;
						case 2:
							$ext = "nd Birthday!";
							break;
						case 3:
							$ext = "rd Birthday!";
							break;
						default:
							$ext = "th Birthday!";
							break;
					}
	                $missionName = $yomHoledes . " - Happy " . $year . $ext;
	                $mission = mysql_real_escape_string( $missionName );
                    $description = 'Yom Holedes Mission';
                    
                    if ( $date > 2459089 ) { // Aug 20, 2020
                        @mail(
                            "bugs@tzivoshashem.org", "Error: Invalid Birthday Dates", 
                            json_encode([
                                "date" => $date,
                                "jewishtojd" => [ $hMonth, $hDay, $this->year ],
                                "user_id" => $user_id,
                                "mission" => $mission,
                                "server" => $_SERVER,
                                "request" => $_REQUEST
                            ])
						);
						return false;
                    }
	                    
	                if ( $t->createMission( $mission, $description ) ) {
	                    if ( $t->needToCreateTasks() ) {
	                        $points = 0.5;
	                        $t->setCategory('Birthday');
	
	                        foreach( $this->mandTasks as $task ) {
	                            if ( !$t->createTask( $task, $points, 1 ) ) {
	                                $this->errors[$user_id][] = "problem creating task " . mysql_error();
	                            }
	                        }
	
	                        foreach( $this->optTasks as $task ) {
	                            if ( !$t->createTask( $task, $points, 0 ) ) {
	                                $this->errors[$user_id][] = "problem creating task " . mysql_error();
	                            }
	                        }
	
	                        foreach( $this->qtyTasks as $qty => $task ) {
	                            if ( !$t->createTask( $task, $points, 1, $qty ) ) {
	                                $this->errors[$user_id][] = "problem creating task " . mysql_error();
	                            }
	                        }
	
	                        foreach( $this->ageTasks as $age => $task ) {
	                            if ( $year >= $age && $gender == 'M' ){  
	                                if ( !$t->createTask( $task, $points, 0 ) ) {
	                                    $this->errors[$user_id][] = "problem creating task " . mysql_error();
	                                }
	                            }
	                        }
	                    }
	
	                    //check if user already has birthday missions and delete - only for english b/c it's the first one to run
	                    $sql = "delete from birthdays where user_id = " . $user_id;
	                    @mysql_query( $sql );
	
	                    //add user_id and mission_id to birthday database
						$mission_id = $t->getMissionID();
	                    $sql = "insert ignore into birthdays values( $user_id, $mission_id )";
						@mysql_query( $sql );
						return true;
                    } else {
                        $this->errors[$user_id][] = $user_id . " is not signed up to a class and is not signed up to yoma depagra / yom tov";
                    }
                } else {
                    $this->errors[$user_id][] = $user['first'] . ' ' . $user['last'] . ' is missing a dob.' . "<br />";
                }    
            }
        }
    }

    private function setHeDob( $heDOB, $user_id ) {
        $heDOB = mysql_real_escape_string( $heDOB );
        $sql = "update users set dob_he = '" . $heDOB . "' where user_id = " . $user_id;
        mysql_query( $sql ) or die( mysql_error() );
    } 

    public function getErrors() {
        if ( count( $this->errors ) > 0 ) {
            return $this->errors;
        } else {
            return false;
        }
    }  
}
?>