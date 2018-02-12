<?php $debug = false;
// enable debuging
if ($_GET['debug']) {
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
    $debug = true; // set debug to true
}
/********************** AUTHENTICATION ******************/
$admin_auth = array('school');
require 'header.php';

/********************** IMPORTS ******************/
require_once 'class.adminSchools.php';
require_once 'class.schoolsUsers.php';    

/********************** GET DATES FOR THE REPORT ******************/
function get_dates(){
    $starting_date = 2458076;
    $dates_query = mysql_query("SELECT mission_description, start_date" // get the name of the month as well as the date
                               ." FROM date_tasks dt JOIN date_tasks_missions dtm USING (date_tasks_mission_id)" // from the date tasks and date tasks missoins table
                               ." WHERE dt.grid_id = 8001 AND start_date >= $starting_date GROUP BY start_date"); // where it is a shabbos mevorchim tehillim report and after kislev 5778
    // load the dates from the DBS
    $dates = [];
    while($date = mysql_fetch_assoc($dates_query)){
        $dates[$date['start_date']] = $date['mission_description'];
    }
    
    return $dates;
}

$dates = get_dates();

/********************** GET DATE FROM POST PARAMS IF SENT ******************/
if(isset($_POST['date']) && isset($dates[$_POST['date']])){
    $date = mysql_real_escape_string($_POST['date']);
} else {
    $date = 2458076;
}

/********************** GET SCHOOL_ID FROM POST PARAMS (IF SENT) ******************/
$tehillim_school_id = isset($_POST['school_id']) ? mysql_real_escape_string($_POST['school_id']) : false;

$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
$schools = $as->getSchools();

if(!$tehillim_school_id && count($schools) == 1){
    $tehillim_school_id = array_keys($schools)[0];
}

//print_r(["date" => $date, "school_id" => $tehillim_school_id, "schools" => $schools[$tehillim_school_id]]);

?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <title>Tehillim Quotas | Mashpia.com</title>
        <link href="/styles/admin/grey_select.css" rel="stylesheet" type="text/css"/>
        <link href="/styles/admin/fancy-checkbox.css" rel="stylesheet" type="text/css"/>
        <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
        <style type='text/css'>
            table {font-size: 12px; width: 100%;}
            th, td {padding: 5px;}
            .options{text-align: center;margin: 10px;}
            p.error{text-align: center;margin-top: 25px;color: red;}
        </style>
    </head>
    
    <body>
        <?php include('admin_header.php'); // this script sets $school_id to -1 for some reason?>
        <h1>Tehillim Quotas</h1>
        
        <h2>Report Options</h2>
        <form method="POST" action="#">
            <? if(count($schools) > 1) { ?>
                <div class="options">
                    <div class="row">
                        <i class="fa fa-university" aria-hidden="true"></i> School: 
                        <select id="school_id" name="school_id">
                            <option value="" selected disabled>Please Select A School</option>
                            <? foreach($schools as $option_school_id => $school_name){?>
                                <option value="<?=$option_school_id?>" <?= $option_school_id == $tehillim_school_id ? "selected" : "";?>><?=$school_name?></option>
                            <?}?>
                        </select>
                    </div>
                </div>
            <? } ?>
            <div class="options">
                <div class="row">
                    <i class="fa fa-calendar" aria-hidden="true"></i> Shabbos Mevorchim: 
                    <select id="date" name="date">
                        <? foreach($dates as $option_date => $option_name){?>
                            <option value="<?=$option_date?>" <?=$date == $option_date ? "selected" : "";?>><?=$option_name?></option>
                        <?}?>
                    </select>
                </div>
            </div>
            <div class="options">
                <input type="submit" class="button" value="Genrate Report"/>
            </div>
        </form>
        
        <? if(!$tehillim_school_id) { ?>
        <p class="error">
            <strong>Please Select a School To Generate The Report</strong>
        </p>
        <? } else {
            
            $schoolsUsers = array(); // array to hold the students in
            $s = new SchoolsUsers( $tehillim_school_id );
            $schoolsUsers[$tehillim_school_id] = $s->getUsers(true, true);
            
            foreach ( $schoolsUsers as $school => $users ) {
                echo "<h2>" . $schools[$tehillim_school_id] . "</h2>";
                echo "<table>";
                echo "<tr><th>Grade</th><th>First Name</th><th>Last Name</th><th>Quota</th></tr>";
                foreach ( $users as $user ) {
                    // get level, track_id
                    $sql = "select * from user_tracks where subject_id = 1 and user_id = " . $user['user_id'];
                    $result = mysql_query( $sql );
                    $row = mysql_fetch_assoc( $result );
                    
                    // get quota
                    $sql2 = "select dt.quantity from date_tasks dt
                            join date_tasks_missions dtm using (date_tasks_mission_id) 
                            where dt.grid_id = 8001
                            and dtm.start_date = " . $date . " 
                            and dtm.end_date = " . $date . " 
                            and dtm.school_type_id = " . $user['school_type_id'] . " 
                            and dtm.track_id = " . $row['track_id'] . " 
                            and dtm.level = " . $row['level'] . " 
                            and dtm.lang_id = " . $user['lang_id'];
                    //echo $sql . "<br /><br />";
                    $result2 = mysql_query( $sql2 );
                    $row2 = mysql_fetch_assoc( $result2 );
                    echo "<tr><td>" . $user['class_grade'] . (empty($user['class_sub']) ? '' : '-' . $user['class_sub']) . "</td><td>" .
                        $user['first'] . "</td><td>" . $user['last'] . "</td><td>" . $row2['quantity'] . "</td></tr>";
                }
                echo "</table>";
            }
            ?>
        <? } // end if there is a school id?>
    </body>
</html>