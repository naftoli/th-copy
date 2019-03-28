<?php /********** SUPPORT DEBUGGING ***********/
$debug = false;
if (isset($_GET['debug']) || isset($_POST['debug'])){
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
    $debug = true;
}
/************************ LOAD UP ADMIN OBJECT ************************/
require_once $_SERVER['DOCUMENT_ROOT']."/db.php"; // connect to the database....

require_once(dirname(__FILE__)."/../reg/ajax/encrypt.php");
$admin_id = encrypt_decrypt('decrypt', $_COOKIE['admin']);
//include($_SERVER['DOCUMENT_ROOT']."/classes/admin.php");
//$admin_row = mysql_fetch_assoc(mysql_query("SELECT * FROM admins WHERE admin_id=".$admin_id));
//$admin = new \classes\admin($admin_row);

/************************ LOAD UP THE CURRENT YEAR ************************/
require_once $_SERVER['DOCUMENT_ROOT'].'/class.globalSettings.php';
$year = GlobalSettings::getCurrentYear();
$yearDates = GlobalSettings::getCurYearDates();

/************************ LOAD UP THE LIST OF PARSHIOS ************************/
$parshos = [];
$parshos_query = mysql_query("SELECT * FROM parshos WHERE year = " . $year);;
while ($parsha = mysql_fetch_assoc($parshos_query)) {
    $parshos[$parsha['id']] = [ 'name' => $parsha['name'], 'start' => $parsha['start'], 'end'   => $parsha['end']];
}

/************************ GET THE POST ID ************************/
$subject    = $_POST['campaign_id']; // get the subject id
$lang       = $_POST['lang_id'];    // get the language
$short      = mysql_real_escape_string(trim($_POST['shortName']));   // get the title
$task       = mysql_real_escape_string(trim($_POST['name']));       // get the details...
$category   = "Custom Task: ".$short; // add Custom Task: to the name so we can find it later...
$points     = 0.5; // hardcoded 0.5 points
$mandatory  = 0;  // not mandatory
$label_id   = $_POST['label_id'];
$parsha_ids = $_POST['parshos'];    // array of parsha id's selected...
$children   = $_POST['children'];  // array of user_ids sent...
// determine if the label_id is daily or not....
$daily = in_array($label_id, [30,31,32,38,50,51,52,58]); // hardcoded list of daily labels...

/************************ PREVENT DUPLICATES ************************/
$check_duplicate_sql = "SELECT DISTINCT dt.cat FROM date_tasks dt " // get the catagorys
    ." JOIN date_tasks_missions dtm USING ( date_tasks_mission_id ) " // join with date_tasks_missions for filtering it...
    ." WHERE dt.cat = '$category' " // where it is like "My Personal Task"
    ." AND dtm.created_by_school IS NULL AND dtm.created_by_parent = '$admin_id' AND dtm.default_on = 0 "
    ." AND end_date > " . $yearDates['start']; // and it is not created by a school, made by a parent and is off by default...
// if the task already exists....
if(mysql_num_rows(mysql_query($check_duplicate_sql)) > 0){
    echo json_encode(["success" => false, 'error_message' => "It appears that this task is a duplicate"]); die();
}

/************************ GET LEVELS AND SCHOOL TYPES ************************/
$levels = array();
foreach ($children as $user_id) { // for each child...
    //find out level of child and his school_type_id
    $level_query = mysql_query("SELECT school_type_id, level FROM users JOIN user_tracks using (user_id) "
                               ."WHERE user_id = '".$user_id."' AND enrolled = 1 AND subject_id = '".$subject."'");
    $level_row = mysql_fetch_assoc($level_query); // fetch the first result
    $levels[$level_row['school_type_id']][$level_row['level']] = 1; // set it to 1 so that the keys are present in the array...
}

/************************ GENERATE MISSION SQL ************************/
$missionSQLs = []; // array of insert commands for the missions...
foreach ($levels as $type => $info) {
    foreach ($info as $level => $value) {
        foreach ($parsha_ids as $parsha_id) {
            //get start and end date
            $dates = $parshos[$parsha_id]; // get the parsha based on it's parsha id
            //create mission(s)
            $mission_sql = "INSERT INTO date_tasks_missions SET "
                    ."start_date = " . $dates['start'] . ", " 
                    ."end_date = " . $dates['end'] . ", " 
                    ."mission_name = \"" . mysql_real_escape_string($dates['name']) . "\", " 
                    ."mission_value = 1.0, " 
                    ."track_id = 1, " 
                    ."subject_id = $subject, " 
                    ."school_type_id = $type, "
                    ."level = $level, "
                    ."personal = 1, "
                    ."default_on = 0, "
                    ."created_by_parent = $admin_id, "
                    ."lang_id = $lang";
            $missionSQLs[] = $mission_sql;
        }
    }
}

/************************ SETUP TRANSACTION ************************/
require_once($_SERVER['DOCUMENT_ROOT']."/class.defaults.php"); // load up the defaults....
mysql_query('set autocommit=0'); // start the transaction....
mysql_query('begin'); // start the transaction
$success    = true;  // by default this worked...
$errors     = [];   // with no errors. (hey, a man can wish)
$key = 0;

/************************ CREATE MISSIONS ************************/
foreach ($missionSQLs as $mission_sql) { // for each mission's insert statement
    
    /************************ CREATE EACH MISSION ************************/
    if (!mysql_query($mission_sql)) { // if it failes to insert into the DB....
        $success = false; // we failed :-(;
        $errors[] = $sql2 . "<br />" . mysql_error();
        break; // throw in the towel, we are done here.
    } else { // it worked after all :-)
        $mission_id = mysql_insert_id(); // what is our fancy new id number?
        
        /************************ SET THE MISSION UP FOR THE USERS ************************/
        // sign up the kids
        foreach ($children as $user_id) { // for each kid that we are adding this mission to...
            $user_defaults = new Defaults($user_id); // load up the defaults class...
            $user_defaults->addOn($mission_id, 'mission'); // and add this mission...
        }
        
        /************************ CREATE MISSION ************************/
        // create the task
        $taskSQL = "INSERT INTO date_tasks SET "
                    ."date_tasks_mission_id = " . $mission_id . ", "
                    ."short_name = \"" . $short . "\", "
                    ."name = \"" . $task . "\", "
                    ."points = $points, "
                    ."cat = \"" . $category . "\", "
                    ."mission_marking = 1, "
                    ."default_on = 0, "
                    ."mandatory_qty = 0, "
                    ."optional_qty = 1";
        // if a label was selected
        if ($label_id) {
            $taskSQL .= ", label_id = " . $label_id; // add the label id
            if ($daily) { // if the label is a daily label...
                $taskSQL .= ", daily_task = 1, needed = 5"; // set it to daily and 5/7 required...
            } // end if daily
        } // end if labelID
    } // end if the mission_sql even worked in the first place...
    
    /************************ CREATE TASK ************************/
    if (!mysql_query($taskSQL)) { // so close, we just can't add the task...
        $success = false; // we failed :-(
        $errors[] = $taskSQL . "<br />" . mysql_error(); // log our mistake...
        break; // and jump on the railroad tracks.
    } else { // if everything turned out alright...
        $taskID = mysql_insert_id(); // lets get our new ID from the DMV
        foreach ($children as $userID) { // and set it up for all of the kids
            $user_defaults = new Defaults($userID);
            $user_defaults->addOn($taskID, 'task'); // add the task to the kid. see class.defaults.php in the public_html folder.
        } // end registering each kid.
        
        /************************ SET LABEL ORDER ************************/
        if ($label_id) { // if a label id was provided (it will be, client side validation is unbreakable right?)
            //figure out what the label order should be
            $label_ord_query = mysql_query("SELECT * FROM date_tasks_missions JOIN date_tasks USING (date_tasks_mission_id) WHERE date_task_id = " . $taskID);
            //echo $qry . "<br />";
            $label_ord_result = mysql_fetch_assoc($label_ord_query);
            $subject = $label_ord_result['subject_id'];
            $school_type = $label_ord_result['school_type_id'];
            $level = $label_ord_result['level'];
            
            $labelQry = "SELECT dt.label_ord FROM date_tasks dt " // get the label order from the date_tasks_table
                        ."JOIN date_tasks_missions dtm USING (date_tasks_mission_id) " // join with the date_tasks_missions table
                        ."WHERE dtm.school_type_id = $school_type " // where the school type matches the school type that we are adding the mission for
                        ."AND dtm.level = $level AND dtm.subject_id = $subject " // and the level/subject match up
                        ."AND dt.label_id = $label_id " // and the label id matches the one that we where sent in
                        ."ORDER BY dt.label_ord DESC LIMIT 1"; // get the highest label order
            //echo $labelQry . "<br />";
            $labelRes = mysql_query($labelQry); // get the highest label order
            if (mysql_num_rows($labelRes) > 0) { // if we have an existing order....
                $labelRow = mysql_fetch_assoc($labelRes); // get the order...
                $labelOrd = $labelRow['label_ord']; // and set our order to match
            } else { // if it was not set
                $labelOrd = 0; // then the order is 0
            }
            $labelOrd++; // add one (so it will be 1 or max+1);
            mysql_query("UPDATE date_tasks SET label_ord = $labelOrd WHERE date_task_id = $taskID"); // and update the date_task to be in the correct order.
        }
    }
}

/************************ FINISH TRANSACTION ************************/
if ($success) {
    mysql_query('commit'); // we did it. now lets save what we just did :-)
} else {
    mysql_query('rollback'); // something went wrong. dont do anything...
} 
mysql_query('set autocommit=1'); // turn autocommit back on....

/************************ RETURN THE RESULTS TO THE USER ************************/
if (count($errors) > 0) {
    echo json_encode(["success" => false, "errors" => $errors, "error_message" => "It appears that there was an error while creating your task"]);
} else {
    echo json_encode(["success" => true, "errors" => $errors]);
}