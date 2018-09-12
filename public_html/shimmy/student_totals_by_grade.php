<?php
require '../db.php';

$grades = array('pre1a','1','2','3','4','5','6','7','8');

$info = array();
foreach ($grades as $grade) {
    $sql = "select count(*) as total from users where class_id in (
        select class_id from classes where class_grade = '" . $grade . "') 
        and user_registered > 0";
    $result = mysql_query( $sql );
    $row = mysql_fetch_assoc( $result );
    $info[$grade] = $row['total'];
}
?>
<!DOCTYPE html>
<html>
    <head>
        <style>
            tr, th, td {
                font-size: 14px;
                padding: 5px;
            }
        </style>
    </head>
    <body>
        <h1>Total Children Registered in Chayolei Tzivos Hashem By Grade</h1>
        <table>
            <tr>
                <th>Grade</th>
                <th>Total</th>
            </tr>
            <?php 
            $grandTotal = 0;
            foreach ($grades as $grade) {
                $grandTotal += $info[$grade];
                echo "<tr><td>" . $grade . "</td><td>" . $info[$grade] . "</td></tr>";
            }
            ?>
            <tr><th>Grand Total</th><td><?=$grandTotal?></th></tr>
        </table>
    </body>
</html>