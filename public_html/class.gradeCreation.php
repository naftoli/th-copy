<?php
require_once 'class.globalSettings.php';

class GradeCreation
{
    private $errors;
    private $schools;
    private $year;
    
    public function __construct( $strSchools ) {
        // echo $strSchools . "<br />";
        $this->schools = explode(',', $strSchools);
        $year = GlobalSettings::getRegistrationYear();
        $this->year = --$year;
        $this->errors = array();
    }
    
    public function createGrades() {
        foreach ($this->schools as $school) {
            // echo $school . "<br />";
            if ($school > 0) {
                $info = array();
                $sql = "select * from classes where school_id = " . $school . " and class_era = " . $this->year;
                $result = mysql_query($sql);
                while ($row = mysql_fetch_assoc($result)) {
                    $info[] = $row;
                }
                mysql_query('set autocommit = 0');
                mysql_query('begin');
                foreach ($info as $row) {
                    // check if class exists already
                    $sql = "select * from classes
                            where school_id = " . $row['school_id'] . "
                            and class_era = 0
                            and class_grade = '" . $row['class_grade'] . "'
                            and class_sub = '" . $row['class_sub'] . "'";
                    $result = mysql_query($sql);
                    if (mysql_num_rows($result) == 0) {
                        $sql = "insert into classes
                                set school_id = " . $row['school_id'] . ",
                                class_grade = '" . $row['class_grade'] . "',
                                class_grade_fr = '" . $row['class_grade_fr'] . "',
                                class_sub = '" . $row['class_sub'] . "',
                                class_teacher = '" . $row['class_teacher'] . "',
                                email = '" . $row['email'] . "',
                                cell = '" . $row['cell'] . "',
                                default_level = " . $row['default_level'] . ",
                                gender_view = '" . $row['gender_view'] . "',
                                class_era = 0,
                                teacher_gender = '" . $row['teacher_gender'] . "',
                                teacher_hname = \"" . $row['teacher_hname'] . "\",
                                class_gender = '" . $row['class_gender'] . "',
                                whatsapp = " . $row['whatsapp'];
                        //echo $sql . "<br />";
                        if (!mysql_query($sql)) {
                            $this->errors[] = $sql . "<br />" . mysql_error();
                        } else {
                            // update admin_auths table with new class id
                            $old_id = $row['class_id'];
                            $new_id = mysql_insert_id();
                            $sql = "update admin_auths
                                    set id = " . $new_id . "
                                    where id = " . $old_id . "
                                    and auth = 'class'";
                            if (!mysql_query( $sql )) {
                                $this->errors[] = $sql . "<br />" . mysql_error();
                            }
                        }
                    }
                }
                if (empty($this->errors)) {
                    mysql_query('commit');
                } else {
                    mysql_query('rollback');
                }
                mysql_query('set autocommit = 1');
            }
        }
        return true;
    }
    
    public function getErrors() {
        echo "<pre>";
        print_r($this->errors);
        echo "</pre>";
    }
}