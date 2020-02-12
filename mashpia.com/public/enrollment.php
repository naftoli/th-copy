<?php
//ini_set('display_errors', 1);
$admin_auth = array('school'); 
require('header.php');

require_once 'class.adminSchools.php';       
$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'], true, true ); // add chidon schools
$schools = $as->getSchools();

require_once 'class.globalSettings.php';
$year = GlobalSettings::getChidonYear();
// $year = 5779;

$errorMsg = '';
$userInfo = array();
foreach ($schools as $sid => $schoolName) {
    // check that school has at least one chaperone and as many walking supervisors as needed
    $sql = "select needed from th_chidon_chaps_needed where year = " . $year . " and school_id = " . $sid;
    $result = mysql_query( $sql );
    $row = mysql_fetch_assoc( $result );
    $numSupersNeeded = $row['needed'];

    // check how many chaps have registered
    $sql = "select * from th_chidon_chaps where chap_type = 1 and year = " . $year . " and school_id = " . $sid;
    $result = mysql_query( $sql );
    $numChaps = mysql_num_rows( $result );

    // check how many walking supervisors we have
    $sql = "select * from th_chidon_chaps where is_walking = 1 and year = " . $year . " and school_id = " . $sid;
    $result = mysql_query( $sql );
    $numSupers = mysql_num_rows( $result );

    if ( $numChaps == 0 || $numSupers < $numSupersNeeded ) {
        $errorMsg .= $schoolName . " needs to have at least one chaperone registered and " . $numSupersNeeded . " walking supervisor(s).<br />";
        continue;
    }

    $sql = "SELECT tc.*, u.first, u.last, c.* "
            ."FROM th_chidon tc "
            ."JOIN users u USING (user_id) "
            ."JOIN classes c ON u.class_id = c.class_id "
            ."WHERE tc.year = " . $year . " "
            
            ."AND u.school_id = " . $sid . " "
            ."AND tc.deleted = 0 "; // only deleted kids
    $sql .= "ORDER BY class_grade, class_sub, tc.school_rep desc, u.last, u.first";
    //echo "<input type='hidden' name='sql' value=\"" . $sql . "\" />";
    $result = mysql_query($sql);
    while ($row = mysql_fetch_assoc($result)) {
        $grade = $row['class_grade'] . (empty($row['class_sub']) ? '' : '-' . $row['class_sub']);
        $name = $row['first'] . ' ' . $row['last'];
        $userInfo[$sid][$grade][$row['th_chidon_id']][$name] = array(
            't1a'           => $row['test1a'],
            't1b'           => $row['test1b'],
            't2a'           => $row['test2a'],
            't2b'           => $row['test2b'],
            't3a'           => $row['test3a'],
            't3b'           => $row['test3b'],
            'khk'           => $row['khk'],
            'trophy'        => $row['trophy_contestant'],
            'contestant'    => $row['contestant'],
            'rep'           => $row['school_rep'],
            'enrolled'      => $row['date_paid'],
            'edit'          => $row['allow_edit'],
            'can_enroll'    => $row['can_enroll']
        );
    }
}
?>
<!DOCTYPE html>
<HTML>
    <HEAD>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Shabbaton Eligibility</title>
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <link href="/styles/admin/fancy-checkbox.css" rel="stylesheet" type="text/css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
        <style type='text/css'>
            a.button{display: inline-block;}
            a#next_page{float: right;margin-bottom: 20px;}
            a#prev_page{float: left;}
            table {font-size: 12px;}
            th, td {padding: 3px 5px;}
            table caption#school_name {font-size: 1.6em;}
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
        </style>
    </HEAD>

    <BODY>
        <? include('admin_header.php'); ?>
        <?php include($_SERVER['DOCUMENT_ROOT']."/chidon_passwords.php"); ?>
        <h1>Shabbaton Eligibility</h1>

        <?php
        if ( !empty( $errorMsg ) ) {
            echo "<div style='color: red;'>";
            echo $errorMsg;
            if ( count( $schools ) == 1 ) {
                echo "You need to register a chaperone and / or more walking supervisor(s) before you can enroll students.";
                echo "</div>";
                exit;
            }
            echo "</div>";
        }
        ?>
 
        <?php foreach ($userInfo as $sid => $info) { ?>
            <table class="tests">
                <caption id="school_name"><?=$schools[$sid]?></caption>
                <caption>
                    Activate All:
                    <label class="fancy-check-container">
                        <input type='checkbox' id='activate_all' />
                        <span class="fancy-check"></span>
                    </label>
                </caption>
                <tr>
                    <th>Grade</th>
                    <th>Student</th>
                    <th>Avg Part 1</th>
                    <th>Avg Part 2</th>
                    <th>Avg All</th>
                    <th>Shabbaton Status</th>
                    <th>Activate Enrollment</th>
                    <th>Enrolled to Shabbaton</th>
                    <th>Allow Edits</th>
                </tr>
                <?php
                $curGrade = 0;
                foreach ($info as $grade => $other) {
                    foreach ($other as $chidon_id => $more) {
                        foreach ($more as $name => $tests) {
                            // calculate the averages for each student
                            $avg1 = (floatval($tests['t1a']) + floatval($tests['t2a']) + floatval($tests['t3a'])) / 3;
                            $avg2 = (floatval($tests['t1b']) + floatval($tests['t2b']) + floatval($tests['t3b'])) / 3;
                            $avg = ($avg1 + $avg2) / 2;
                            
                            // if the grade matches the current grade... print a grey line
                            if ($curGrade != $grade) {
                                $curGrade = $grade; // update the grade?>
                                <tr><td colspan='9'><h2></h2></td></tr>
                            <?  } // end check for grade change ?>
                            
                            <tr id='<?=$chidon_id?>'>
                                <td><?=$grade?></td>
                                <td><?=$name?></td>
                                <td><?=number_format($avg1, 2)?></td>
                                <td><?=number_format($avg2, 2)?></td>
                                <td><?=number_format($avg, 2)?></td>
                                <td>
                                    <?php
                                    if ( $tests['khk'] ) echo "Kol Hatorah Kulo";
                                    else if ( $tests['rep'] ) echo "Representative";
                                    else if ( $tests['trophy'] ) echo "Trophy Contestant";
                                    else if ( $tests['contestant'] ) echo "Contestant";
                                    else echo '';
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    if ( intval($tests['khk']) || intval($tests['rep']) || intval($tests['trophy']) || intval($tests['contestant']) ) {
                                        echo "<label class='fancy-check-container'><input type='checkbox' class='activate'";
                                        if ($tests['can_enroll']) echo "checked ";
                                        echo " />";
                                        echo "<span class='fancy-check'></span></label>";
                                    }
                                    ?>
                                </td>
                                <td><?=$tests['enrolled'] ? $tests['enrolled'] : ""?></td>
                                <td>
                                    <label class="fancy-check-container">
                                        <input type='checkbox' class='edit' <?=$tests['edit'] ? "checked" : ""?>/>
                                        <span class="fancy-check"></span>
                                    </label>
                                </td>
                            </tr>
                            <?php // close all the loops
                        }
                    }
                }
                ?>
            </table>
            <br />
        <?php } // end foreach school ?>
        <a class='button' id="prev_page" href='/chidon_school_reg2.php'><i class="fa fa-arrow-left"></i> Enroll Chaperones</a>
        <a class='button' id="next_page" href='/review_enrollment.php'>Review Enrollment <i class="fa fa-arrow-right"></i></a>
    </body>
    <script>
        $(".activate").change( function(event) {
            //alert("Activation will only occur if your schools has registered the chaperones.");
            var id = $(this).parent().parent().parent().attr('id');
            var val = $(this).is(":checked") ? 1 : 0;
            $.post('ajax/activateEnrollment.php', { id : id, can_enroll: val }, function( success ) {
                response = JSON.parse(success);
                if (!response.success) {
                    event.target.checked = !event.target.checked;
                    alert("Error updating enrollment Status");
                }
            });
        });
        
        $(".edit").change( function() {
            var id = $(this).parent().parent().parent().attr('id');
            var val = $(this).is(":checked") ? 1 : 0;
            $.post('ajax/updateChidon.php', { id : id, field : 'allow_edit', val : val }, function( error ) {
                if (parseInt(error) != 0) {
                    alert('Error updating.');
                }
            });
        });
        
        $("#activate_all").click( function(event) {
            var table = $(event.target).parent().parent().parent();
            $.each(table.find(".activate"), function(index, item){
                if (item.checked !== event.target.checked) {
                    item.checked = event.target.checked;
                    $(item).change();
                }
            });
        });
    </script>
</html>