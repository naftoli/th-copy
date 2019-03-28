<?php
require 'db.php';
$missing = array();
$sql = "select * from th_chidon tc 
        join users u using (user_id)
        join schools s on s.school_id = u.school_id 
        join classes c on c.class_id = u.class_id 
        left join admin_auths aa on aa.id = u.user_id 
        where tc.year = 5777
        and aa.auth is null 
        and shabbaton = 1";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $missing[] = $row;
}
//echo "<pre>"; print_r($missing); echo "</pre>";
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf8">
        <style>
            table {
                font-family: sans-serif;
            }
            tr, th, td {
                padding: 5px;
                font-size: 12px;
            }
        </style>
    </head>
    <body>
        <h2>Chidon Children Missing Parent Account</h2>
        <table>
            <tr>
                <th>School</th>
                <th>Grade</th>
                <th>Student</th>
                <th>Student ID</th>
                <th>Student Serial Number</th>
            </tr>
            <?php
            foreach ($missing as $child) {
                $grade = $child['class_grade'] . (empty($child['class_sub']) ? '' : '-' . $child['class_sub']);
                echo "<tr><td>" . $child['school_name'] . "</td><td>" . $grade . "</td><td>" . $child['first'] . ' ' . $child['last'] .
                    "</td><td>" . $child['user_id'] . "</td><td>" . $child['user_serial'] . "</td></tr>";
            }
            ?>
        </table>
    </body>
</html>