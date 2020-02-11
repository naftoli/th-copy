<?php
/***************** DEBUGGING **********************/
if (isset( $_GET['debug'] ) && $_GET['debug']) {
    //error_reporting(E_ALL);
    ini_set("display_errors", 1);
    $debug = true; // set debug to true
} else {
    $debug = false;
}

// ini_set('display_errors', 1);
$admin_auth = array('school'); 
require('header.php');

require 'class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

require_once 'class.adminSchools.php';       
$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'], true, true ); // add chidon schools
$schools = $as->getSchools();
if ($admin_user['auth'] == 'super') {
    $schools[82] = "Avrohom Academy";
}

$users = array();
foreach ($schools as $id => $school) {
    $sql = "SELECT tc.*, u.first, u.last, c.* "
        ."FROM th_chidon tc "
        ."JOIN users u USING (user_id) "
        ."JOIN classes c ON u.class_id = c.class_id "
        ."WHERE tc.year = " . $year . " "
        ."AND u.school_id = " . $id;
    if ($admin_user['auth'] != 'super') $sql .= " AND deleted = 0";
    //if ($admin_user['auth'] != 'super' && $shutdown && !in_array($id, $exceptions)) $sql .= " and tc.shabbaton = 1"; 
    $sql .= " ORDER BY class_grade, class_sub";       
    if($debug) echo "<input type='hidden' name='sql' value='" . $sql . "' />";
    $result = mysql_query($sql) or die($sql . "<br />" . mysql_error());
    while ($row = mysql_fetch_assoc($result)) {
        $grade_sub = $row['class_grade'] . (empty($row['class_sub']) ? '' : '-' . $row['class_sub']);
        $grade = $row['class_grade'];
        $name = $row['first'] . ' ' . $row['last'];
        $t1a = $row['test1a'];
        $t1b = $row['test1b'];
        $t2a = $row['test2a'];
        $t2b = $row['test2b'];
        $t3a = $row['test3a'];
        $t3b = $row['test3b'];
        $avg1 = number_format((($t1a + $t2a + $t3a) / 3), 2);
        $avg2 = number_format((($t1b + $t2b + $t3b) / 3), 2);
        if ( $row['tie_breaker'] ) $t3a += 1.0;
        $avg = number_format((($t1a + $t1b + $t2a + $t2b + $t3a + $t3b) / 6), 2);
        $users[$id][$grade][$avg][] = array(
            'grade_info'    => $grade_sub, 
            'id'    => $row['th_chidon_id'], 
            'name'  => $name,
            'avg1'  => $avg1, 
            'avg2'  => $avg2, 
            'avg'   => $avg,
            'tie'   => $row['tie_breaker'],
            'paid'  => $row['paid'] 
        );
    }
}
// sort by avg
foreach ( $users as $school => $more ) {
    foreach ( $more as $grade => $avgs ) {
        krsort( $users[$school][$grade] );
    }
}
// echo "<pre>"; print_r( $users ); echo "</pre>";
$avgs = [];
$sql = "SELECT * FROM th_chidon_avgs where year = " . $year;
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
    $avgs[$row['school_id']][$row['grade']] = $row['avg'];
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Review Eligibility</title>
        <link href="admin_styles.css" rel="stylesheet" type="text/css">  
        <style>
            tr, th, td {
                font-size: 14px;
                padding: 5px;
            }
        </style>
    </head>

    <body>
        <? include('admin_header.php'); ?>
        <?php include($_SERVER['DOCUMENT_ROOT']."/chidon_passwords.php"); ?>
        <h1>Review Eligibility</h1>
        <?php
        foreach ( $users as $school_id => $more ) {
            echo "<h2>" . $schools[$school_id] . "</h2>";
            ?>
            <table>
                <tr>
                    <th>Grade</th>
                    <th>Name</th>
                    <th>Avg Part 1</th>
                    <th>Avg Part 2</th>
                    <th>Total Avg</th>
                    <th>Avg Needed</th>
                    <th>Status</th>
                    <th>Class</th>
                </tr>
                <?php
                foreach ( $more as $grade => $other ) {
                    $idx = 0;
                    foreach ( $other as $avg => $more ) {
                        foreach ( $more as $info ) {
                            $status = "n/a";
                            $needed = isset( $avgs[$school_id][$grade] ) ? $avgs[$school_id][$grade] : 70;
                            if ( $info['avg'] >= $needed && in_array( $idx, [0, 1] ) ) {
                                if ( $idx == 0 ) $status = "Representative";
                                else if ( $idx == 1 ) $status = "Trophy Contestant";
                            } else if ( $info['avg1'] >= $needed ) {
                                $status = "Contestant";
                            }
                            echo "<tr><td>" . $grade . "</td><td>" . $info['name'] . "</td><td>" . $info['avg1'] . "</td><td>" . $info['avg2'] . "</td><td>" . $info['avg'] . 
                                "</td><td>" . $needed . "</td><td>" . $status . "</td><td>" . $info['grade_info'] . "</td><td>";
                            $idx++;
                        }
                    }
                }
                ?>
            </table>
            <?php
        }
        ?>
    </body>
</html>