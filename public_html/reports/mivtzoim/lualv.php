<?php
$debug = false;
/***************** DEBUGGING **********************/
// enable debuging
if ( isset( $_GET['debug'] ) ) {
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
        'start' =>  2458386,
        'end'   =>  2458389
    ],
    [
        'start' =>  2458390,
        'end'  =>  2458392
    ]
];

$sql = "SELECT s.school_name, c.class_grade, c.class_sub, u.user_serial, u.first, u.last, u.user_id
        from users u  
        join schools s on s.school_id = u.school_id 
        join classes c on c.class_id = u.class_id 
        where u.user_registered > 0 
        and u.school_id in (" . implode(',', array_keys($schools)) . ") 
        order by school_name, class_grade, class_sub, last, first";

$result = $MASHPIA_DB->query( $sql );
$info = $result->fetchAll();
//echo "<pre>"; print_r( $info ); echo "</pre>"; exit;

$lulav_query = $MASHPIA_DB->prepare(
    "SELECT user_id, start_date, done_qty "
    ."FROM date_tasks_marks dtm "
    ."JOIN date_tasks dt USING (date_task_id) "
    ."JOIN date_tasks_missions dtmm USING (date_tasks_mission_id) "
    ."WHERE dtmm.start_date >= :start_date AND dtmm.end_date <= :end_date "
    ."AND dt.grid_id = :grid_id GROUP BY user_id, start_date"
);

$lulav_query->execute([
    ':start_date' => $dates[0]['start'],
    ':end_date' => $dates[1]['end'],
    ':grid_id' => $grid_id
]);

$lulav_marks = [];
while( $mark = $lulav_query->fetch() ) {
    $lulav_marks[ $mark['user_id'] ][ $mark['start_date'] ] = $mark['done_qty'];
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
        <style>
            @media print {
               .noPrint {
                   display: none;
                }
            }
            body {
                font-family: Verdana;
            }
        </style>
    </head>
    <body>
        <table class="table table-sm table-striped">
            <thead>
                <tr>
                    <th>Base</th>
                    <th>Platoon</th>
                    <th>Serial Number</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th># PPL shook Lulav Week 1</th>
                    <th># PPL shook Lulav Week 2</th>
                    <th>Total #</th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ( $info as $row ) { ?>
                    <tr>
                        <td> <?= $row['school_name'] ?></td>
                        <td> <?= Platoon::generateName( $row['class_grade'], $row['class_sub'] ) ?></td>
                        <td> <?= $row['user_serial'] ?></td>
                        <td> <?= $row['first'] ?></td>
                        <td> <?= $row['last'] ?></td>
                <?php
                    $total = 0;
                    // find out mivtza lulav numbers
                    foreach ( $dates as $date ) {
                        $mark = 0;
                        if ( isset( $lulav_marks[ $row['user_id'] ][ $date['start'] ] ) )
                            $mark = $lulav_marks[ $row['user_id'] ][ $date['start'] ];

                        $total += $mark;
                        echo "<td>".number_format( $mark )."</td>";
                    } 
                    echo "<td>" . number_format( $total ) . "</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </body>
</html>
