<?
require_once 'class.defaults.php';

class TasksCustomizationNew { 
    private $start;
    private $end;
    private $type;
    private $id;
    private $schoolType;
    private $school_id;
    private $d;

    public function __construct() {
        $this->start = null;
        $this->end = null;
        $this->type = null;
        $this->id = null;
        $this->schoolType = null;
        $this->school_id = null;
    }
    
    public function setStart( $start ) {
        $this->start = $start;
    }

    public function setEnd( $end ) {
        $this->end = $end;
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
            $sql = "select school_id from users where user_id = " . $user;
            $result = mysql_query( $sql );
            $row = mysql_fetch_assoc( $result );
            $school = $row['school_id'];
        }
        
        $this->setSchoolType( $school );
        $this->d = new Defaults($this->id, $this->type);
    }
	
    private function setSchoolType( $id ) {
        $sql = "select inst_id from schools where school_id = " . $id;
        $result = mysql_query( $sql );
        $row = mysql_fetch_assoc( $result );
        $instType = $row['inst_id'];
        if ( $instType == 2 ) {
            $this->schoolType = "(2,3)";
        } else if ( $instType == 4 ) {
            $this->schoolType = "(12,13)";
        }
        $this->school_id = $id;
    }
    
    //for parent accounts
    public function getCampaignsForChild( $user ) {
        $campaigns = array();
        $enrolled = array();
        $sql = "select s.subject_id, s.subject_name, ut.enrolled  
                from subjects s 
                join user_tracks ut using (subject_id) 
                join users u using (user_id) 
                where s.subject_id not in (21, 27) 
                and u.user_id = $user   
                group by s.subject_id"; 
        $result = mysql_query( $sql );
        while ( $row = mysql_fetch_assoc( $result ) ) {
            $campaigns[$row['subject_id']] = $row['subject_name'];
            if ( $row['enrolled'] )
                $enrolled[] = $row['subject_id'];
        }
        
        $both = array(
            'campaigns' =>  $campaigns, 
            'enrolled'  =>  $enrolled
        );
        return $both;
    }
	
    public function getCampaigns( $id, $additional = true ) {
        $campaigns = array(); 
        
        //find out institution type
        $sql = "select inst_id from schools where school_id = " . $id;
        $result = mysql_query( $sql );
        $row = mysql_fetch_assoc( $result );
        $type = $row['inst_id'];

        switch( $type ) {
            case '2':
                $sql = "select subject_id, subject_name from subjects s 
                        join school_type_subjects sts using (subject_id) 
                        where s.subject_type in ('', 'WWTC') 
                        and sts.school_type_id in (2,3) 
                        and s.subject_id not in (21, 27) 
                        group by s.subject_id 
                        order by s.subject_name";
                break;
            case '4':
                $sql = "select subject_id, subject_name from subjects s 
                        join school_type_subjects sts using (subject_id) 
                        where s.subject_type in ('', 'WWTC') 
                        and sts.school_type_id in (12,13) 
                        group by s.subject_id 
                        order by s.subject_name";
                break; 
        }
        //echo $sql . "<br />";
        $result = mysql_query( $sql );
        while ( $row = mysql_fetch_assoc($result) ) {
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
                    where s.subject_type in ('', 'WWTC') 
                    and s.subject_id not in (21, 27) 
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
                    and ut.subject_id not in (27) 
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
                foreach ($users as $user) {
                    $sql = "select subject_id from user_tracks ut 
                            where ut.user_id = $user 
                            and ut.enrolled = 1 
                            and ut.subject_id = $subject";
                    $result = mysql_query( $sql );
                    if (mysql_num_rows($result) > 0) {
                        if (!in_array($subject, $classEnrolled)) {
                            $classEnrolled[] = $subject;
                            break;
                        }
                    }
                }
            } 
            $enrolled = $classEnrolled;
        } else if ($school > 0) { //check that at least one student in school is enrolled
            $users = $this->getAllUsers($school);
            $schoolEnrolled = array();
            foreach ($enrolled as $subject) {
                $sql = "select subject_id from user_tracks 
                        where user_id in (" . implode(',', $users) . ") 
                        and enrolled = 1 
                        and subject_id = $subject";
                $result = mysql_query($sql);
                if (mysql_num_rows($result) > 0) {
                    $schoolEnrolled[] = $subject;
                }
            }
            $enrolled = $schoolEnrolled;                                                 
        }
        return $enrolled;
    }
    
    private function getTaskIDs( $cat, $missions = array() ) {
        $tasks = array();
        $sql = "select dt.date_task_id from date_tasks dt 
                join date_tasks_missions dtm using (date_tasks_mission_id) 
                join user_tracks ut using (subject_id, track_id, level) 
                join users u using (user_id) 
                where dt.cat = '" . mysql_real_escape_string( $cat ) . "'   
                and dtm.school_type_id = u.school_type_id 
                and dtm.start_date >= $this->start 
                and dtm.end_date <= $this->end 
                and u.{$this->type}_id = " . $this->id;  
        if ( count( $missions ) > 0 ) {
            if ( count( $missions == 1 ) ) {
                $sql .= " and dtm.mission_name = '" . mysql_real_escape_string( $missions[0] ) . "' ";
            } else {
                $sql .= " and dtm.mission_name in (\"" . mysql_real_escape_string( implode("\",\"", $missions) ) . "\")";
            }
        }
        $sql .= " group by dt.date_task_id";
        //echo $sql;
        $result = mysql_query( $sql ) or die( $sql );
        while ( $row = mysql_fetch_assoc( $result ) ) {
            $tasks[] = $row['date_task_id'];
        }
        return $tasks;
    }

    private function isException( $taskIDs ) {
        if ( !empty( $taskIDs ) ) {
            //for schools, find out school exceptions, 
            //for classes find school and class exceptions, 
            //for users find school, class, and user exceptions
            
            switch ($this->type) {
                case 'school':
                    foreach( $taskIDs as $taskID ) {
                        $sql = "select * from {$this->type}_task_exceptions 
                                where date_task_id = $taskID  
                                and {$this->type}_id = " . $this->id;
                        //echo $sql;
                        $result = mysql_query( $sql );
                        if ( mysql_num_rows( $result ) == 0 ) {
                            return false;
                        }
                    }
                    break;
                case 'class':
                    $classExceptions = true;
                    foreach( $taskIDs as $taskID ) {
                        $sql = "select * from {$this->type}_task_exceptions 
                                where date_task_id = $taskID  
                                and {$this->type}_id = " . $this->id;
                        //echo $sql;
                        $result = mysql_query( $sql );
                        if ( mysql_num_rows( $result ) == 0 ) {
                            $classExceptions = false;
                            break;
                        }
                    }
                    
                    $schoolExceptions = true;
                    foreach( $taskIDs as $taskID ) {
                        $sql = "select * from school_task_exceptions 
                                where date_task_id = $taskID  
                                and school_id = " . $this->school_id;
                        //echo $sql;
                        $result = mysql_query( $sql );
                        if ( mysql_num_rows( $result ) == 0 ) {
                            $schoolExceptions = false;
                            break;
                        }
                    }
                    
                    if (!$classExceptions && !$schoolExceptions) {
                        return false;
                    }
                    break;
                case 'user':
                    $userExceptions = true;
                    foreach( $taskIDs as $taskID ) {
                        $sql = "select * from {$this->type}_task_exceptions 
                                where date_task_id = $taskID  
                                and {$this->type}_id = " . $this->id;
                        //echo $sql;
                        $result = mysql_query( $sql );
                        if ( mysql_num_rows( $result ) == 0 ) {
                            $userExceptions = false;
                            break;
                        }
                    }
                    /*
                    $classExceptions = true;
                    $class_id = $this->getClassID($this->id);
                    foreach( $taskIDs as $taskID ) {
                        $sql = "select * from class_task_exceptions 
                                where date_task_id = $taskID  
                                and class_id = " . $class_id;
                        //echo $sql;
                        $result = mysql_query( $sql );
                        if ( mysql_num_rows( $result ) == 0 ) {
                            $classExceptions = false;
                            break;
                        }
                    }
                    
                    $schoolExceptions = true;
                    foreach( $taskIDs as $taskID ) {
                        $sql = "select * from school_task_exceptions 
                                where date_task_id = $taskID  
                                and school_id = " . $this->school_id;
                        //echo $sql;
                        $result = mysql_query( $sql );
                        if ( mysql_num_rows( $result ) == 0 ) {
                            $schoolExceptions = false;
                            break;
                        }
                    }
                     * 
                     */
                    if (!$userExceptions) {
                        return false;  
                    }
                    break;
            }
            //if we get here then all tasks were in exceptions table
            return true;
        } else {
            return false;
        }
    }

    public function getTasks( $subject_id ) {
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
                    order by dt.cat, dtm.school_type_id, dtm.level, dt.name";
        } else {
            $sql = "select distinct dt.cat, dt.name, dt.quantity, dtm.school_type_id, dtm.level, dt.default_on from date_tasks dt 
                    join date_tasks_missions dtm using (date_tasks_mission_id) 
                    where dtm.subject_id = " . $subject_id . " 
                    and dtm.start_date >= " . $this->start . " 
                    and dtm.end_date <= " . $this->end . "  
                    and dtm.school_type_id in " . $this->schoolType . " 
                    and (dtm.created_by_school is null or dtm.created_by_school = $this->school_id)                         
                    order by dt.cat, dtm.school_type_id, dtm.level, dt.name";
        }
        //echo $sql; 

        $result = mysql_query( $sql );
        while ( $row = mysql_fetch_assoc( $result ) ) {
            if ($row['cat'] == 'Birthday')
                continue;
            $tasks[$row['cat']] = $row['default_on'];
            $info[$row['cat']][$row['name']][$row['school_type_id']][$row['level']] = $row['quantity'];
        }
        
        //check for defaults
        foreach ($tasks as $task => $on) {
            if (!$on) {
                //find out if school/class/user has the default on 
                $defaults = $this->getDefaultIDs($subject_id, $task);
                //print_r($defaults);
                $isOn = false;
                if (!empty($defaults)) 
                    $isOn = true;
                foreach ($defaults as $id) {
                    if (!$this->d->isOn($id, 'task')) {
                        $isOn = false;
                        break;
                    }
                }
                if ($isOn) {
                    //echo $task . " is on.";
                    $tasks[$task] = 1;
                }
            }
        }   
        
        //check that user/class/school is enrolled to campaign     
        if ( $this->type == 'user' ) {
            $userTasks = array();
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
                    group by dt.cat";
            //echo $sql;
            $result = mysql_query( $sql );
            while ( $row = mysql_fetch_assoc( $result ) ) {
                $userTasks[] = $row['cat'];
                $userEnrolled[$row['cat']] = $row['enrolled'];
            }
            //find out which tasks user is not enrolled into
            foreach( $tasks as $task => $enrolled ) {
                if ( !in_array( $task, $userTasks ) ) {
                    //remove task from tasks array and info array
                    unset( $tasks[$task] );
                    unset( $info[$task] );
                } else {
                    $tasks[$task] = $userEnrolled[$task];
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
                        group by dt.cat";
                //echo $sql;
                $result = mysql_query( $sql );
                while ($row = mysql_fetch_assoc($result)) {
                    if ($row['enrolled'] == 1) {
                        if (!in_array($row['cat'], $classEnrolled)) {
                            $classEnrolled[] = $row['cat'];
                        }
                    }
                }
            }
            //find out which tasks class is not enrolled into
            foreach( $tasks as $task => $enrolled ) {
                if (!in_array( $task, $classEnrolled )) {
                    //set task to unenrolled
                    $tasks[$task] = 0;
                }
            }
        }
        
        //find out if all task ids for category are in exceptions table
        foreach( $tasks as $task => $enrolled ) {
            $ids = $this->getTaskIDs( $task );
            if ( $this->isException( $ids ) ) {
                $tasks[$task] = 0;
            }
        }

        //create user friendly info array
        /*
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
         *
         */
        
        $friendly = array();
        foreach( $info as $task => $arr ) {
            foreach( $arr as $name => $arr2 ) {
                foreach( $arr2 as $type => $arr3 ) { 
                    foreach( $arr3 as $level => $quantity ) {
                        $friendly[$task][$name][$type][$level] = $quantity;
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
        return $allInfo;
    }

    private function getDefaultIDs($subject_id, $task) {
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
                    and dt.default_on = 0 
                    and dt.cat = \"" . mysql_real_escape_string($task) . "\"";
        } else {
            $sql = "select distinct dt.date_task_id from date_tasks dt 
                    join date_tasks_missions dtm using (date_tasks_mission_id) 
                    where dtm.subject_id = " . $subject_id . " 
                    and dtm.start_date >= " . $this->start . " 
                    and dtm.end_date <= " . $this->end . "  
                    and dtm.school_type_id in " . $this->schoolType . " 
                    and dt.default_on = 0 
                    and dt.cat = \"" . mysql_real_escape_string($task) . "\"";
        }
        //echo $sql;
        $result = mysql_query($sql);
        while ($row = mysql_fetch_assoc($result)) {
            $defaults[] = $row['date_task_id'];
        }
        return $defaults;
    }
	
    public function getMissions($cat) {
        $missions = array();
        $sql = "select dtm.mission_name, dtm.start_date, dtm.end_date, dt.mandatory_qty from date_tasks dt 
                join date_tasks_missions dtm using (date_tasks_mission_id) 
                where dt.cat = '" . mysql_real_escape_string( $cat ) . "' 
                and dtm.start_date >= " . $this->start . " 
                and dtm.end_date <= " . $this->end . " 
                group by dtm.mission_name 
                order by dtm.start_date";
        //echo $sql;
        $result = mysql_query($sql);
        while ($row = mysql_fetch_assoc($result)) {
            $mission = $row['mission_name'];
            $missions[$mission]['enrolled'] = 1;
            $missions[$mission]['mandatory'] = $row['mandatory_qty'];
            $heStart = cal_from_jd($row['start_date'], CAL_JEWISH);
            $heEnd = cal_from_jd($row['end_date'], CAL_JEWISH);
            $missions[$mission]['start'] = $heStart['day'] . ' ' . $heStart['monthname'];
            $missions[$mission]['end'] = $heEnd['day'] . ' ' . $heEnd['monthname'];
        }

        //to find exceptions we need to check all task ids for each mission within each category  
        //and see if all of them are in exceptions table
        foreach( $missions as $mission => $enrolled ) {
            $ids = $this->getTaskIDs($cat, array($mission));
            if ($this->isException($ids)) {
                $missions[$mission]['enrolled'] = 0;
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
	
    public function unenrollSchool( $school_id, $campaigns ) {
        foreach( $campaigns as $campaign ) {
            $sql = "delete from school_subjects where school_id = " . $school_id . " and subject_id = " . $campaign;
            mysql_query( $sql );
        }
    }
	
    public function enroll( $user_ids, $campaigns ) {
        foreach( $user_ids as $user_id ) { 
            foreach( $campaigns as $campaign ) { 
                $sql = "update user_tracks set enrolled = 1 where user_id = $user_id and subject_id = $campaign";
                mysql_query( $sql );
            }
        }
    }
	
    public function enrollSchool( $school_id, $campaigns ) {
        foreach( $campaigns as $campaign ) {
            $sql = "insert ignore into school_subjects set school_id = " . $school_id . ", subject_id = " . $campaign;
            mysql_query( $sql );
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
        $this->enrollInto( $tasks, true );
    }
	
    private function enrollInto( $tasks, $missions = false ) {
        $taskMissions = array(); 
        if ( $missions ) {
            //break up tasks array into two seperate arrays - tasks and missions 
            //in order that the same function can work both for tasks without missions
            //as well as for tasks with missions
            $newTasks = array();
            foreach( $tasks as $task => $missions ) {
                $newTasks[] = $task;
                $taskMissions[$task] = $missions;
            }
            $tasks = $newTasks;
        } 
        
        foreach( $tasks as $task ) {
            $sql = "delete {$this->type}_task_exceptions.* from {$this->type}_task_exceptions  
                    join date_tasks dt using (date_task_id) 
                    join date_tasks_missions dtm using (date_tasks_mission_id) 
                    join user_tracks ut using (subject_id, track_id, level) 
                    join users u on (u.user_id = ut.user_id)  
                    where dt.cat = '" . mysql_real_escape_string( $task ) . "' 
                    and dtm.school_type_id = u.school_type_id  
                    and dtm.start_date >= $this->start 
                    and dtm.end_date <= $this->end 
                    and u.{$this->type}_id = " . $this->id; 
            if ( $missions ) {
                $sql .= " and dtm.mission_name in (\"" . implode("\",\"", $taskMissions[$task]) . "\")";
            }               
            //echo $sql;
            mysql_query( $sql );
            
            //if this is a user, we need to check if class and / or school has this exception 
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
                        where dt.cat = '" . mysql_real_escape_string( $task ) . "' 
                        and dtm.school_type_id = u.school_type_id  
                        and dtm.start_date >= $this->start 
                        and dtm.end_date <= $this->end 
                        and u.school_id = " . $this->school_id; 
                if ( $missions ) {
                    $sql .= " and dtm.mission_name in (\"" . implode("\",\"", $taskMissions[$task]) . "\")";
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
                            where dt.cat = '" . mysql_real_escape_string( $task ) . "' 
                            and dtm.school_type_id = u.school_type_id  
                            and dtm.start_date >= $this->start 
                            and dtm.end_date <= $this->end 
                            and u.school_id = " . $this->school_id; 
                    if ( $missions ) {
                        $sql .= " and dtm.mission_name in (\"" . implode("\",\"", $taskMissions[$task]) . "\")";
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
                            where dt.cat = '" . mysql_real_escape_string( $task ) . "' 
                            and dtm.school_type_id = u.school_type_id  
                            and dtm.start_date >= $this->start 
                            and dtm.end_date <= $this->end 
                            and u.class_id = " . $class_id; 
                    if ( $missions ) {
                        $sql .= " and dtm.mission_name in (\"" . implode("\",\"", $taskMissions[$task]) . "\")";
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
                                where dt.cat = '" . mysql_real_escape_string( $task ) . "' 
                                and dtm.school_type_id = u.school_type_id  
                                and dtm.start_date >= $this->start 
                                and dtm.end_date <= $this->end 
                                and u.class_id = " . $class_id; 
                        if ( $missions ) {
                            $sql .= " and dtm.mission_name in (\"" . implode("\",\"", $taskMissions[$task]) . "\")";
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
                            where dt.cat = '" . mysql_real_escape_string( $task ) . "' 
                            and dtm.school_type_id = u.school_type_id  
                            and dtm.start_date >= $this->start 
                            and dtm.end_date <= $this->end 
                            and u.user_id = " . $user; 
                    if ( $missions ) {
                        $sql .= " and dtm.mission_name in (\"" . implode("\",\"", $taskMissions[$task]) . "\")";
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
                        where dt.cat = '" . mysql_real_escape_string( $task ) . "' 
                        and dtm.school_type_id = u.school_type_id  
                        and dtm.start_date >= $this->start 
                        and dtm.end_date <= $this->end 
                        and u.school_id = " . $this->school_id; 
                if ( $missions ) {
                    $sql .= " and dtm.mission_name in (\"" . implode("\",\"", $taskMissions[$task]) . "\")";
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
                            where dt.cat = '" . mysql_real_escape_string( $task ) . "' 
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
                            where dt.cat = '" . mysql_real_escape_string( $task ) . "' 
                            and dtm.school_type_id = u.school_type_id  
                            and dtm.start_date >= $this->start 
                            and dtm.end_date <= $this->end 
                            and u.class_id = " . $class; 
                    if ( $missions ) {
                        $sql .= " and dtm.mission_name in (\"" . implode("\",\"", $taskMissions[$task]) . "\")";
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
                                where dt.cat = '" . mysql_real_escape_string( $task ) . "' 
                                and dtm.school_type_id = u.school_type_id  
                                and dtm.start_date >= $this->start 
                                and dtm.end_date <= $this->end 
                                and u.user_id = " . $user; 
                        if ( $missions ) {
                            $sql .= " and dtm.mission_name in (\"" . implode("\",\"", $taskMissions[$task]) . "\")";
                        }
                        mysql_query( $sql );   
                    }       
                }
            }
            
            //add task to defaults table if needed
            //find out if task is off by default and add to defaults table
            $sql = "select subject_id from date_tasks_missions dtm 
                    join date_tasks dt using (date_tasks_mission_id) 
                    where dt.cat = \"" . mysql_real_escape_string($task) . "\"";
            //echo $sql;
            $result = mysql_query($sql);
            $row = mysql_fetch_assoc($result);
            $defaults = $this->getDefaultIDs($row['subject_id'], $task);
            foreach ($defaults as $id) {
                $this->d->addOn($id, 'task');
            }
        }        
    }

    public function setTaskExceptions( $tasks ) {
        $this->setExceptions( $tasks );
    }

    public function setMissionExceptions( $missions ) {
        //reconstruct missions array to have category name with list of missions per category name
        $tasks = $this->getTaskMissions( $missions );
        $this->setExceptions( $tasks, true );
    }

    private function setExceptions( $tasks, $missions = false ) {
        $taskMissions = array(); 
        if ( $missions ) {
            //break up tasks array into two seperate arrays - tasks and missions 
            //in order that the same function can work both for tasks without missions
            //as well as for tasks with missions
            $newTasks = array();
            foreach( $tasks as $task => $missions ) {
                $newTasks[] = $task;
                $taskMissions[$task] = $missions;
            }
            $tasks = $newTasks;
        }
        
        $exceptions = array();
        foreach( $tasks as $task ) {
            if ( isset( $taskMissions[$task] ) ) 
                $ids = $this->getTaskIDs( $task, $taskMissions[$task] );
            else 
                $ids = $this->getTaskIDs ( $task );
            $exceptions += $ids;
        }
        foreach( $exceptions as $exception ) {
            $sql = "insert ignore into {$this->type}_task_exceptions values (null, " . $this->id . ", $exception)";
            mysql_query( $sql );
            if ($this->d->isOn($exception, 'task'))
                $this->d->deleteOn($exception, 'task');
        }
    }

    public function reset($school_id) {
        $info = array();
        $sql = "select class_id, user_id from users where school_id = " . $school_id;
        $result = mysql_query($sql);
        while ($row = mysql_fetch_assoc($result)) {
            $info[$row['class_id']][] = $row['user_id'];
        }
        
        $classes = array();
        $users = array();
        foreach ($info as $class => $students) {
            $classes[] = $class;
            foreach ($students as $student) {
                $users[] = $student;
            }
        }
        
        $campaigns = $this->getCampaigns($school_id, FALSE);
        foreach ($campaigns as $subject_id => $subject) {
            foreach ($users as $user) {
                $sql = "update user_tracks set enrolled = 1 where user_id = $user and subject_id = $subject_id";
                //echo $sql;
                if (!mysql_query($sql)) {
                    return false;
                }
            }
        }
        
        $sql1 = "delete from school_task_exceptions where school_id = " . $school_id;
        $sql2 = "delete from class_task_exceptions where class_id in (" . implode(',', $classes) . ")";
        $sql3 = "delete from user_task_exceptions where user_id in (" . implode(',', $users) . ")";
        if (!(mysql_query($sql1) && mysql_query($sql2) && mysql_query($sql3))) {
            return false;
        }
        return true;
    }
}
?>