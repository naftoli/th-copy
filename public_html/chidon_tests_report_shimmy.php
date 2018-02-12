<?php

/***************** DEBUGGING **********************/
if ($_GET['debug']) {
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
    $debug = true; // set debug to true
}

ini_set('display_errors', 1);
$admin_auth = array('school'); 
require('header.php');

if ($admin_user['auth'] != 'super') {
    echo "No Permission.";
    exit;
}

require_once 'class.adminSchools.php';

$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'], true, true );
$schools = $as->getSchools();

require_once 'class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

$users = array();
foreach ($schools as $id => $school) {
    $sql = "select tc.*, u.first, u.last, u.gender, c.*, a.admin_email, a.admin_phone_mobile, a.admin_phone_mobile2, a.admin_phone_home
            from th_chidon tc 
            join users u using (user_id)
            left join classes c on u.class_id = c.class_id
            join admin_auths aa on aa.id = u.user_id 
            join admins a using (admin_id) 
            where tc.year = " . $year . " 
            and aa.auth = 'user' 
            and u.school_id = $id 
            order by class_grade, class_sub, u.last, u.first";
    //echo "<input type='hidden' name='sql' value='" . $sql . "' />";
    $result = mysql_query($sql) or die($sql . "<br />" . mysql_error());
    while ($row = mysql_fetch_assoc($result)) {
        $grade = $row['class_grade'] . (empty($row['class_sub']) ? '' : '-' . $row['class_sub']);
        $name = $row['first'] . ' ' . $row['last'];
        $gender = $row['gender'];
        $t1a = $row['test1a'];
        $t1b = $row['test1b'];
        $t2a = $row['test2a'];
        $t2b = $row['test2b'];
        $t3a = $row['test3a'];
        $t3b = $row['test3b'];
        $users[$id][$grade][$row['th_chidon_id']][$name][$gender] = array(
            't1a' => $t1a,
            't1b' => $t1b,
            't2a' => $t2a,
            't2b' => $t2b,
            't3a' => $t3a,
            't3b' => $t3b
        );
    }
}
//echo "<pre>"; print_r($users); echo "</pre>";
?>
<!DOCTYPE html>
<HTML>

    <HEAD>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Enter Chidon Test Results</title>
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <style type='text/css'>
            table {
                font-size: 12px;
            }
            th, td {
                padding: 3px 10px;
            }
            caption {
                border-bottom: dashed 1px black;
            }
        </style>
    </HEAD>

    <BODY>
        <? include('admin_header.php'); ?>
        <h1>Enter Chidon Test Results</h1>
        <table class="tests">
            <tr>
                <th>School</th>
                <th>Grade</th>
                <th>Student</th>
                <th>Gender</th>
                <th width="35px">Test 1 Part 1</th>
                <th width="35px">Test 1 Part 2</th>
                <th width="35px">Test 2 Part 1</th>
                <th width="35px">Test 2 Part 2</th>
                <th width="35px">Test 3 Part 1</th>
                <th width="35px">Test 3 Part 2</th>
                <th width="35px">Avg Part 1</th>
                <th width="35px">Avg Part 2</th>
                <th width="35px">Avg All</th>
            </tr>
        <?php    
        foreach ($users as $school_id => $info) {
            foreach ($info as $grade => $other) {
                foreach ($other as $user_id => $more) {
                    foreach ($more as $name => $other) {
                        foreach ($other as $gender => $tests) {
                            // calculate avgs
                            $t1a = intval($tests['t1a']);
                            $t1b = intval($tests['t1b']);
                            $t2a = intval($tests['t2a']);
                            $t2b = intval($tests['t2b']);
                            $t3a = intval($tests['t3a']);
                            $t3b = intval($tests['t3b']);
                            
                            $div1 = 0;
                            $div2 = 0;
                            //$div3 = 0;
                            
                            if ($t1a) {
                                $div1++;
                                //$div3++;
                            }
                            if ($t2a) {
                                $div1++;
                                //$div3++;
                            }
                            if ($t3a) {
                                $div1++;
                                //$div3++;
                            }
                            if ($t1b) {
                                $div2++;
                                //$div3++;
                            }
                            if ($t2b) {
                                $div2++;
                                //$div3++;
                            }
                            if ($t3b) {
                                $div2++;
                                //$div3++;
                            }
                            
                            $avg1 = $div1 ? (intval($tests['t1a']) + intval($tests['t2a']) + intval($tests['t3a'])) / $div1 : 0;
                            $avg2 = $div2 ? (intval($tests['t1b']) + intval($tests['t2b']) + intval($tests['t3b'])) / $div2 : 0;
                            //$avg = $div3 ? (intval($tests['t1a']) + intval($tests['t2a']) + intval($tests['t3a']) +
                            //    intval($tests['t1b']) + intval($tests['t2b']) + intval($tests['t3b'])) / $div3 : 0;
                            $avg = $div2 ? ($avg1 + $avg2) / 2 : $avg1;
                            echo "<tr><td>" . $schools[$school_id] . "</td><td>" . $grade . "</td><td>" . $gender . "</td><td>" . 
                                $name . "</td><td>" . $t1a . "</td><td>" . $t1b . "</td><td>" .
                                $t2a . "</td><td>" . $t2b . "</td><td>" . $t3a . "</td><td>" . $t3b . "</td><td>" . $avg1 . "</td><td>" .
                                $avg2 . "</td><td>" . $avg . "</td></tr>";
                        }
                    }
                }
            }
        }
        ?>
        </table>
    </body>
</html>