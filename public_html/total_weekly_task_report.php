<?

header("Location: https://mashpia.com/yearly_prize/reports/eligible_students.php?type=summary"); // redirect to the new page
die();

$admin_auth = array('school'); 
require('header.php');
// enable debuging
if ($_GET['debug']) {
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
    $debug = true;
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<HTML>

    <HEAD>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Total Weekly Report</title>
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <style type='text/css'>
            table {
                font-size: 12px;
            }
            th, td {
                padding: 3px 10px;
            }
        </style>
    </HEAD>

    <BODY>
        <? include('admin_header.php'); ?>
        <h1>Total Weekly Task Report</h1>
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
        
        $totalWeeklyTasks = new TotalWeeklyTasks(0, unixtojd());
        
        // update start and end dates if we are in debug mode
        if($debug && $_GET['end'])
            $totalWeeklyTasks = new TotalWeeklyTasks(0, $_GET['end']);
        
        if ($debug && $_GET['start'])
            $totalWeeklyTasks->start_date = $_GET['start'];
        
        $totalWeeklyTasks->get_week_dates();
        
        if ($debug){
            echo "<pre>";
            print_r($totalWeeklyTasks);
            echo "</pre>";
        }
        
        foreach ( $schoolsUsers as $school => $users ) {
            echo "<h2>" . $schools[$school] . "</h2>";
            echo "<table>";
            echo "<tr><th>Grade</th><th>Student</th><th>Weeks Marked</th></tr>";
            foreach ( $users as $user ) {
                $totalWeeklyTasks->user_id = $user['user_id'];
                echo "<tr><td>" . $user['class_grade'] . ( empty( $user['class_sub']) ? '' : "-" . $user['class_sub'] ) . 
                    "</td><td>" . $user['first'] . " " . $user['last'] .
                    "</td><td>" . $totalWeeklyTasks->total_weeks_with_task() . "/" . count($totalWeeklyTasks->week_dates) . "</td></tr>"; 
            }
            echo "</table><br />";
        }
        ?>
    </body>
</html>