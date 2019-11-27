<?php
require $_SERVER['DOCUMENT_ROOT'] . '/db.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

$info = [];
$school_id = 588; // boro park school
//$school_id = 584; // yavne school montreal

// add all kids in school to admin account
$admin_id = 193349;
$users = [];
$sql = "select user_id from users where school_id = " . $school_id;
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
    $users[] = $row['user_id'];
}

$added = 0;
foreach ( $users as $user_id ) {
    $sql = "insert ignore into admin_auths set admin_id = " . $admin_id . ", auth = 'user', role_id = 1, id = " . $user_id;
    if ( mysql_query( $sql ) ) $added++;
}
echo "Added: " . $added;

$sql = "
    SELECT 
        user_id, admin_id, class_grade
    FROM
        users u
            JOIN
        admin_auths aa ON aa.id = u.user_id
            JOIN
        classes c ON c.class_id = u.class_id
    WHERE
        u.school_id = " . $school_id;
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
    $info[] = $row;
}

$updated = 0;
$success = true;
mysql_query('set autocommit=0');
mysql_query('begin');

foreach ( $info as $row ) {
    $grade = intval( $row['class_grade'] );
    if ( $grade < 4 ) continue; // only register kids in grades 4 and up    
    $user_id = $row['user_id'];
    $parent = $row['admin_id'];

    if ( $user_id ) {
        $sql = "select * from registration_charges 
                where user_id = " . $user_id . " 
                and type = 'chidon' 
                and year = " . $year;
        $result = mysql_query( $sql );
        if ( mysql_num_rows( $result ) == 0 ) {
            $sql = "insert into registration_charges 
                    set user_id = " . $user_id . ", 
                    school_id = " . $school_id . ", 
                    type = 'chidon', 
                    amount = 0.00, 
                    date = now(), 
                    year = " . $year;
            //echo $sql . "<br />";
            if ( !mysql_query( $sql ) ) {
                $success = false;
                break;
            }
            $sql = "insert ignore into th_chidon 
                    set year = " . $year . ", 
                    school_id = " . $school_id . ", 
                    user_id = " . $user_id . ", 
                    size = 'children m', 
                    reg_date = now(), 
                    book = " . ($grade - 4) . ", 
                    parent_id = " . $parent;
            //echo $sql . "<br /><br />";
            if ( !mysql_query( $sql ) ) {
                $success = false;
                break;
            }
            $updated++;
        }
    }
}

if ( $success ) {
    echo "Updated: " . $updated;
    mysql_query('commit');
} else {
    echo mysql_error();
    mysql_query('rollback');
}
mysql_query('set autocommit=1');