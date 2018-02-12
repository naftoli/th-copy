<?
class BirthdayYi {
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
            "איך האב געזאגט מיין נייער קאפיטל", 
            "איך האב געמאכט א פארבריינגען צו דאנקעןדעםאויבערשטן פאר מירצובריינגען צו דערספעציעלערטאג", 
            "איך האב געמאכט א נייער החלטה. וואסאיז דיין החלטה:_________________"
        );
        $this->optTasks = array(
            "איך האב געדאווענט מיט מער כוונה",  
            "איך האב געגעבן צדקה פאר שחרית און פאר מנחה (אויב מיין יום הולדת פאלטאויסאויף שבת, האב איך געגעבן צדקה פאר און נאךשבת)", 
            "איך האב געלערנט מיין נייער קאפיטל",
            "איך האב געמאכט א חשבון הנפש, זאכן וואסאיך האב געטאןגוט, און זאכן וואס איך דארףפארבעסערן", 
            "איך האב געמאכט א ברכה שהחיינו אויף א נייעפרוכט", 
            "איך האב צוגעגבן אין לערנען תורה. וואסהאסטוגעלערנט?_________________", 
            "איך האב צוגעגעבן אין אהבת ישראל. וואסהאסטוגעטאן?_________________" 
        );
            
        $this->qtyTasks = array(
            '1-150' =>  "איך האב צוגעגעבן אין זאגן תהילים. וויפלקאפיטלעךהאסטוגעזאגט?___________________"
        );
            
        $this->ageTasks = array(
            '10' => "איך האב געלערנט א טייל פון א מאמר אויףאויסווייניק, און עסגע'חזר'ט (אויפןטאג פון מיין יום הולדת, אדערדער שבת נאךדעם). וועלכער מאמר האסטוגע'חזר'ט? _____________________",
            '13' => "שבת פאר מיין יום הולדת (אויב מיין יום הולדת איז שבת, איזאויף שבת) האב איך געהאט אן עלי' לתורה"
        );
        
        $this->errors = array();
        require_once 'db.php';
        require_once 'class.NewTasks.php';
		// get the year from the global_settings table
		//$sql = "select `val` from global_settings where `key` = 'birthday_year'";
		//$result = mysql_query($sql);
		//$row = mysql_fetch_assoc($result);
		//$this->year = intval($row['val']);
		
		// dynamically get the current year ...
		// get the current jewish date from the julian date from the unix timestamp,
		// then split that by the date seperator ("/") and get the year (the third item from index 0)
		$this->year = explode("/", jdtojewish(unixtojd()))[2];
    }
    
    private function setUsers() {
        $sql = "select user_id from users where dob > 0 and school_type_id in (2,3) order by user_id desc";
        $result = mysql_query( $sql );
        while ( $row = mysql_fetch_assoc( $result ) ) {
            $this->users[] = $row['user_id'];
        }
    }

    public function setBirthday() {
        foreach ( $this->users as $user_id ) {
            $lang_id = 2;
            $t = new NewTasks( $user_id, $lang_id );
    
            if ( $t->setUserInfo() ) {
                //get birthday date
                $user = $t->getUserInfo();
                $dob = $user['dob'];
				/*
                if ( !empty( $user['dob_he'] ) && strpos( $user['dob_he'], '"' ) ) {
                	//echo $user['dob_he'] . "<br />"; 
                    continue;
                }
				 * 
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
	                if ( empty( $user['dob_he'] ) || ( !empty( $user['dob_he'] ) && !strpos( $user['dob_he'], '"' ) ) ) 
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
	                $missionName = "מזל טוב פאר דיין יום הולדת - " . $yomHoledes;
	                $mission = mysql_real_escape_string( $missionName );
                    $description = 'יום הולדת';
	                    
	                if ( $t->createMission( $mission, $description ) ) {
	                    if ( $t->needToCreateTasks() ) {
	                        $points = 0.5;
	                        $t->setCategory('יום הולדת');
	
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
	
	                    //add user_id and mission_id to birthday database
						$mission_id = $t->getMissionID();
	                    $sql = "insert ignore into birthdays values( $user_id, $mission_id )";
	                    mysql_query( $sql );
                    } else {
                        $this->errors[$user_id][] = $user_id . " is not signed up to a class and is not signed up to yoma depagra";
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