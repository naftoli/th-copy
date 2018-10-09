<?php
$debug = false;
/***************** DEBUGGING **********************/
$_POST['debug'] = true;
// enable debuging
if ($_POST['debug']) {
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
    $debug = true; // set debug to true
    echo "<pre>";
}

/***************** AUTHENTICATION **********************/
$admin_auth = array('school'); 
require_once($_SERVER["DOCUMENT_ROOT"].'/header.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/api/header/db.php');

// get schools connected to account
require_once($_SERVER["DOCUMENT_ROOT"].'/class.adminSchools.php');
$as = new adminSchools( $admin_user['admin_id'], $admin_user['auth'] );
$schools = $as->getSchools();

//***************** LOAD CURRENT YEAR **********************/
require_once($_SERVER['DOCUMENT_ROOT']."/class.globalSettings.php");
$year = GlobalSettings::getRegistrationYear();

// find number of ppl that each child shook lulav and esrog with
$grid_id = 9034;
$dates = [
    [
        'start' =>  2458390,
        'end'  =>  2458392
    ], 
    [
        'start' =>  2458386,
        'end'   =>  2458389
    ]
];

$sql = "select s.school_name, c.class_grade, c.class_sub, u.first, u.last, u.user_id, dtm.done_qty   
        from users u  
        join schools s on s.school_id = u.school_id 
        join classes c on c.class_id = u.class_id 
        where u.user_registered > 0 
        and u.school_id in (" . implode(',', array_keys($schools)) . ") 
        order by school_name, class_grade, class_sub, last, first";
$result = $MASHPIA_DB->query( $sql );
$info = $result->fetchAll();
//echo "<pre>"; print_r( $info ); echo "</pre>"; exit;
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <style>
            @media print {
               .noPrint {
                   display: none;
                }
            }
            body {
                font-family: Verdana;
            }
            tr, th, td {
                font-size: 14px;
                padding-top: 10px;
                padding-bottom: 10px;
                font-family: Verdana;
            }
        </style>
    </head>
    <body>
        <table>
            <thead>
                <tr>
                    <th>School</th>
                    <th>Grade</th>
                    <th>Sub</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th># PPL shook Lulav Week 1</th>
                    <th># PPL shook Lulav Week 2</th>
                    <th>Total #</th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ( $info as $row ) {
                    echo "<tr><td>" . $row['school_name'] 
                        ."</td><td>" . $row['class_grade'] 
                        ."</td><td>" . $row['class_sub'] 
                        ."</td><td>" . $row['first'] 
                        ."</td><td>" . $row['last'] 
                        ."</td>";
                    $total = 0;
                    // find out mivtza lulav numbers
                    foreach ( $dates as $date ) {
                        $sql = "select done_qty from date_tasks_marks dtm 
                                join date_tasks dt using (date_task_id) 
                                join date_tasks_missions dtmm using (date_tasks_mission_id) 
                                where dtmm.start_date = :start 
                                and dtmm.end_date = :end  
                                and dt.grid_id = :id 
                                and dtm.user_id = :user_id";
                        $sth = $MASHPIA_DB->prepare( $sql );
                        $sth->execute([
                            ':start'    =>  $date['start'], 
                            ':end'      =>  $date['end'], 
                            ':id'       =>  $grid_id,
                            ':user_id'  =>  $row['user_id']
                        ]);
                        if ( $sth->rowCount() ) {
                            $done = $sth->fetch( PDO::FETCH_ASSOC );
                            echo "<td>" . $done['done_qty'] . "</td>";
                            $total += $done['done_qty'];
                        } else {
                            echo "<td>0</td>";
                        }
                    } 
                    echo "<td>" . $total . "</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </body>
</html>