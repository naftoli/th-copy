<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

// report for next year eligibility including serial number, name, school
// only show kids that are eligible
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';

if ($admin_user['auth'] != 'super') {
    echo "No Permission.";
    exit;
}

$children = [];
$sql = "select user_id, user_serial, first, last, u.school_id, school_name   
        from users u 
        join schools s using (school_id) 
        join classes c on c.class_id = u.class_id 
        where u.user_registered > 0 
        and c.class_grade = '7' 
        order by school_name, last, first";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $children[$row['user_id']] = $row;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/chidonTests/class.chidonTests.php';
$eligible = KHK::getKHKEligibility(array_keys($children), 5784, 2)[0];
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf8" />
        <title>Next Year Eligibility</title>
        <link href="../../admin_styles.css" rel="stylesheet" type="text/css">
        <style>
            tr, th, td {
                padding: 10px;
                font-size: 12px;
                border-bottom: 1px solid grey;
            }
        </style>
    </head>
    <body>
        <?php include $_SERVER['DOCUMENT_ROOT'] . '/admin_header.php' ;?>
        <h1>Next Year Eligibility</h1>
        <table>
            <tr>
                <th>School</th>
                <th>Serial Number</th>
                <th>Student</th>
            </tr>
            <?php
            $totals = [];
            foreach ($children as $user_id => $child) {
                if (isset($eligible[$user_id]) && $eligible[$user_id] == 1) {
                    echo "<tr>";
                    echo "<td>" . $child['school_name'] . "</td>";
                    echo "<td>" . $child['user_serial'] . "</td>";
                    echo "<td>" . $child['first'] . " " . $child['last'] . "</td>";
                    echo "</tr>";
                    if (isset($totals[$child['school_name']])) $totals[$child['school_name']]++;
                    else $totals[$child['school_name']] = 1;
                }
            }
            ?>
        </table>
        <br />
        <br />
        <table>
            <caption>Totals</caption>
            <tr>
                <th>School</th>
                <th>Total</th>
            </tr>
            <?php
            foreach ($totals as $school => $total) {
                echo "<tr><th>$school</th><th>$total</th></tr>";
            }
            ?>
        </table>
    </body>