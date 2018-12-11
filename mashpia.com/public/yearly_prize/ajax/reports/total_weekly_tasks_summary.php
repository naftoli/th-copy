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

require_once dirname(__FILE__).'/../../functions/get_distributed_marks.php';

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

// get the distributed marks and the shipped marks
$distributed_marks = get_distributed_marks($school_id);
if($debug) print_r($distributed_marks);

if($debug) echo "</pre>";

foreach ( $schoolsUsers as $school => $users ) {?>
    <h2><?=$schools[$school]?></h2>
    <table>
        <tr>
            <th>Grade</th><th>Student</th><th>Weeks Marked</th><th>Distributed</th>
        </tr>
        <?foreach ( $users as $user ) {
            $user_class_full = $user['class_grade'] . ( empty( $user['class_sub']) ? '' : "-" . $user['class_sub'] );
            $shipped = isset($distributed_marks["user"][$user['user_id']]);
            $distributed = $shipped ? $distributed_marks["user"][$user['user_id']] : false;
            // if the class is passed in, make sure the user is part of the class before loading the form
            if($class_grade && !($class_grade == $user['class_grade'] || $class_grade == $user['class_id'])) continue;
            $totalWeeklyTasks->user_id = $user['user_id'];?>
            <tr>
                <td><?=$user_class_full?></td>
                <td><?=$user['first'] . " " . $user['last']?></td>
                <td><?=$totalWeeklyTasks->total_weeks_with_task() . "/" . count($totalWeeklyTasks->week_dates)?></td>
                <td>
                    <? if($shipped) {?>
                    <label class="fancy-check-container">
                        <input type="checkbox" class="distributed_mark" data-id="<?=$user['user_id']?>"
                            <?=$distributed ? "checked" : ""?>/>
                        <span class="fancy-check"></span>
                    </label>
                    <?} else {?>
                    Not Shipped Yet
                    <?}?>
                </td>
            </tr>
        <?} // end for each student?>
    </table><br />    
<?}?>