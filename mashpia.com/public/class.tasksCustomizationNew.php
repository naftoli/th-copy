<?
require_once 'class.defaults.php';

class TasksCustomizationNew { 
    private $start;
    private $end;
    private $type;
    private $id;
    private $schoolType;
    private $school_id;
    private $parent_id; // the parent ID to load the i
    private $d;
    private $debug;
    private $mission_type_subjects = array(
        'chabad' => array(
            1,  4,  12, 13, 15, 16, 21, 27, 40, 41, 42, 45, 90,             100 #=> 12, 13, 40
        ),
        'frum' => array(
            1,  4,          15, 16, 21, 27,     41, 42, 45, 90, 92, 93, 94, 100 #=> 92, 93, 94
        ), 
        'day_school' =>  array(
            121, 122, 124, 125, 126, 127, 129, 130, 131, 132, 133, 134, 135
        )
    );

    public function __construct() {
        $this->start = null;
        $this->end = null;
        $this->type = null;
        $this->id = null;
        $this->schoolType = null;
        $this->school_id = null;
		$this->parent_id = null;
		$this->lang = 1;
    }
    
    public function setStart( $start ) {
        $this->start = $start;
    }

    public function setEnd( $end ) {
        $this->end = $end;
    }
	
	public function setLang( $value ) {
		$this->lang = $value;
	}
    
    public function setType( $user, $class, $school ) {
        if ( $user > 0 ) {
            $this->type = 'user';
            $this->id = $user;
        } else if ( $class > 0 ) {
            $this->type = 'class'; 
            $this->id = $class;
        } else if ( $school > 0 ) {
            $this->type = 'school';
            $this->id = $school;
        }

        //if we're coming from a parent account
        if ( $user > 0 && $school == 0 ) {
            $sql = "SELECT school_id FROM users WHERE user_id = " . $user;
            $result = mysql_query( $sql );
            $row = mysql_fetch_assoc( $result );
            $school = $row['school_id'];
        }
        
        $this->setSchoolType( $school );
        $this->d = new Defaults($this->id, $this->type);
    }
	
    private function setSchoolType( $id ) {
    	/*
        $sql = "select inst_id from schools where school_id = " . $id;
        $result = mysql_query( $sql );
        $row = mysql_fetch_assoc( $result );
        $instType = $row['inst_id'];
        if ( $instType == 2 ) {
            $this->schoolType = "(2,3)";
        } else if ( $instType == 4 ) {
            $this->schoolType = "(12,13)";
        }
		*/
		$this->schoolType = "(2,3,4,5,12,13)";
        $this->school_id = $id;
    }
	
	public function setParentID( $parent_id, $encrypted = true ) {
		// if we get an encrypted parent ID, first decrypt it, then use it...
		if($encrypted) {
			require_once ($_SERVER['DOCUMENT_ROOT'].'/mobile/reg/ajax/encrypt.php');
			$parent_id = encrypt_decrypt( 'decrypt', $parent_id ); // decrypt the parent id
		}
		
		$this->parent_id = mysql_real_escape_string( $parent_id );
	}
    
    //for parent accounts
    public function getCampaignsForChild( $user, $not_enrolled = false, $mission_type = false ) {
		// find out if child is in "chabad" or "frum"
		$sql = "select school_type_id from users where user_id = " . mysql_real_escape_string($user);
		$result = mysql_query($sql);
		$row = mysql_fetch_assoc($result);
		$type = $row['school_type_id'];
		
        $campaigns = array();
        $enrolled = array();
		/*
        $sql = "select s.subject_id, s.subject_name, ut.enrolled  
                from subjects s 
                join user_tracks ut using (subject_id) 
                join users u using (user_id) 
                where s.subject_id not in (42, 91) 
                and s.subject_type in ('', 'WWTC', 'Tanya')
                and u.user_id = " . $user;
		if ($type == 2 || $type == 3) {
			$sql .= " and s.subject_id not in (42,91,92,93,94) ";
		} else if ($type == 12 || $type == 13) {
			$sql .= " and s.subject_id not in (21,42,91,90,13,40) ";
		}
		$sql .= "group by s.subject_id 
                order by s.subject_name";
        */
        $sql = "select s.subject_id, s.subject_name, ut.enrolled "
                ."from subjects s "
                ."join user_tracks ut using (subject_id) "
                ."join users u using (user_id) "
                ."where u.user_id = " . $user . " "
                . ($mission_type ? "and s.subject_id in (" . implode(',', $this->mission_type_subjects[$mission_type]) . ") " : "");
		if(!$not_enrolled){ // if not enrolled is not set to true, limit the results to the enrolled campaigns
			$sql .= "and ut.enrolled = 1 ";
		}
		// skip hakhel campaign
		$sql .= "and subject_id not in (15) "
				."group by s.subject_id "
				."order by s.subject_name";
        $result = mysql_query( $sql );
        while ( $row = mysql_fetch_assoc( $result ) ) {
            $campaigns[$row['subject_id']] = $row['subject_name'];
            if ( $row['enrolled'] )
                $enrolled[] = $row['subject_id'];
        }
		
		//reorder english campaigns to show up at the end of the list
		$keys = array_keys( $campaigns );
		$english = array( 92, 93, 94, 99 );
		foreach ( $english as $id ) {
			if ( in_array($id, $keys) ) {
				$name = $campaigns[$id];
				unset( $campaigns[$id] );
				$campaigns[$id] = $name;
			}
		}
        
        $both = array(
            'campaigns' =>  $campaigns, 
            'enrolled'  =>  $enrolled
        );
        return $both;
    }
	
    public function getCampaigns( $school_id, $mission_type = false) {
        $campaigns = array(); 
        /*
        //find out institution type
        $sql = "select inst_id from schools where school_id = " . $id;
        $result = mysql_query( $sql );
        $row = mysql_fetch_assoc( $result );
        $type = $row['inst_id'];

        switch( $type ) {
            case '2':
                $sql = "select subject_id, subject_name from subjects s 
                        join school_type_subjects sts using (subject_id) 
                        where s.subject_type in ('', 'WWTC', 'Tanya') 
                        and sts.school_type_id in (2,3) 
                        group by s.subject_id 
                        order by s.subject_name";
                break;
            case '4':
                $sql = "select subject_id, subject_name from subjects s 
                        join school_type_subjects sts using (subject_id) 
                        where s.subject_type in ('', 'WWTC', 'Tanya') 
                        and sts.school_type_id in (12,13) 
                        group by s.subject_id 
                        order by s.subject_name";
                break; 
        }
        //echo $sql . "<br />";
        $result = mysql_query( $sql );
        while ( $row = mysql_fetch_assoc($result) ) {
        	if ($row['subject_id'] == 99) continue;
            $campaigns[$row['subject_id']] = $row['subject_name'];
        }
        
        if ($additional) {
            //find out if there are additional campaigns to be added from other school type 
            //for example if some children are enrolled in other campaigns
            $sql = "select s.subject_id, s.subject_name from subjects s 
                    join school_subjects ss using (subject_id) 
                    join schools sc using (school_id) 
                    join users u using (school_id) 
                    join user_tracks ut on (ut.user_id = u.user_id and ut.subject_id = s.subject_id) 
                    where s.subject_type in ('', 'WWTC', 'Tanya') 
                    and s.subject_id not in (21, 91, 99) 
                    and sc.school_id = $id 
                    and ut.enrolled = 1 
                    group by ss.school_id, s.subject_id";
            $extraCampaigns = array();
            $result = mysql_query($sql);
            while ($row = mysql_fetch_assoc($result)) {
                $extraCampaigns[$row['subject_id']] = $row['subject_name'];
            }
            foreach ($extraCampaigns as $id => $name) {
                if (!in_array($name, $campaigns)) {
                    $campaigns[$id] = $name;
                }
            }
        }
        */
        // find out school type by school id
//        $sql = "select inst_id from schools where school_id = " . $school_id;
//        $result = mysql_query($sql);
//        $inst_id = mysql_fetch_assoc($result)['inst_id'];
//        if ($inst_id == 4) {
//            $type_ids = "4,5";
//        } else if ($inst_id == 2) {
//            $type_ids = "2,3,12,13";
//        }
        $sql = "select subject_id, subject_name from subjects s 
                join school_type_subjects sts using (subject_id) 
                where s.subject_type in ('', 'WWTC', 'Tanya', 'Hakhel') 
                and sts.school_type_id in (2,3,4,5,12,13)  
                " . ($mission_type ? "and s.subject_id in (" . implode(',', $this->mission_type_subjects[$mission_type]) . ") " : "") . "
                group by s.subject_id 
                order by s.subject_name";
		$result = mysql_query($sql) or die(mysql_error());
		while ($row = mysql_fetch_assoc($result)) {
			$campaigns[$row['subject_id']] = $row['subject_name'];
		}

        // rearrange array to show english at end
        $keys = array_keys($campaigns);
        $english = array(92, 93, 94, 99);
        foreach ($english as $id) {
            if (in_array($id, $keys)) {
                $name = $campaigns[$id];
                unset($campaigns[$id]);
                $campaigns[$id] = $name;
            }
        }
        
        return $campaigns;
    }

    public function getCampaignsEnrolled( $school, $class, $user ) {
        $enrolled = array(); 
        $sql = "select subject_id from school_subjects  
                where school_id = $school";
        $result = mysql_query( $sql );
        while ( $row = mysql_fetch_assoc($result) ) {
            $enrolled[] = $row['subject_id'];
        }
        //if user is passed in, check also that user is enrolled
        if ( $user > 0 ) {
            $userEnrolled = array();
            $sql = "select subject_id from user_tracks ut 
                    where ut.user_id = $user 
                    and ut.enrolled = 1 
                    and ut.subject_id in (" . implode(',', $enrolled) . ")";
            $result = mysql_query( $sql );
            while ( $row = mysql_fetch_assoc( $result ) ) {
                $userEnrolled[] = $row['subject_id'];
            }
            $enrolled = $userEnrolled;
        } else if ($class > 0) { //check that at least one child is enrolled
            $users = $this->getUsersInGrade($class);
            $classEnrolled = array();
            foreach ($enrolled as $subject) {
                $sql = "select subject_id from user_tracks ut
                        where ut.user_id in (" . implode( ',', $users ) . ")
                        and ut.enrolled = 1 
                        and ut.subject_id = $subject";
                $result = mysql_query( $sql );
                if (mysql_num_rows($result) > 0) {
                    if (!in_array($subject, $classEnrolled)) {
                        $classEnrolled[] = $subject;
                    }
                }
            } 
            $enrolled = $classEnrolled;
        } else if ( $school > 0 ) { //check that at least one student in school is enrolled
            $users = $this->getAllUsers( $school );
            $schoolEnrolled = array();
            foreach ( $enrolled as $subject ) {
                $sql = "SELECT subject_id FROM user_tracks 
                        WHERE user_id IN (" . implode( ',', $users ) . ") 
                        AND enrolled = 1 
                        AND subject_id = $subject";
                $result = mysql_query($sql);
                if ( $result && mysql_num_rows($result) > 0 ) {
                    $schoolEnrolled[] = $subject;
                }
            }
            $enrolled = $schoolEnrolled;                                                 
        }
        return $enrolled;
    }

    private function isException( $taskIDs ) {
        if ( !empty( $taskIDs ) ) {
            //for schools, find out school exceptions, 
            //for classes find school and class exceptions, 
            //for users find school, class, and user exceptions
            //only return true if ALL task ids are in the exceptions table
            $numTaskIDs = count($taskIDs);
            $in = implode(',', $taskIDs);
            switch ($this->type) {
                case 'school':
					$sql = "select * from {$this->type}_task_exceptions 
                            where date_task_id in ($in)  
                            and {$this->type}_id = " . $this->id;
                    //if ($this->debug) {
                    //	echo $sql . "<br />";
					//	return false;
					//}
                    $result = mysql_query( $sql );
                    if ( mysql_num_rows( $result ) == $numTaskIDs ) {
                        return true;
                    }
                    break;
                case 'class':
                    $classExceptions = false;
                    $sql = "select * from {$this->type}_task_exceptions 
                            where date_task_id in ($in)  
                            and {$this->type}_id = " . $this->id;
                    //echo $sql;
                    $result = mysql_query( $sql );
                    if ( mysql_num_rows( $result ) == $numTaskIDs ) {
                    	//echo $sql;
                        $classExceptions = true;
                    } 
                    
                    $schoolExceptions = false;
                    $sql = "select * from school_task_exceptions 
                            where date_task_id in ($in)  
                            and school_id = " . $this->school_id;
                    //echo $sql;
                    $result = mysql_query( $sql );
                    if ( mysql_num_rows( $result ) == $numTaskIDs ) {
                    	//echo $sql;
                        $schoolExceptions = true;
                    } 
                    
                    if ($classExceptions || $schoolExceptions) {
                        return true;
                    } 
                    break;
                case 'user':
					//echo $this->id; exit;
                    $userExceptions = false;
                    $sql = "select * from {$this->type}_task_exceptions 
                            where date_task_id in ($in)  
                            and {$this->type}_id = " . $this->id;
                    //echo $sql;
                    $result = mysql_query( $sql );
                    if ( mysql_num_rows( $result ) == $numTaskIDs ) {
                        $userExceptions = true;
                    }
                    
                    $classExceptions = false;
                    $class_id = $this->getClassID($this->id);
                    $sql = "select * from class_task_exceptions 
                            where date_task_id in ($in)  
                            and class_id = " . $class_id;
                    //echo $sql;
                    $result = mysql_query( $sql );
                    if ( mysql_num_rows( $result ) == $numTaskIDs ) {
                        $classExceptions = true;
                    }
                    
                    $schoolExceptions = false;
                    $sql = "select * from school_task_exceptions 
                            where date_task_id in ($in)  
                            and school_id = " . $this->school_id;
                    //echo $sql;
                    $result = mysql_query( $sql );
                    if ( mysql_num_rows( $result ) == $numTaskIDs ) {
                        $schoolExceptions = true;
                    }
                    if ($userExceptions || $classExceptions || $schoolExceptions) {
                        return true;
                    }
                    break;
            }
        }
        //if we get here then not ALL task ids are in exceptions table
        return false;
    }

    public function getTasks( $subject_id, $debug = false, $forPersonalization = false, $missionType = false ) {
    	$this->debug = $debug;
        $tasks = array();
        $info = array();
        $mandatory = array();
        switch ($missionType) {
            case 'chabad':
                $mission_type = 2,3;
                break;
            case 'frum':
                $mission_type = 12,13;
                break;
            case 'day_school':
                $mission_type = 4,5;
                break;
            default:
                $mission_type = false;
                break;
        }
        
        $orderBy = " order by dt.cat_ord_new, dtm.level, dtm.school_type_id, dt.name";
        if ( $subject_id == 40 ) $orderBy = " order by IFNULL(dt.yd_cat_num, 10000), dt.cat_ord_new, dtm.level, dtm.school_type_id, dt.name";
        
        if ( $this->type == 'user' ) {
			$sql = "SELECT distinct dt.cat, dt.name, dt.quantity, dtm.school_type_id, dtm.level, dt.default_on, dt.mandatory_qty "
				." FROM date_tasks dt "
                ." JOIN date_tasks_missions dtm USING (date_tasks_mission_id) "
				." JOIN user_tracks ut USING (subject_id, level, track_id) "
				." JOIN users u USING (user_id) "
				." WHERE ut.user_id = $this->id "
				." AND ut.enrolled = 1 "
				." AND dtm.subject_id = " . $subject_id . " "
				." AND dtm.school_type_id = u.school_type_id "
				." AND dtm.start_date >= $this->start "
				." AND dtm.end_date <= $this->end "
				." AND (dtm.created_by_school IS NULL or dtm.created_by_school = $this->school_id) "
				." AND (dtm.created_by_parent IS NULL OR dtm.created_by_parent = '$this->parent_id') "
				." AND u.user_registered > 0 "
				." and dtm.lang_id = " . $this->lang. " ";
			if ($mission_type) $sql .= " and dtm.mission_type in (" . $mission_type . ")";
			//if ($this->id == 5548)echo $sql;
        } else if ($this->type == 'class') {
//            $users = $this->getUsersInGrade($this->id);
//            if ( empty($users) ) return false;
//            where ut.user_id in (" . implode(',', $users) . ")
            $sql = "select distinct dt.cat, dt.name, dt.quantity, dtm.school_type_id, dtm.level, dt.default_on, dt.mandatory_qty 
                    from date_tasks dt 
                    join date_tasks_missions dtm using (date_tasks_mission_id) 
                    join user_tracks ut using (subject_id, level, track_id) 
                    join users u using (user_id) 
                    where u.class_id = " . $this->id . "
                    and dtm.subject_id = " . $subject_id . " 
                    and dtm.school_type_id = u.school_type_id 
                    and dtm.start_date >= $this->start 
                    and dtm.end_date <= $this->end 
                    and (dtm.created_by_school is null or dtm.created_by_school = $this->school_id) 
                    and u.user_registered > 0 
                    and dtm.personal = 0 
                    and dtm.lang_id = " . $this->lang;
            if ($mission_type) $sql .= " and dtm.mission_type in (" . $mission_type . ")";
//			 echo "<input type='hidden' name='sql' value='" . $sql . "' />";
//            echo $sql;
        } else {
            $sql = "select distinct dt.cat, dt.name, dt.quantity, dtm.school_type_id, dtm.level, dt.default_on, dt.mandatory_qty 
                    from date_tasks dt 
                    join date_tasks_missions dtm using (date_tasks_mission_id) 
                    where dtm.subject_id = " . $subject_id . " 
                    and dtm.start_date >= " . $this->start . " 
                    and dtm.end_date <= " . $this->end . "  
                    and dtm.school_type_id in " . $this->schoolType . " 
                    and (dtm.created_by_school is null or dtm.created_by_school = $this->school_id)  
					and dtm.personal = 0                        
                    and dtm.lang_id = " . $this->lang;
            if ($mission_type) $sql .= " and dtm.mission_type in (" . $mission_type . ")";
        }
        $sql .= ' GROUP BY cat, name, level '.$orderBy;
//        echo $sql; exit;

        $result = mysql_query( $sql );
        if (!$result) {
        	return false;
        } 
        
        while ( $row = mysql_fetch_assoc( $result ) ) {
            //if ($row['cat'] == 'Birthday')
                //continue;
            //since there can be more than one task with the same category and each task can 
            //be on or off be default we need to store in array and then determine category default next
            $tasks[$row['cat']][] = $row['default_on'];
            $info[$row['cat']][$row['name']][$row['school_type_id']][$row['level']] = $row['quantity'];
            if ($forPersonalization) $mandatory[$row['cat']] = $row['mandatory_qty'];
        }
        
        //if even one level/type has default on, the cat will show default on
        foreach ($tasks as $cat => $defaults) {
            if (in_array(1, $defaults)) {
                $tasks[$cat] = 1;
            } else {
                $tasks[$cat] = 0;
            }
        }
                
        //check for defaults 
        foreach ($tasks as $task => $on) {
            if (!$on) {
                //get defaults 
                $defaults = $this->getDefaultIDs($task, array(), $subject_id);
                $isOn = false;
				
				if ($this->d->isOn($defaults, 'task')) {
					if($debug){echo"Hi!";} // this code is run so...
					$isOn = true;
				}
				/*
				//even if one default is set to on, the category should be checked
                foreach ($defaults as $id) {
                	//check defaults for this type
                    if ($this->d->isOn($id, 'task')) {
                        $isOn = true;
						//echo $task . '-' . $id; exit;
                        break;
                    }
                }
				*/
				
				if (!$isOn) {
					//if class check defaults for any of the students
					if ($this->type == 'class') {
						$users = $this->getUsersInGrade($this->id);
						foreach ($users as $user) {
							$def = new Defaults($user);
							if ($def->isOn($defaults, 'task')) {
		                        $isOn = true;
								break;
							}
							/*
							foreach ($defaults as $id) {
								if ($def->isOn($id, 'task')) {
			                        $isOn = true;
									//if ($task == 'My Personal Task 20') echo "$task #$id is on for $user"; exit;
									//echo $task . '-' . $id; exit;
			                        break 2;
			                    } 
							}
							*/
						}
					}
					
					//if school check defaults for any of the classes / students
					if ($this->type == 'school') {
						$classes = array();
						$sql = "select class_id from classes where school_id = $this->id and class_era = 0";
						$result = mysql_query($sql);
						while ($row = mysql_fetch_assoc($result)) {
							$classes[] = $row['class_id'];
						}
						
						foreach ($classes as $class) {
							$def = new Defaults($class, 'class');
							if ($def->isOn($defaults, 'task')) {
		                        $isOn = true;
								break;
							}
						}
						
						if (!$isOn) {
							foreach ($classes as $class) {
								$users = $this->getUsersInGrade($class);
								foreach ($users as $user) {
									$def = new Defaults($user);
									if ($def->isOn($defaults, 'task')) {
				                        $isOn = true;
										break 2;
									}
								}
							}
						}
					}
				}
					
                if ($isOn || empty($defaults)) {
                    //echo $task . " is on.";
                    $tasks[$task] = 1;
                }
            }
        }
		if ($debug) {
			echo "<pre>"; print_r( $tasks ); echo "</pre>";
		}   
		
		//check for exceptions (even if the default is on for the school there may be an exception for the class / user) 
		foreach ($tasks as $task => $on) {
			if ($on) {
            	$ids = $this->getTaskIDs( $task, array(), $subject_id );
	            if ( $this->isException( $ids ) ) {
	                $tasks[$task] = 0;
	            }
			}
		}

        if ($debug) {
			echo $sql;
			echo "<pre>"; print_r( $tasks ); echo "</pre>";
		}

        //create user friendly info array
        $levels = array(
            '6'	=> 'Pre1A', 
            '7'	=> 'Grade 1', 
            '8'	=> 'Grade 2', 
            '9'	=> 'Grade 3', 
            '10'=> 'Grade 4', 
            '11'=> 'Grade 5', 
            '12'=> 'Grade 6', 
            '13'=> 'Grade 7', 
            '14'=> 'Grade 8'
        );

        $types = array(
            '2'	=> 'Chabad Boys', 
            '3'	=> 'Chabad Girls', 
            '4' => 'Day School Boys', 
            '5' => 'Day School Girls', 
            '6' => 'Hebrew School Boys',
            '7' => 'Hebrew School Girls', 
            '8' => 'Unaffiliated Boys',  
            '9' => 'Unaffiliated Girls', 
            '12'=> 'Frum Boys', 
            '13'=> 'Frum Girls', 
            '14'=> 'Friendship Circle Boys', 
            '15'=> 'Friendship Circle Girls'
        );
        
        $friendly = array();
        foreach( $info as $task => $arr ) {
            foreach( $arr as $name => $arr2 ) {
                foreach( $arr2 as $type => $arr3 ) { 
                    foreach( $arr3 as $level => $quantity ) {
                        if ( isset( $levels[ $level ] ) ) {
                            @$friendly[ $task ][ $name ][ $types[ $type ] ][ $levels[ $level ] ] = $quantity;
                        }
                    }
                }
            }
        }
        
        //put all info together into one array
        $allInfo = array();
        foreach( $tasks as $task => $enrolled ) {
            if ( isset( $friendly[$task] ) ) {
                if ($forPersonalization) {
                    $allInfo['taskInfo'][$task][$enrolled] = $friendly[$task];
                    $allInfo['mandatory'][$task] = $mandatory[$task];
                }
                else $allInfo[$task][$enrolled] = $friendly[$task];
            }
        }
        return $allInfo;
    }

	public function getYDTasks( $catNum, $debug = false ) {
    	$this->debug = $debug;
        $tasks = array();
        $info = array();
        if ( $this->type == 'user' ) {
			$sql = "select distinct dt.cat, dt.name, dt.quantity, dtm.school_type_id, dtm.level, dt.default_on from date_tasks dt 
                    join date_tasks_missions dtm using (date_tasks_mission_id) 
                    join user_tracks ut using (subject_id, level, track_id) 
                    join users u using (user_id) 
                    where ut.user_id = $this->id  
                    and dtm.subject_id = " . $subject_id . " 
                    and dtm.school_type_id = u.school_type_id 
                    and dtm.start_date >= $this->start 
                    and dtm.end_date <= $this->end 
                    and (dtm.created_by_school is null or dtm.created_by_school = $this->school_id) 
                    and u.user_registered > 0 
                    and dtm.lang_id = " . $this->lang . " 
                    and dt.yd_cat_num = " . $catNum . " 
                    order by dt.cat, dtm.school_type_id, dtm.level, dt.name";
        } else if ($this->type == 'class') {
            $users = $this->getUsersInGrade($this->id);
            $sql = "select distinct dt.cat, dt.name, dt.quantity, dtm.school_type_id, dtm.level, dt.default_on from date_tasks dt 
                    join date_tasks_missions dtm using (date_tasks_mission_id) 
                    join user_tracks ut using (subject_id, level, track_id) 
                    join users u using (user_id) 
                    where ut.user_id in (" . implode(',', $users) . ")   
                    and dtm.subject_id = " . $subject_id . " 
                    and dtm.school_type_id = u.school_type_id 
                    and dtm.start_date >= $this->start 
                    and dtm.end_date <= $this->end 
                    and (dtm.created_by_school is null or dtm.created_by_school = $this->school_id) 
                    and u.user_registered > 0 
                    and dtm.personal = 0 
                    and dtm.lang_id = " . $this->lang . " 
                    and dt.yd_cat_num = " . $catNum . " 
                    order by dt.cat, dtm.school_type_id, dtm.level, dt.name";
			 //echo "<input type='hidden' name='sql' value='" . $sql . "' />";
        } else {
            $sql = "select distinct dt.cat, dt.name, dt.quantity, dtm.school_type_id, dtm.level, dt.default_on from date_tasks dt 
                    join date_tasks_missions dtm using (date_tasks_mission_id) 
                    where dtm.subject_id = " . $subject_id . " 
                    and dtm.start_date >= " . $this->start . " 
                    and dtm.end_date <= " . $this->end . "  
                    and dtm.school_type_id in " . $this->schoolType . " 
                    and (dtm.created_by_school is null or dtm.created_by_school = $this->school_id)  
					and dtm.personal = 0                        
                    and dtm.lang_id = " . $this->lang . " 
                    and dt.yd_cat_num = " . $catNum . " 
                    order by dt.cat, dtm.school_type_id, dtm.level, dt.name";
        } 
        //echo "<input type='hidden' name='sql' value='" . $sql . "' />";

        $result = mysql_query( $sql );
        if (!$result) {
        	return false;
        } 
        
        while ( $row = mysql_fetch_assoc( $result ) ) {
            //if ($row['cat'] == 'Birthday')
                //continue;
            //since there can be more than one task with the same category and each task can 
            //be on or off be default we need to store in array and then determine category default next
            $tasks[$row['cat']][] = $row['default_on']; 
            $info[$row['cat']][$row['name']][$row['school_type_id']][$row['level']] = $row['quantity'];
        }
        
        //if even one level/type has default on, the cat will show default on
        foreach ($tasks as $cat => $defaults) {
            if (in_array(1, $defaults)) {
                $tasks[$cat] = 1;
            } else {
                $tasks[$cat] = 0;
            }
        }
                
        //check for defaults 
        foreach ($tasks as $task => $on) {
            if (!$on) {
                //get defaults 
                $defaults = $this->getDefaultIDs($task, array(), $subject_id);
                $isOn = false;
				
				if ($this->d->isOn($defaults, 'task')) {
					$isOn = true;
				}
				/*
				//even if one default is set to on, the category should be checked
                foreach ($defaults as $id) {
                	//check defaults for this type
                    if ($this->d->isOn($id, 'task')) {
                        $isOn = true;
						//echo $task . '-' . $id; exit;
                        break;
                    }
                }
				*/
				
				if (!$isOn) {
					//if class check defaults for any of the students
					if ($this->type == 'class') {
						$users = $this->getUsersInGrade($this->id);
						foreach ($users as $user) {
							$def = new Defaults($user);
							if ($def->isOn($defaults, 'task')) {
		                        $isOn = true;
								break;
							}
							/*
							foreach ($defaults as $id) {
								if ($def->isOn($id, 'task')) {
			                        $isOn = true;
									//if ($task == 'My Personal Task 20') echo "$task #$id is on for $user"; exit;
									//echo $task . '-' . $id; exit;
			                        break 2;
			                    } 
							}
							*/
						}
					}
					
					//if school check defaults for any of the classes / students
					if ($this->type == 'school') {
						$classes = array();
						$sql = "select class_id from classes where school_id = $this->id and class_era = 0";
						$result = mysql_query($sql);
						while ($row = mysql_fetch_assoc($result)) {
							$classes[] = $row['class_id'];
						}
						
						foreach ($classes as $class) {
							$def = new Defaults($class, 'class');
							if ($def->isOn($defaults, 'task')) {
		                        $isOn = true;
								break;
							}
							/*
							foreach ($defaults as $id) {
								if ($def->isOn($id, 'task')) {
			                        $isOn = true;
									//echo $task . '-' . $id; exit;
			                        break 2;
			                    } 
							}
							*/
						}
						
						if (!$isOn) {
							foreach ($classes as $class) {
								$users = $this->getUsersInGrade($class);
								foreach ($users as $user) {
									$def = new Defaults($user);
									if ($def->isOn($defaults, 'task')) {
				                        $isOn = true;
										break 2;
									}
									/*
									foreach ($defaults as $id) {
										if ($def->isOn($id, 'task')) {
					                        $isOn = true;
											//echo $task . '-' . $id; exit;
					                        break 3;
					                    } 
									}
									*/
								}
							}
						}
					}
				}
					
                if ($isOn || empty($defaults)) {
                    //echo $task . " is on.";
                    $tasks[$task] = 1;
                }
            }
        }
		if ($debug) {
			//echo "<pre>"; print_r( $tasks ); echo "</pre>"; exit;
		}   
		
		//check for exceptions (even if the default is on for the school there may be an exception for the class / user) 
		foreach ($tasks as $task => $on) {
			if ($on) {
            	$ids = $this->getTaskIDs( $task, array(), $subject_id );
				//print_r($ids)
	            if ( $this->isException( $ids ) ) {
	            	//echo $task . " has exception.";
					//exit;
	                $tasks[$task] = 0;
	            }
			}
		}
        
        //check that user/class/school is enrolled to campaign     
        if ( $this->type == 'user' ) {
            $userEnrolled = array();
            $sql = "select dt.cat, ut.enrolled from date_tasks dt 
                    join date_tasks_missions dtm using (date_tasks_mission_id) 
                    join user_tracks ut using (subject_id, level, track_id) 
                    join users u using (user_id) 
                    where dtm.subject_id = " . $subject_id . "  
                    and ut.user_id = $this->id  
                    and dtm.school_type_id = u.school_type_id 
                    and dtm.start_date >= $this->start 
                    and dtm.end_date <= $this->end 
                    and ut.enrolled = 1 
                    and dtm.lang_id = " . $this->lang . " 
                    group by dt.cat";
            //echo $sql;
            $result = mysql_query( $sql );
            while ( $row = mysql_fetch_assoc( $result ) ) {
                $userEnrolled[] = $row['cat'];
            }
            //find out which tasks user is not enrolled into remove from tasks array
            foreach( $tasks as $task => $on ) {
                if ( !in_array( $task, $userEnrolled ) ) {
                    //remove task from tasks array and info array
                    unset( $tasks[$task] );
                    unset( $info[$task] );
                }
            }
        } else if ($this->type == 'class') {
            //make sure that at least 'one' child is enrolled from class
            $users = $this->getUsersInGrade($this->id);
            $classEnrolled = array();
            foreach ($users as $user) {
                $sql = "select dt.cat, ut.enrolled from date_tasks dt 
                        join date_tasks_missions dtm using (date_tasks_mission_id) 
                        join user_tracks ut using (subject_id, level, track_id) 
                        join users u using (user_id) 
                        where dtm.subject_id = " . $subject_id . "  
                        and ut.user_id = $user  
                        and dtm.school_type_id = u.school_type_id 
                        and dtm.start_date >= $this->start 
                        and dtm.end_date <= $this->end 
                        and dtm.personal = 0 
                        and ut.enrolled = 1 
                    	and dtm.lang_id = " . $this->lang . " 
                        group by dt.cat";
                //echo $sql;
                $result = mysql_query( $sql );
                while ($row = mysql_fetch_assoc($result)) {
                    //if (!in_array($row['cat'], $classEnrolled)) {
                        $classEnrolled[] = $row['cat'];
                    //}
                }
            }
            //find out which tasks class is not enrolled into
            foreach( $tasks as $task => $on ) {
                if (!in_array( $task, $classEnrolled )) {
                    //set task to unenrolled
                    //$tasks[$task] = 0;
                    //remove task from tasks array and info array
                    unset( $tasks[$task] );
                    unset( $info[$task] );
                }
            }
        }
        if ($debug) {
        	//echo $sql . "<br />";
        }

        //create user friendly info array
        $levels = array(
            '6'	=> 'Pre1A', 
            '7'	=> 'Grade 1', 
            '8'	=> 'Grade 2', 
            '9'	=> 'Grade 3', 
            '10'=> 'Grade 4', 
            '11'=> 'Grade 5', 
            '12'=> 'Grade 6', 
            '13'=> 'Grade 7', 
            '14'=> 'Grade 8'
        );

        $types = array(
            '2'	=> 'Yeshiva Boys', 
            '3'	=> 'Yeshiva Girls', 
            '12'=> 'Hebrew School Boys', 
            '13'=> 'Hebrew School Girls'
        );
        
        $friendly = array();
        foreach( $info as $task => $arr ) {
            foreach( $arr as $name => $arr2 ) {
                foreach( $arr2 as $type => $arr3 ) { 
                    foreach( $arr3 as $level => $quantity ) {
                        $friendly[$task][$name][$types[$type]][$levels[$level]] = $quantity;
                    }
                }
            }				
        }
        //print_r( $friendly );        

        //put all info together into one array
        $allInfo = array();
        foreach( $tasks as $task => $enrolled ) {
            $allInfo[$task][$enrolled] = $friendly[$task];
        }
		if ($debug) {
			//echo "<pre>"; print_r( $allInfo ); echo "</pre>"; exit;
		}
        return $allInfo;
    }

	private function getCatIDs($task, $missions = array(), $subject_id = null, $default = false) {
		if (strrpos($task, "|") !== FALSE) {
    		$pos = strrpos($task, "|");
			if (is_null($subject_id)) {	
    			$subject_id = substr($task, 0, $pos);
			}
			$task = substr($task, ($pos+1));
    	}
		//if ($this->debug) echo $task; exit;
		$ids = array();
		if ($this->type == 'user') {
			$sql = "select distinct dt.date_task_id from date_tasks dt 
                    join date_tasks_missions dtm using (date_tasks_mission_id) 
                    join user_tracks ut using (subject_id, level, track_id) 
                    join users u using (user_id) 
                    where ut.user_id = ". $this->id . "  
                    and dtm.subject_id = " . $subject_id . " 
                    and dtm.school_type_id in " . $this->schoolType . " 
                    and dtm.start_date >= " .$this->start . " 
                    and dtm.end_date <= " . $this->end . " 
                    and (dtm.created_by_school is null or dtm.created_by_school = " .$this->school_id . ") 
                    and dtm.lang_id = " . $this->lang . " 
                    and dt.cat = \"" . mysql_real_escape_string($task) . "\"";
            /*
            $sql = "select distinct dt.date_task_id from date_tasks dt 
                    join date_tasks_missions dtm using (date_tasks_mission_id) 
                    join user_tracks ut using (subject_id, level, track_id) 
                    join users u using (user_id) 
                    where ut.user_id = $this->id  
                    and dtm.subject_id = " . $subject_id . " 
                    and dtm.school_type_id = u.school_type_id 
                    and dtm.start_date >= $this->start 
                    and dtm.end_date <= $this->end 
                    and (dtm.created_by_school is null or dtm.created_by_school = $this->school_id) 
                    and dt.cat = \"" . mysql_real_escape_string($task) . "\"";
			*/
        } else if ($this->type == 'class') {
            $users = $this->getUsersInGrade($this->id);
			$sql = "select distinct dt.date_task_id from date_tasks dt 
                    join date_tasks_missions dtm using (date_tasks_mission_id) 
                    join user_tracks ut using (subject_id, level, track_id) 
                    join users u using (user_id) 
                    where ut.user_id in (" . implode(',', $users) . ")   
                    and dtm.subject_id = " . $subject_id . " 
                    and dtm.school_type_id in " . $this->schoolType . " 
                    and dtm.start_date >= $this->start 
                    and dtm.end_date <= $this->end 
                    and (dtm.created_by_school is null or dtm.created_by_school = $this->school_id) 
                    and dtm.lang_id = " . $this->lang . " 
                    and dt.cat = \"" . mysql_real_escape_string($task) . "\"";
			/*
            $sql = "select distinct dt.date_task_id from date_tasks dt 
                    join date_tasks_missions dtm using (date_tasks_mission_id) 
                    join user_tracks ut using (subject_id, level, track_id) 
                    join users u using (user_id) 
                    where ut.user_id in (" . implode(',', $users) . ")   
                    and dtm.subject_id = " . $subject_id . " 
                    and dtm.school_type_id = u.school_type_id 
                    and dtm.start_date >= $this->start 
                    and dtm.end_date <= $this->end 
                    and (dtm.created_by_school is null or dtm.created_by_school = $this->school_id) 
                    and dt.cat = \"" . mysql_real_escape_string($task) . "\"";
			*/
        } else {
            $sql = "select distinct dt.date_task_id from date_tasks dt 
                    join date_tasks_missions dtm using (date_tasks_mission_id) 
                    where dtm.subject_id = " . $subject_id . " 
                    and dtm.start_date >= " . $this->start . " 
                    and dtm.end_date <= " . $this->end . "  
                    and dtm.school_type_id in " . $this->schoolType . " 
                    and (dtm.created_by_school is null or dtm.created_by_school = $this->school_id) 
                    and dtm.lang_id = " . $this->lang . " 
                    and dt.cat = \"" . mysql_real_escape_string($task) . "\"";
        }
		if ($this->type != 'user') {
			$sql .= " and dtm.personal = 0";
		}
		if ($default) {
			$sql .= " and dt.default_on = 0";
		}
        if (count($missions) > 0) {
            if (count($missions) == 1) {
                $sql .= " and dtm.mission_name = '" . mysql_real_escape_string($missions[0]) . "' ";
            } else {
            	$sql .= " and (";
				$num = count($missions);
				for ($i = 0; $i < $num; $i++) {
					$sql .= "dtm.mission_name = '" . mysql_real_escape_string($missions[$i]) . "'";
					if ($i < ($num-1)) {
						$sql .= " or ";
					} else {
						$sql .= ")";
					}
				}
            }
        }
        //echo $sql; exit;
        //echo $sql;
		//if ($this->debug) {
		//	echo $sql . "<br />";
		//} else {
			$result = mysql_query($sql);
			while ($row = mysql_fetch_assoc($result)) {
				$ids[] = $row['date_task_id'];
			}
		//}
		return $ids;
	}

    private function getDefaultIDs($task, $missions = array(), $subject_id = null) {
		return $this->getCatIDs($task, $missions, $subject_id, true);
		/*
        $defaults = array();
        if ($this->type == 'user') {
            $sql = "select distinct dt.date_task_id from date_tasks dt 
                    join date_tasks_missions dtm using (date_tasks_mission_id) 
                    join user_tracks ut using (subject_id, level, track_id) 
                    join users u using (user_id) 
                    where ut.user_id = $this->id  
                    and dtm.subject_id = " . $subject_id . " 
                    and dtm.school_type_id = u.school_type_id 
                    and dtm.start_date >= $this->start 
                    and dtm.end_date <= $this->end 
                    and (dtm.created_by_school is null or dtm.created_by_school = $this->school_id) 
                    and dt.default_on = 0  
                    and dt.cat = \"" . mysql_real_escape_string($task) . "\"";
        } else if ($this->type == 'class') {
            $users = $this->getUsersInGrade($this->id);
            $sql = "select distinct dt.date_task_id from date_tasks dt 
                    join date_tasks_missions dtm using (date_tasks_mission_id) 
                    join user_tracks ut using (subject_id, level, track_id) 
                    join users u using (user_id) 
                    where ut.user_id in (" . implode(',', $users) . ")   
                    and dtm.subject_id = " . $subject_id . " 
                    and dtm.school_type_id = u.school_type_id 
                    and dtm.start_date >= $this->start 
                    and dtm.end_date <= $this->end 
                    and (dtm.created_by_school is null or dtm.created_by_school = $this->school_id) 
                    and dt.default_on = 0 
                    and dtm.personal = 0 
                    and dt.cat = \"" . mysql_real_escape_string($task) . "\"";
        } else {
            $sql = "select distinct dt.date_task_id from date_tasks dt 
                    join date_tasks_missions dtm using (date_tasks_mission_id) 
                    where dtm.subject_id = " . $subject_id . " 
                    and dtm.start_date >= " . $this->start . " 
                    and dtm.end_date <= " . $this->end . "  
                    and dtm.school_type_id in " . $this->schoolType . " 
                    and (dtm.created_by_school is null or dtm.created_by_school = $this->school_id) 
                    and dt.default_on = 0 
                    and dtm.personal = 0 
                    and dt.cat = \"" . mysql_real_escape_string($task) . "\"";
        }
        //echo $sql;
        $result = mysql_query($sql);
        while ($row = mysql_fetch_assoc($result)) {
            $defaults[] = $row['date_task_id'];
        }
        return $defaults;
		*/
    }

	private function getTaskIDs($cat, $missions = array(), $subject_id = null) {
		return $this->getCatIDs($cat, $missions, $subject_id);
        /*
        $tasks = array();
		$subject = null;
    	if (strrpos($cat, "|") !== FALSE) {
    		$pos = strrpos($cat, "|");	
    		$subject = substr($cat, 0, $pos);
			$cat = substr($cat, ($pos+1));
    	}
        $sql = "select distinct dt.date_task_id from date_tasks dt 
                join date_tasks_missions dtm using (date_tasks_mission_id) 
                join user_tracks ut using (subject_id, track_id, level) 
                join users u using (user_id) 
                where dt.cat = \"" . mysql_real_escape_string( $cat ) . "\"    
                and dtm.school_type_id = u.school_type_id 
                and dtm.start_date >= $this->start 
                and dtm.end_date <= $this->end 
                and u.{$this->type}_id = " . $this->id; 
		if ($this->type != 'user') {
			$sql .= " and dtm.personal = 0";
		}
		if (!is_null($subject)) {
			$sql .= " and dtm.subject_id = " . $subject;
		}
        if ( count( $missions ) > 0 ) {
            if ( count( $missions == 1 ) ) {
                $sql .= " and dtm.mission_name = '" . mysql_real_escape_string( $missions[0] ) . "' ";
            } else {
                $sql .= " and dtm.mission_name in (\"" . mysql_real_escape_string( implode("\",\"", $missions) ) . "\")";
            }
        }
        //echo $sql;
        $result = mysql_query( $sql ) or die( $sql );
        while ( $row = mysql_fetch_assoc( $result ) ) {
            $tasks[] = $row['date_task_id'];
        }
        return $tasks;
		*/
    }
	
    public function getMissions($cat, $subject_id) {
    	if (strrpos($cat, "|") !== FALSE) {
    		$pos = strrpos($cat, "|");	
    		$subject_id = substr($cat, 0, $pos);
			$cat = substr($cat, ($pos+1));
    	}
        $missions = array();
        $sql = "SELECT dtm.mission_name, dtm.start_date, dtm.end_date, dt.mandatory_qty from date_tasks dt 
                join date_tasks_missions dtm using (date_tasks_mission_id) 
                where dt.cat = \"" . mysql_real_escape_string( $cat ) . "\"  
                and dtm.start_date >= " . $this->start . " 
                and dtm.end_date <= " . $this->end . " 
				and dtm.subject_id = " . $subject_id . " 
                and dtm.lang_id = " . $this->lang . " 
				and (dtm.created_by_school is null or dtm.created_by_school = $this->school_id)";
		if ($this->type != 'user') {
			$sql .= " and dtm.personal = 0";
        }
        // $sql .= " group by dtm.mission_name order by dtm.start_date";
        $sql .= " group by dtm.start_date order by dtm.start_date";

        $result = mysql_query($sql);
        //echo mysql_num_rows($result);
        $i = 0; // some missions have same name for entire yr so we need to add an extra index to make sure not to overwrite (Naftoli - 05/27/20)
        while ($row = mysql_fetch_assoc($result)) {
            $mission = $row['mission_name'];
            $missions[$i][$mission]['enrolled'] = 1;
            $missions[$i][$mission]['mandatory'] = $row['mandatory_qty'];
            $heStart = cal_from_jd($row['start_date'], CAL_JEWISH);
            $heEnd = cal_from_jd($row['end_date'], CAL_JEWISH);
            $missions[$i][$mission]['start'] = $heStart['day'] . ' ' . $heStart['monthname'];
            $missions[$i][$mission]['end'] = $heEnd['day'] . ' ' . $heEnd['monthname'];
            $missions[$i][$mission]['start_date']   = $row['start_date'];
            $missions[$i][$mission]['end_date']     = $row['end_date'];
            $i++;
        }

        //to find exceptions we need to check all task ids for each mission within each category  
        //and see if all of them are in exceptions table
        foreach( $missions as $idx => $info ) {
            foreach ( $info as $mission => $enrolled ) {
                $ids = $this->getTaskIDs($cat, array($mission), $subject_id);
                if ($this->isException($ids)) {
                    $missions[$idx][$mission]['enrolled'] = 0;
                }
            }
        }
        return $missions;
    }
	
    public function getUsersInGrade( $class_id ) {
        $users = array();
        $sql = "select user_id from users where class_id = " . $class_id;
        $result = mysql_query( $sql );
        while ( $row = mysql_fetch_assoc( $result ) ) {
            $users[] = $row['user_id'];
        }
        return $users;
    }
	
	public function getOtherUsersInSchool($school, $id, $type = 'user') {
	    $users = array();
	    $sql = "select user_id from users where school_id = $school and {$type}_id != $id";
        $result = mysql_query($sql);
        while ($row = mysql_fetch_assoc($result)) {
            $users[] = $row['user_id'];
        }
        return $users;
	}
    
    public function getAllUsers($school) {
        $users = array();
        $sql = "select user_id from users where school_id = " . $school;
        $result = mysql_query($sql);
        while ($row = mysql_fetch_assoc($result)) {
            $users[] = $row['user_id'];
        }
        return $users;
    }
    
    private function getClassID($user) {
        $sql = "select class_id from users where user_id = " . $user;
        $result = mysql_query($sql);
        $row = mysql_fetch_assoc($result);
        return $row['class_id'];
    }
    
    public function unenroll( $user_ids, $campaigns ) {
        foreach( $user_ids as $user_id ) { 
            foreach( $campaigns as $campaign ) {
                $sql = "update user_tracks set enrolled = 0 where user_id = $user_id and subject_id = $campaign";
                mysql_query( $sql );
            }
        }
    }
	
    //we don't enroll or unenroll school anymore - only users within the school
    public function unenrollSchool( $school_id, $campaigns ) {
        foreach( $campaigns as $campaign ) {
            $sql = "delete from school_subjects where school_id = " . $school_id . " and subject_id = " . $campaign;
            //mysql_query( $sql );
        }
    }

    private function getLevel( $user_id ) {
        $sql = "select class_grade from users u join classes c on u.class_id = c.class_id where u.user_id = " . $user_id;
        $result = mysql_query( $sql );
        if ( mysql_num_rows( $result ) > 0 ) {
            $row = mysql_fetch_assoc( $result );
            $grade = $row['class_grade'];
        } else {
            $grade = 'Pre1a'; // put in lowest grade
        }
        if ( is_numeric( $grade ) ) return intval( $grade ) + 6;
        else return 6;
    }
	
    public function enroll( $user_ids, $campaigns ) {
        foreach( $user_ids as $user_id ) { 
            $type = 0;
            $level = 0;
            foreach( $campaigns as $campaign ) {
                // find out if row exists
                $sql = "select * from user_tracks where user_id = $user_id and subject_id = $campaign";
                $result = mysql_query( $sql );
                if ( mysql_num_rows( $result ) > 0 )
                    $sql = "update user_tracks set enrolled = 1 where user_id = $user_id and subject_id = $campaign";
                else {
                    if ( $level == 0 ) {
                        // find out correct level
                        $level = $this->getLevel( $user_id );
                    }
                    if ( $type == 0 ) {
                        // find out school type
                        $sql = "select school_type_id from users where user_id = " . mysql_real_escape_string($user);
                        $result = mysql_query($sql);
                        $row = mysql_fetch_assoc($result);
                        $type = intval($row['school_type_id']);
                    }
                    if ( $campaign == 1 ) {
                        switch ( $type ) {
                            case 2: case 3:
                                $track = 5;
                                break;
                            case 12: case 13:
                                $track = 3;
                                break;
                            default:
                                $track = 5;
                                break;
                        }
                    } else {
                        $track = 1;
                    }    
                    $sql = "insert into user_tracks set enrolled = 1, user_id = " . $user_id . ", subject_id = " . $campaign . ", level = " . $level . ", track_id = " . $track;
                }
                mysql_query( $sql );
				//if enrolling into yoma depagra we need to create birthday mission
				/*
				if ($campaign == 40) {
					require_once 'class.birthdayEn.php';
					require_once 'class.birthdayYi.php';
					$b = new BirthdayEn($user_id);
					$b->setBirthday();
					$b = new BirthdayYi($user_id);
					$b->setBirthday();
				}
				*/
            }
        }
    }
	
	//we don't enroll or unenroll school anymore - only users within the school
    public function enrollSchool( $school_id, $campaigns ) {
        foreach( $campaigns as $campaign ) {
            $sql = "insert ignore into school_subjects set school_id = " . $school_id . ", subject_id = " . $campaign;
            //mysql_query( $sql );
        }
    }
    
    private function getTaskMissions( $missions ) { 
        $tasks = array();
        foreach( $missions as $mission ) { 
            $missionInfo = explode( '~', $mission );
            $task = $missionInfo[0]; 
            $missionName = $missionInfo[1];
            $tasks[$task][] = $missionName;
        }
        return $tasks;
    }

    public function enrollIntoTasks( $tasks ) {		
        $this->enrollInto( $tasks );
    }
	
    public function enrollIntoMissions( $missions ) {
        //reconstruct missions array to have category name with list of missions per category name
        $tasks = $this->getTaskMissions( $missions );
		//print_r($tasks);		
        $this->enrollInto( $tasks, true );
    }
	
    private function enrollInto($tasks, $missions = false) {
        $arrTasks = $this->prepareTasks($tasks, $missions);
        $defaults = $arrTasks[0];
		$exceptions = $arrTasks[1];
		
        //add tasks that are off by default to defaults table
        //echo "<pre>"; print_r( $defaults ); echo "</pre>";
		foreach ($defaults as $id) {
            $this->d->addOn($id, 'task');
        }
		//print_r($defaults);
		$this->update($exceptions, 'enroll');
    }

    public function setTaskExceptions( $tasks ) {
        $this->setExceptions( $tasks );
    }

    public function setMissionExceptions( $missions ) {
        //reconstruct missions array to have category name with list of missions per category name
        $tasks = $this->getTaskMissions( $missions );
        $this->setExceptions( $tasks, true );
    }

    private function setExceptions($tasks, $missions = false) {
		$arrTasks = $this->prepareTasks($tasks, $missions);
        $defaults = $arrTasks[0];
		$exceptions = $arrTasks[1];
		
		//add exceptions to table
        foreach($exceptions as $exception) {
            $sql = "insert ignore into {$this->type}_task_exceptions values (null, " . $this->id . ", $exception)";
            mysql_query($sql);
        }
		
		$this->update($defaults, 'unenroll');
    }

	private function prepareTasks($tasks, $missions = false) {
		$taskMissions = array(); 
        if ($missions) {
            //break up tasks array into two seperate arrays - tasks and missions 
            //in order that the same function can work both for tasks without missions
            //as well as for tasks with missions
            $newTasks = array();
            foreach($tasks as $task => $missions) {
                $newTasks[] = $task;
                $taskMissions[$task] = $missions;
            }
            $tasks = $newTasks;
        } 
		
		$defaults = array();
		$exceptions = array();
        foreach($tasks as $task) {
        	if (isset($taskMissions[$task])) {
                $ids = $this->getTaskIDs($task, $taskMissions[$task]);
				$d = $this->getDefaultIDs($task, $taskMissions[$task]);
			} else {
                $ids = $this->getTaskIDs($task);
				$d = $this->getDefaultIDs($task);
			}
			foreach ($ids as $id) {
				$exceptions[] = $id;
			}
			foreach ($d as $id) {
				$defaults[] = $id;
			}
		}
		
		return array($defaults, $exceptions);
	}

	private function update($ids, $action) {
    		
    	$dt = implode(",", $ids);
    	
    	if ($action == 'enroll') {
    		$table = "task_exceptions";
    		$field = "date_task_id";
			//echo $dt;
    	} else if ($action == 'unenroll') {
    		$table = "tasks";
			$field = "task_id";
			//echo $dt;
    	}
		
		//if school, delete from school, all classes, all users
		//if class, delete from class, users in class, school (and give back to other classes)
		//if user, delete from user, class (and give back to other users in class), school (and give back to other classes)
		
    	$classes = array();
        $sql = "select class_id from classes where school_id = $this->school_id and class_era = 0";
        $result = mysql_query($sql);
        while ($row = mysql_fetch_assoc($result)) {
            $classes[] = $row['class_id'];
        }
						
		if ($this->type == 'school') {
			$sql = "delete from school_{$table} where school_id = $this->id and {$field} in (" . $dt . ")";
			mysql_query($sql);
			
			foreach ($classes as $class) {
				$users = $this->getUsersInGrade($class);
				$sql1 = "delete from class_{$table} where class_id = $class and {$field} in (" . $dt . ")";
				$sql2 = "delete from user_{$table} where user_id in (" . implode(",", $users) . ") and {$field} in (" . $dt . ")";
				mysql_query($sql1);
				mysql_query($sql2);
			}
		
		} else if ($this->type == 'class') {
			$users = $this->getUsersInGrade($this->id);
			$sql1 = "delete from class_{$table} where class_id = $this->id and {$field} in (" . $dt . ")";
			$sql2 = "delete from user_{$table} where user_id in (" . implode(",", $users) . ") and {$field} in (" . $dt . ")";
			mysql_query($sql1);
			mysql_query($sql2);
			
			$schoolTasks = array();
			$sql = "SELECT * FROM school_{$table} WHERE school_id = $this->school_id AND {$field} IN (" . $dt . ")";
            $result = mysql_query($sql);
            
            if ( $result )
			    while ($row = mysql_fetch_assoc($result))
				    $schoolTasks[] = $row["$field"];
			
			//delete from school
			$sql = "DELETE FROM school_{$table} WHERE school_id = $this->school_id AND {$field} IN (" . $dt . ")";
			mysql_query($sql);
			
			//add to other classes				
			foreach ($classes as $class) {
				if ($class == $this->id) continue;
                foreach ($schoolTasks as $id) {
                	if ($action == 'enroll') { 
                    	$sql = "insert into class_{$table} values (null, $class, $id)";
					} else if ($action == 'unenroll') {
						$sql = "insert into class_{$table} values ($class, $id)";
					}
                    mysql_query($sql);
                }
            }
            
		} else if ($this->type == 'user') {
			$sql = "delete from user_{$table} where user_id = $this->id and {$field} in (" . $dt . ")";
			//echo $sql; exit;
			mysql_query($sql);
			
			//first take away from school and give to classes		
			$schoolTasks = array();
			$sql = "select * from school_{$table} where school_id = $this->school_id and {$field} in (" . $dt . ")";
            $result = mysql_query($sql);
            if ( $result )
			    while ($row = mysql_fetch_assoc($result))
			        $schoolTasks[] = $row["$field"];
			
			//delete from school
			$sql = "delete from school_{$table} where school_id = $this->school_id and {$field} in (" . $dt . ")";
			mysql_query($sql);
			
			//add to classes	
			foreach ($classes as $class) {
                foreach ($schoolTasks as $id) {
                	if ($action == 'enroll') { 
                    	$sql = "insert into class_{$table} values (null, $class, $id)";
					} else if ($action == 'unenroll') {
						$sql = "insert into class_{$table} values ($class, $id)";
					}
                    mysql_query($sql);
                }
            }
			
			//then take away from class and give to other users in class
			$classTasks = array();
			$class_id = $this->getClassID($this->id);
			$sql = "select * from class_{$table} where class_id = $class_id and {$field} in (" . $dt . ")";
            $result = mysql_query($sql);
            if ( $result )
			    while ($row = mysql_fetch_assoc($result))
				    $classTasks[] = $row["$field"];
			
			//delete from class
			$sql = "delete from class_{$table} where class_id = $class_id and {$field} in (" . $dt . ")";
			mysql_query($sql);
			
			//add to other users
			$users = $this->getUsersInGrade($class_id);
			foreach ($users as $user) {
				if ($user == $this->id) continue;
				foreach ($classTasks as $id) {
					if ($action == 'enroll') { 
                    	$sql = "insert into user_{$table} values (null, $user, $id)";
					} else if ($action == 'unenroll') {
						$sql = "insert into user_{$table} values ($user, $id)";
					}
                    mysql_query($sql);
				}
			}
        }
    }

    public function reset($school_id) {
		$classes = array();
		$sql = "select class_id from classes where school_id = $school_id and class_era = 0";
		$result = mysql_query($sql);
		while ($row = mysql_fetch_assoc($result)) {
			$classes[] = $row['class_id'];
		}
		
		$users = array();
		$sql = "select user_id from users where school_id = $school_id and user_registered > 0";
		$result = mysql_query($sql);
		while ($row = mysql_fetch_assoc($result)) {
			$users[] = $row['user_id'];
		}
        
		//$campaigns = $this->getCampaigns($school_id);
		$sql = "update user_tracks set enrolled = 1 where user_id in (" . implode(",", $users) . ")";
		//echo $sql;
        if (!mysql_query($sql)) {
        	echo $sql;
            return false;
        }
        
		//delete exceptions
        $sql1 = "delete from school_task_exceptions 
        		where school_id = " . $school_id;
        $sql2 = "delete from class_task_exceptions 
        		where class_id in (" . implode(',', $classes) . ")";
        $sql3 = "delete from user_task_exceptions 
        		where user_id in (" . implode(',', $users) . ")";
		/*
        $sql1 = "delete ste.* from school_task_exceptions ste 
        		join date_tasks using (date_task_id) 
        		join date_tasks_missions dtm using (date_tasks_mission_id) 
        		where school_id = " . $school_id . "
				and dtm.start_date >= " . unixtojd();
        $sql2 = "delete cte.* from class_task_exceptions cte 
        		join date_tasks using (date_task_id) 
        		join date_tasks_missions dtm using (date_tasks_mission_id) 
        		where class_id in (" . implode(',', $classes) . ") 
				and dtm.start_date >= " . unixtojd();
        $sql3 = "delete ute.* from user_task_exceptions ute 
        		join date_tasks using (date_task_id) 
        		join date_tasks_missions dtm using (date_tasks_mission_id) 
        		where user_id in (" . implode(',', $users) . ") 
				and dtm.start_date >= " . unixtojd();
		*/
        if (!(mysql_query($sql1) && mysql_query($sql2) && mysql_query($sql3))) {
        	echo $sql1 . " " . $sql2 . " " . $sql3;
            return false;
        }
		
		//delete default ons
		$names = array('missions', 'tasks');
		foreach ($names as $name) {
			$sql1 = "delete from user_{$name} 
					where user_id in (" . implode(",", $users) . ")"; 
			$sql2 = "delete from class_{$name} 
					where class_id in (" . implode(",", $classes) . ")";
			$sql3 = "delete from school_{$name} 
					where school_id = " . $school_id;
			/*
			$id_name = substr($name, 0, (strlen($name) - 1));
			$sql1 = "delete u.* from user_{$name} u 
					join date_tasks dt on dt.date_task_id = u.{$id_name}_id  
					join date_tasks_missions dtm using (date_tasks_mission_id) 
					where user_id in (" . implode(",", $users) . ") 
					and dtm.start_date >= " . unixtojd();
			$sql2 = "delete c.* from class_{$name} c 
					join date_tasks dt on dt.date_task_id = c.{$id_name}_id  
					join date_tasks_missions dtm using (date_tasks_mission_id) 
					where class_id in (" . implode(",", $classes) . ") 
					and dtm.start_date >= " . unixtojd();
			$sql3 = "delete s.* from school_{$name} s 
					join date_tasks dt on dt.date_task_id = s.{$id_name}_id  
					join date_tasks_missions dtm using (date_tasks_mission_id) 
					where school_id = " . $school_id . " 
					and dtm.start_date >= " . unixtojd();
			*/
			if (!(mysql_query($sql1) && mysql_query($sql2) && mysql_query($sql3))) {
				echo $sql1 . " " . $sql2 . " " . $sql3;
				return false;
			}
		}
		
		//if we get here then all queries were successfully executed	
        return true;
    }
    
    public function resetChild($child) {
    	//delete exceptions
        $sql = "delete from user_task_exceptions 
        		where user_id = $child";
		if (!mysql_query($sql)) {
			return false;
		}
		
		//delete default ons
		$names = array('missions', 'tasks');
		foreach ($names as $name) {
			$sql = "delete from user_{$name} 
					where user_id = $child";
			if (!mysql_query($sql)) {
				return false;
			}
		}
		
		return true;	
    }
}

/*
$subject = null;
if (strrpos($task, "|") !== FALSE) {
	$pos = strrpos($task, "|");	
	$subject = substr($task, 0, $pos);
	$task = substr($task, ($pos+1));
}

$sql = "delete {$this->type}_task_exceptions.* from {$this->type}_task_exceptions  
        join date_tasks dt using (date_task_id) 
        join date_tasks_missions dtm using (date_tasks_mission_id) 
        join user_tracks ut using (subject_id, track_id, level) 
        join users u on (u.user_id = ut.user_id)  
        where dt.cat = \"" . mysql_real_escape_string( $task ) . "\"  
        and dtm.school_type_id = u.school_type_id  
        and dtm.start_date >= $this->start 
        and dtm.end_date <= $this->end 
        and u.{$this->type}_id = " . $this->id; 
if ( $missions ) {
    $sql .= " and dtm.mission_name in (\"" . implode("\",\"", $taskMissions[$task]) . "\")";
}  
if (!is_null($subject)) {
	$sql .= " and dtm.subject_id = " . $subject;
}             
//echo $sql;
mysql_query( $sql );


//separate subject from category
$subject = null;
if (strrpos($task, "|") !== FALSE) {
	$pos = strrpos($task, "|");	
	$subject = substr($task, 0, $pos);
	$task = substr($task, ($pos+1));
}

//clean taskMissions so that task only contains category and not subject id
foreach ($taskMissions as $task => $missions) {
	if (strrpos($task, "|") !== FALSE) {
		unset($taskMissions[$task]);
		$pos = strrpos($task, "|");	
		$task = substr($task, ($pos+1));
		$taskMissions[$task] = $missions;
	}
}

//if this is a user, we need to check if class and / or school has this exception or default on
//if either of them do, we need to remove it from class/school and give it back to all other classes 
//as well as all other students in this class except for this one student
if ($this->type == 'user') {
    
    //need to check if school has this exception, if it does, we need to remove the 
    //exception from the school, and give it back to all classes except for this class
    $class_id = $this->getClassID($this->id);                
    $sql = "select * from school_task_exceptions 
            join date_tasks dt using (date_task_id) 
            join date_tasks_missions dtm using (date_tasks_mission_id) 
            join user_tracks ut using (subject_id, track_id, level) 
            join users u on (u.user_id = ut.user_id)  
            where dt.cat = \"" . mysql_real_escape_string( $task ) . "\" 
            and dtm.school_type_id = u.school_type_id  
            and dtm.start_date >= $this->start 
            and dtm.end_date <= $this->end 
            and dtm.personal = 0 
            and u.school_id = " . $this->school_id; 
    if ($missions) {
        if (count($taskMissions[$task]) == 1) {
            $sql .= " and dtm.mission_name = '" . mysql_real_escape_string($taskMissions[$task][0]) . "' ";
        } else {
        	$sql .= " and (";
			$num = count($taskMissions[$task]);
			for ($i = 0; $i < $num; $i++) {
				$sql .= "dtm.mission_name = '" . mysql_real_escape_string($taskMissions[$task][$i]) . "'";
				if ($i < ($num-1)) {
					$sql .= " or ";
				} else {
					$sql .= ")";
				}
			}
        }
        //$sql .= " and dtm.mission_name in (\"" . implode("\",\"", $taskMissions[$task]) . "\")";
    }
    $result = mysql_query($sql);
    if (mysql_num_rows($result) > 0) {
        $exceptions = array();
        while ($row = mysql_fetch_assoc($result)) {
            $exceptions[] = $row['date_task_id'];
        }            
        $sql = "delete school_task_exceptions.* from school_task_exceptions 
                join date_tasks dt using (date_task_id) 
                join date_tasks_missions dtm using (date_tasks_mission_id) 
                join user_tracks ut using (subject_id, track_id, level) 
                join users u on (u.user_id = ut.user_id)  
                where dt.cat = \"" . mysql_real_escape_string( $task ) . "\" 
                and dtm.school_type_id = u.school_type_id  
                and dtm.start_date >= $this->start 
                and dtm.end_date <= $this->end 
                and u.school_id = " . $this->school_id; 
        if ($missions) {
            if (count($taskMissions[$task]) == 1) {
                $sql .= " and dtm.mission_name = '" . mysql_real_escape_string( $taskMissions[$task][0] ) . "' ";
            } else {
            	$sql .= " and (";
				$num = count($taskMissions[$task]);
				for ($i = 0; $i < $num; $i++) {
					$sql .= "dtm.mission_name = '" . mysql_real_escape_string($taskMissions[$task][$i]) . "'";
					if ($i < ($num-1)) {
						$sql .= " or ";
					} else {
						$sql .= ")";
					}
				}
            }
            //$sql .= " and dtm.mission_name in (\"" . implode("\",\"", $taskMissions[$task]) . "\")";
        }
        mysql_query($sql);
        
        //now add all exceptions to all classes except for the users's class
        $classes = array();
        $sql = "select class_id from classes c 
                join users u using (class_id) 
                where c.school_id = " . $this->school_id . " 
                and u.user_id != " . $this->id . " 
                and c.class_era = 0";
        $result = mysql_query($sql);
        while ($row = mysql_fetch_assoc($result)) {
            $classes[] = $row['class_id'];
        }
        foreach ($classes as $class) {
            foreach ($exceptions as $exception) { 
                $sql = "insert into class_task_exceptions values (null, $class, $exception)";
                mysql_query($sql);
            }
        }
        //now add exceptions to all users in class except for this user
        $users = $this->getUsersInGrade($class_id);
        foreach ($users as $user) {
            if ($user == $this->id)
                continue;
            else {
                foreach ($exceptions as $exception) {
                    $sql = "insert into user_task_exceptions values(null, $user, $exception)";
                    mysql_query($sql);
                }
            }
        }
    } else {
        $sql = "select * from class_task_exceptions 
                join date_tasks dt using (date_task_id) 
                join date_tasks_missions dtm using (date_tasks_mission_id) 
                join user_tracks ut using (subject_id, track_id, level) 
                join users u on (u.user_id = ut.user_id)  
                where dt.cat = \"" . mysql_real_escape_string( $task ) . "\"  
                and dtm.school_type_id = u.school_type_id  
                and dtm.start_date >= $this->start 
                and dtm.end_date <= $this->end 
                and dtm.personal = 0 
                and u.class_id = " . $class_id; 
        if ($missions) {
            if (count($taskMissions[$task]) == 1) {
                $sql .= " and dtm.mission_name = '" . mysql_real_escape_string( $taskMissions[$task][0] ) . "' ";
            } else {
            	$sql .= " and (";
				$num = count($taskMissions[$task]);
				for ($i = 0; $i < $num; $i++) {
					$sql .= "dtm.mission_name = '" . mysql_real_escape_string($taskMissions[$task][$i]) . "'";
					if ($i < ($num-1)) {
						$sql .= " or ";
					} else {
						$sql .= ")";
					}
				}
            }
            //$sql .= " and dtm.mission_name in (\"" . implode("\",\"", $taskMissions[$task]) . "\")";
        }
        $result = mysql_query($sql);
        if (mysql_num_rows($result) > 0) {
            $exceptions = array();
            while ($row = mysql_fetch_assoc($result)) {
                $exceptions[] = $row['date_task_id'];
            }  
            $sql = "delete class_task_exceptions.* from class_task_exceptions 
                    join date_tasks dt using (date_task_id) 
                    join date_tasks_missions dtm using (date_tasks_mission_id) 
                    join user_tracks ut using (subject_id, track_id, level) 
                    join users u on (u.user_id = ut.user_id)  
                    where dt.cat = \"" . mysql_real_escape_string( $task ) . "\"  
                    and dtm.school_type_id = u.school_type_id  
                    and dtm.start_date >= $this->start 
                    and dtm.end_date <= $this->end 
                    and u.class_id = " . $class_id; 
            if ($missions) {
	            if (count($taskMissions[$task]) == 1) {
	                $sql .= " and dtm.mission_name = '" . mysql_real_escape_string( $taskMissions[$task][0] ) . "' ";
	            } else {
	            	$sql .= " and (";
					$num = count($taskMissions[$task]);
					for ($i = 0; $i < $num; $i++) {
						$sql .= "dtm.mission_name = '" . mysql_real_escape_string($taskMissions[$task][$i]) . "'";
						if ($i < ($num-1)) {
							$sql .= " or ";
						} else {
							$sql .= ")";
						}
					}
	            }
                //$sql .= " and dtm.mission_name in (\"" . implode("\",\"", $taskMissions[$task]) . "\")";
            }
            mysql_query($sql);
            
            //now add exceptions to all users in class except for this user
            $users = $this->getUsersInGrade($class_id);
            foreach ($users as $user) {
                if ($user == $this->id)
                    continue;
                else {
                    foreach ($exceptions as $exception) {
                        $sql = "insert into user_task_exceptions values(null, $user, $exception)";
                        mysql_query($sql);
                    }
                }
            }
        }
    }
    
//take away class / user exceptions as well
} else if ($this->type == 'class') {
    //also take away exceptions from users
    $users = $this->getUsersInGrade($this->id);
    foreach ($users as $user) {
        $sql = "delete user_task_exceptions.* from user_task_exceptions  
                join date_tasks dt using (date_task_id) 
                join date_tasks_missions dtm using (date_tasks_mission_id) 
                join user_tracks ut using (subject_id, track_id, level) 
                join users u on (u.user_id = ut.user_id)  
                where dt.cat = \"" . mysql_real_escape_string( $task ) . "\"  
                and dtm.school_type_id = u.school_type_id  
                and dtm.start_date >= $this->start 
                and dtm.end_date <= $this->end 
                and u.user_id = " . $user; 
        if ($missions) {
            if (count($taskMissions[$task]) == 1) {
                $sql .= " and dtm.mission_name = '" . mysql_real_escape_string( $taskMissions[$task][0] ) . "' ";
            } else {
            	$sql .= " and (";
				$num = count($taskMissions[$task]);
				for ($i = 0; $i < $num; $i++) {
					$sql .= "dtm.mission_name = '" . mysql_real_escape_string($taskMissions[$task][$i]) . "'";
					if ($i < ($num-1)) {
						$sql .= " or ";
					} else {
						$sql .= ")";
					}
				}
            }
            //$sql .= " and dtm.mission_name in (\"" . implode("\",\"", $taskMissions[$task]) . "\")";
        }
        mysql_query( $sql );   
    }
    
    //need to check if school has this exception, if it does, we need to remove the 
    //exception from the school, and give it back to all classes except for this class
    $sql = "select * from school_task_exceptions 
            join date_tasks dt using (date_task_id) 
            join date_tasks_missions dtm using (date_tasks_mission_id) 
            join user_tracks ut using (subject_id, track_id, level) 
            join users u on (u.user_id = ut.user_id)  
            where dt.cat = \"" . mysql_real_escape_string( $task ) . "\"  
            and dtm.school_type_id = u.school_type_id  
            and dtm.start_date >= $this->start 
            and dtm.end_date <= $this->end 
            and dtm.personal = 0 
            and u.school_id = " . $this->school_id; 
    if ($missions) {
        if (count($taskMissions[$task]) == 1) {
            $sql .= " and dtm.mission_name = '" . mysql_real_escape_string( $taskMissions[$task][0] ) . "' ";
        } else {
        	$sql .= " and (";
			$num = count($taskMissions[$task]);
			for ($i = 0; $i < $num; $i++) {
				$sql .= "dtm.mission_name = '" . mysql_real_escape_string($taskMissions[$task][$i]) . "'";
				if ($i < ($num-1)) {
					$sql .= " or ";
				} else {
					$sql .= ")";
				}
			}
        }
        //$sql .= " and dtm.mission_name in (\"" . implode("\",\"", $taskMissions[$task]) . "\")";
    }
    $result = mysql_query($sql);
    if (mysql_num_rows($result) > 0) {
        $exceptions = array();
        while ($row = mysql_fetch_assoc($result)) {
            $exceptions[] = $row['date_task_id'];
        }            
        $sql = "delete school_task_exceptions.* from school_task_exceptions 
                join date_tasks dt using (date_task_id) 
                join date_tasks_missions dtm using (date_tasks_mission_id) 
                join user_tracks ut using (subject_id, track_id, level) 
                join users u on (u.user_id = ut.user_id)  
                where dt.cat = \"" . mysql_real_escape_string( $task ) . "\" 
                and dtm.school_type_id = u.school_type_id  
                and dtm.start_date >= $this->start 
                and dtm.end_date <= $this->end 
                and u.school_id = " . $this->school_id; 
        mysql_query($sql);
        
        //now add all exceptions to all classes except for this one
        $classes = array();
        $sql = "select class_id from classes where school_id = " . $this->school_id . " 
                and class_id != " . $this->id . " and class_era = 0";
        $result = mysql_query($sql);
        while ($row = mysql_fetch_assoc($result)) {
            $classes[] = $row['class_id'];
        }
        foreach ($classes as $class) {
            foreach ($exceptions as $exception) { 
                $sql = "insert into class_task_exceptions values (null, $class, $exception)";
                mysql_query($sql);
            }
        }
    }               
                        
} else if ($this->type == 'school') {
    //also take away exceptions from both classes and users
    $classes = array();
    $sql = "select class_id from classes where school_id = " . $this->id . " and class_era = 0";
    $result = mysql_query($sql);
    while ($row = mysql_fetch_assoc($result)) {
        $classes[] = $row['class_id'];
    }
    foreach ($classes as $class) {
        $sql = "delete class_task_exceptions.* from class_task_exceptions  
                join date_tasks dt using (date_task_id) 
                join date_tasks_missions dtm using (date_tasks_mission_id) 
                join user_tracks ut using (subject_id, track_id, level) 
                join users u on (u.user_id = ut.user_id)  
                where dt.cat = \"" . mysql_real_escape_string( $task ) . "\"  
                and dtm.school_type_id = u.school_type_id  
                and dtm.start_date >= $this->start 
                and dtm.end_date <= $this->end 
                and u.class_id = " . $class; 
        if ($missions) {
            if (count($taskMissions[$task]) == 1) {
                $sql .= " and dtm.mission_name = '" . mysql_real_escape_string( $taskMissions[$task][0] ) . "' ";
            } else {
            	$sql .= " and (";
				$num = count($taskMissions[$task]);
				for ($i = 0; $i < $num; $i++) {
					$sql .= "dtm.mission_name = '" . mysql_real_escape_string($taskMissions[$task][$i]) . "'";
					if ($i < ($num-1)) {
						$sql .= " or ";
					} else {
						$sql .= ")";
					}
				}
            }
            //$sql .= " and dtm.mission_name in (\"" . implode("\",\"", $taskMissions[$task]) . "\")";
        }
        mysql_query( $sql );
    }
    foreach ($classes as $class) {
        //also take away exceptions from users
        $users = $this->getUsersInGrade($class);
        foreach ($users as $user) {
            $sql = "delete user_task_exceptions.* from user_task_exceptions  
                    join date_tasks dt using (date_task_id) 
                    join date_tasks_missions dtm using (date_tasks_mission_id) 
                    join user_tracks ut using (subject_id, track_id, level) 
                    join users u on (u.user_id = ut.user_id)  
                    where dt.cat = \"" . mysql_real_escape_string( $task ) . "\"  
                    and dtm.school_type_id = u.school_type_id  
                    and dtm.start_date >= $this->start 
                    and dtm.end_date <= $this->end 
                    and u.user_id = " . $user; 
            if ($missions) {
	            if (count($taskMissions[$task]) == 1) {
	                $sql .= " and dtm.mission_name = '" . mysql_real_escape_string( $taskMissions[$task][0] ) . "' ";
	            } else {
	            	$sql .= " and (";
					$num = count($taskMissions[$task]);
					for ($i = 0; $i < $num; $i++) {
						$sql .= "dtm.mission_name = '" . mysql_real_escape_string($taskMissions[$task][$i]) . "'";
						if ($i < ($num-1)) {
							$sql .= " or ";
						} else {
							$sql .= ")";
						}
					}
	            }
                //$sql .= " and dtm.mission_name in (\"" . implode("\",\"", $taskMissions[$task]) . "\")";
            }
            mysql_query( $sql );   
        }       
    }
}
*/
?>