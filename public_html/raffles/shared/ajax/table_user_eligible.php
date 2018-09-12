<?
// enable debugging
if ($_GET['debug']) {
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
    $debug = true;
}
// enable test mode
$report_mode = false;
if ($_POST['type'] == "report") {
    $report_mode = true;
}

/***************** AUTHENTICATION **********************/
$admin_auth = array('school'); 
require_once $_SERVER["DOCUMENT_ROOT"].'/header.php';

/***************** IMPORTS **********************/
require_once(dirname(__FILE__).'/../classes/Raffle.php');
// namespace fixing
use raffles\weekly\Raffle as Raffle; // use the raffle from its namespace
// load the required classes
require_once $_SERVER["DOCUMENT_ROOT"].'/class.adminSchools.php';
require_once $_SERVER["DOCUMENT_ROOT"].'/class.schoolsUsers.php';

/***************** LOAD THE DATA **********************/
$schools = [];
// get all the schools (should only return 1)
if(!$_POST['school_id']){
    $as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
    $schools = $as->getSchools();
} else {
    $school_id = mysql_real_escape_string($_POST['school_id']);
    $name_query = mysql_query("SELECT school_name FROM schools WHERE school_id=$school_id");
    $school_name = mysql_fetch_assoc($name_query)['school_name'];
    $schools[$school_id] = $school_name;
}
// get the users for each school
$schoolsUsers = array();
// for each school get its users
foreach ( $schools as $id => $school ) {
    $s = new SchoolsUsers( $id );
    $schoolsUsers[$id] = $s->getUsers(false, false);
}
// get all the raffles (limit here for the different months)
$raffle = Raffle::load($_POST['raffle_id']);

/***************** RENDER THE FORM **********************/
// for each school
foreach ( $schoolsUsers as $school => $users ) {
    echo "<h2>" . $schools[$school] . "</h2>"; // show the school title
    echo "<div id='table-marks'>"; // allow for the table to scroll in a div
    echo "<table>"; // start the table
    echo "<tr><th>Grade</th><th>Student</th>"; // start the header
    echo "<th>" . $raffle->name . "</th>"; // show the raffle name
    echo "</tr>"; // end the top row
    foreach ( $users as $user ) { // for each student
        // print the user's name and grade
        echo "<tr><td>" . $user['class_grade'] . ( empty( $user['class_sub']) ? '' : "-" . $user['class_sub'] ) . 
            "</td><td>" . $user['first'] . " " . $user['last'];
        if( $report_mode ){
            $user_ids = $raffle->get_eligable_user_ids($user['user_id']); // in test mode run the full raffle check
            echo "<td class='". ( isset($user_ids[$user['user_id']]) ? "green'>✓" : "red'>x" ) ."</td>"; // determine the class and content
        } else {
            $user_ids = $raffle->get_raffle_eligable_user_ids($user['user_id']); // pass the school id in to only get the students that we need
            echo "</td><td><input class='eligible-toggle' type='checkbox' name='" .
                $user['user_id'] . ":" . $raffle->raffle_id .
                "' " . ( isset($user_ids[$user['user_id']]) ? "checked" : "" ) .
                "/></td>"; // render a checkbox with the correct params and set it to checked if it has a task
        }
        
        // go to the next user
        echo "</tr>"; 
    } // end for each student
    echo "</table></div><br />"; // end the table
}// end for each school
?>