<?php
require_once '../db.php';
$school = mysql_real_escape_string( $_GET['sid'] );
$child = mysql_real_escape_string( $_GET['cid'] );

if ($school == 0) {
    $sql = "update users set school_id = null, class_id = null where user_id = " . $child;
    mysql_query($sql);
} else {
    // find same class in new school
    $sql = "select c.class_grade from classes c
            join users u on u.class_id = c.class_id
            where u.user_id = " . $child;
    $result = mysql_query( $sql );
    $row = mysql_fetch_assoc( $sql );
    $grade = $row['class_grade'];
    
    $sql = "select class_id from classes where class_era = 0 and class_grade = '" . $grade . "' and school_id = " . $school;
    $result = mysql_query( $sql );
    $row = mysql_fetch_assoc( $result );
    $class_id = $row['class_id'];
    
    $sql = "update users set school_id = " . $school . ", class_id = " . $class_id . " where user_id = " . $child;
    mysql_query($sql);
}
echo $sql;
?>