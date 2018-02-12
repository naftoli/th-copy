<?php
ini_set('max_execution_time', 300); // set a timeout for this script of 5 minutes (longer then normal)
$admin_auth = array('school'); // only schools are allowed here
require('header.php'); // connect to the database and preform the authentication
/*
echo "<pre>";
print_r($_POST);
echo "</pre>";
*exit;
*/
// get current working year
require_once 'class.globalSettings.php';
$year = GlobalSettings::getCurrentYear();

$parshos = array(); // get the parshios
$sql1 = "select * from parshos where year = " . $year . " and start >= " . unixtojd();
$result1 = mysql_query($sql1); // gets the data
// punch all the rows of the database into the array in the following format: <name>: {start: <start>, end: <end>}
while ($row1 = mysql_fetch_assoc($result1)) {
    $parshos[$row1['name']] = array( 
        'start' => $row1['start'], 
        'end'   => $row1['end']
    );
}
// escape all the input against SQL injection
foreach ($_POST as $k => $v) {
	if (!is_array($v))
	$_POST[$k] = mysql_real_escape_string($v);
}
// start pulling the data from the post request
$subject = $_POST['campaign']; // campaign becomes subject
$lang = $_POST['lang'];

$shortName = $_POST['shortName'];
$task = $_POST['name']; // name becomes task
$category = "My Personal Task"; // catagory defaults to My Personal Task
//$points = (int)$_POST['points']; // points where commented out
$mandatory = $_POST['mandatory'];
$labelID = intval( $_POST['label'] ); // label becomes labelID
if ($lang == 2) {
	// make sure we have the correct label id
	if (in_array($labelID, array(30,31,32,38))) $labelID += 20;
}
$dailyLabels = array(30,31,32,38,50,51,52,58); // hardcoded array of labels which are daily rather then weekly
// set $daily to true if $labelID is in $dailyLables
if (in_array($labelID, $dailyLabels)) {
    $daily = true;
} else {
    $daily = false;
}
// get the school ids from post headers
$schoolIDs = array();
$schoolID = $_POST['school_id'];
if (strpos($schoolID, ',')) { // if there is more then one...
    $schoolIDs = explode(',', $schoolID); // explode them by the devider into the array
} else {
    $schoolIDs = array($schoolID); // otherwise just create an array of one
}

// set $mission_marking and $grid_marking to 1 or 0 depending on if they are set in the post header
$mission_marking = isset($_POST['mission_marking']) ? 1 : 0;
$grid_marking = isset($_POST['grid_marking']) ? 1 : 0;

$grid_id = '';
if ($grid_marking) {
	// find out what grid id to give
	$sql = "select max(grid_id) as id from date_tasks";
	$result = mysql_query($sql);
	$row = mysql_fetch_assoc($result);
	$grid_id = $row['id'];
	if ($grid_id < 100) $grid_id = 100; // must be at least 100
	else $grid_id++; // if it is more then 100 just incrament it by one
}

// handle Catagories (or Cat)
$num = 0;
$sqlCat = "SELECT DISTINCT dt.cat
	        FROM date_tasks dt
	        JOIN date_tasks_missions dtm
	        USING ( date_tasks_mission_id ) 
	        WHERE dt.cat LIKE  '%My Personal Task%' 
	        AND (dtm.created_by_school is null or dtm.created_by_school in (" . $schoolID . "))";
$resCat = mysql_query($sqlCat);
$numCat = mysql_num_rows($resCat);
// if there are other personal tasks, then get the last number used at the end of one of them (e.g. My Personal Task 5)
if ($numCat) {
	$cats = array();
	while ($rowCat = mysql_fetch_assoc($resCat)) {
		$str = $rowCat['cat'];
		$pos = strrpos($str, ' ');
		if ($pos) {
			$num = substr($str, $pos);
			if (is_numeric($num)) $cats[] = $num;
		}
	}
	if (!empty($cats)) {
		sort($cats);
		$num = end($cats);
	}
}
//echo $num; exit;
$category .= " " . ++$num; // incrament the biggest number and add it to the end

$school_types = array(2,3,12,13); // hardcoded school types

// get all the missions from the post request
$missions = array();
if (isset($_POST['missions'])) {
    foreach ($_POST['missions'] as $mission) {
        $missions[] = $mission;
    }
}
// get all the classes from the post array
$classes = $_POST['classes'];
$classIDs = array();
if ($classes[0]) { //if we have class info
	foreach ($classes as $class) {
		$sql1 = "select class_id from classes where class_era = 0 and class_grade = '$class' and school_id in (" . $schoolID . ")";
	    $res1 = mysql_query($sql1);
	    while ($row1 = mysql_fetch_assoc($res1)) {
	        $classIDs[$class][] = $row1['class_id'];
	    }
	}
}
// hardcoded levels lookup
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
if ($classes[0]) { //if we have class info
	foreach ($classes as $class) {
		$levels[] = $levels_lookup[$class];
	}
} else { // if not we just use all the classes
    $levels = array_values($levels_lookup);
}

$missionSQLs = array(); // array to fill with sql commands to create date_tasks_missions rows
foreach ($schoolIDs as $school) {
    foreach ($school_types as $type) { // hardcoded array on line 95
        foreach ($levels as $level) { // each grade
            foreach ($missions as $mission) { // each mission (parsha)
                //get start and end date
                $dates = $parshos[$mission]; // get the start and end dates for the parsha
                
                $create_mission = true;
                //first check if mission already exists and get existing id
                $sqlM = "select date_tasks_mission_id from date_tasks_missions 
                        where school_type_id = $type 
	                    and subject_id = $subject 
	                    and level = $level 
	                    and track_id = 1 
	                    and start_date = " . $dates['start'] . " 
	                    and end_date = " . $dates['end'] . " 
	                    and mission_name = '" . mysql_real_escape_string($mission) . "' 
	                    and personal = 0 
	                    and created_by_school = $school 
	                    and lang_id = $lang";
	            //echo $sqlM . "<br />";
	            $resM = mysql_query($sqlM);
	            if (mysql_num_rows($resM) > 0) {
	                $rowM = mysql_fetch_assoc($resM);
	                $missionSQLs[][$level] = $rowM['date_tasks_mission_id'];
	                $create_mission = false;
	            }
				
                if ($create_mission) {
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
                            default_on = 0, 
                            lang_id = $lang,
                            created_by_school = $school";
                    $missionSQLs[][$level] = $sql1;
                }
            }
        }
    }
}

//echo "<pre>";
//print_r($missionSQLs);
//echo "<pre>";
//exit;

require_once 'class.defaults.php';
mysql_query('set autocommit=0'); // create a transaction
mysql_query('begin');
$success = true; // default to good execution
$errors = array(); // and no errors
$key = 0;
$missionIDs = array(); // blank array of mission ids
$taskIDs = array(); // and blank array of task ids

foreach ($missionSQLs as $info) { // load up each of the generated queries
	foreach ($info as $level => $sql2) { // get the sql by level
		//echo $sql2 . "<br />";
		if (is_numeric($sql2)) { // if it exists already
			$missionID = $sql2; // set the mission id to the sql data
		} else { // otherwise, attempt to run the insert query into the database
			if (!mysql_query($sql2)) { // if it fails...
				$success = false; // success is false
				$errors[] = $sql2 . "<br />" . mysql_error(); // add the error with the sql before it into the array
				break; // and quit the loop
			} // so it was sucessfull (or we would quit the loop)
			$missionID = mysql_insert_id(); // so get the last ID and set it to the missionID
		}
		$missionIDs[] = $missionID; // add the missionID to the array
		// join the classes to the missions?
		if (empty($classIDs)) {
			foreach ($schoolIDs as $id) {
				$d = new Defaults($id, 'school');
				$d->addOn($missionID, 'mission');
			}
		} else if (!empty($classIDs)) {
			foreach ($classIDs[array_search($level, $levels_lookup)] as $classID) {
				//echo $classID . "<br />";
				$d = new Defaults($classID, 'class');
				$d->addOn($missionID, 'mission');
			}
		}
		
		$taskSQL = "insert into date_tasks 
					set date_tasks_mission_id = " . $missionID . ", 
					short_name = '" . $shortName . "', 
					name = '" . $task . "', 
					points = 0.5, 
					cat = '" . $category . "',
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
		if ($mission_marking) {
			$taskSQL .= ", mission_marking = 1";
		}
		if ($grid_marking) {
			$taskSQL .= ", grid_marking = 1, grid_id = " . $grid_id;
		}
		
		if (!mysql_query($taskSQL)) {
			$success = false;
			$errors[] = $taskSQL . "<br />" . mysql_error();
			break;
		} else { 
			$taskID = mysql_insert_id();
			$taskIDs[] = $taskID;
			// join tasks to schools and classes?
			if (empty($classIDs)) {
				foreach ($schoolIDs as $id) {
					$d = new Defaults($id, 'school');
					$d->addOn($taskID, 'task');
				}
			} else if (!empty($classIDs)) {
			foreach ($classIDs[array_search($level, $levels_lookup)] as $classID) {
					//echo $classID . "<br />";
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
} // end foreach on mysql queries generated before
//$success = false;
if ($success) {
    mysql_query('commit');
} else {
    mysql_query('rollback');
}
mysql_query('set autocommit=1');
//exit;
if (count($errors) > 0) {
    echo "<pre>";
    print_r($errors);
    echo "</pre>";
} else {
    $msg = urlencode("Congratulations! You have successfully created a new Task.");
    header("Location: newTask.php?msg=$msg");
}
?>
