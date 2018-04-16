<?php
require 'db.php';

$ranks = array();
$sql = "select rank_ord, rank_name from ranks order by rank_ord";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $ranks[$row['rank_ord']] = $row['rank_name'];
}

$info = array();
$sql = "select u.first, u.last, u.first_he, u.last_he, u.user_id, u.user_code, u.dob, u.user_serial, c.class_grade, c.class_sub, rm.rank_ord 
        from users u
        join classes c on u.class_id = c.class_id
        join rank_marks rm using (user_id) 
        where u.school_id = 9
        order by class_grade, class_sub, last, first";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $info[] = $row;
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf8" />
        <style>
            tr, th, td {
                padding: 3px;
                font-size: 12px;
                font-family: sans-serif;
            }
        </style>
    </head>
    
    <body>
        <table>
            <tr>
                <th>User ID</th>
                <th>English First Name</th>
                <th>English Last Name</th>
                <th>Hebrew First Name</th>
                <th>Hebrew Last Name</th>
                <th>Rank</th>
                <th>Serial Number</th>
                <th>Barcode</th>
                <th>DOB</th>
                <th>Grade</th>
            </tr>
            <?php
            foreach ($info as $row) {
                echo "<tr><td>" . $row['user_id'] . "</td><td>" . $row['first'] . "</td><td>" . $row['last'] . "</td><td>" . $row['first_he'] . "</td><td>" . $row['last_he'] .
                    "</td><td>" . ($row['rank_ord'] ? $ranks[$row['rank_ord']] : '') . "</td><td>" . $row['user_serial'] . "</td><td>" . '3' . $row['user_code'] . "</td><td>" .
                    $row['dob'] . "</td><td>" . $row['class_grade'] . '-' . $row['class_sub'] . "</td></tr>";
            }
            ?>
        </table>
    </body>
</html>