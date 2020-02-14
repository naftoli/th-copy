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
            'paid'  => $row['paid'],
            'khk'   => $row['khk'],
            'rep'   => $row['school_rep'], 
            'trophy'=> $row['trophy_contestant'], 
            'contestant' => $row['contestant']  
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
            <!-- <div align="center">
                <input type="submit" name="submit" value="Save Tie Breaker(s)" style="padding: 10px;" /><br /><br />
                <input type="submit" name="submit" value="Save Eligibility" style="padding: 10px;" />
            </div> -->
            <table>
                <tr>
                    <th>ID</th>
                    <th>School</th>
                    <th>Grade</th>
                    <th>Name</th>
                    <th>Avg Part 1</th>
                    <th>Avg Part 2</th>
                    <th>Total Avg</th>
                    <!-- <th>Set Tie Breaker</th> -->
                    <th>Avg Needed</th>
                    <!-- <th>Eligibility Status</th> -->
                    <th>Actual Status</th>
                    <th>Class</th>
                </tr>
                <?php
                $reps = [];
                $trophy = [];
                $contestants = [];
                foreach ( $users as $school_id => $other ) {
                    foreach ( $other as $gender => $more ) {
                        $type = '';
                        if ( $gender == 'M' ) $type = 'Boys';
                        else if ( $gender == 'F' ) $type = 'Girls';
                        foreach ( $more as $grade => $other ) {
                            $idx = 0;
                            foreach ( $other as $avg => $more ) {
                                foreach ( $more as $info ) {
                                    $status = "n/a";
                                    $stat = 0;
                                    // $needed = isset( $avgs[$school_id][$grade] ) ? $avgs[$school_id][$grade] : 70.00;
                                    // if ( $info['avg1'] >= $needed && $info['avg2'] >= $needed && in_array( $idx, [0, 1] ) ) {
                                    //     if ( $idx == 0 ) {
                                    //         $status = "Representative";
                                    //         $stat = 1;
                                    //     }
                                    //     else if ( $idx == 1 ) {
                                    //         $status = "Trophy Contestant";
                                    //         $stat = 2;
                                    //     }
                                    // } else if ( $info['avg1'] >= $needed ) {
                                    //     $status = "Contestant";
                                    //     $stat = 3;
                                    //     if ( isset( $contestants[$school_id][$gender][$grade] ) ) $contestants[$school_id][$gender][$grade]++;
                                    //     else $contestants[$school_id][$gender][$grade] = 1;
                                    // }
                                    if ( $info['khk'] ) {
                                        $status = "Kol Hatorah Kula";
                                        $stat = 0;
                                    } else if ( $info['rep'] ) {
                                        $status = "Representative";
                                        $stat = 1;
                                    } else if ( $info ['trophy'] ) {
                                        $status = "Trophy Contestant";
                                        $stat = 2;
                                    } else if ( $info['contestant'] ) {
                                        $status = "Contestant";
                                        $stat = 3;
                                    }
                                    switch ($status) {
                                        case 'Representative':
                                            if ( isset( $reps[$gender][$grade] ) ) $reps[$gender][$grade]++;
                                            else $reps[$gender][$grade] = 1;
                                            break;
                                        case 'Trophy Contestant':
                                            if ( isset( $trophy[$gender][$grade] ) ) $trophy[$gender][$grade]++;
                                            else $trophy[$gender][$grade] = 1;
                                            break;
                                        case 'Contestant':
                                            if ( isset( $contestants[$school_id][$gender][$grade] ) ) $contestants[$school_id][$gender][$grade]++;
                                            else $contestants[$school_id][$gender][$grade] = 1;
                                            break;
                                        default:
                                            break;
                                    }
                                    echo "<input type='hidden' name='status[" . $info['id'] . "]' value='" . $stat . "' />";
                                    echo "<tr><td>" . $info['id'] . "</td><td>" . $schools[$school_id] . " (" . $type . ")</td><td>" . $grade . "</td><td>" . $info['name'] . 
                                        "</td><td>" . $info['avg1'] . "</td><td>" . $info['avg2'] . "</td><td>" . $info['avg'] . 
                                        "</td><td>" . $needed . "</td><td>" . $status . "</td><td>" . $info['grade_info'] . "</td><td>";
                                    $idx++;
                                }
                            }
                        }
                    }  
                }
        echo "</table></form>";
        // echo "<pre>"; print_r( $reps ); print_r( $trophy ); echo "</pre>";
        if ( $admin_user['auth'] == 'super' ) :
            ?>
            <h2></h2>
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
            <br />
            <table>
                <caption>Number of Contestants per School / Grade</caption>
                <tr>
                    <th>School</th>
                    <th>Grade</th>
                    <th>Number of Contestants</th>
                </tr>
                <?php
                // sort contestants by school / grade 
                foreach ( $contestants as $school_id => $other ) {
                    ksort( $contestants[$school_id] );
                }
                foreach ( $contestants as $school_id => $other ) {
                    foreach ( $other as $gender => $more ) {
                        if ( $gender == 'M' ) $type = 'boys';
                        else if ( $gender == 'F' ) $type = 'girls';
                        foreach ( $more as $grade => $num ) {
                            echo "<tr><td>" . $schools[$school_id] . " (" . $type . ")</td><td>" . $grade . "</td><td>" . $num . "</td></tr>";
                        }
                    }
                }
                ?>
            </table>
        <?php endif; ?>
    </body>
    <script>
        // check if we have over 40 reps in a grade
        const reps = <?=json_encode($reps)?>;
        const genders = ['M', 'F'];
        const grades = [4, 5, 6, 7, 8];
        console.log(reps);
        for (let gender of genders) {
            for (let grade of grades) {
                if (reps[gender][grade] > 40) {
                    let g;
                    if (gender == 'M') g = 'boys';
                    else if (gender == 'F') g = "girls";
                    alert("You have more than 40 representatives in grade " + grade + " (" + g + ")");
                }
            }
        }
    </script>
</html>