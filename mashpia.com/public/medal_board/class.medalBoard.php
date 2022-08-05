<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.medalReport.php';

class MedalBoard extends MedalReport {
    protected $campaigns;
    protected $nameLang;
    protected $medalDetails;
    protected $userInfo;
    protected $missionsInfo;

    public function __construct() {
        parent::__construct( false );
        $this->campaigns = [ 1, 4, 12, 13, 16, 21, 27, 40, 41, 42, 45, 90, 92, 93, 94, 100 ];
    }

    public function setNameLang( $lang ) {
        $this->nameLang = $lang;
    }

    // override setMedalsDetails function
    public function setMedalDetails() {
        $this->setUsers();

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
                    WHERE mm.date_awarded <= " . unixtojd() . "   
                    AND sch.school_id = " . $school_id . " 
                    AND u.user_id in (" . implode(',', $students) . ")
                    AND s.subject_id in (" . implode(',', $this->campaigns) . ") 
                    ORDER BY c.class_grade, c.class_sub, u.last, u.first, s.subject_id, mm.medal_ord
                ";
//                echo $sql . "<br />"; continue;
                $result = mysql_query($sql) or die(mysql_error());
                while ($row = mysql_fetch_assoc($result)) {
                    
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

                    $medal = $row['medal_name'];
                    $this->medalDetails[$name][$grade][$teacher][$user_id][$subject][] = $medal; 
                    $this->userInfo[$user_id] = $user_name;
                }
            }
        }
        //echo "<pre>"; print_r( $this->medalDetails ); echo "</pre>"; exit;
    }

    public function setMissionsInfo() {

        foreach ( $this->users as $school_id => $school ) {
            foreach ( $school as $name => $user ) {
                $students = $this->users[$school_id][$name];
                
                // get missions done per child per subject
                $sql = "SELECT user_id, subject_id, SUM( mission_count ) AS total
                        FROM date_tasks_mission_marks 
                        WHERE user_id in (" . implode(',', $students) . ") 
                        AND subject_id in (" . implode(',', $this->campaigns) . ") 
                        GROUP BY user_id, subject_id";
                $result = mysql_query( $sql );
                while ( $row = mysql_fetch_assoc( $result ) ) {
                    $user_id = $row['user_id'];
                    $subject = $row['subject_id'];
                    $total = $row['total'];
                    $this->missionsInfo[$user_id][$subject]['done'] = $total;
                }
                
                // find needed amount of missions per medal per subject
                $needed = [];
                foreach ( $this->campaigns as $subject ) {
                    $sql2 = "SELECT medal_ord, medal_name, missions_required FROM medals_subjects 
                            JOIN medals USING (medal_ord)    
                            WHERE subject_id = " . $subject . " 
                            ORDER BY medal_ord"; 
                    //echo $sql2 . "<br />"; 
                    $result2 = mysql_query( $sql2 );
                    while ( $row2 = mysql_fetch_assoc( $result2 ) ) {
                        $ord = $row2['medal_ord'];
                        $medal = $row2['medal_name'];
                        $required = (int)$row2['missions_required'];
                        $needed[$subject][$ord][$medal] = $required;                        
                    }
                }

                foreach ( $students as $user_id ) {
                    foreach ( $this->campaigns as $subject ) {
                        $totalNeeded = 0;
                        $done = isset( $this->missionsInfo[$user_id][$subject]['done'] ) ?  $this->missionsInfo[$user_id][$subject]['done'] : 0;
                        foreach ( $needed[$subject] as $ord => $info ) {
                            foreach ( $info as $medal => $required ) {
                                $totalNeeded += $required;
                                if ( $totalNeeded > $done ) {
                                    $this->missionsInfo[$user_id][$subject]['left'] = ($totalNeeded - $done);
                                    $this->missionsInfo[$user_id][$subject]['color'] = $medal;
                                    $this->missionsInfo[$user_id][$subject]['prevColor'] = key( $needed[$subject[$ord-1]] );
                                    break 2;
                                }
                            }
                        }
                        // if we get here and done hasn't been less than needed, then child has completed everything
                        if ( $totalNeeded <=  $done ) {
                            $this->missionsInfo[$user_id][$subject]['left'] = "Completed!";
                        }
                        // figure out what the max missions is
                        switch ( $subject ) {
                            case 40:
                                $max = 585;
                                break;
                            case 1:
                            case 12:
                                $max = 95;
                                break;
                            default:
                                $max = 375;
                                break;
                        }
                        $this->missionsInfo[$user_id][$subject]['max'] = $max;
                    }
                }
            }
        }      
    }

    public function getMedalDetails() {
        return $this->medalDetails;
    }

    public function getUserInfo() {
        return $this->userInfo;
    }

    public function getMissionsInfo() {
        return $this->missionsInfo;
    }
}