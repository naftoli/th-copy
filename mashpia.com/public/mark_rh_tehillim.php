<? // start the script after the catch all title
if ($_GET['debug']) {
    error_reporting(E_ALL);
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

$subject_id = 40; // tehillim on RH is under yomei depagra
$start = 2459822; // beg of 5783 yr in system

if (isset($_POST['submit'])) {
//    if($debug) echo "<pre>";
//    if($debug) print_r($_POST);
//    if($debug) echo "</pre>";

    $school = $_POST['school'];
    $grade = $_POST['grade'];
    //echo $date;
    //exit;
    if ($school == 0) {
        header("Location: mark_rh_tehillim.php" + ($debug ? "?debug=true" : ""));
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

    $grids = [
        'first_day_rh'    => 2132,
        'second_day_rh'   => 2170
    ];

    $mark_dates = [
        'first_day_rh'  => 2459849,
        'second_day_rh' => 2459850
    ];

    $tehillim = [];
    foreach ($users as $user) {
        $sql = "SELECT u.school_type_id, u.lang_id, ut.level, ut.track_id 
                FROM user_tracks ut 
                JOIN users u USING (user_id) 
                WHERE ut.subject_id = $subject_id   
                AND ut.user_id = " . $user;

        $result = mysql_query( $sql );
        $row = mysql_fetch_assoc( $result );

        if ($row['level'] && $row['track_id']) {
            $level 	= $row['level'];
            $lang 	= $row['lang_id'];
            $track 	= $row['track_id'];
            $type 	= $row['school_type_id'];

            foreach ($grids as $desc => $grid_id) {
                $sql = "
                    SELECT 
                        *
                    FROM
                        date_tasks_missions dtm
                            JOIN
                        date_tasks dt USING (date_tasks_mission_id)
                    WHERE
                        dtm.subject_id = $subject_id
                            AND dt.grid_id = $grid_id 
                            AND dtm.lang_id = $lang 
                            AND dtm.level = $level 
                            AND dtm.track_id = $track 
                            AND dtm.school_type_id = $type 
                            AND dtm.start_date = " . $mark_dates[$desc];
                //if ($user == 22946) echo $sql . "<br />";
                $result = mysql_query($sql);
                $row = mysql_fetch_assoc($result);
                $tehillim[$user][$desc] = $row;
            }
        }
    }
    //echo "<pre>"; print_r($tehillim); echo "</pre>";
    $mark = true;
    if (isset($_POST['oldGrade']) && $_POST['oldGrade'] != $grade) $mark = false;
    if (isset($_POST['marks']) && $mark) {
        //echo count($_POST['kapitelach']);
        //echo "<br />" . count($_POST['minutes']);
        //echo "<pre>"; print_r($_POST); echo "</pre>"; exit;
        $qrys = array();
        foreach ($grids as $key => $grid_id) {
            foreach ($_POST[$key] as $user => $task) {
                foreach ($task as $id => $val) {
                    if ($val == '') $val = 0;
                    if (is_numeric($val)) {
                        //find out if mark exists and get task id of mark
                        $sql = "SELECT * from date_tasks_marks dtm
                                JOIN date_tasks dt USING (date_task_id) 
                                WHERE user_id = " . $user . "
                                AND grid_id = " . $grid_id . " 
                                AND mark_date >= " . $start;
                        $result = mysql_query($sql);
                        if (mysql_num_rows($result) > 0) {
                            // get task id
                            $row = mysql_fetch_assoc($result);
                            $task_id = $row['date_task_id'];
                            if ($val == 0) {
                                // delete mark and mission if value is 0
                                $sql = "DELETE FROM date_tasks_marks WHERE date_task_id = " . $task_id . " AND user_id = " . $user;
                            } else {
                                $sql = "UPDATE date_tasks_marks 
                                        SET done_qty = " . (int) mysql_real_escape_string($val)
                                    . " WHERE date_task_id = " . $task_id
                                    . " AND user_id = " . $user;
                            }
                            $qrys[] = $sql;
                        } else {
                            if ($val > 0) {
                                // limit the max kapitelach to 150
                                $val = $val > 150 ? 150 : $val;

                                $sql = "INSERT INTO date_tasks_marks 
                                        SET date_task_id = " . $id . ",  
                                        user_id = " . $user . ", 
                                        mark_date = " . $mark_dates[$key] . ", 
                                        done_qty = " . (int) mysql_real_escape_string($val) . ", 
                                        mark_description = \"" . $tehillim[$user][$key]['description'] . "\", 
                                        mark_points = " . $tehillim[$user][$key]['points'];
                                $qrys[] = $sql;
                            }
                        }
                        //echo $sql . "<br />";
                    }
                }
            }
        }
//        if ($debug) {
//            echo "<pre>";
//            print_r($qrys);
//            echo "</pre>";
//            exit;
//        }
        foreach ($qrys as $qry) {
//            echo $qry . "<br />";
            mysql_query($qry);
        }
    }

    //get marked info
    $marked = [];
    foreach ($users as $user) {
        if (isset($tehillim[$user])) {
            foreach ($grids as $key => $grid_id) {
                $sql = "select done_qty from date_tasks_marks where user_id = " . $user . " and date_task_id = " . $tehillim[$user][$key]['date_task_id'];
                if ($debug) echo $sql . "<br />";
                $result = mysql_query($sql);
                if (mysql_num_rows($result) > 0) {
                    $row = mysql_fetch_assoc($result);
                    $marked[$user][$key] = $row['done_qty'];
                } else {
                    // check if any marks were put in for a different ladder
                    $sql = "select done_qty from date_tasks_marks dtm 
                            join date_tasks dt using (date_task_id)
                            join date_tasks_missions dtmm using (date_tasks_mission_id) 
                            where dtm.user_id = " . $user . "
                            and dt.grid_id = " . $grid_id . " 
                            and dtm.mark_date >= " . $start;
                    if ($debug) echo $sql . "<br />";
                    $result = mysql_query($sql);
                    if (mysql_num_rows($result) > 0) {
                        $row = mysql_fetch_assoc($result);
                        $marked[$user][$key] = $row['done_qty'];
                    }
                }
            }
        }
    }
//    echo "<pre>"; print_r( $tehillim ); echo "</pre>"; exit;
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
<h1>Mark RH Tehillim</h1>

<? if (isset($_POST['submit'])) {
    ?>
    <form action='mark_rh_tehillim.php<?=$debug ? "?debug=true" : ""?>' method='post'>
        <input type="hidden" name="marks" value="1" />
        <input type="hidden" name="school" value="<?=$school?>" />
        <input type="hidden" name="oldGrade" id="oldGrade" value="<?=$grade?>" />

        <h2>RH Tehillim Marking</h2>

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
                <th>Day 1</th>
                <th>Day 2</th>
            </tr>
            <?php
            foreach ($users as $user) {
                if (isset($tehillim[$user])) {
                    $gradeName = $userInfo[$user]['class_grade'] . (empty($userInfo[$user]['class_sub']) ? '' : '-' . $userInfo[$user]['class_sub']);
                    echo "<tr><td>" . $gradeName . "</td><td>" . $userInfo[$user]['first'] . ' ' . $userInfo[$user]['last'] . "</td><td>";
                    foreach ($grids as $key => $grid_id) {
                        $id = $tehillim[$user][$key]['date_task_id'];
                        $val = isset($marked[$user][$key]) ? $marked[$user][$key] : 0;
                        echo "<input type='text' size='5' name='" . $key . "[" . $user . "][" . $id . "]' " .
                            ($val ? 'value=' . $val : '') . " /></td><td>";
                    }
                    echo "</td></tr>";
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
<? } else { ?>
    <form action='mark_rh_tehillim.php<?=$debug ? "?debug=true" : ""?>' method="post">
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
        </select>
        <br />
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