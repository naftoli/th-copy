<?php
class CampaignEnrollment {
    
    private $user_id;
    private $userInfo;
    private $type;
    private $year;
    
    private $campaigns = array(
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
    
    public function __construct($user_id) {
        $this->user_id = $user_id;
    }
    
    public function getEligibleCampaigns($school_type_id = false) {
        // if the school type id is not provided load it up from the user...
        if(!$school_type_id && $this->user_id){
            $school_type_id_query = mysql_query("SELECT school_type_id FROM users WHERE user_id = " . $this->user_id);
            $school_type_id = mysql_fetch_assoc($school_type_id_query)['school_type_id'];
        }
        
        switch ($school_type_id) {
            // ckids
            case 4: case 5:
                return $this->campaigns['day_school'];
            case 12: case 13: // 12 and 13 are just frum
                return $this->campaigns['frum'];
            case 2: case 3: default: // 2 and 3 and others are chabad
                return $this->campaigns['chabad'];
        }
    }
    
    public function setType( $school_type_id = false ) {
        if ( !$school_type_id ) {
            $sql = "SELECT * FROM users WHERE user_id = " . $this->user_id;
            $result = mysql_query($sql);
            $row = mysql_fetch_assoc($result);
            $this->userInfo = $row;
            $school_type_id = $row['school_type_id'];
        }
        
        switch ($school_type_id) {
            case 12:
            case 13:
                $this->type = 'frum';
                break;
            case 4:
            case 5:
                $this->type = 'day_school';
                break;
            case 2:
            case 3:
            default:
                $this->type = 'chabad';
                break;
        }
    }
    
    public function getCampaigns() {
        // skip hakhel for now
        $skip = [15];
        $campaigns = [];
        foreach ( $this->campaigns[$this->type] as $id ) {
            if ( !in_array( $id, $skip ) ) $campaigns[] = $id;
        }
        return $campaigns;
    }
    
    private function setYear() {
        // figure out age / year
         $d1 = new DateTime();
         $d2 = new DateTime($this->userInfo['dob']);
         $age = $d2->diff($d1);
         $level = $age->format('%y');
         if ($level < 6) $level = 6;
         if ($level > 14) $level = 14;
         $this->year = $level;

        // changed to deciding year by grade
//        $sql = "select class_grade from users u join classes c on u.class_id = c.class_id where u.user_id = " . $this->user_id;
//        $result = mysql_query( $sql );
//        if ( mysql_num_rows( $result ) > 0 ) {
//            $row = mysql_fetch_assoc( $result );
//            $grade = $row['class_grade'];
//        } else {
//            $grade = 'Pre1a';
//        }
//        if ( is_numeric( $grade ) ) $this->year = intval( $grade ) + 6;
//        else $this->year = 6;
    }
    
    private function resetTracks() {
        // switch ($this->type) {
        //     case 'chabad':
        //         $sql = "DELETE FROM user_tracks WHERE subject_id in (92,93,94) AND user_id = " . $this->user_id;
        //         break;
        //     case 'frum':
        //         $sql = "DELETE FROM user_tracks WHERE subject_id in (12,13,40) AND user_id = " . $this->user_id;
        //         break;
        // }
        $sql = "DELETE FROM user_tracks WHERE user_id = " . $this->user_id;
        //echo $sql . "<br />"; die();
        return mysql_query($sql);
    }
    
    private function enrollIntoCampaigns() {
        $qrys = array();     
        foreach ($this->campaigns[$this->type] as $subject) {
            if ($subject == 1) {
                switch ($this->type) {
                    case 'chabad':
                        $track = 5;
                        break;
                    case 'frum':
                        $track = 3;
                        break;
                    default:
                        $track = 5;
                        break;
                }
            } else {
                $track = 1;
            }                
            $qrys[] = "insert ignore into user_tracks
                set subject_id = " . $subject . ",
                level = " . $this->year . ",
                user_id = " . $this->user_id . ",
                track_id = " . $track . ", 
                enrolled = 1 
                on duplicate key update
                enrolled = 1,
                level = " . $this->year . ",
                track_id = " . $track;
        }
        
        $success = true;
        mysql_query('set autocommit=0');
        mysql_query('begin');
        foreach ($qrys as $qry) {
            if (!mysql_query($qry)) {
                $success = false;
                break;
            }
        }
        if ($success) {
            mysql_query('commit');
        } else {
            mysql_query('rollback');
        }
        
        mysql_query('set autocommit=1');
        return $success;
    }
    
    public function enroll() {
        $this->setType();
        $this->setYear();
        if (!$this->resetTracks()) {
            throw new EnrollmentException("Error resetting user campaigns.");
        }
        if (!$this->enrollIntoCampaigns()) {
            throw new EnrollmentException("Error enrolling into campaigns.");
        }
    }
}