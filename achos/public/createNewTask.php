<?
$admin_auth = array('school'); 
require('header.php');
/*
echo "<pre>";
print_r($_POST);
echo "</pre>";
exit;
*/
$parshos = array();
$sql1 = "select * from parshos where start >= 2456530 and end <= 2456914";
$result1 = mysql_query($sql1);
while ($row1 = mysql_fetch_assoc($result1)) {
    $parshos[$row1['name']] = array( 
        'start' => $row1['start'], 
        'end'   => $row1['end']
    );
}

$subject = $_POST['campaign'];
$task = $_POST['name'];
$category = "My Personal Task";
$points = (int)$_POST['points'];
$mandatory = $_POST['mandatory'];
$labelID = $_POST['label'];
$dailyLabels = array(30,31,32,38);
if (in_array($labelID, $dailyLabels)) {
    $daily = true;
} else {
    $daily = false;
}

$schoolID = $_POST['school_id'];
if (strpos($schoolID, ',')) {
    $schoolIDs = explode(',', $schoolID);
} else {
    $schoolIDs = array($schoolID);
}
$school_types = array(2,3,12,13);

$missions = array();
if (isset($_POST['missions'])) {
    foreach ($_POST['missions'] as $mission) {
        $missions[] = $mission;
    }
}

$classes = $_POST['classes'];
$classIDs = array();
if ($classes[0] > 0) {
    $sql1 = "select class_id from classes where class_era = 0 and class_grade in ('" . implode("','", $classes) . "')";
    if ($schoolIDs[0] > 0) {
        $sql1 .= " and school_id in (" . implode(',', $schoolIDs) . ")";
    }
    $res1 = mysql_query($sql1);
    while ($row1 = mysql_fetch_assoc($res1)) {
        $classIDs[] = $row1['class_id'];
    }
}

$levels_lookup = array(
    'Pre1a' =>  6, 
    '1'     =>  7, 
    '2'     =>  8, 
    '3'     =>  9, 
    '4'     =>  10, 
    '5'     =>  11, 
    '6'     =>  12, 
    '7'     =>  13, 
    '8'     =>  14
);
$levels = array();
if ($classes[0] > 0) {
    foreach ($classes as $class) {
        $levels[] = $levels_lookup[$class];
    }
} else {
    $levels = array_values($levels_lookup);
}

$missionSQLs = array();
foreach ($schoolIDs as $schoolID) {
    foreach ($school_types as $type) {
        foreach ($levels as $level) {
            foreach ($missions as $mission) {
                //get start and end date
                $dates = $parshos[$mission];

                //create mission(s)
                mysql_query('set autocommit=0');
                mysql_query('begin');
                $sql1 = "insert into date_tasks_missions 
                        set start_date = " . $dates['start'] . ", 
                        end_date = " . $dates['end'] . ", 
                        mission_name = '" . mysql_real_escape_string($mission) . "', 
                        mission_value = 1.0, 
                        track_id = 1, 
                        subject_id = $subject, 
                        school_type_id = $type, 
                        level = $level, 
                        default_on = 0";
                if ($schoolID > 0)
                    $sql1 .= ", created_by_school = $schoolID";
                $missionSQLs[] = $sql1;
            }
        }
    }
}
/*
echo "<pre>";
print_r($missionSQLs);
echo "<pre>";
exit;
*/ 
require_once 'class.defaults.php';
mysql_query('set autocommit=0');
mysql_query('begin');
$success = true;
$errors = array();
$key = 0;
foreach ($missionSQLs as $sql2) {
    if (!mysql_query($sql2)) {
        $success = false;
        $errors[] = $sql2 . "<br />" . mysql_error();
        break;
    } else { 
        $missionID = mysql_insert_id();
        if ($schoolID > 0 && empty($classIDs)) {
            $d = new Defaults($schoolID, 'school');
            $d->addOn($missionID, 'mission');
        } else if (!empty($classIDs)) {
            foreach ($classIDs as $classID) {
                $d = new Defaults($classID, 'class');
                $d->addOn($missionID, 'mission');
            }
        }
        
        $taskSQL = "insert into date_tasks 
                    set date_tasks_mission_id = " . $missionID . ", 
                    name = '" . mysql_real_escape_string($task) . "', 
                    points = $points, 
                    cat = '" . mysql_real_escape_string($category) . "', 
                    default_on = 0";
        if ($mandatory) {
            $taskSQL .= ", mandatory_qty = 1, optional_qty = 0";
        } else {
            $taskSQL .= ", mandatory_qty = 0, optional_qty = 1";
        }
        if ($labelID) {
            $taskSQL .= ", label_id = " . $labelID;
            if ($daily) {
                $taskSQL .= ", daily_task = 1, needed = 5";
            }
        }
        
        if (!mysql_query($taskSQL)) {
            $success = false;
            $errors[] = $taskSQL . "<br />" . mysql_error();
            break;
        } else { 
            $taskID = mysql_insert_id();
            if ($schoolID > 0 && empty($classIDs)) {
                $d = new Defaults($schoolID, 'school');
                $d->addOn($taskID, 'task');
            } else if (!empty($classIDs)) {
                foreach ($classIDs as $classID) {
                    $d = new Defaults($classID, 'class');
                    $d->addOn($taskID, 'task');
                }
            }

            //figure out what the label order should be
            $qry = "select * from date_tasks_missions 
                        join date_tasks using (date_tasks_mission_id) 
                        where date_task_id = " . $taskID;
            $resQry = mysql_query($qry);
            $r = mysql_fetch_assoc($resQry);
            $subject = $r['subject_id'];
            $school_type = $r['school_type_id'];
            $level = $r['level'];

            $labelQry = "select label_ord from date_tasks dt 
                        join date_tasks_missions dtm using (date_tasks_mission_id) 
                        where dtm.school_type_id = $school_type 
                        and dtm.level = $level 
                        and label_id = $labelID 
                        order by label_ord desc";
            $labelRes = mysql_query($labelQry);
            if (mysql_num_rows($labelRes) > 0) {
                $labelRow = mysql_fetch_assoc($labelRes);
                $labelOrd = $labelRow['label_ord'];
            } else {
                $labelOrd = 0;
            } 
            $labelOrd++;
            mysql_query("update date_tasks set label_ord = $labelOrd where date_task_id = $taskID");
        }
    }
} 

if ($success) {
    mysql_query('commit');
} else {
    mysql_query('rollback');
}
mysql_query('set autocommit=1');

if (count($errors) > 0) {
    echo "<pre>";
    print_r($errors);
    echo "</pre>";
} else {
    $msg = urlencode("Congratulations! You have successfully created a new Task.");
    header("Location: newTask.php?msg=$msg");
}
?>
