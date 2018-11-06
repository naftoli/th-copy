<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.medalReport.php';

class Slides extends MedalReport {
    protected $showPrevMedals;
    protected $showPrevMedalsLight;
    protected $nameLang;

    public function __construct( $showPrevMedals = false, $showLight = false, $previousStart = false ) {
        parent::__construct( $previousStart );
        $this->showPrevMedals = $showPrevMedals;
        $this->showPrevMedalsLight = $showLight;
    }

    public function setNameLang( $lang ) {
        $this->nameLang = $lang;
    }

    // override setMedalsDetails function
    public function setMedalDetails() {
        $this->setUsers();
		        
        $start = $this->reportDates['start'];
        $end = $this->reportDates['end']; 

        foreach ( $this->users as $school_id => $school ) {
            foreach ( $school as $name => $user ) {
            	$students = $this->users[$school_id][$name];
                $sql = "
                    SELECT s.subject_name, m.medal_name, u.user_id, u.last, u.first, u.first_he, u.last_he, u.lang_id,  
                    c.class_grade, c.class_sub, c.class_teacher, mm.*, s.subject_id
                    FROM medal_marks mm
                    JOIN medals m
                    USING ( medal_ord )
                    JOIN users u
                    USING ( user_id )
                    JOIN subjects s
                    USING ( subject_id )
                    JOIN schools sch
                    USING ( school_id ) 
                    JOIN classes c using (class_id)  
                    WHERE mm.date_awarded <= $end  
                    and sch.school_id = $school_id  
                    and u.user_id in (" . implode(',', $students) . ")
                    and s.subject_id != 106 
                    ORDER BY c.class_grade, c.class_sub, u.last, u.first, s.subject_id, mm.medal_ord
                ";
                //echo $sql . "<br />"; continue;
                $result = mysql_query($sql) or die(mysql_error());
                while ($row = mysql_fetch_assoc($result)) {
                    // figure out if we are only showing currently earned medals or all medals
                    if ( $this->showPrevMedals ||
                       ( !$this->showPrevMedals && $row['date_awarded'] >= $start )
                    ) {
                        // figure out which language to show name
                        if ( $this->nameLang == 'he' ) {
                            $user_name = $row['first_he'] . ' ' . $row['last_he'];
                        } else if ( $this->nameLang == 'en' ) {
                            $user_name = $row['first'] . ' ' . $row['last'];
                        }
                        $user_id = $row['user_id'];
                        $teacher = $row['class_teacher']; 
                        $grade = $row['class_grade'] . (empty( $row['class_sub']) ? '' : "-" . $row['class_sub']);
                        $subject = $row['subject_name'];
                        if ( $subject == 'שבת מברכים תהילים' ) $subject = "WWTC";

                        // in order to know if medal should be greyed out or not, we will append a 0 or 1 to the medal name
                        // and then extract that info later. 1 means to grey out; 0 means regular
                        $medal = $row['medal_name'];
                        if ( $this->showPrevMedals && $this->showPrevMedalsLight && $row['date_awarded'] < $start ) $medal .= "|1";
                        else $medal .= "|0";

                        $this->medalDetails[$name][$grade][$teacher][$user_id][$subject][] = $medal; 
                        $this->userInfo[$user_id] = $user_name;
                    }
                }
            }
        }
        //echo "<pre>"; print_r( $this->medalDetails ); echo "</pre>"; exit;
    }

    public function getMedalDetails() {
        return $this->medalDetails;
    }

    public function getUserInfo() {
        return $this->userInfo;
    }
}