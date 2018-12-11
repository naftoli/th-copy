<?php
ini_set('display_errors',1);
require '../../../db.php';

$users = $_POST['users'];
$year = mysql_real_escape_string( $_POST['year'] );
$admin_id = mysql_real_escape_string( $_POST['admin_id'] );

require 'encrypt.php';
$admin_id = encrypt_decrypt('decrypt', $admin_id);

// get school IDs
$schools = array();
$sql = "select user_id, school_id from users where user_id in (" . implode(',', $users) . ")";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$schools[$row['user_id']] = $row['school_id'];
}

// update user_registration
require_once 'regFeeSchools.php';
foreach ($users as $user_id) {
    if (!in_array($schools[$user_id], $tuitionSchoolsNoPay)) {
        echo $user_id . " needs to pay for registration, the school is not paying for it.\n";
        continue;
    }
    $user_id = mysql_real_escape_string($user_id);
    $userAmount = 0;
    $sql = "insert into user_registration 
            set user_id = " . $user_id . ", 
            admin_id = " . $admin_id . ", 
            year = " . $year . ", 
            reg_date = now(), 
            paid = " . $userAmount . ",
            school_id = " . $schools[$user_id];
    //echo $sql;
    if (!@mysql_query( $sql )) {
        $to = "naftolir@gmail.com";
        $subject = "Error in mobile registration.";
        $msg = $sql . " - " . $mysql_error();
        @mail($to, $subject, $msg);
    } 
    //@mysql_query("update users set user_registered = now() where user_registered is null and user_id = " . $user_id);
    //@mysql_query("update users set user_start_date = " . unixtojd() . " where user_start_date is null and user_id = " . $user_id);
    
    //create private rank for soldier if no rank exists
    $sql = "select * from rank_marks where user_id = " . $user_id;
    $result = mysql_query($sql);
    if (mysql_num_rows($result) == 0) {
        $jd = unixtojd();
        $sql = "insert into rank_marks 
                set rank_ord = 1, 
                user_id = " . $user_id . ",  
                date_promoted = " . $jd;
        @mysql_query($sql);
    }
}					
echo 1;
?>