<? // start the script after the catch all title
if ($_GET['debug']) {
    //error_reporting(E_ALL);
    ini_set("display_errors", 1);
    $debug = true; // set debug to true
} else {
    $debug = false;
}

$admin_auth = array('school'); 
require('header.php');

require_once 'class.adminSchools.php';
$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
$schools = $as->getSchools();

require_once 'class.globalSettings.php';
$year = GlobalSettings::GetCurrentYear();

$sm = calculateSM( $year );
$months = array(
    0	=>	'Tishrei', 
    1	=>	'Cheshvon', 
    2	=>	'Kislev',
    3	=>	'Teves', 
    4	=>	'Shvat', 
    5	=>	'Adar I', 
    6	=>	'Adar II', 
    7	=>	'Nissan', 
    8	=>	'Iyar', 
    9	=>	'Sivan', 
    10	=>	'Tamuz', 
    11	=>	'Av', 
    12	=>	'Elul' 
);
//echo "<pre>"; print_r($sm); echo "</pre>"; exit;
//echo "<pre>"; print_r($_POST); echo "</pre>"; exit;
$msg = '';

if (isset($_POST['submit'])) {
    if($debug) echo "<pre>";
    if($debug) print_r($_POST);
    if($debug) echo "</pre>";
    
    $date = $_POST['date'];
    $school = $_POST['school'];
    $grade = $_POST['grade'];
    //echo $date;
    //exit;
    if ($date == 0 || $school == 0) {
        header("Location: mark_tehillim2.php" + ($debug ? "?debug=true" : ""));
        exit;
    }
    
    $sql = "SELECT class_grade, class_sub FROM classes WHERE class_id = " . $grade;
    $result = mysql_query($sql);
    $row = mysql_fetch_assoc($result);
    $gradeName = $row['class_grade'] . (empty($row['class_sub']) ? '' : '-' . $row['class_sub']);
    
    $users = array();
    $userInfo = array();
    // generate the SQL
    $sql = 	"SELECT * FROM users JOIN classes USING (class_id) "
            ."WHERE user_registered > 0 "
            ."AND users.school_id = " . $school . " ";
    // add a limit to the grade/school
    if( $grade ) 
        $sql .= "AND users.class_id = " . $grade . " ";
    else
        $sql .= "AND classes.school_id = '$school' ";
    
    $sql .=	"ORDER BY class_grade, class_sub, last, first";

    $result = mysql_query($sql);
    while ($row = mysql_fetch_assoc($result)) {
        $users[] = $row['user_id'];
        $userInfo[$row['user_id']] = $row;
    }
    
    $tehillim = array();
    foreach ($users as $user) {
        $sql = "SELECT u.school_type_id, u.lang_id, ut.level, ut.track_id 
                FROM user_tracks ut 
                JOIN users u USING (user_id) 
                WHERE ut.subject_id = 1 
                AND ut.user_id = " . $user;

        $result = mysql_query( $sql );
        $row = mysql_fetch_assoc( $result );

        if ( $row['level'] && $row['track_id'] ) {
            $level 	= $row['level'];
            $lang 	= $row['lang_id'];
            $track 	= $row['track_id'];
            $type 	= $row['school_type_id'];
            
            $sql = "SELECT date_tasks_mission_id 
                    FROM date_tasks_missions 
                    WHERE subject_id = 1
                    AND lang_id = " . $lang
                . " AND level = " . $level
                . " AND track_id = " . $track 
                . " AND school_type_id = " . $type 
                . " AND start_date = " . $date 
                . " AND end_date = " . $date;
            //if ($user == 22946) echo $sql . "<br />";
            $result = mysql_query($sql);
            $row = mysql_fetch_assoc($result);
            $mission_id = $row['date_tasks_mission_id'];
            
            if ($mission_id) {
                $tasks = array();
                $sql = "SELECT * FROM date_tasks WHERE date_tasks_mission_id = " . $mission_id;
                //if ($user == 20757) echo $sql . "<br />"; exit;
                $result = mysql_query($sql);
                while ($row = mysql_fetch_assoc($result)) {
                    $tasks[] = $row;
                }
                $tehillim[$user] = $tasks;
            }
        }
    }
    //echo "<pre>"; print_r($tehillim); echo "</pre>";
    $mark = true;
    if (isset($_POST['oldGrade']) && $_POST['oldGrade'] != $grade) $mark = false;
    if (isset($_POST['marks']) && $mark) {
        //echo count($_POST['kapitelach']);
        //echo "<br />" . count($_POST['minutes']);
//        echo "<pre>"; print_r($_POST); echo "</pre>"; exit;
        $qrys = array();
        $types = array('kapitelach', 'minutes');
        $grids = array(
            'kapitelach'    => 8001,
            'minutes'       => 8002
        );

        foreach ($types as $key => $type) {
            foreach ($_POST[$type] as $user => $task) {
                foreach ($task as $id => $val) {
                    if ($val == '') $val = 0;
                    if (is_numeric($val)) {
                        //find out if mark exists and get task id of mark
                        $sql = "SELECT * from date_tasks_marks dtm
                                JOIN date_tasks dt USING (date_task_id) 
                                WHERE user_id = " . $user 
                            . " AND mark_date = " . $date 
                            . " AND grid_id = " . $grids[$type];
                        $result = mysql_query($sql);
                        if (mysql_num_rows($result) > 0) {
                            // get task id
                            $row = mysql_fetch_assoc($result);
                            $task_id = $row['date_task_id'];
                            if ($val == 0) {
                                // delete mark and mission if value is 0
                                $sql = "DELETE FROM date_tasks_marks WHERE date_task_id = " . $task_id . " AND user_id = " . $user . " AND mark_date = " . $date;
                                $qrys[] = $sql;
                                // get the date tasks mission id
                                $sql = "SELECT date_tasks_mission_id FROM date_tasks WHERE date_task_id = " . $task_id;
                                $result = mysql_query($sql);
                                $row = mysql_fetch_assoc($result);
                                // and delete the mission mark if earned as well
                                $missionID = $row['date_tasks_mission_id'];
                                $sql = "DELETE FROM date_tasks_mission_marks WHERE user_id = " . $user . " AND date_tasks_mission_id = " . $missionID;
                            } else {
                                $sql = "UPDATE date_tasks_marks 
                                        SET done_qty = " . (int) mysql_real_escape_string($val) 
                                    . " WHERE date_task_id = " . $task_id
                                    . " AND user_id = " . $user
                                    . " AND mark_date = " . $date;
                            }
                            $qrys[] = $sql;
                        } else {
                            if ($val > 0) {
                                // limit the max minutes to 770 as requested via EMAIL
                                if ( $grids[$type] == 8002 && $val > 770) $val = 770;

                                $sql = "INSERT INTO date_tasks_marks 
                                        SET date_task_id = " . $id . ",  
                                        user_id = " . $user . ", 
                                        mark_date = " . $date . ", 
                                        done_qty = " . (int) mysql_real_escape_string($val) . ", 
                                        mark_description = \"" . $tehillim[$user][$key]['description'] . "\", 
                                        mark_points = " . $tehillim[$user][$key]['points'];
                                $qrys[] = $sql;
                                
                                // find out quota
                                $sql = "SELECT dtm.*, dt.quantity FROM date_tasks_missions dtm
                                        JOIN date_tasks dt USING (date_tasks_mission_id)
                                        WHERE dt.date_task_id = " . $id;
                                $result = mysql_query( $sql );
                                $row = mysql_fetch_assoc( $result );
                                $quota = $row['quantity'];
                                $missionID = $row['date_tasks_mission_id'];
                                
                                // check if mission was marked
                                $sql = "SELECT * FROM date_tasks_mission_marks
                                        WHERE user_id = " . $user . "
                                        AND date_tasks_mission_id = " . $missionID;
                                $result = mysql_query( $sql );
                                
                                // check if quota was reached 
                                if (intval($val) >= $quota && mysql_num_rows( $result ) == 0) {
                                    $sql = "INSERT IGNORE INTO date_tasks_mission_marks
                                            SET user_id = " . $user . ",
                                            date_tasks_mission_id = " . $missionID . ",
                                            mission_value = 1.0, 
                                            subject_id = 1,
                                            mission_name = \"" . mysql_real_escape_string( $row['mission_name'] ) . "\",
                                            mark_date = " . $date . " 
                                            ON DUPLICATE KEY UPDATE 
                                            mark_date = " . $date;
                                    $qrys[] = $sql;
                                } else if (intval($val) < $quota && mysql_num_rows( $result ) == 1) {
                                    $sql = "DELETE FROM date_tasks_mission_marks
                                            WHERE user_id = " . $user . ",
                                            AND date_tasks_mission_id = " . $missionID;
                                    $qrys[] = $sql;    
                                }
                            }
                        }
                        //echo $sql . "<br />";
                    }
                }
            }
        }
        //echo "<pre>"; print_r($qrys); echo "</pre>"; exit;
        $updated = true;
        mysql_query('set autocommit=0');
        mysql_query('start transaction');
        foreach ($qrys as $qry) {
            // echo $qry . "<br />";
            if (! mysql_query($qry)) {
//              echo $qry . "<br />" . mysql_error();
              $updated = false;
              break;
            }
        }
        if ($updated) {
            mysql_query('commit');
            $msg = 'Saved.';
        } else {
            mysql_query('rollback');
            $msg = 'Error Saving.';
        }
        mysql_query('set autocommit=1');
    }
    
    //get marked info
    $marked = array();
    foreach ($users as $user) {
        if (isset($tehillim[$user])) {
            $sql = "select done_qty from date_tasks_marks where user_id = " . $user . " and date_task_id = " . 
                $tehillim[$user][0]['date_task_id'];
            $result = mysql_query($sql);
            if (mysql_num_rows($result) > 0) {
                $row = mysql_fetch_assoc($result);
                $marked[$user]['kap'] = $row['done_qty'];
            } else {
                // check if any marks were put in for a different ladder
                $sql = "select done_qty from date_tasks_marks dtm 
                        join date_tasks dt using (date_task_id)
                        join date_tasks_missions dtmm using (date_tasks_mission_id) 
                        where dtm.user_id = " . $user . "
                        and dtmm.start_date = " . $date . " 
                        and dt.grid_id = " . $tehillim[$user][0]['grid_id'];
                $result = mysql_query($sql);
                if (mysql_num_rows($result) > 0) {
                    $row = mysql_fetch_assoc($result);
                    $marked[$user]['kap'] = $row['done_qty'];
                }
            }
            
            $sql = "select done_qty from date_tasks_marks where user_id = " . $user . " and date_task_id = " . 
                $tehillim[$user][1]['date_task_id'];
            $result = mysql_query($sql);
            if (mysql_num_rows($result) > 0) {
                $row = mysql_fetch_assoc($result);
                $marked[$user]['min'] = $row['done_qty'];
            } else {
                // check if any marks were put in for a different ladder
                $sql = "select done_qty from date_tasks_marks dtm 
                        join date_tasks dt using (date_task_id)
                        join date_tasks_missions dtmm using (date_tasks_mission_id) 
                        where dtm.user_id = " . $user . "
                        and dtmm.start_date = " . $date . " 
                        and dt.grid_id = " . $tehillim[$user][1]['grid_id'];
                $result = mysql_query($sql);
                if (mysql_num_rows($result) > 0) {
                    $row = mysql_fetch_assoc($result);
                    $marked[$user]['min'] = $row['done_qty'];
                }
            }
        } 
    }
    //echo "<pre>"; print_r( $tehillim ); echo "</pre>"; exit;
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Mark Tehillim</title>
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <style>
            caption {
                border-bottom: 1px solid grey;
                margin-bottom: 10px;
            }
            tr, td {
                padding: 3px;
                font-size: 12px;
            }
        </style>
    </head>
    
    <body>
        <? include('admin_header.php') ?>
        <h1>Mark Tehillim</h1>

        <?= $msg ? "<div style='color: red;'>$msg</div>" : '' ?>
        
        <?php if (isset($_POST['submit'])) { ?>
            <form action='mark_tehillim2.php<?=$debug ? "?debug=true" : ""?>' method='post'>
            <input type="hidden" name="marks" value="1" />
            <input type="hidden" name="date" value="<?=$date?>" />
            <input type="hidden" name="school" value="<?=$school?>" />
            <input type="hidden" name="oldGrade" id="oldGrade" value="<?=$grade?>" />

            <h2>Shabbos Mevorchim 
                <?php
                foreach ($sm as $month => $smdate) {
                    if ($sm[6] == $sm[7]) {
                        if ($month == 5) $months[$month] = "Adar";
                        if ($month == 6) continue;
                    }
                    if ( $date == $smdate ) {
                        echo $months[$month];
                        break;
                    }
                }
                ?>
            </h2>

            Change Grade to: 
            <select name="grade" id="grade">
                <option value='0'>All Grades</option>
                <?php
                require_once 'class.schoolClasses.php';
                    $sc = new SchoolClasses($school);
                    $classes = $sc->getClasses();
                    foreach ($classes as $row) {
                        $class = $row['class_grade'] . (empty($row['class_sub']) ? '' : '-' . $row['class_sub']);
                        echo "<option value='" . $row['class_id'] . "'";
                        if ($row['class_id'] == $grade) echo " selected";
                        echo ">" . $class . "</option>";
                    }
                ?>
            </select>
            <input type="submit" name="submit" value="change" id="changeGrade" />
            <br /><br />
            <table>
                <caption><?=$schools[$school]?></caption>
                <tr>
                    <td colspan="4" align="center">
                        <input type="submit" name="submit" value="Save" />
                        <br /><br />
                    </td>
                </tr>
                <tr>
                    <th>Grade</th>
                    <th>Student</th>
                    <th>Kapitelach</th>
                    <th>Minutes</th>
                </tr>
                <?php
                foreach ($users as $user) {
                    if (isset($tehillim[$user])) {
                        $kap = $tehillim[$user][0]['date_task_id'];
                        $min = $tehillim[$user][1]['date_task_id'];
                        $kapVal = isset($marked[$user]['kap']) ? $marked[$user]['kap'] : 0;
                        $minVal = isset($marked[$user]['min']) ? $marked[$user]['min'] : 0;
                        $gradeName = $userInfo[$user]['class_grade'] . (empty($userInfo[$user]['class_sub']) ? '' : '-' . $userInfo[$user]['class_sub']);
                        echo "<tr><td>" . $gradeName . "</td><td>" . $userInfo[$user]['first'] . ' ' . $userInfo[$user]['last'] . "</td><td>" . 
                            "<input type='text' size='5' name='kapitelach[" . $user . "][" . $kap . "]' " . 
                            ($kapVal ? 'value=' . $kapVal : '') . " /></td><td>" . 
                            "<input type='text' size='5' name='minutes[" . $user . "][" . $min . "]' " . 
                            ($minVal ? 'value=' . $minVal : '') . " /></td></tr>";
                    }
                }
                ?>
                <tr>
                    <td colspan="4" align="center">
                        <input type="submit" name="submit" value="Save" />
                    </td>
                </tr>
            </table>
            </form>
        <?php } else { ?>
            <form action='mark_tehillim2.php<?=$debug ? "?debug=true" : ""?>' method="post">				
                <select name="school" id="school">
                    <?php
                    if (count($schools) > 1) {
                        echo "<option value='0'>Choose School</option>";
                    }
                    foreach ($schools as $id => $name) {
                        echo "<option value='" . $id . "'>" . $name . "</option>";
                    }
                    ?>
                </select><br />
                <br />				
                                
                <select name="grade" id="grade">
                    <option value='0'>All Grades</option>
                    <?php
                    if (count($schools) == 1) {
                        $id = key($schools);
                        //echo $id;
                        require_once 'class.schoolClasses.php';
                        $sc = new SchoolClasses($id);
                        $classes = $sc->getClasses();
                        foreach ($classes as $row) {
                            $grade = $row['class_grade'] . (empty($row['class_sub']) ? '' : '-' . $row['class_sub']);
                            echo "<option value='" . $row['class_id'] . "'>" . $grade . "</option>";
                        }
                    }
                    ?>
                </select><br />
                <br />
                
                <select name="date" required>
                    <option value='' disabled selected>Choose Shabbos Mevorchim</option>
                    <?php
                    foreach ($sm as $month => $date) {
                        if ($sm[6] == $sm[7]) {
                            if ($month == 5) $months[$month] = "Adar";
                            if ($month == 6) continue;
                        }
                        echo "<option value='" . $date . "'>" . $months[$month] . "</option>";
                    }
                    ?>
                </select><br />
                <br />
                <input type="submit" name="submit" value="Submit" />
            </form>
        <? } ?>
    </body>
    <script>
        $("#grade").change( function() {
            $(this).parent().submit();
        });
        
        $("#school").change( function() {
            var school = $(this).val();
            $.get('ajax/getClasses.php?flat=true', { id : school }, function( info ) {
                var grades = JSON.parse( info );
                var html = "<option value='0'>Choose Grade</option>";
                for (var g in grades) {
                    html += "<option value='" + grades[g][0] + "'>" + grades[g][1] + "</option>";
                }
                $("#grade").empty();
                $("#grade").append( html );
            });
        });
        
        $("#changeGrade").click( function() {
            if ($("#oldGrade").val() == $("#grade").val()) {
                alert('You have not changed the grade.');
                return false;
            }
        });
    </script>
</html>