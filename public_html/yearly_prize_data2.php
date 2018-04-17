<?php
ini_set('display_errors',1);
require_once 'db.php';

// get all registered users
$sql = "select u.user_id, u.first, u.last, s.school_name, c.class_grade, c.class_sub  
        from users u
        join schools s using (school_id)
        join classes c on c.class_id = u.class_id
        where u.user_registered > 0 
        order by school_name, class_grade, class_sub, last, first";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $users[$row['user_id']] = $row;
}

// get number of weeks missions done
foreach ($users as $user_id => $info) {
    $sql = "select count(*) as total from user_yearly_gift where user_id = " . $user_id;
    $result = mysql_query($sql);
    if (mysql_num_rows($result)) {
        $row = mysql_fetch_assoc($result);
        $users[$user_id]['total'] = $row['total'];
    } else {
        $users[$user_id]['total'] = 0;
    }
}
//echo "<pre>"; print_r($users); echo "</pre>"; exit;
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf8" />
        <title>Yearly Prize Eligibility Status</title>
        <style>
            body, table {
                font-size: 12px;
            }
            tr, th, td {
                padding: 5px;
            }
        </style>
    </head>
    <body>
        <table>
            <tr>
                <th>School</th>
                <th>Grade</th>
                <th>Student</th>
                <th>Total</th>
                <th>Eligibility</th>
            </tr>
            <?php
            $grandtotals = array();
            // initialize array
            for ($i = 0; $i < 13; $i++) {
                $grandtotals[$i] = 0;
            }
            foreach ($users as $user_id => $info) {
                $name = $info['first'] . ' ' . $info['last'];
                $grade = $info['class_grade'] . (empty($info['class_sub']) ? '' : '-' . $info['class_sub']);
                echo "<tr><td>" . $info['school_name'] . "</td><td>" . $grade . "</td><td>" . $name . "</td><td>" . $info['total'] . "</td><td>";
                if ($info['total'] >= 12) {
                    echo "eligible / received";
                    $grandtotals[12]++;
                } else {
                    $num = 12 - $info['total'];
                    echo $num . " weeks left to eligibility";
                    $grandtotals[$num]++;
                }
                echo "</td></tr>";
            }
            ?>
        </table>
        <hr />
        <h3>Grand Totals</h3>
        <table>
            <tr>
                <th>Number of Weeks Done</th>
                <th>Total</th>
            </tr>
            <?php
            $totalUsers = 0;
            foreach ($grandtotals as $num => $total) {
                //if ($num) echo "<tr><td>" . $num . "</td><td>" . $total . "</td></tr>";
                echo "<tr><td>" . $num . "</td><td>" . $total . "</td></tr>";
                $totalUsers += $total;
            }
            ?>
            <tr><td>Total Users:</td><td><?=$totalUsers?></td></tr>
        </table>
    </body>
</html>