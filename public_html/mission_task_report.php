<?php
ini_set('max_execution_time', 300);
ini_set('display_errors', 1);
$admin_auth = array('school'); 
require('header.php');

$users = array();
if (isset($_GET['id'])) {
    require_once 'class.adminSchools.php';       
    $as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
    $schools = $as->getSchools();
    
    $auction_id = $_GET['id'];
    $auctionInfo = array();
    $sql = "select * from auctions where auction_id in (" . ($auction_id-1) . "," . $auction_id . ")";
    $result = mysql_query($sql);
    while ($row = mysql_fetch_assoc($result)) {
        $auctionInfo[$row['auction_id']] = $row['auction_date'];
    }
    $start = $auctionInfo[$auction_id-1] + 1;
    $end = $auctionInfo[$auction_id];
    //echo "Start: " . $start . ", End: " . $end; exit;
    
    $sql = "select u.user_id, u.first, u.last, c.class_grade, c.class_sub, s.school_name  
            from users u
            join schools s on s.school_id = u.school_id 
            join classes c on c.class_id = u.class_id 
            where u.user_registered > 0 
            and u.school_id in (" . implode(',', array_keys($schools)) . ")
            order by school_name, class_grade, class_sub, last, first";
    //echo $sql . "<br />";
    $result = mysql_query($sql);
    while ($row = mysql_fetch_assoc($result)) {
        // find out if user won this year
        $sqlWon = "select * from auction_winners where auction_id >= 71 and user_id = " . $row['user_id'];
        $resWon = mysql_query($sqlWon);
        if (mysql_num_rows($resWon) > 0) continue;
        $users[] = $row;
    }
    
    $info = array();
    foreach ($users as $student) {
        $sql = "select count(*) as tasks from date_tasks_marks
                where mark_date >= " . $start . "
                and mark_date <= " . $end . "
                and user_id = " . $student['user_id'];
        $result = mysql_query($sql);
        $row = mysql_fetch_assoc($result);
        $info[$student['user_id']]['tasks'] = $row['tasks'];
        
        $sql = "SELECT SUM( mission_count ) as missions from date_tasks_mission_marks 
                where mark_date >= " . $start . "
                and mark_date <= " . $end . "
                and user_id = " . $student['user_id'];
        $result = mysql_query($sql);
        $row = mysql_fetch_assoc($result);
        $info[$student['user_id']]['missions'] = $row['missions'];
    }
}
//echo "<pre>"; print_r($info); echo "</pre>";
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Task / Mission Report</title>
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <style type='text/css'>
            table {
                font-size: 12px;
            }
            th, td {
                padding: 3px 10px;
            }
        </style>
    </head>
    
    <body>
        <?php include('admin_header.php'); ?>
        <h1>Task / Mission Report</h1>
        
        <?php
        echo "<table><tr><th>School</th><th>Grade</th><th>First Name</th><th>Last Name</th><th>Missions</th><th>Tasks</th><th>User ID</th></tr>";
        foreach ($users as $student) {
            $grade = $student['class_grade'] . ($student['class_sub'] ? '-' . $student['class_sub'] : '');
            echo "<tr><td>" . $student['school_name'] . "</td><td>" . $grade . "</td><td>" . $student['first'] . "</td><td>" .
                $student['last'] . "</td><td>" . $info[$student['user_id']]['missions'] . "</td><td>" . $info[$student['user_id']]['tasks'] .
                "</td><td>" . $student['user_id'] . "</td></tr>";
        }
        ?>
    </body>
</html>