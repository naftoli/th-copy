<?
class Birthday {
    private $users;
    private $mandTasks;
    private $optTasks;
    private $qtyTasks;
    private $ageTasks;
    private $errors;
    
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
				 * 
				 */
                $gender = $user['gender'];
                if ( !empty( $dob ) ) {
                    $arrDOB = explode('-', $dob);
                    //check that dob makes sense
                    if ( $arrDOB[0] > date('Y') || $arrDOB[0] < ( date('Y') - 15 ) || $arrDOB[1] == 0 || $arrDOB[2] == 0 ) {
                        //echo $user['user_id'] . "<br />";    
                        //print_r( $arrDOB );
                        //echo "<br /><br />";    
                        continue;
                    } 
                    //check if dob_he should be one day further
                    if ( $user['dob_he_offset'] ) {
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

                $jewish = jdtojewish($jd);
                $arrJewish = explode('/', $jewish);
                $hMonth = $arrJewish[0];
                $hDay = $arrJewish[1];
                $jewishNow = jdtojewish(unixtojd());
                $arrJewishNow = explode('/', $jewishNow);
                if ($hMonth == 13)
                    $hYear = 5773;
                else 
                    $hYear = 5774; 

                $date = jewishtojd($hMonth, $hDay, $hYear);
                $t->setDates( $date, $date );

                //get hebrew date of birthday for mission name
                $he_date = jdtojewish( $date, true, CAL_JEWISH_ADD_GERESHAYIM + CAL_JEWISH_ADD_ALAFIM_GERESH );
                $yomHoledes = iconv( 'WINDOWS-1255', 'UTF-8', $he_date );
                $year = $hYear - $arrJewish[2];
                $missionName = $yomHoledes . " - Happy " . $year . "th Birthday!";
                $mission = mysql_real_escape_string( $missionName );
                    
                if ( $t->createMission( $mission ) ) {
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

                    //check if user already has birthday mission and delete
                    $mission_id = $t->getMissionID();
                    $sql = "delete from birthdays where user_id = " . $user_id;
                    mysql_query( $sql );

                    //add user_id and mission_id to birthday database
                    $sql = "insert ignore into birthdays values( $user_id, $mission_id )";
                    mysql_query( $sql );
                    } else {
                        $this->errors[$user_id][] = $user_id . " is not signed up to a class and is not signed up to yoma depagra";
                    }
                } else {
                    echo $this->errors[$user_id][] = $user['first'] . ' ' . $user['last'] . ' is missing a dob.' . "<br />";
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