<?php
require 'db.php';
$school_id = 180;

$grades = [];
$sql = "select * from classes where school_id = $school_id";
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
    $grades[$row['class_id']] = $row['class_grade'];
}

$users = [];
$sql = "select user_id, class_id from users where school_id = $school_id";
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
    $users[$row['user_id']] = $row['class_id'];
}

$subjects = [ 1, 4, 12, 13, 15, 16, 21, 27, 40, 41, 42, 45, 90, 100 ];
$levels = [
    'Pre1a' =>  6, 
    '1'     =>  7, 
    '2'     =>  8, 
    '3'     =>  9, 
    '4'     =>  10, 
    '5'     =>  11, 
    '6'     =>  12, 
    '7'     =>  13, 
    '8'     =>  14
];
foreach ( $users as $user => $class ) {
    $sql = "update user_tracks set 
            level = " . $levels[$grades[$class]] . "  
            where user_id = " . $user;
    mysql_query( $sql ) or die( mysql_error() . "<br />" . $sql . "<br />" );
}
echo "done";