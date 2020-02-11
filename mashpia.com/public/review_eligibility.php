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

    if ( isset( $_POST['submit'] ) ) {
        // echo "<pre>"; print_r( $_POST ); echo "</pre>"; exit;
        $qrys = [];

        if ( $_POST['submit'] == 'Save Tie Breaker(s)' ) {
            foreach ( $_POST['tie'] as $id => $on ) {
                $qrys[] = "UPDATE th_chidon SET tie_breaker = 1 WHERE th_chidon_id = " . $id;
            }
        } else if ( $_POST['submit'] == 'Save Eligibility' ) {
            foreach ( $_POST['status'] as $id => $stat ) {
                switch ( intval( $stat ) ) {
                    case 1:
                        $qrys[] = "UPDATE th_chidon SET representative = 1 WHERE th_chidon_id = " . $id;
                        break;
                    case 2:
                        $qrys[] = "UPDATE th_chidon SET trophy_contestant = 1 WHERE th_chidon_id = " . $id;
                        break;
                    case 3:
                        $qrys[] = "UPDATE th_chidon SET contestant = 1 WHERE th_chidon_id = " . $id;
                        break;
                    default:
                        break;
                }
            }
        }

        // execute qrys
        foreach ( $qrys as $qry ) {
            mysql_query( $qry );
        }
    }
}

$users = array();
foreach ($schools as $id => $school) {
    $sql = "SELECT tc.*, u.first, u.last, u.gender, c.* "
        ."FROM th_chidon tc "
        ."JOIN users u USING (user_id) "
        ."JOIN classes c ON u.class_id = c.class_id "
        ."WHERE tc.year = " . $year . " "
        ."AND u.school_id = " . $id;
    if ($admin_user['auth'] != 'super') $sql .= " AND deleted = 0";
    //if ($admin_user['auth'] != 'super' && $shutdown && !in_array($id, $exceptions)) $sql .= " and tc.shabbaton = 1"; 
    $sql .= " ORDER BY u.gender, class_grade, class_sub";       
    if($debug) echo "<input type='hidden' name='sql' value='" . $sql . "' />";
    $result = mysql_query($sql) or die($sql . "<br />" . mysql_error());
    while ($row = mysql_fetch_assoc($result)) {
        $grade_sub = $row['class_grade'] . (empty($row['class_sub']) ? '' : '-' . $row['class_sub']);
        $grade = $row['class_grade'];
        $name = $row['first'] . ' ' . $row['last'];
        $t1a = floatval( $row['test1a'] );
        $t1b = floatval( $row['test1b'] );
        $t2a = floatval( $row['test2a'] );
        $t2b = floatval( $row['test2b'] );
        $t3a = floatval( $row['test3a'] );
        $t3b = floatval( $row['test3b'] );
        if ( $row['tie_breaker'] ) $t3b += 0.25;
        $avg1 = number_format((($t1a + $t2a + $t3a) / 3), 2);
        $avg2 = number_format((($t1b + $t2b + $t3b) / 3), 2);
        $avg = number_format((($t1a + $t1b + $t2a + $t2b + $t3a + $t3b) / 6), 2);
        $users[$id][$row['gender']][$grade][$avg][] = array(
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
    foreach ( $more as $gender => $other ) {
        foreach ( $other as $grade => $avgs ) {
            krsort( $users[$school][$gender][$grade] );
        }
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
                font-size: 13px;
                padding: 5px;
            }
        </style>
    </head>

    <body>
        <? include('admin_header.php'); ?>
        <?php include($_SERVER['DOCUMENT_ROOT']."/chidon_passwords.php"); ?>
        <h1>Review Eligibility</h1>

        <form action="review_eligibility.php" method="post">
            <div align="center">
                <input type="submit" name="submit" value="Save Tie Breaker(s)" style="padding: 10px;" /><br /><br />
                <input type="submit" name="submit" value="Save Eligibility" style="padding: 10px;" />
            </div>
            <?php
            $reps = [];
            $trophy = [];
            foreach ( $users as $school_id => $other ) {
                foreach ( $other as $gender => $more ) {
                    $type = '';
                    if ( $gender == 'M' ) $type = 'Boys';
                    else if ( $gender == 'F' ) $type = 'Girls';
                    echo "<h2>" . $schools[$school_id] . " (" . $type . ")</h2>";
                    ?>
                    <table>
                        <tr>
                            <th>ID</th>
                            <th>Grade</th>
                            <th>Name</th>
                            <th>Avg Part 1</th>
                            <th>Avg Part 2</th>
                            <th>Total Avg</th>
                            <th>Set Tie Breaker</th>
                            <th>Avg Needed</th>
                            <th>Eligibility Status</th>
                            <th>Class</th>
                        </tr>
                        <?php
                        $prevGrade = 4;
                        foreach ( $more as $grade => $other ) {
                            $idx = 0;
                            if ( intval( $grade ) != $prevGrade ) {
                                echo "<tr><td colspan='10'><h2></h2></td></tr>";
                                $prevGrade = $grade;
                            }
                            foreach ( $other as $avg => $more ) {
                                foreach ( $more as $info ) {
                                    $status = "n/a";
                                    $stat = 0;
                                    $needed = isset( $avgs[$school_id][$grade] ) ? $avgs[$school_id][$grade] : 70.00;
                                    if ( $info['avg'] >= $needed && in_array( $idx, [0, 1] ) ) {
                                        if ( $idx == 0 ) {
                                            $status = "Representative";
                                            $stat = 1;
                                            if ( isset( $reps[$gender][$grade] ) ) $reps[$gender][$grade]++;
                                            else $reps[$gender][$grade] = 1;
                                        }
                                        else if ( $idx == 1 ) {
                                            $status = "Trophy Contestant";
                                            $stat = 2;
                                            if ( isset( $trophy[$gender][$grade] ) ) $trophy[$gender][$grade]++;
                                            else $trophy[$gender][$grade] = 1;
                                        }
                                    } else if ( $info['avg1'] >= $needed ) {
                                        $status = "Contestant";
                                        $stat = 3;
                                    }
                                    echo "<input type='hidden' name='status[" . $info['id'] . "]' value='" . $stat . "' />";
                                    echo "<tr><td>" . $info['id'] . "</td><td>" . $grade . "</td><td>" . $info['name'] . "</td><td>" . $info['avg1'] . "</td><td>" . 
                                        $info['avg2'] . "</td><td>" . $info['avg'] . "</td><td><input type='checkbox' name='tie[" . $info['id'] . "]'";
                                    if ( $info['tie'] ) echo " checked='checked'";
                                    echo " /></td><td>" . $needed . "</td><td>" . $status . "</td><td>" . $info['grade_info'] . "</td><td>";
                                    $idx++;
                                }
                            }
                        }
                        ?>
                    </table>
                    <?php
                }
            }
        echo "</form>";
        // echo "<pre>"; print_r( $reps ); print_r( $trophy ); echo "</pre>";
        if ( $admin_user['auth'] == 'super' ) :
            ?>
            <br />
            <table>
                <caption>Number of Reps per Grade</caption>
                <tr>
                    <th>Gender</th>
                    <th>Grade</th>
                    <th>Number of Reps</th>
                </tr>
                <?php
                foreach ( $reps as $gender => $more ) {
                    if ( $gender == 'M' ) $type = 'boys';
                    else if ( $gender == 'F' ) $type = 'girls';
                    foreach ( $more as $grade => $num ) {
                        echo "<tr><td>" . $type . "</td><td>" . $grade . "</td><td>" . $num . "</td></tr>";
                    }
                }
                ?>
            </table>
            <br />
            <table>
                <caption>Number of Trophy Contestants per Grade</caption>
                <tr>
                    <th>Gender</th>
                    <th>Grade</th>
                    <th>Number of Trophy Contestants</th>
                </tr>
                <?php
                foreach ( $trophy as $gender => $more ) {
                    if ( $gender == 'M' ) $type = 'boys';
                    else if ( $gender == 'F' ) $type = 'girls';
                    foreach ( $more as $grade => $num ) {
                        echo "<tr><td>" . $type . "</td><td>" . $grade . "</td><td>" . $num . "</td></tr>";
                    }
                }
                ?>
            </table>
        <?php endif; ?>
    </body>
</html>