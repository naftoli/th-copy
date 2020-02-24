<?php
require $_SERVER['DOCUMENT_ROOT'] . '/db.php';

$info = [];
$sql = "select * from th_chidon where year = 5780 and (khk = 1 or school_rep = 1 or trophy_contestant = 1 or contestant = 1)";
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
    $info[] = $row;
}

$issues = [];
foreach ( $info as $row ) {
    $sql2 = "select user_id, first, last, school_name, class_grade from classes c 
            join users u using (class_id) 
            join schools s on s.school_id = u.school_id
            where u.user_id = " . $row['user_id'];
    $result2 = mysql_query( $sql2 );
    $row2 = mysql_fetch_assoc( $result2 );
    $grade = $row2['class_grade'];
    if ( intval($grade) != intval($row['book']) + 3 ) {
        $row2['book'] = $row['book'];
        $issues[] = $row2;
    }
}

foreach ($issues as $issue) {
    echo "User ID: " . $issue['user_id'] . " Name: " . $issue['first'] . ' ' . $issue['last'] . " from " . $issue['school_name'] . " is in grade " . $issue['class_grade'] . " but was set to book " . $issue['book'] . "<br />";
}