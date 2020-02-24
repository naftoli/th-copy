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
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf8" />
        <style>
            tr, th, td {
                font-size: 14px;
                padding: 5px;
            }
        </style>
    </head>
    <body>
        <table>
            <tr>
                <th>User ID</th>
                <th>Name</th>
                <th>School</th>
                <th>Grade</th>
                <th>Book</th>
            </tr>
            <?php
            foreach ( $issues as $issue ) {
                echo "<tr><td>" . $issue['user_id'] . "</td><td>" . $issue['first'] . ' ' . $issue['last'] . "</td><td>" . $issue['school_name'] . "</td><td>" . 
                    $issue['class_grade'] . "</td><td>" . $issue['book'] . "</td></tr>";
            }
            ?>
        </table>
    </body>
</html>