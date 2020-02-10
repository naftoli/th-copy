<?php
//***************** CREATE CHAPERONES **********************/
function createChap( $info ) {
    global $year;
    $full_program   = 1; // hardcoded as of 2019
    $school_id      = mysql_real_escape_string($info['school_id']);
    $first_name     = mysql_real_escape_string($info['first_name']);
    $last_name      = mysql_real_escape_string($info['last_name']);
    $email          = mysql_real_escape_string($info['email']);
    $phone          = mysql_real_escape_string($info['phone']);
    $dob            = mysql_real_escape_string($info['dob']);
    $chidon_type    = mysql_real_escape_string($info['chidon_type']);
    $vehicle        = intval(mysql_real_escape_string($info['vehicle']));
    //$full_program   = intval(mysql_real_escape_string($chaperone['full_program']));
    // accomidation info...
    $acc_name       = mysql_real_escape_string($info['acc_name']);
    $acc_address    = mysql_real_escape_string($info['acc_address']);
    $acc_phone      = mysql_real_escape_string($info['acc_phone']);
    $chap_type      = mysql_real_escape_string($info['chap_type']);
    
    $chaperone_sql = "INSERT INTO th_chidon_chaps "
            ." SET school_id = " . $school_id . ", "
            ." first_name = '" . $first_name . "', "
            ." last_name = '" . $last_name . "', "
            ." year = '$year', "
            ." dob = '" . $dob . "', "
            ." acc_name = '" . $acc_name . "', "
            ." acc_address = \"" . $acc_address . "\", "
            ." acc_phone = '" . $acc_phone . "', "
            ." vehicle = " . $vehicle . ", "
            ." phone = '" . $phone . "', "
            ." email = '" . $email . "', "
            ." chap_type = " . $chap_type . ", "
            ." chidon_type = '" . $chidon_type . "', "
            ." full_program = " . $full_program;
    // get the sweater size if needed...
    if( $info['s_size'] != '' ) {
        $sweater_size = mysql_real_escape_string($info['s_size']);
        $chaperone_sql .= ", sweater = 1, sweater_size = '" . $sweater_size . "'";
    } else {
        $chaperone_sql .= ", sweater = 0, sweater_size = null";
    }

    if  (mysql_query($chaperone_sql) ) { // if we can create the chaperone...
        return mysql_insert_id();
    } 

    return false;
}