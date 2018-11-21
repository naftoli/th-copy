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
            't1a' => 'test1a',
            't1b' => 'test1b',
            't2a' => 'test2a',
            't2b' => 'test2b',
            't3a' => 'test3a',
            't3b' => 'test3b'
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

// check if the school has a chaperone in the chidon
if(count($schools) == 1){
    $chap_check = mysql_query("SELECT * FROM th_chidon_schools WHERE year = " . $year . " AND school_id = ". array_keys($schools)[0]);
    $has_chaps  = mysql_num_rows($chap_check) != 0;
}

require_once('chidon_shutdown_vars.php'); // get the deadlines
// $exceptions = array(13,61,269);

$users = array();
foreach ($schools as $id => $school) {
    $sql = "SELECT tc.*, u.first, u.last, c.* "
        ."FROM th_chidon tc "
        ."JOIN users u USING (user_id) "
        ."JOIN classes c ON u.class_id = c.class_id "
        ."WHERE tc.year = " . $year . " "
        ."AND u.school_id = " . $id;
    if($debug) echo $sql;
    if ($admin_user['auth'] != 'super') $sql .= " AND deleted = 0";
    //if ($admin_user['auth'] != 'super' && $shutdown && !in_array($id, $exceptions)) $sql .= " and tc.shabbaton = 1";        
    $sql .= " ORDER BY class_grade, class_sub, u.last, u.first";
    
    if($debug) echo "<input type='hidden' name='sql' value='" . $sql . "' />";
    $result = mysql_query($sql) or die($sql . "<br />" . mysql_error());
    while ($row = mysql_fetch_assoc($result)) {
        $grade = $row['class_grade'] . (empty($row['class_sub']) ? '' : '-' . $row['class_sub']);
        $name = $row['first'] . ' ' . $row['last'];
        $t1a = $row['test1a'];
        $t1b = $row['test1b'];
        $t2a = $row['test2a'];
        $t2b = $row['test2b'];
        $t3a = $row['test3a'];
        $t3b = $row['test3b'];
        $users[$id][$grade][$row['th_chidon_id']][$name] = array(
            't1a' => $t1a,
            't1b' => $t1b,
            't2a' => $t2a,
            't2b' => $t2b,
            't3a' => $t3a,
            't3b' => $t3b,
            'paid' => $row['paid'], 
            'deleted' => $row['deleted'],
            'rep' => $row['school_rep'], 
            'contestant' => $row['contestant'] 
        );
    }
}
//echo "<pre>"; print_r($users); echo "</pre>";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Enter Chidon Test Results</title>
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
        <h1>Enter Chidon Test Results</h1>
        
        <? //if ($admin_user['auth'] == 'super') { ?>
<!--        <p style="font-size: 16px; font-weight: bold; color: red;">
            <i>Please Set/Refresh your Shabbaton Eligibility and School Representatives once all 3 test grades have been entered</i>
        </p>-->
        <? //} // end if user is superuser ?>
        
        <? if ($admin_user['auth'] == 'super' || count($schools) > 1) { ?>
            <form method="post" action="chidon_tests.php" style="text-align: center;">
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

            <form method="post" action="chidon_tests.php">
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
                            <th width="35px">Test 1 Part 1</th>
                            <th width="35px">Test 1 Part 2</th>
                            <th width="35px">Test 2 Part 1</th>
                            <th width="35px">Test 2 Part 2</th>
                            <th width="35px">Test 3 Part 1</th>
                            <th width="35px">Test 3 Part 2</th>
                            <th width="35px">Avg Part 1</th>
                            <th width="35px">Avg Part 2</th>
                            <th width="35px">Avg All</th>
                            <th>Eligibility Status</th>
                            <th></th>
                        </tr>
                        <?php
                        foreach ($info as $grade => $other) {
                            foreach ($other as $user_id => $more) {
                                foreach ($more as $name => $tests) {
                                    // get marks
                                    $t1a = intval($tests['t1a']);
                                    $t1b = intval($tests['t1b']);
                                    $t2a = intval($tests['t2a']);
                                    $t2b = intval($tests['t2b']);
                                    $t3a = intval($tests['t3a']);
                                    $t3b = intval($tests['t3b']);
                                    
                                    $div1 = count(array_filter([$t1a, $t2a, $t3a])); // count the number of Part 1 Tests which are actually set
                                    $div2 = count(array_filter([$t1b, $t2b, $t3b])); // count the number of Part 2 Tests which are actually set
                                    
                                    // now that 3 tests have been done, divide by 3
                                    $div1 = $div2 = 3;
                                    
                                    // calculate the averages
                                    $avg1 = $div1 ? (intval($tests['t1a']) + intval($tests['t2a']) + intval($tests['t3a'])) / $div1 : 0;
                                    $avg2 = $div2 ? (intval($tests['t1b']) + intval($tests['t2b']) + intval($tests['t3b'])) / $div2 : 0;
                                    $avg = $div2 ? ($avg1 + $avg2) / 2 : $avg1;
                                    
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
                                            <input type='text' name='t1a[<?=$user_id?>]' value='<?=$t1a?>'
                                                <?//= $admin_user['auth'] != 'super' && $shutdown1 && !in_array($school_id, $exceptions) ? "disabled"  : ""?>
                                                <?= $admin_user['auth'] != 'super' && $shutdown1 ? "disabled"  : ""?>
                                            />
                                        </td>
                                        <td>
                                            <input type='text' name='t1b[<?=$user_id?>]' value='<?=$t1b?>'
                                                <?//= $admin_user['auth'] != 'super' && $shutdown1 && !in_array($school_id, $exceptions) ? "disabled"  : ""?>
                                                <?= $admin_user['auth'] != 'super' && $shutdown1 ? "disabled"  : ""?>
                                            />
                                        </td>
                                        <td>
                                            <input type='text' name='t2a[<?=$user_id?>]' value='<?=$t2a?>'
                                                <?//= $admin_user['auth'] != 'super' && $shutdown2 && !in_array($school_id, $exceptions) ? "disabled"  : ""?>
                                                <?= $admin_user['auth'] != 'super' && $shutdown2 ? "disabled"  : ""?>
                                            />
                                        </td>
                                        <td>
                                            <input type='text' name='t2b[<?=$user_id?>]' value='<?=$t2b?>'
                                                <?//= $admin_user['auth'] != 'super' && $shutdown2 && !in_array($school_id, $exceptions) ? "disabled"  : ""?>
                                                <?= $admin_user['auth'] != 'super' && $shutdown2 ? "disabled"  : ""?>
                                            />
                                        </td>
                                        <td>
                                            <input type='text' name='t3a[<?=$user_id?>]' value='<?=$t3a?>'
                                                <?//= $admin_user['auth'] != 'super' && $shutdown3 && !in_array($school_id, $exceptions) ? "disabled"  : ""?>
                                                <?= $admin_user['auth'] != 'super' && $shutdown3 ? "disabled"  : ""?>
                                            />
                                        </td>
                                        <td>
                                            <input type='text' name='t3b[<?=$user_id?>]' value='<?=$t3b?>'
                                                <?//= $admin_user['auth'] != 'super' && $shutdown3 && !in_array($school_id, $exceptions) ? "disabled"  : ""?>
                                                <?= $admin_user['auth'] != 'super' && $shutdown3 ? "disabled"  : ""?>
                                            />
                                        </td>
                                        <td><?=number_format($avg1, 2)?></td>
                                        <td><?=number_format($avg2, 2)?></td>
                                        <td><?=number_format($avg , 2)?></td>
                                        <td>
                                        <?php
                                            if ($tests['rep']) echo "Representative";
                                            else if ($tests['contestant']) echo "Contestant";
                                            else echo "Not Eligible";
                                        ?>
                                        </td>
                                        <td>
                                        <?php
                                        if ($admin_user['auth'] == 'super') {
                                            if ($tests['deleted'] == 1) {
                                                echo "<a href='#' class='putBack' id=" . $user_id . " />put back</a>";
                                            } else if (empty($tests['paid'])) {
                                                echo "<a href='#' class='remove' id=" . $user_id . " />delete</a>";
                                            } else {
                                                echo "<span class='remove' id=" . $user_id . "></span>";
                                            }
                                        } else {
                                            if (empty($tests['paid'])) echo "<a href='#' class='remove' id=" . $user_id . " />delete</a>";
                                            else echo "<span class='remove' id=" . $user_id . "></span>";
                                        }?>
                                        </td>
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

            <a class='button' id="prev_page" href='/chidon_school_reg.php'><i class="fa fa-arrow-left"></i> Register Chaperones</a>
            <a class='button' id="next_page" href='/enrollment.php'>Activate Enrollment <i class="fa fa-arrow-right"></i></a>

        <?php endif; ?>
    </body>
    <script>
        $(function() {
            $("#submit_marks_button").click(function(){
                //alert("Please contact HQ to choose your contestants and representatives.");
            });
            
            var year = <?=$year?>;
            var school = <?=$admin_user['auths']['school'][0]?>;
            
            <?php if (isset($_POST['school'])) { // if the school is set in the post request then update the school variable ?>
                school = <?=$_POST['school']?>;
            <?php } ?>
            
            /***************** ON CLICK LISTENERS ***************/
            // allow a user to remove from chidon
            $(".remove").click( function() {
                if (confirm("Are you sure you want to delete this boy/girl from the list. In order to add him back you will have to re-register him.")) {
                    var id = $(this).attr('id'); // get the chidon_id
                    var elem = this; // get the current element
                    var superadmin = <?=$admin_user['auth'] == 'super' ? "true" : "false"; // bring super_user into JS?>;
                    // change the URL based on what they select
                    var url = 'ajax/disableFromChidon.php';
                    if (superadmin) {
                        url = 'ajax/removeFromChidon.php';
                    }
                    // remove the user
                    $.post(url, { id : id }, function( error ) {
                        if (parseInt(error)) {
                            alert(error);
                        } else {
                        	if (!superadmin) $(elem).parent().parent().remove();
                        	else $(elem).text('');
                        }
                    });
                }
            });
            // bring back into the chidon
            $(".putBack").click( function() {
            	var id = $(this).attr('id');
            	var elem = this;
            	$.post("ajax/putBackIntoChidon.php", { id : id }, function( error ) {
                    if (parseInt(error)) {
                        alert(error);
                    } else {
                    	$(elem).text('');
                    }
                });
            });
            
            // define a function to handle setContestants.php and setReps.php
            function handleSetResponse( response ) {
                response = JSON.parse( response );
                if(response.chap) {
                    if (response.success) {
                        alert('Done. Please wait until page reloads.');
                        location.reload(true);
                    } else {
                        alert('There was an error. Please try again. If the problem persists please contact HQ.');
                    }
                } else {
                    alert('It appears that you have not setup any Chaperones yet!. Redirecting you to Chaperones page.');
                    location.href = "/chidon_school_reg.php";
                }
            }
            
            // set the contestants based on the marks
            $("#setContestants").click( function() {
                $.post('ajax/setContestants.php', { year : year, school : school }, handleSetResponse);
            });
            // set the representatives
            $("#setReps").click( function() {
                $.post('ajax/setReps.php', { year : year, school : school }, handleSetResponse);
            });
            
            <? if(isset($has_chaps) && !$has_chaps) { ?>
                $("a#next_page").click(function(event){
                    event.preventDefault();
                    alert('It appears that you have not setup any Chaperones yet!. Redirecting you to Chaperones page...');
                    location.href = "/chidon_school_reg.php";
                });
            <? }; // end if has$chaps ?>
            
        });
    </script>
</html>