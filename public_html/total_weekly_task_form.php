<?

header("Location: https://mashpia.com/yearly_prize/reports/eligible_students.php?type=form"); // redirect to the new page
die();

$admin_auth = array('school'); 
require('header.php');
// enable debuging
if ($_GET['debug']) {
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
    $debug = true;
}

function get_parsha_names($start, $end) {
    $result = [];
    
    $sql = "SELECT * FROM parshos WHERE start >= $start AND end <= $end";
    $query = mysql_query($sql);
    while($row = mysql_fetch_assoc($query)){
        $result[$row['start']] = $row['name'];
    }
    return $result;
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<HTML>

    <HEAD>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Total Weekly Report Form</title>
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <style type='text/css'>
            table {
                font-size: 12px;
            }
            tr {
                border-bottom: 1px solid #aaa;
            }
            th, td {
                padding: 3px 10px;
                white-space: nowrap;
            }
        </style>
    </HEAD>

    <BODY>
        <? include('admin_header.php'); ?>
        <h1>Total Weekly Task Report Form</h1>
        <? 
        require_once 'class.adminSchools.php';
        require_once 'class.schoolsUsers.php';
        require_once('yearly_prize/class.totalWeeklyTasks.php'); 
       
        $as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
        $schools = $as->getSchools();
        $schoolsUsers = array();
        
        foreach ( $schools as $id => $school ) {
            $s = new SchoolsUsers( $id );
            $schoolsUsers[$id] = $s->getUsers(true, true);
        }
        
        echo "<h2>Total Weeks with missions marked since October 20, 2017</h2>";
        
        if($debug && $_GET['end']){
            $totalWeeklyTasks = new TotalWeeklyTasks(0, $_GET['end']); // if we are in debug mode then move to the selected end_date
        } else { // no debug mode
            $totalWeeklyTasks = new TotalWeeklyTasks(0, unixtojd());
        }
        // set the start date in debugging mode
        if($debug && $_GET['start']){
            $totalWeeklyTasks->start_date = $_GET['start'];
        }
        // generate the weekly tasks
        $totalWeeklyTasks->get_week_dates();
        // calculate the end_date for the last week and use that to get the parsha names
        $end_date = $totalWeeklyTasks->week_dates[count($totalWeeklyTasks->week_dates) - 1]['end'];
        $parshos = get_parsha_names($totalWeeklyTasks->start_date, $end_date);
        // echo some debugging info
        if($debug){
            echo "<pre>";
            print_r($totalWeeklyTasks);
            print_r($parshos);
            echo "</pre>";
        }
        // for each school
        foreach ( $schoolsUsers as $school => $users ) {
            echo "<h2>" . $schools[$school] . "</h2>"; // show the school title
            echo "<div id='table-marks'>"; // allow for the table to scroll in a div
            echo "<table>"; // start the table
            echo "<tr><th>Grade</th><th>Student</th>"; // start the header
            foreach($totalWeeklyTasks->week_dates as $week){ // render each parsha in the top row
                echo "<th>" . $parshos[$week['start']] . "</th>";
            }
            echo "</tr>";
            foreach ( $users as $user ) { // for each user
                $totalWeeklyTasks->user_id = $user['user_id']; // reset the user ID on the cached $totalWeeklyTasks
                // print the user's name and grade
                echo "<tr><td>" . $user['class_grade'] . ( empty( $user['class_sub']) ? '' : "-" . $user['class_sub'] ) . 
                    "</td><td>" . $user['first'] . " " . $user['last'];
                // print each weeks result
                foreach($totalWeeklyTasks->week_dates as $week){ // go through all the weeks
                    echo "</td><td><input class='week-toggle' type='checkbox' name='" .
                        $user['user_id'] . ":" . $week['start'] . ":" . $week['end'] .
                        "' " . ($totalWeeklyTasks->week_has_task_sql($week['start'], $week['end']) ? "checked" : "" ) .
                        "/></td>"; // render a checkbox with the correct params and set it to checked if it has a task
                }
                // go to the next user
                echo "</tr>"; 
            }
        echo "</table></div><br />";
        }
        ?>
        <script src="js/admin/total_weekly_task_form.php.js"></script>
    </body>
</html>