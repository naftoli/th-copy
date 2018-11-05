<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.medalReport.php';

class Slides extends MedalReport {
    public function __construct( $previousStart = false ) {
        parent::__construct( $previousStart );
    }

    public function setMedalDetails() {
        $this->setUsers();
		        
        $start = $this->reportDates['start'];
        $end = $this->reportDates['end']; 

        foreach ( $this->users as $school_id => $school ) {
            foreach ( $school as $name => $user ) {
            	$students = $this->users[$school_id][$name];
                $sql = "
                    SELECT s.subject_name, m.medal_name, u.user_id, u.last, u.first, 
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
                    LEFT JOIN classes c using (class_id)  
                    WHERE mm.date_awarded >= $start 
                    AND mm.date_awarded <= $end  
                    and sch.school_id = $school_id  
                    and u.user_id in (" . implode(',', $students) . ")
                    and s.subject_id != 106 
                    ORDER BY c.class_grade, c.class_sub, u.last, u.first, s.subject_id, mm.medal_ord
                ";
                //echo $sql . "<br />"; continue;
                $result = mysql_query($sql) or die(mysql_error());
                while ($row = mysql_fetch_assoc($result)) {
                    if ($row['last'] != "") {
                        $user_id = $row['user_id'];
                        $teacher = $row['class_teacher']; 
                        $grade = $row['class_grade'] . (empty( $row['class_sub']) ? '' : "-" . $row['class_sub']);
                        $user_name = $row['first'] . " " . $row['last']; 
                        $subject = $row['subject_name'];
                        if ( $subject == 'שבת מברכים תהילים' ) $subject = "WWTC";
                        $this->medalDetails[$name][$grade][$teacher][$user_id][$subject][] = $row['medal_name']; 
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