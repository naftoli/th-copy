<?php
require '../db.php';

foreach ($_POST as $k => $v) {
    $_POST[$k] = mysql_real_escape_string($v);
}

$ids = explode(':', $_POST['grade']);
$school_id = $ids[0];
$class_id = $ids[1];
$first = $_POST['fname'];
$last = $_POST['lname'];
$username = $_POST['username'];
$password = $_POST['password'];
$email = $_POST['email'];
$phone = $_POST['phone'];
$subject = $_POST['type'];

function createUserCode() {
    $count = 0;
    do {
        if ($count++ > 100000) 
            trigger_error('could not get ID', E_USER_ERROR);
            
        $user_code = mysql_result(mq('SELECT FLOOR(RAND() * 9223372036854775807)'),0);
    } while (mysql_result(mq("SELECT COUNT(*) FROM users WHERE user_code = $user_code"),0) != 0);
    return $user_code;
}

function findUserSerial() {
    $sql = "select user_serial from users order by user_serial desc limit 1";
    $result = mysql_query($sql);
    $row = mysql_fetch_assoc($result);
    $serial = $row['user_serial'];
    return ++$serial;
}

mysql_query("set autocommit=0");
mysql_query("begin");
$sql = "insert into admins
        set first = '" . $first . "',
        last = '" . $last . "',
        username = '" . $username . "' ,
        password = 'BRHS5777',
        is_parent = 1, 
        admin_email = '" . $email . "',
        admin_phone_mobile = '" . $phone . "'";
//echo $sql . "<br />";
if (mysql_query($sql)) {
    $admin_id = mysql_insert_id();
    $user_code = createUserCode();
    $user_serial = findUserSerial();
    $sql = "insert into users
            set last = '" . $last . "',
            first = '" . $first . "',
            class_id = " . $class_id . ",
            school_id = " . $school_id . ",
            email = '" . $email . "',
            user_phone = '" . $phone . "',
            gender = 'F', 
            user_start_date = " . unixtojd() . ", 
            user_registered = now(),
            heb_year = 5777, 
            school_type_id = 1,
            lang = 'en',
            user_code = " . $user_code . ",
            user_serial = " . $user_serial;
            //echo $sql . "<br />";
    if (mysql_query($sql)) {
        $user_id = mysql_insert_id();
        $sql = "insert into admin_auths
                set admin_id = " . $admin_id . ",
                id = " . $user_id . ",
                auth = 'user',
                role_id = 1";
        //echo $sql . "<br />";
        if (mysql_query($sql)) {
            $sql = "insert into user_tracks
                    set subject_id = " . $subject . ",
                    user_id = " . $user_id . ",
                    track_id = 1,
                    level = 1,
                    enrolled = 1";
            //echo $sql . "<br /><br />";
            if (mysql_query($sql)) {
                mysql_query("commit");
                mysql_query("set autocommit=1");
                echo 0;
                exit;
            }
        }
    }
}
mysql_query("rollback");
mysql_query("set autocommit=1");
echo mysql_error();