<?php
require_once 'class.report.php';
require_once 'class.globalSettings.php';

class MedalReport extends Report {
    private $medalSummary;
    private $medalsTotal;
    private $medalTotals;
    private $medalDetails;
    private $medalsInfo;
    private $userInfo;
    private $medalOrds;
    private $subjects;
    private $totalSchools = 0;
    private $totalGrades = 0;
    private $totalStudents = 0;
    private $year;
    private $medals_for_shipping;

    public function __construct($previousStart = false) {
        parent::__construct($previousStart);
        $this->medalsInfo = array();
        $this->userInfo = array();
        $this->medalOrds = array();
        $this->subjects = array();
        $this->year = GlobalSettings::getCurrentYear();
        $this->medals_for_shipping = [];
    }

    private function createSql($detailed = false, $forShipping = false, $gender = '')
    {
        $start = $this->reportDates['start'];
        $end = $this->reportDates['end'];
        $filter = " 
            AND (
                (mm.date_awarded >= $start AND mm.date_awarded <= $end) 
                OR 
                (mm.date_awarded > " . $this->dates[0] . " AND mm.date_awarded < $start AND mm.date_shipped IS NULL)
            ) 
        ";
        if ($detailed) {
            $sql = "
                SELECT sch.school_name, s.subject_name, m.medal_name, u.user_id, u.first, u.last, mm.*, c.*  
                FROM medal_marks mm
                JOIN medals m USING ( medal_ord )
                JOIN users u USING ( user_id )
                JOIN subjects s USING ( subject_id )
                JOIN schools sch USING ( school_id ) 
                JOIN classes c ON c.class_id = u.class_id 
                JOIN user_registration ur ON ur.user_id = u.user_id 
                WHERE u.medals_ranks = 1 
                AND u.user_registered > 0 
                AND s.subject_id != 106 
                AND ur.year = " . $this->year . " 
                $filter ";
            if ( $this->school_id > 0 )
                $sql .= " AND u.school_id = $this->school_id ";
            if (! $forShipping ) {
                $exceptions = $this->schoolExceptions;
                if ( in_array($this->school_id, [61, 269]) ) {
                    $exceptions = array_diff($this->schoolExceptions, [61, 269]);
                }
                $sql .= " AND u.school_id not in (" . implode(',', $exceptions) . ") ";
            }
            if ( $forShipping ) {
                $sql .= "
                    AND mm.date_shipped IS NULL
                ";
            }
            if ( $gender == 'm' || $gender == 'f' ) {
                $sql .= "
                    AND u.gender = '" . strtoupper($gender) . "' ";
            }
            $sql .= "
                ORDER BY sch.school_name, s.subject_id, mm.medal_ord, u.last, u.first 
            ";
            // echo $sql; exit;
        } else {
            $exceptions = $this->schoolExceptions;
            if ( in_array($this->school_id, [61, 269]) ) {
                $exceptions = array_diff($this->schoolExceptions, [61, 269]);
            }
            $sql = "
                SELECT sch.school_name, s.subject_name, m.medal_name, count( u.user_id ) as total  
                FROM medal_marks mm
                JOIN medals m USING ( medal_ord )
                JOIN users u USING ( user_id )
                JOIN subjects s USING ( subject_id )
                JOIN schools sch USING ( school_id ) 
                JOIN user_registration ur ON ur.user_id = u.user_id 
                WHERE u.medals_ranks = 1 
                AND u.user_registered > 0 
                AND s.subject_id != 106 
                AND ur.year = " . $this->year . " 
                $filter ";
            if ( $this->school_id > 0 )
                $sql .= " AND u.school_id = $this->school_id ";
            $sql .= "
                AND u.school_id not in (" . implode(',', $exceptions) . ")
            ";
            $sql .= "
                GROUP BY sch.school_name, s.subject_id, mm.medal_ord   
                ORDER BY sch.school_name, s.subject_id, mm.medal_ord
            ";
        }
        // echo $sql; exit;
        return $sql;
    }

    public function setMedalSummary() {
        $sql = $this->createSql();
//        echo $sql . "<br />"; return;
        $this->medalsTotal = array();
        $this->medalTotals = array();
        $result = mysql_query($sql);
        while ($row = mysql_fetch_assoc($result)) {
            $this->medalSummary[$row['school_name']][$row['subject_name']][$row['medal_name']] = $row['total'];        
            if ( isset( $this->medalsTotal[$row['school_name']] ) ) 
                $this->medalsTotal[$row['school_name']] += $row['total'];
            else
                $this->medalsTotal[$row['school_name']] = $row['total'];
            if ( isset( $this->medalTotals[$row['school_name']][$row['subject_name']] ) ) 
                $this->medalTotals[$row['school_name']][$row['subject_name']] += $row['total'];
            else 
                $this->medalTotals[$row['school_name']][$row['subject_name']] = $row['total'];
        }
    }
    
    public function getMedalSummary() {
        return $this->medalSummary;
    }
    
    public function getMedalsTotal() {
        return $this->medalsTotal;
    }
    
    public function getMedalTotals() {
        return $this->medalTotals;
    }
    
    public function setMedalDetails($forShipping = false, $gender = '') {
        $sql = $this->createSql(true, $forShipping, $gender);
        // echo $sql . "<br />"; exit;
        $prevGrade = "";
        $result = mysql_query($sql);
        while ($row = mysql_fetch_assoc($result)) {
            if ($row['last'] != "") {
                $this->totalStudents++;
                $user_id = $row['user_id'];
                $teacher = $row['class_teacher'];
                $grade = $row['class_grade'] . (empty( $row['class_sub']) ? '' : "-" . $row['class_sub']);
                if ($prevGrade != $grade) {
                    $this->totalGrades++;
                    $prevGrade = $grade;
                }
                $user_name = $row['first'] . " " . $row['last'];
                $subject = $row['subject_name'];
                if ( $subject == 'שבת מברכים תהילים' ) $subject = "WWTC";
                $this->medalDetails[$row['school_name']][$teacher][$grade][$user_id][$subject][] = $row['medal_name'];

                $this->medalsInfo[$user_id]['earned'] = $row['date_awarded'];
                $this->medalsInfo[$user_id]['shipped'] = $row['date_shipped'];
                $this->medalsInfo[$user_id]['received'] = $row['date_received'];

                $this->medalsInfo[$user_id]['shipped_specific'][$subject][$row['medal_name']] = ['medal_ord' => $row['medal_ord'],
                    'shipped' => $row['date_shipped'],
                    'subject_id' => $row['subject_id'],
                    'date_awarded' => $row['date_awarded']];

                $this->userInfo[$user_id] = $user_name;
                $this->medals_for_shipping[$row['user_id']][$row['subject_id']][] = $row;
            }
        }
    }
    
    public function getMedalDetails() {
        return $this->medalDetails;
    }
    
    public function getMedalsInfo() {
        return $this->medalsInfo;
    }
    
    public function getUserInfo() {
        return $this->userInfo;
    }
    
    public function getMedalOrds() {
        if (empty($this->medalOrds)) {
            $sql = "select * from medals";
            $result = mysql_query($sql);
            while ($row = mysql_fetch_assoc($result)) {
                $this->medalOrds[$row['medal_name']] = $row['medal_ord'];
            }
        }
        return $this->medalOrds;
    }
    
    public function getSubjects() {
        if (empty($this->subjectOrds)) {
            $sql = "select * from subjects";
            $result = mysql_query($sql);
            while ($row = mysql_fetch_assoc($result)) {
                $subject = $row['subject_name'];
                if ($row['subject_name'] == 'שבת מברכים תהילים') 
                    $subject = "WWTC";
                $this->subjects[$subject] = $row['subject_id'];
            }
        }
        return $this->subjects;
    }

    public function getTotalSchools() {
        return $this->totalSchools;
    }

    public function getTotalGrades() {
        return $this->totalGrades;
    }

    public function getTotalStudents() {
        return $this->totalStudents;
    }

    public function getMedalsForShipping() {
        return $this->medals_for_shipping;
    }
}
?>