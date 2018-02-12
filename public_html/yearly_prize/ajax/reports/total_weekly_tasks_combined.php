<?
// enable debuging
if ($_GET['debug']) {
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
    $debug = true;
    echo "<pre>";
}

/***************** AUTHENTICATION **********************/
$admin_auth = array('school'); 
require_once($_SERVER["DOCUMENT_ROOT"].'/header.php');

/***************** SANITIZE THE INPUTS **********************/
$end_date = $_POST['end'];
$start_date = $_POST['start'];
$school_id = $_POST['school_id'];
$class_grade = $_POST['class_grade'];

require_once $_SERVER["DOCUMENT_ROOT"].'/class.adminSchools.php';
require_once $_SERVER["DOCUMENT_ROOT"].'/class.schoolsUsers.php';
require_once dirname(__FILE__).'/../../classes/TotalWeeklyTasks.php';
require_once dirname(__FILE__).'/../../functions/get_parsha_names.php';

// if there was no school id
if(!$school_id) { 
    $as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
    $schools = $as->getSchools();
} else {
    $school_name_sql = "SELECT school_name FROM schools WHERE school_id='$school_id';";
    $row = mysql_fetch_assoc(mysql_query($school_name_sql));
    $schools = [$school_id => $row['school_name']]; // set the school to the one selected
}

// populate the school users array
$schoolsUsers = array();
foreach ( $schools as $id => $school ) {
    $s = new SchoolsUsers( $id );
    $schoolsUsers[$id] = $s->getUsers(true, true);
}
// create the object
$totalWeeklyTasks = new TotalWeeklyTasks(0, $end_date);
// set the start date
$totalWeeklyTasks->start_date = $start_date;
// generate the week_dates
$totalWeeklyTasks->get_week_dates();

// calculate the end_date for the last week and use that to get the parsha names
$last_start_date = $totalWeeklyTasks->week_dates[count($totalWeeklyTasks->week_dates) - 1]['start']; // used to set the defaults in the dropdown
$end_date = $totalWeeklyTasks->week_dates[count($totalWeeklyTasks->week_dates) - 1]['end'];
$parshos = get_parsha_names($totalWeeklyTasks->start_date, $end_date);

if($debug) echo "</pre>";

// for each school
foreach ( $schoolsUsers as $school => $users ) {?>
    <h2><?=$schools[$school]?></h2>
    <div id='table-marks'>
        <table>
            <thead> 
                <tr>
                    <th>Grade</th><th>Student</th>
                    <?foreach($totalWeeklyTasks->week_dates as $week){ // render each parsha in the top row ?>
                        <th><?=$parshos[$week['start']]?></th>
                    <?}?>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?foreach ( $users as $user ) { // for each user
                    $user_class_full = $user['class_grade'] . ( empty( $user['class_sub']) ? '' : "-" . $user['class_sub'] );
                    // if the class is passed in, make sure the user is part of the class before loading the form
                    if($class_grade && !($class_grade == $user['class_grade'] || $class_grade == $user['class_id'])) continue;
                    $totalWeeklyTasks->user_id = $user['user_id']; // reset the user ID on the cached $totalWeeklyTasks
                    // print the user's name and grade?>
                    <tr>
                        <td class="wide"><?=$user_class_full?></td>
                        <td class="wide"><?=$user['first'] . " " . $user['last']?></td>
                        <?$count = 0;   // count the total number of weekly tasks
                        foreach($totalWeeklyTasks->week_dates as $week){// go through all the weeks?>
                            <td>
                                <label class="fancy-check-container">
                                    <input class='week-toggle' type='checkbox' name='<?=$user['user_id'] . ":" . $week['start'] . ":" . $week['end']?>'
                                        <? if($totalWeeklyTasks->week_has_task($week['start'], $week['end'])){
                                            echo "checked"; $count++;
                                        } // check if it is marked?> />
                                    <span class="fancy-check"></span>
                                </label>
                            </td>
                        <?} // end for each week and go to the next user?>
                        <td id="total_<?=$user['user_id']?>"><?=$count . "/" . count($totalWeeklyTasks->week_dates)?></td>
                    </tr>
                <?}?>
            </tbody>
        </table>
    </div><br />
<?}// end the loop for each school?>