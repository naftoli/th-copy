<?php
ini_set('diplay_errors',1);
$admin_auth = array('user'); 
require('header.php');

include("classes/admin.php");
$sql = "SELECT * FROM admins WHERE admin_id=" . $admin_user['admin_id'];
$query = mysql_query($sql);
$row = mysql_fetch_assoc($query);
$admin = new \classes\admin($row);
$admin->get_markable_children();

require_once 'class.globalSettings.php';
$year = GlobalSettings::getCurrentYear();

$parshos = array();
$sql1 = "select * from parshos where year = " . $year;
$result1 = mysql_query($sql1);
while ($row1 = mysql_fetch_assoc($result1)) {
    $parshos[$row1['name']] = array( 
        'start' => $row1['start'], 
        'end'   => $row1['end']
    );
}

$subject = $_POST['campaign'];
$lang = $_POST['lang'];
$short = trim($_POST['shortName']);
$task = trim($_POST['name']);
$category = "My Personal Task";
$points = (int)$_POST['points'];
$mandatory = $_POST['mandatory'];
$labelID = $_POST['label'];
$dailyLabels = array(30,31,32,38,50,51,52,58);
if (in_array($labelID, $dailyLabels)) {
    $daily = true;
} else {
    $daily = false;
}

$sqlCat = "SELECT DISTINCT dt.cat 
            FROM date_tasks dt 
            JOIN date_tasks_missions dtm 
            USING ( date_tasks_mission_id ) 
            WHERE dt.cat LIKE '%My Personal Task%' 
            AND dtm.created_by_school is null 
            AND dtm.personal = 1  
            and dtm.default_on = 0";
//echo $sqlCat;
$resCat = mysql_query($sqlCat);
$numCat = mysql_num_rows($resCat);
if ($numCat) {
    $category .= " " . ++$numCat;
}

$missions = array();
if (isset($_POST['missions'])) {
    foreach ($_POST['missions'] as $mission) {
        $missions[] = $mission;
    }
}
// unused array
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

$children = array();
if ($_POST['children'][0]) {
    $children = $_POST['children'];
} else {
    foreach ($admin->children as $child) {
        $children[] = $child->user_id;
    }
}

$levels = array();
foreach ($children as $userID) {
    //find out level of child
    $sql1 = "select level from user_tracks 
            where user_id = " . $userID . "
			and enrolled = 1 
			limit 1";
	$result1 = mysql_query($sql1);
	$row1 = mysql_fetch_assoc($result1);
	$level = $row1['level'];
	
	//find out school type id of child
	$sql2 = "select school_type_id from users where user_id = " . $userID;
	$result2 = mysql_query($sql2);
	$row2 = mysql_fetch_assoc($result2);
	$school_type = $row2['school_type_id'];
	$levels[$school_type][$level] = 1;
}
/*
echo "<pre>";
print_r($children);
print_r($levels);
echo "</pre>";
exit;
*/
$missionSQLs = array();
foreach ($levels as $type => $info) {
	foreach ($info as $level => $value) {
		foreach ($missions as $mission) {
			//get start and end date
			$dates = $parshos[$mission];
	
			//create mission(s)
			$sql1 = "insert into date_tasks_missions 
					set start_date = " . $dates['start'] . ", 
					end_date = " . $dates['end'] . ", 
					mission_name = '" . mysql_real_escape_string($mission) . "', 
					mission_value = 1.0, 
					track_id = 1, 
					subject_id = $subject, 
					school_type_id = $type, 
					level = $level, 
					personal = 1, 
					default_on = 0,
					lang_id = $lang";
			$missionSQLs[] = $sql1;
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
        foreach ($children as $userID) {
            $d = new Defaults($userID);
            $d->addOn($missionID, 'mission');
        }
        
        //get latest cat_ord
		/*
        $s = "select cat_ord from date_tasks dt 
        	  join date_tasks_missions dtm using (date_tasks_mission_id) 
        	  where dtm.subject_id = " . $subject . " 
			  order by cat_ord desc limit 1";
        $re = mysql_query($s);
        $r = mysql_fetch_assoc($re);
        $catOrd = $r['cat_ord'];
        */
        $taskSQL = "insert into date_tasks 
                    set date_tasks_mission_id = " . $missionID . ", 
					short_name = \"" . mysql_real_escape_string($short) . "\", 
                    name = \"" . mysql_real_escape_string($task) . "\", 
                    points = $points, 
                    cat = '" . mysql_real_escape_string($category) . "',
					mission_marking = 1, 
                    default_on = 0,
					mandatory_qty = 0,
					optional_qty = 1";
        if ($labelID) {
            $taskSQL .= ", label_id = " . $labelID;
            if ($daily) {
                $taskSQL .= ", daily_task = 1, needed = 5";
            }
        }
    }

    if (!mysql_query($taskSQL)) {
        $success = false;
        $errors[] = $taskSQL . "<br />" . mysql_error();
        break;
    } else { 
        $taskID = mysql_insert_id();
        foreach ($children as $userID) {
            $d = new Defaults($userID);
            $d->addOn($taskID, 'task');
        }
        
		if ($labelID) {
			//figure out what the label order should be
			$qry = "select * from date_tasks_missions 
					join date_tasks using (date_tasks_mission_id) 
					where date_task_id = " . $taskID;
			//echo $qry . "<br />";
			$resQry = mysql_query($qry);
			$r = mysql_fetch_assoc($resQry);
			$subject = $r['subject_id'];
			$school_type = $r['school_type_id'];
			$level = $r['level'];
			
			$labelQry = "select dt.label_ord from date_tasks dt 
						join date_tasks_missions dtm using (date_tasks_mission_id) 
						where dtm.school_type_id = $school_type 
						and dtm.level = $level 
						and dtm.subject_id = $subject
						and dt.label_id = $labelID 
						order by dt.label_ord desc
						limit 1";
			//echo $labelQry . "<br />";
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
	//echo $msg;
    header("Location: newParentTask.php?msg=" . $msg);
	exit;
}
?>