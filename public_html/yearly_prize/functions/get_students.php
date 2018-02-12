<?php

/*
 *  get_staff([$school_id[, $end_date[, $start_date[, $debug]]]])
 *
 *  NOTE: THIS SCRIPT DOES NOT CONNECT TO THE DATABASE, CONNECTION MUST BE ESTABLISHED BEFORE CALLING
 *
 *  Gets all users for the yearly prize.
 *      $school_id  => filter to a school
 *      $start_date => filter registration to a start date (MUST BE MySQL VALID)
 *      $end_date   => filter registration to a end date (MUST BE MySQL VALID)
 *      $debug      => just in case
 *      
 *  Data is normalized like so:
 *      -   user_id         => the id of the user
 *      -   school_id       => the id of the school
 *      -   user_serial     => serial number of student
 *      -   name            => Name as "last, first"
 *      -   user_registered => the date that the user registered
 *      -   class_grade     => the grade of the student
 *      -   class_sub       => the subject for the class
 */

function get_students($school_id=false, $start_date=false, $end_date=false, $debug=false){
    
    /***************** GET THE LIST OF STUDENTS **********************/
    $students = []; // list of all students
    
    $students_sql = "SELECT user_id, users.school_id, user_serial, CONCAT(last, ', ', first) AS 'name', user_registered, class_grade, class_sub "; // select these fiels
    $students_sql .= "FROM users JOIN classes USING (class_id) WHERE user_registered IS NOT NULL "; // with these joins and basic limits
    if($school_id) $students_sql .= "AND users.school_id = $school_id "; // limit the school if we where passed a school_id
    if($start_date) $students_sql .= "AND user_registered >= $start_date "; // limit the registration to a specific start date if passed in
    if($end_date) $students_sql .= "AND user_registered <= $end_date "; // limit the registration to a specific date if provided
    $students_sql .= "ORDER BY class_grade, class_sub, last, first;"; // sort as per the spec
    
    if($debug) echo "Students SQL: ".$students_sql ."\n"; // debugging
    
    $students_query = mysql_query($students_sql);
    
    while ($student_row = mysql_fetch_assoc($students_query)){
        $students[$student_row['school_id']][] = $student_row;
    }
    
    return $students;
}
