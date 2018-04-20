<?php
/*
Oholei Torah 
Lubavitch Yeshiva Crown heights 
Lubavitch Yeshiva ocan parkway 
Darchei  Menachem 
Cheder at the ohel 
Lamplighters 
Beis Chaya Mushka Crown Heights 
Beis Rivka Crown Heights 
Bnos Menachem 

School 
Grade 
Rank 
First name 
Last name 
Parents Cell 
Parents Email Address

Please add total number of each rank 

General 
1* general 
2* general 
3* general 
4* general
*/
ini_set('display_errors',1);
require 'db.php';

$info = array();
$schools = array(255,9,471,33,63,194,30,54,7);
$sql = "select u.first, u.last, c.class_grade, c.class_sub, s.school_name, rm.rank_ord, r.rank_name, a.admin_phone_mobile, a.admin_phone_mobile2, a.admin_email 
        from users u
        join schools s using (school_id)
        join classes c on c.class_id = u.class_id
        join rank_marks rm using (user_id)
        join ranks r using (rank_ord)
        join admin_auths aa on aa.id = u.user_id
        join admins a using (admin_id) 
        where u.user_registered > 0
        and rm.rank_ord >= 9
        and u.school_id in (" . implode(',', $schools) . ")
        order by school_name, class_grade, class_sub, last, first";
$result = mysql_query( $sql );
while ($row = mysql_fetch_assoc( $result )) {
    $info[] = $row;
}
//echo "<pre>"; print_r( $info ); echo "</pre>";
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf8" />
        <style>
            table, tr, th, td {
                font-size: 12px;
                padding: 5px;
            }
        </style>
    </head>
    <body>
        <table>
            <tr>
                <th>School</th>
                <th>Grade</th>
                <th>Rank</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Parent's Cell</th>
                <th>Parent's Email</th>
            </tr>
            <?php
            $totals = array();
            for ($i = 9; $i < 15; $i++) {
                $totals[$i] = 0;
            }
            foreach ($info as $row) {
                $school = $row['school_name'];
                $grade = $row['class_grade'] . (empty($row['class_sub']) ? '' : '-' . $row['class_sub']);
                $rank = $row['rank_name'];
                $first = $row['first'];
                $last = $row['last'];
                $cell = $row['admin_phone_mobile'] ? $row['admin_phone_mobile'] : '';
                $cell .= $row['admin_phone_mobile2'] ? ($cell == '' ? $row['admin_phone_mobile2'] : "<br />" . $row['admin_phone_mobile2']) : '';
                $email = $row['admin_email'];
                echo "<tr><td>" . $school . "</td><td>" . $grade . "</td><td>" . $rank . "</td><td>" . $first . "</td><td>" . $last . "</td><td>" . $cell . "</td><td>" . $email . "</td></tr>";
                $totals[$row['rank_ord']]++;
            }
            ?>
        </table>
        <hr />
        <?php
        $ranks = array();
        $sql = "select rank_ord, rank_name from ranks where rank_ord >= 9";
        $result = mysql_query( $sql );
        while ($row = mysql_fetch_assoc( $result )) {
            $ranks[$row['rank_ord']] = $row['rank_name'];
        }
        ?>
        <table>
            <tr>
                <th>Rank</th>
                <th>Total</th>
            </tr>
            <?php
            $totalNumber = 0;       
            foreach ($totals as $ord => $total) {
                echo "<tr><td>" . $ranks[$ord] . "</td><td>" . $total . "</td></tr>";
                $totalNumber += $total;
            }
            echo "<tr><th>Grand Total</th><th>" . $totalNumber . "</th></tr>";
            ?>
        </table>
    </body>
</html>