<?php
ini_set('display_errors', 1);

$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';

if ($admin_user['auth'] != 'super') {
    echo "No permission to be here.";
    exit;
}

$schoolsInfo = [];
$sql = "select * from schools";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $schoolsInfo[$row['school_id']] = $row;
}

require $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'], true, true ); // add chidon schools
$schools = $as->getSchools();
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf8" />
        <title>Chidon List Report</title>
        <style>
            body  {
                font-family: Arial, Helvetica, sans-serif;
                font-size: 16px;
            }
            tr, th, td {
                font-family: Arial, Helvetica, sans-serif;
                font-size: 12px;
                padding: 5px;
            }
        </style>
    </head>
    <body>
    <?php
    foreach ($schools as $id => $name) {
        $children = [];
        $sql = "select first, last, class_grade, class_sub from users u join classes c using (class_id) where class_grade in ('4', '5', '6', '7', '8') and u.school_id = $id";
        $result = mysql_query($sql);
        while ($row = mysql_fetch_assoc($result)) {
            $children[$row['class_grade']][] = $row;
        }

        $schoolInfo = $schoolsInfo[$id];
        if ($schoolInfo['test_school'] == 1) continue;
        echo "School: " . $name . "<br />";
        echo "Shipping: " . $schoolInfo['shipping_method'] . "<br />";
        echo "Address: " . $schoolInfo['school_address1'] . " " . $schoolInfo['school_address2'] . "<br />";
        echo $schoolInfo['school_city'] . ", " . $schoolInfo['school_state'] . " " . $schoolInfo['school_postal'] . "<br />";
        echo $schoolInfo['school_country'] . "<br />";
        $totals = 0;
        for ($i = 4; $i <= 8; $i++) {
            $total = (isset($children[$i]) ? count($children[$i]) : 0);
            echo "Total for Grade $i: " . $total . "<br />";
            $totals += $total;
        }
        echo "Grand Total: " . $totals . "<br />";
        ?>
<!--        <table>-->
<!--            <tr>-->
<!--                <th>Grade</th>-->
<!--                <th>Class</th>-->
<!--                <th>Student</th>-->
<!--            </tr>-->
<!--            --><?php
//            for ($i = 4; $i <= 8; $i++) {
//                foreach ($children[$i] as $child) {
//                    $class = $child['class_grade'] . (empty($child['class_sub']) ? '' : '-' . $child['class_sub']);
//                    echo "<tr><td>" . $i . "</td><td>" . $class . "</td><td>" . $child['first'] . ' ' . $child['last'] . "</td></tr>";
//                }
//            }
//            ?>
<!--        </table>-->
        <hr />
        <div style="page-break-after: always"></div>
    <?php
    }
    ?>
    </body>
</html>
