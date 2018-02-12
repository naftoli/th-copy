<?php
require 'db.php';

function generateCode() {
    if (mysql_result(mq("SELECT GET_LOCK('users', 30)"),0) != 1) 
        trigger_error('could not get lock', E_USER_ERROR);
        
    $count = 0;
    do {
        if ($count++ > 100000) 
            trigger_error('could not get ID', E_USER_ERROR);
        $user_code = mysql_result(mq('SELECT FLOOR(RAND() * 9223372036854775807)'),0);
    } while (mysql_result(mq("SELECT COUNT(*) FROM users WHERE user_code = $user_code"),0) != 0);
    return $user_code;
}

function generateSerial() {
    $sql = "select user_serial from users order by user_serial desc limit 1";
    $result = mysql_query($sql);
    $row = mysql_fetch_assoc($result);
    $serial = $row['user_serial'];
    $serial++;
    return $serial;
}

function create( $school, $grade ) {
    $barcode = generateCode();
    $serial = generateSerial();

    $sql = "insert into users 
            set user_code = $barcode, 
            first = 'Tanya', 
            last = 'Extras', 
            lang = 'en', 
            school_type_id = 2, 
            school_id = $school, 
            class_id = $grade, 
            user_serial = $serial, 
            lang_id = 1";
    mysql_query($sql);
}

$sql = "select * from schools where school_era is null";
$result = mysql_query($sql);