<?php
/***************** DEBUGGING **********************/
if ($_GET['debug']) {
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
    $debug = true; // set debug to true
} else {
    $debug = false;
}

ini_set('display_errors', 1);
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
if ( count($schools) > 1 ) $allSchools = $schools;

if (isset($_POST['submit'])) {
    //echo "<pre>"; print_r($_POST); echo "</pre>"; exit;
    if (isset($_POST['school'])) {
        $allSchools = $schools;
        $school_name = $schools[$_POST['school']];
        $schools = array($_POST['school'] => $school_name);
    } else {
        $qrys = array();
        $tests = array(
            't1' => 'mm_test1',
            't2' => 'mm_test2',
            't3' => 'mm_test3' 
        );
        foreach ($tests as $field => $column) {
            if (isset($_POST[$field])) {
                foreach ($_POST[$field] as $id => $mark) {
                    if (ceil($mark) > 0) {
                        $qrys[] = "UPDATE th_chidon SET " . $column . " = " . ceil($mark) . " WHERE th_chidon_id = " . $id;
                    } else {
                        $qrys[] = "UPDATE th_chidon SET " . $column . " = 0 WHERE th_chidon_id = " . $id;
                    }
                }
            }
        }
    
        //echo "<pre>"; print_r($qrys); echo "</pre>";
        foreach ($qrys as $qry) {
            mysql_query($qry);
        }
    }
}

// require_once('chidon_shutdown_vars.php'); // get the deadlines
// $exceptions = array(13,61,269);

$users = array();
foreach ($schools as $id => $school) {
    $sql = "SELECT tc.*, u.first, u.last, c.* "
        ."FROM th_chidon tc "
        ."JOIN users u USING (user_id) "
        ."JOIN classes c ON u.class_id = c.class_id "
        ."WHERE tc.year = " . $year 
        ." AND u.school_id = " . $id 
        ." ORDER BY class_grade, class_sub, u.last, u.first";
    if($debug) echo "<input type='hidden' name='sql' value='" . $sql . "' />";
    $result = mysql_query($sql) or die($sql . "<br />" . mysql_error());
    while ($row = mysql_fetch_assoc($result)) {
        $grade = $row['class_grade'] . (empty($row['class_sub']) ? '' : '-' . $row['class_sub']);
        $name = $row['first'] . ' ' . $row['last'];
        $t1 = $row['mm_test1'];
        $t2 = $row['mm_test2'];
        $t3 = $row['mm_test3'];
        $users[$id][$grade][$row['th_chidon_id']][$name] = array(
            't1'    =>  $t1,
            't2'    =>  $t2,
            't3'    =>  $t3
        );
    }
}
//echo "<pre>"; print_r($users); echo "</pre>";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Mitzvah Maven Test Results</title>
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <link href="/styles/admin/grey_select.css" rel="stylesheet" type="text/css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
        <style type='text/css'>
            table {font-size: 12px;}
            th, td {padding: 3px 5px;}
            caption {
                border-bottom: solid 1px black;
                padding-top: 10px;
                padding-bottom: 10px;
            }
            .tests input {width: 30px;}
            input[disabled] {
                color: #A9A9A9;
                padding: 2px;
                margin: 0 0 0 0;
                background-image: none;
            }
            a.button{display: inline-block;}
            p#refresh_options{text-align: center;margin-top:20px;padding-bottom: 10px;border-bottom: 1px solid #888;}
            input#submit_marks_button {margin: 0 auto;display: block;}
            a#next_page{float: right;}
            a#prev_page{float: left;}
        </style>
    </head>

    <body>
        <? include('admin_header.php'); ?>
        <?php include($_SERVER['DOCUMENT_ROOT']."/chidon_passwords.php"); ?>
        <h1>Mitzvah Maven Test Results</h1>
        
        <? //if ($admin_user['auth'] == 'super') { ?>
<!--        <p style="font-size: 16px; font-weight: bold; color: red;">
            <i>Please Set/Refresh your Shabbaton Eligibility and School Representatives once all 3 test grades have been entered</i>
        </p>-->
        <? //} // end if user is superuser ?>
        
        <? if ($admin_user['auth'] == 'super' || count($schools) > 1) { ?>
            <form method="post" action="chidon_mm_tests.php" style="text-align: center;">
                Choose School: 
                <select name="school">
                    <?php
                    foreach ($allSchools as $id => $school) {
                        echo "<option value='" . $id . "'";
                        if (isset($_POST['school']) && $_POST['school'] == $id) echo " selected";
                        echo ">" . $school . "</option>";
                    }
                    ?>
                </select> 
                <input type="submit" name="submit" value="Go To School" />
            </form>
        <? } // end if user is superuser ?>
        
        <?php if (count($schools) == 1) : ?>
        
            <? //if ($admin_user['auth'] == 'super') { ?>
                <!--<p id="refresh_options">
                    <a class="button" id='setContestants'>Set/Refresh Shabbaton Eligibility</a>
                    <a class="button" id='setReps'>Set/Refresh School Representatives</a>
                </p>-->
                <hr/>
            <? //} ?>

            <form method="post" action="chidon_mm_tests.php">
                <input type="submit" name="submit" value="Save Updated Marks" id="submit_marks_button" /><br />
                <!--
                <? if ($admin_user['auth'] != 'super') { ?>
                    <input type="checkbox" name="conf" id="conf" /> I have reviewed all the information and confirm that it is accurate.
                    <br /><br />
                <? }; ?>
                -->
                <?php
                $curGrade = 0;
                foreach ($users as $school_id => $info) { ?>
                    <table class="tests">
                        <caption><?=$schools[$school_id]?></caption>
                        <tr>
                            <th width="35px">Grade</th>
                            <th width="100px">Student</th>
                            <th width="35px">Test 1</th>
                            <th width="35px">Test 2</th>
                            <th width="35px">Test 3</th>
                            <th width="35px">Avg</th>
                        </tr>
                        <?php
                        foreach ($info as $grade => $other) {
                            foreach ($other as $user_id => $more) {
                                foreach ($more as $name => $tests) {
                                    // get marks
                                    $t1 = intval($tests['t1']);
                                    $t2 = intval($tests['t2']);
                                    $t3 = intval($tests['t3']);
                                    
                                    $divideBy = count(array_filter([$t1, $t2, $t3])); // count the number of Tests which are actually set
                                    
                                    // calculate the averages
                                    if ( $divideBy ) $avg = number_format( ($t1 + $t2 + $t3) / $divideBy, 2 );
                                    else $avg = 0;
                                    
                                    //if ($admin_user['auth'] != 'super') {
                                        //if ($avg1 < 55) continue;
                                    //}
                                    // render a blank like for each grade
                                    if ($curGrade != $grade) {?>
                                        <tr><td colspan='13'><h2></h2></td></tr>
                                        <?$curGrade = $grade;
                                    } ?>
                                    
                                    <tr>
                                        <td><?=$grade?></td>
                                        <td><?=$name?></td>
                                        <td>
                                            <input type='text' name='t1[<?=$user_id?>]' value='<?=$t1?>'
                                            />
                                        </td>
                                        <td>
                                            <input type='text' name='t2[<?=$user_id?>]' value='<?=$t2?>'
                                            />
                                        </td>
                                        <td>
                                            <input type='text' name='t3[<?=$user_id?>]' value='<?=$t3?>'
                                            />
                                        </td>
                                        <td><?=$avg?></td>
                                    </tr>
                                    <?php
                                } 
                            }
                        } // end foreach grade
                        ?>
                    </table>
                    <br />
                    <?php
                } // end foreach school
                ?>
            </form>

        <?php endif; ?>
    </body>
</html>