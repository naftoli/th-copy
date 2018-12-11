<?
$admin_auth = array('school'); 
require('header.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<HTML>

    <HEAD>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Extra Points Report</title>
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
        <h1>Extra Points Report</h1>
        <? 
        require_once 'class.adminSchools.php';
        require_once 'class.schoolsUsers.php';         
       
        $as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
        $schools = $as->getSchools();
        $schoolsUsers = array();
        
        foreach ( $schools as $id => $school ) {
            $s = new SchoolsUsers( $id );
            $schoolsUsers[$id] = $s->getUsers(true, true);
        }
        
        foreach ( $schoolsUsers as $school => $users ) {
            // get points info
            $points = array();
            $sql = "select user_id, sum(mark_points) as points 
                    from date_tasks_marks dtm 
                    join date_tasks dt using (date_task_id) 
                    join date_tasks_missions dtmm using (date_tasks_mission_id) 
                    where dtm.user_id in (select user_id from users where school_id = " . $school . " and user_registered > 0) 
                    and dtm.mark_date >= 2457934  
                    and dtmm.personal = 1 
                    group by dtm.user_id";
            $result = mysql_query($sql);
            while ($row = mysql_fetch_assoc($result)) {
                $points[$row['user_id']] = $row['points'];
            }
            
            echo "<h2>" . $schools[$school] . "</h2>";
            echo "<table>";
            echo "<tr><th>Grade</th><th>Student</th><th>Extra Points</th></tr>";
            foreach ( $users as $user ) {
                echo "<tr><td>" . $user['class_grade'] . ( empty( $user['class_sub']) ? '' : "-" . $user['class_sub'] ) . 
                    "</td><td>" . $user['first'] . " " . $user['last'] . "</td><td>";
                    if (isset($points[$user['user_id']])) {
                        echo $points[$user['user_id']];
                    }
                    echo "</td></tr>"; 
            }
            echo "</table><br />";
        }
        ?>
    </body>
</html>