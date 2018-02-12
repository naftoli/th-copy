<?php

/*
 *  get_staff([$school_id])
 *
 *  NOTE: THIS SCRIPT DOES NOT CONNECT TO THE DATABASE, CONNECTION MUST BE ESTABLISHED BEFORE CALLING
 *
 *  Get all the staff memebers (pass $school_id to limit by school) from the following tables
 *      -   schools
 *      -   admins
 *      -   staff_info
 *      
 *  Data is normalized like so:
 *      -   id          => the id in the table
 *      -   name        => the name of the staff member
 *      -   email       => email of staff member
 *      -   cell_phone  => cell phone number of staff member
 *      -   work_phone  => the optional work phone
 *      -   positon     => position of the staff member
 *      -   type        => the table it is from
 */

function get_staff($school_id = false){
    /***************** GET THE LIST OF STAFF FROM SCHOOLS TABLE **********************/
    $staff = []; // list of all the staff
    // get the principle
    $principle_sql = "SELECT school_id, school_id as 'id', school_name, principal as 'name', principal_email as 'email', principal_number_work as 'work_phone',".
                    "principal_number as 'cell_phone', principal_position as 'position', 'school' as 'type' FROM schools WHERE chayolei = 1 ";
    if($school_id) $principle_sql .= "AND school_id = $school_id "; // limit the school if we where passed a school_id
    $principle_sql .= "ORDER BY school_name, principal";
    
    //if($debug) print_r($principle_sql); // debugging
    $principle_query = mysql_query($principle_sql); // run the query
    
    while($principal = mysql_fetch_assoc($principle_query)){ // for every school
        if($principal['name']) $staff[$principal['school_id']][] = $principal; // if the name is not null add it to the array
    }
    
    /***************** GET THE LIST OF STAFF FROM ADMINS TABLE **********************/
    
    // Get all the admin auths
    $admin_sql = "SELECT aa.id as 'school_id', admins.admin_id as 'id', CONCAT(admins.first, ' ', admins.last) AS 'name', admin_email AS 'email', admin_phone_mobile AS 'cell_phone', admin_phone_work AS 'work_phone', position, 'admin' as 'type' ";
    $admin_sql .= "FROM admins JOIN admin_auths aa USING (admin_id) WHERE aa.auth = 'school' AND admins.first != '' ";
    if($school_id) $admin_sql .= "AND aa.id = $school_id "; // limit the school if we where passed a school_id
    $admin_sql .= "ORDER BY admins.last, admins.first";
    
    $admin_query = mysql_query($admin_sql);
    while ($admin_row = mysql_fetch_assoc($admin_query)){
        $staff[$admin_row['school_id']][] = $admin_row;
    }
    
    /***************** GET THE LIST OF TEACHERS FROM ADMINS TABLE **********************/
    
    // Get all the admin auths
    $teacher_sql = "SELECT classes.school_id, admins.admin_id AS 'id', admins.last AS 'name', admin_email AS 'email', admin_phone_mobile AS 'cell_phone', admin_phone_work AS 'work_phone', 'Primary Teacher' AS 'position', 'teacher' AS 'type', class_grade, class_sub, classes.class_id ";
    $teacher_sql .= "FROM admins JOIN admin_auths aa USING (admin_id) JOIN classes on classes.class_id = aa.id JOIN roles USING (role_id) WHERE aa.auth = 'class' AND admins.last != '' ";
    if($school_id) $teacher_sql .= "AND classes.school_id = $school_id "; // limit the school if we where passed a school_id
    $teacher_sql .= "ORDER BY class_grade, class_sub, admins.last;";
    //echo $teacher_sql;
    
    $teacher_query = mysql_query($teacher_sql);
    while ($teacher_row = mysql_fetch_assoc($teacher_query)){
        $staff[$teacher_row['school_id']][] = $teacher_row;
    }

    ///***************** GET THE LIST OF STAFF FROM STAFF_INFO TABLE **********************/

    $staff_info_sql = "SELECT staff_info.staff_id as 'id', staff_info.school_id, staff_info.class_id, staff_name as 'name', staff_email as 'email', "
                        ."staff_number as 'cell_phone', staff_work_number as 'work_phone', staff_position as 'position', class_grade, class_sub, 'staff' as 'type' ";
    $staff_info_sql .= "FROM staff_info LEFT JOIN classes USING (class_id) ";
    if($school_id) $staff_info_sql .= "WHERE staff_info.school_id = $school_id "; // limit the school if we where passed a school_id
    $staff_info_sql .= "ORDER BY class_grade, class_sub, staff_name;";
    //echo "Staff SQL: ".$staff_info_sql."\n";
    
    $staff_info_query = mysql_query($staff_info_sql);
    while ($staff_info_row = mysql_fetch_assoc($staff_info_query)){
        $staff[$staff_info_row['school_id']][] = $staff_info_row;
    }
    //
    ///***************** SORT THE STAFF ARRAY BY CLASS BETWEEN TEACHERS AND CUSTOM STAFF **********************/
    foreach($staff as $school_id => &$staff_members){ // for each school
        // create the compare function
        if(!function_exists("staff_compare")) {
            function staff_compare($a, $b) {
                $priority = ["Base Commander" => 1, "Dean" => 2, "Director" => 3,
                             "Principal" => 4, "Assistant Principal" => 5,
                             "Primary Teacher" => 6, "Teacher" => 7];
                // get the priorities
                $a_priority = isset($priority[$a['position']]) ? $priority[$a['position']] : count($priority) + 1;
                $b_priority = isset($priority[$b['position']]) ? $priority[$b['position']] : count($priority) + 1;
                // if a is not in the list but b is then a is less then b
                if(!$a_priority) $a_priority = count($priority) + 1;
                // if b is not in the list but a is then b is less then a
                if(!$b_priority) $b_priority = count($priority) + 1;
                
                if(($a_priority == 6 || $a_priority == 7) && ($b_priority == 6 || $b_priority == 7)){
                    $a_grade = $a['class_grade'].($a['class_sub'] ? " - ".$a['class_sub'] : "");
                    $b_grade = $b['class_grade'].($b['class_sub'] ? " - ".$b['class_sub'] : "");
                    
                    if($a_grade == $b_grade && $a_priority != $b_priority){ // if they are the same grade
                        return ($a_priority < $b_priority) ? -1 : 1;
                    } elseif($a_grade == $b_grade) { // if the grades are equal then sort by the name
                        if($a['name'] == $b['name']) return 0;
                        
                        return ($a['name'] < $b['name']) ? -1 : 1;
                    }
                    
                    $grade_order = ['Pre-school 1' => 1, 'Pre-school 2' => 2,
                                    'Pre-school 3' => 3, 'Pre1a' => 4, '1' => 5,
                                    '2' => 6, '3' => 7, '4' => 8, '5' => 9, '6' => 10,
                                    '7' => 11, '8' => 12, '9' => 13, '10' => 14,
                                    '11' => 15, '12' => 16];
                    
                    if($a['class_grade'] == $b['class_grade']){
                        if($a['class_sub'] == $b['class_sub']) return 0;
                        return ($a['class_sub'] < $b['class_sub']) ? -1 : 1;
                    }
                    
                    return ($grade_order[$a['class_grade']] < $grade_order[$b['class_grade']]) ? -1 : 1;
                }
                
                if($a_priority == $b_priority) {
                    if($a['position'] == $b['position']){ // if the positions are equal then sort by the name
                        if($a['name'] == $b['name']) return 0;
                        return ($a['name'] < $b['name']) ? -1 : 1;
                    }
                    return ($a['position'] < $b['position']) ? -1 : 1;
                }
                // at this point both of the priorites are set and are not equal
                return ($a_priority < $b_priority) ? -1 : 1;
                
            }
        }
        // run the sorting funciton
        usort($staff_members, "staff_compare");
    } // end the loop for each school
    
    return $staff;
}

// Utility function to move elements in the array
function moveElement(&$array, $a, $b) {
    $out = array_splice($array, $a, 1);
    array_splice($array, $b, 0, $out);
}