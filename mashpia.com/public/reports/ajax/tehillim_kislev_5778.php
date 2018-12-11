<?php
/***************** DEBUGGING **********************/
// enable debuging
if ($_GET['debug']) {
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
    $debug = true; // set debug to true
    echo "<pre>";
}

/***************** AUTHENTICATION **********************/
$admin_auth = array('school'); 
require_once($_SERVER["DOCUMENT_ROOT"].'/header.php');
// only superusers can use this page
//if ($admin_user['auth'] != 'super') {
//    echo "Sorry you don't have the privilege(s) necessary to view this page.";
//    exit;
//}

/***************** IMPORTS **********************/
require_once $_SERVER["DOCUMENT_ROOT"].'/class.adminSchools.php';
require_once $_SERVER["DOCUMENT_ROOT"].'/class.schoolsUsers.php';

/***************** LOAD USERS **********************/
if(!$_POST['school_id']){
    $as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
    $schools = $as->getSchools();
} else {
    $school_id = mysql_real_escape_string($_POST['school_id']);
    $sql = "SELECT school_name FROM schools WHERE school_id = $school_id";
    $schools = [$school_id => mysql_fetch_assoc(mysql_query($sql))['school_name']];
}

$schoolsUsers = array();
// for each school get its users
foreach ( $schools as $id => $school ) {
    $s = new SchoolsUsers( $id );
    $schoolsUsers[$id] = $s->getUsers(true, true);
}

/***************** GET THE CURRENT INFO FOR A GIVEN SCHOOL **********************/
function get_info($school_id, $mark_date = "2458076"){
    $info_sql = "SELECT dtm.user_id, done_qty, quantity, level, track_id FROM mashpiadb.date_tasks_marks dtm "
        ."JOIN users USING (user_id) JOIN user_tracks ut on dtm.user_id = ut.user_id and ut.subject_id = 1 JOIN date_tasks USING (date_task_id) "
        ."WHERE mark_date = $mark_date AND school_id = $school_id AND quantity IS NOT NULL AND grid_id = 8001;";
    $query = mysql_query($info_sql);
    $results = [];
    while($row = mysql_fetch_assoc($query)){
        $results[$row['user_id']][] = $row;
    }
    return $results;
}
/***************** GET THE "OLD" INFO FOR A GIVEN SCHOOL **********************/
function get_old_info($school_id, $mark_date = "2458076"){
    $old_info_sql = "SELECT dtm.user_id, done_qty FROM mashpia_backup.date_tasks_marks dtm "
        ."JOIN users USING (user_id) JOIN user_tracks ut on dtm.user_id = ut.user_id and ut.subject_id = 1 JOIN date_tasks USING (date_task_id) "
        ."WHERE mark_date = $mark_date AND school_id = $school_id AND quantity IS NOT NULL AND date_tasks.grid_id = 8001;";
    $old_info_query = mysql_query($old_info_sql);
    $old_info_results = [];
    while($old_info_row = mysql_fetch_assoc($old_info_query)){
        $old_info_results[$old_info_row['user_id']][] = $old_info_row;
    }
    return $old_info_results;
}
// old function to get the lower quota. moved to one query per school for performace
//function get_lower_quota($level, $track, $mark_date = "2458076"){
//    $level -= 1; // go down a level
//    $lower_quota_sql = "SELECT quantity FROM date_tasks dt JOIN date_tasks_missions dtm USING(date_tasks_mission_id) WHERE start_date=$mark_date AND grid_id=8001 AND subject_id = 1 AND quantity IS NOT NULL AND track_id=$track AND level=$level;";
//    return mysql_fetch_assoc(mysql_query($lower_quota_sql))['quantity'];
//}
// new get_lower_quota function. gets all the quotas for a school in one SQL query
function get_lower_quota_new($school_id, $mark_date = "2458076"){
    $lower_quota_sql = "SELECT user_id, first, last, ut.*, dtm.track_id, dtm.level, dt.quantity FROM users "
        ."JOIN user_tracks ut using(user_id) JOIN date_tasks_missions dtm on dtm.track_id=ut.track_id AND dtm.level=ut.level - 1 JOIN date_tasks dt using (date_tasks_mission_id) "
        ."WHERE school_id = $school_id AND ut.subject_id = 1 AND grid_id=8001 AND start_date = $mark_date GROUP BY users.user_id";
    $lower_quota_query = mysql_query($lower_quota_sql);
    $lower_quota = [];
    while($row = mysql_fetch_assoc($lower_quota_query)){
        $lower_quota[$row['user_id']] = $row['quantity'];
    }
    return $lower_quota;
}

function get_has_mission($school_id, $mark_date = "2458076"){
    $sql = "SELECT * FROM date_tasks_mission_marks JOIN date_tasks_missions USING (date_tasks_mission_id) "
        ."JOIN date_tasks USING (date_tasks_mission_id) JOIN users USING (user_id) "
        ."WHERE school_id = $school_id AND grid_id=8001 AND $mark_date = 2458076 GROUP BY users.user_id";
    $query = mysql_query($sql);
    $result = [];
    while($row = mysql_fetch_assoc($query)){
        $result[$row['user_id']] = true;
    }
    return $result;
}

function map_results($array, $key) {
    return implode(", ", array_map(function($entry) use ($key) { // join the array with a , based on the provided key
        return $entry[$key];
    }, $array));
}

foreach ( $schoolsUsers as $school => $users ) {
    if($debug) $start_time = time(); // get the time for performance debugging
    $current_info = get_info($school);
    if($debug) {echo "Current Info Runtime: ".(time() - $start_time)."s\n"; $start_time = time();} // log the time that it takes to run and reset the start time
    $old_info = get_old_info($school);
    if($debug) {echo "Old Info Runtime: ".(time() - $start_time)."s\n"; $start_time = time();} // log the time that it takes to run and reset the start time
    $quotas = get_lower_quota_new($school_id);
    if($debug) {echo "Quota Info Runtime: ".(time() - $start_time)."s\n"; $start_time = time();} // log the time that it takes to run and reset the start time
    $missions = get_has_mission($school_id);
    if($debug) {echo "Mission Info Runtime: ".(time() - $start_time)."s\n"; $start_time = time();} // log the time that it takes to run and reset the start time?>
    <h2><?=$schools[$school]?></h2>
    <table>
        <thead>
            <tr>
                <th>Name</th><th>Grade</th><th>Current Marks</th><th>Backup Marks</th><th>Current Quota</th><th>Lower Quota?</th><th>Got Mission</th>
            </tr>
        </thead>
        <tbody>
            <? foreach ( $users as $user ) { // for each student
                $user_current_info = isset($current_info[$user['user_id']]) ? $current_info[$user['user_id']] : null ; // get the users current info
                $user_old_info = isset($old_info[$user['user_id']]) ? $old_info[$user['user_id']] : null; // get his old info
                $quota = isset($quotas[$user['user_id']]) ? $quotas[$user['user_id']] : null; // get his quota
                $mission = isset($missions[$user['user_id']]) ? $missions[$user['user_id']] : false; // get if he has a mission marked
                if(!$user_current_info && ! $user_old_info) continue;?>
            <tr>
                <td><?=$user['first'] . " " . $user['last']?></td>
                <td><?=$user['class_grade'] . ( empty( $user['class_sub']) ? '' : "-" . $user['class_sub'] )?></td>
                <td><?=$user_current_info ? map_results($user_current_info, 'done_qty') : "N/A" ?></td>
                <td><?=$user_old_info ? map_results($user_old_info, 'done_qty') : "N/A" ?></td>
                <td><?=$user_current_info ? map_results($user_current_info, 'quantity') : "N/A" ?></td>
                <td><?=$quota ? $quota : "N/A";?></td>
                <td><?=$mission ? "✔" : "✘";?></td>
            </tr>
            <?}?>
        </tbody>
    </table>
<?}?>


