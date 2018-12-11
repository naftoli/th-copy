<?php
ini_set('display_errors',1);
require 'db.php';

function createUserCode() {
    $count = 0;
    do {
        if ($count++ > 100000) 
            trigger_error('could not get ID', E_USER_ERROR);
            
        $user_code = mysql_result(mq('SELECT FLOOR(RAND() * 9223372036854775807)'),0);
    } while (mysql_result(mq("SELECT COUNT(*) FROM users WHERE user_code = $user_code"),0) != 0);
    return $user_code;
}

// possible infinite loop if there's 99 girls with same usernames
function checkUsername( $str ) {
    $sql = "select * from admins where username = \"" . $str . "\"";
    $result = mysql_query( $sql );
    if (mysql_num_rows( $result )) {
        $num = rand(0,99);
        return checkUsername( $str . $num );
    }
    return $str;
}

$sql = "select user_serial from users order by user_serial desc limit 1";
$result = mysql_query($sql);
$row = mysql_fetch_assoc($result);
$serial = $row['user_serial'];
$serial++;

$admins = fopen("fc5778_3.csv", "r");
$contents = stream_get_contents($admins);
$arrRows = preg_split("/[\n\r]+/", $contents);

mysql_query('set autocommit=0');
mysql_query('begin');

$num = 0;
$success = true;
foreach ($arrRows as $strLine) {
    $admin_id = 0;
    $user_id = 0;
    
    $data = explode(",", $strLine);
    $i = 0;
    $first = ucwords(strtolower(trim($data[$i++])));
    $last = ucwords(strtolower(trim($data[$i++])));
    $school_id = intval($data[$i++]);
    $class_id = intval($data[$i++]);
    $email = trim($data[$i++]);
    $phone = trim($data[$i++]);
    
    if ($last == '' || $first == '') continue;
    /*
    $classInfo = explode('-', $class);
    $sql = "select class_id from classes where class_era = 0 and class_grade = '" . $classInfo[0] . "' and class_sub = '" . $classInfo[1] . "'";
    $result = mysql_query($sql);
    $row = mysql_fetch_assoc($result);
    $class_id = $row['class_id'];
    */
    // check username
    $username = checkUsername( strtolower($first) . $last );
    // fc password should be 'fc5778'
    // hoo password should be 'HOO5777'
    $sql = "insert into admins
            set first = \"" . $first . "\", 
            last = \"" . $last . "\", 
            username = \"" . $username . "\", 
            password = 'HOO5777',
            admin_email = '" . $email . "',
            admin_phone_mobile = '". $phone . "', 
            is_parent = 1";
    //echo $sql . "<br />";
    if (!mysql_query($sql)) {
        echo $sql . "<br />" . mysql_error();
        $success = false;
        break;
    }
    
    $admin_id = mysql_insert_id();
    $user_code = createUserCode();
    $sql = "insert into users
            set last = \"" . $last . "\",
            first = \"" . $first . "\",
            class_id = " . $class_id . ",
            school_id = " . $school_id . ",
            gender = 'F', 
            user_start_date = " . unixtojd() . ", 
            user_registered = now(),
            heb_year = 5778, 
            school_type_id = 1,
            lang = 'en',
            user_code = " . $user_code . ", 
            user_serial = " . $serial++;
    //echo $sql . "<br />";
    if (!mysql_query($sql)) {
        echo $sql . "<br />" . mysql_error();
        $success = false;
        break;
    }
    
    $user_id = mysql_insert_id();
    $sql = "insert into admin_auths
            set admin_id = " . $admin_id . ",
            id = " . $user_id . ",
            auth = 'user',
            role_id = 1";
    //echo $sql . "<br />";
    if (!mysql_query($sql)) {
        echo $sql . "<br />" . mysql_error();
        $success = false;
        break;
    }
    
    // fc subject id = 3
    // hoo subject id = 2
    $sql = "insert into user_tracks
            set subject_id = 2,
            user_id = " . $user_id . ",
            track_id = 1,
            level = 1,
            enrolled = 1";
    //echo $sql . "<br /><br />";
    if (!mysql_query($sql)) {
        echo $sql . "<br />" . mysql_error();
        $success = false;
        break;
    }
    
}
if ($success) {
    mysql_query('commit');
    echo 'done';
} else {
    mysql_query('rollback');
}
mysql_query('set autocommit=1');

?>