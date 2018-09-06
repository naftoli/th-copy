<?php
$admin_auth = array('school');
require_once 'header.php';
require_once 'class.adminSchools.php';
require_once 'class.schoolsUsers.php';         

$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
$schools = $as->getSchools();
$schoolsUsers = array();

foreach ( $schools as $id => $school ) {
    $s = new SchoolsUsers( $id );
    $schoolsUsers[$id] = $s->getUsers();
}
//echo "<pre>"; print_r( $schoolsUsers ); echo "</pre>"; exit;
// get current shabbos mevorchim
require 'class.globalSettings.php';
require_once 'class.shabbosMevorchim.php';

$year = GlobalSettings::getCurrentYear();
$sm = new ShabbosMevorchim( $year );
$sm->setReportDates();
$dates = $sm->getReportDates();
end( $dates );
$month = key( $dates );
$date = $dates[$month];
//echo "<pre>"; print_r( $dates ); echo "</pre>"; exit;
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf8" />
        <style>
            .card {
                float: left;
                padding-top: 150px;
                padding-left: 20px;
                padding-right: 50px;
                padding-bottom: 118px;
                background-image: url('wwtc1.jpg');
                background-repeat: no-repeat;
            }
            .groupBox {
                height: 15px;
                width: 15px;
                background-color: #eee;
            }
            @media print {
                .no-print {
                    display: none;
                }
            }
            .no-print {
                color: red;
                font-weight: bold;
            }
        </style>
    </head>

    <body>
    <p class="no-print">
        Please Note: When printing this page, please use Firefox, make sure to have the "Print Background" option clicked (in Page Setup) and scale to 95%
    </p>
    <?php 
    foreach ( $schoolsUsers as $school => $users ) {
        // we need to keep track of the cards 
        // so that we can move to new row after 2 cards 
        // and move to new page after 4 cards
        $cards = 0;
        foreach ( $users as $user ) {
            $name = $user['first'] . ' ' . $user['last'];
            $grade = $user['class_grade'] . ($user['class_sub'] ? '-' . $user['class_sub'] : '');
            
            // get quota info
            $grids = array(
                'Kapitelach'  	=>  8001,
                'Minutes'   	=>  8002 
            );
            $user_id = $user['user_id'];
            $lang_id = $user['lang_id'];
            $school_type_id = $user['school_type_id'];

            foreach ( $grids as $grid ) {
                $sql = "SELECT dt.quantity AS total, dt.date_task_id
                        FROM date_tasks dt
                        JOIN date_tasks_missions dtm
                        USING ( date_tasks_mission_id )
                        JOIN user_tracks ut
                        USING ( track_id, level, subject_id )
                        WHERE dtm.subject_id = 1
                        AND dtm.start_date = $date
                        AND dtm.end_date = $date
                        AND dt.grid_id = $grid
                        AND dtm.school_type_id = $school_type_id 
                        AND ut.user_id = $user_id 
                        AND dtm.lang_id = $lang_id 
                        AND ut.enrolled =1";
                $result = mysql_query( $sql );
                $row = mysql_fetch_assoc( $result );
                if ( $grid == 8001 ) $quota = $row['total'];
                else if ( $grid == 8002 ) $minutes = $row['total'];
            }
            ?>
            <div class="card">
                Name: <?=$name?><br />
                Grade: <?=$grade?><br />
                <?=$month?> Quota: <b><?=$quota?> kapitelach, <?=$minutes?> minutes</b>.<br />
                <br />
                How many קאפיטלעך did you say? ____<br />
                How many Minutes did you say תהלים for? ____<br />
                I said תהלים in שול or in a group <input type="checkbox" class="groupBox" />.<br /><br />
                Parent's Signature: ____________________
            </div>
            <?php
            $cards++;
            if ( $cards % 2 == 0 ) echo "<div style='clear: both;'></div>";
            if ( $cards % 4 == 0 ) echo "<div style='page-break-after: always;'></div>";
        }
    }
    ?>
    </body>
</html>