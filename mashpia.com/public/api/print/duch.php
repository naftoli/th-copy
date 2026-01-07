<?php
ini_set('display_errors',1);
require_once( '../header/header.php' ); // load header

require_once( $_SERVER['DOCUMENT_ROOT'] . '/mission_report/classes/missions.php' );
require_once( $_SERVER['DOCUMENT_ROOT'] . '/mission_report/classes/noPicMission.php' );
require_once( $_SERVER['DOCUMENT_ROOT'] . '/mission_report/classes/picMission.php' );
require_once( $_SERVER['DOCUMENT_ROOT'] . '/mission_report/classes/DSMission.php' );
require_once $_SERVER['DOCUMENT_ROOT'] . '/mobile/streaks/classes/class.streaks.php';
require_once( $_SERVER['DOCUMENT_ROOT'] . '/mobile/streaks/classes/class.accomplished.php' );

if ( !isset( $_POST['school_id'] ) ) {
    header('Location: /new/missions/print' ); die();
}

$school = \School::find([ $_POST['school_id'] ]);
$user_ids = $_POST['user_ids'] ? explode( ',', $_POST['user_ids'] ) : false;
$class_ids = $_POST['class_ids'] ? explode( ',', $_POST['class_ids'] ) : false;

// $double_sided = isset( $_POST['double_sided'] ) && $_POST['double_sided'] === 'true';
$dates = $_POST['dates'];
$start = $_POST['start'];
$end = $_POST['end'];
$date_range = $_POST['date_range'];
if ( !$start && !$end ) {
    $end = unixtojd();
    $start = $end - $date_range;
} else {
    // convert the dates to unix timestamps
    $start = unixtojd(strtotime($start));
    $end = unixtojd(strtotime($end));
}

// * Set class_ids and user_ids if not set by client
if ( !$class_ids ) {
    $class_ids = array_map( function ($p) { return $p->class_id; }, $school->platoons );
}

if ( !$user_ids ) {
    $users = [];
    $user_ids = [];
    // do each class separately so that we can order the children alphabetically
    foreach ( $class_ids as $class_id ) {
        $usersTmp = \Soldier::find_all_by_class_id( [ $class_id ] );
        $usersTmp = array_filter($usersTmp, function ($u) { return $u->user_registered; });
        // order users alphabetically
        usort( $usersTmp, function( $a, $b ) {
            return $a->last > $b->last;
        });
        $user_idsTmp = array_map(function ($u) { return $u->user_id; }, $usersTmp);

        $users = array_merge( $users, $usersTmp );
        $user_ids = array_merge( $user_ids, $user_idsTmp );
    }
    // $users = \Soldier::find_all_by_class_id( $class_ids );
    // $users = array_filter($users, function ($u) { return $u->user_registered; });
    // // order users alphabetically
    // usort( $users, function( $a, $b ) {
    //     return $a->last > $b->last;
    // });
    // $user_ids = array_map(function ($u) { return $u->user_id; }, $users);
// make sure the soldiers are in the selected platoons if provided with an array of soldiers.
} else if ( $user_ids ) {
    $users = \Soldier::find( $user_ids );
    $users = is_array( $users ) ? $users : [ $users ]; // make sure it is an array so we can filter it
    $users = array_filter($users, function ($u) use ($class_ids) { return in_array( $u->class_id, $class_ids ); });
    // order users alphabetically
    usort( $users, function( $a, $b ) {
        return $a->last > $b->last;
    });
    $user_ids = array_map(function ($u) { return $u->user_id; }, $users);
}

// * Generate the missions using the legacy code
$missions = [];
foreach( $user_ids as $user_id ) {
    $mission = new Missions( $start, $end, $user_id, 0, 0, true, true, true );
    $missions[] = $mission->getMissions();
}

// * Generate the printed sheets using the legacy code
$objMissions = [];
foreach ( $missions as $info ) {
    foreach ( $info as $mission ) {
        $type = $mission->pic_mission_type;
        if (in_array($mission->school_type_id, [4,5])) {
            $type = 4;
        }
        $objMissions[] = MissionDisplay::getInstance( $type, $mission );
    }
}

// * Convert the dates for the legacy system
$dates_id = 1;
if ( $dates == 'none' ) $dates_id = 0;
if ( $dates == 'english' ) $dates_id = 2;
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Print Missions</title>
    <link rel="stylesheet" href="/mission_report/newStyle.css?v=2.3" type="text/css" />
    <style>
        .userDuch {
            width: 8.5in;
            margin: 10px auto;
            page-break-after: always;
        }
        @media print {
            .userDuch {
                height: 11in;
            }
        }
        .container {
            column-count: 3;
            column-gap: 20px;
            height: auto !important;
            /* page-break-after: avoid !important; */
        }
        .track {
            margin-bottom: 15px;
        }
        .campaign-container, .task-container, .streak-container, .medals-container, .promotions-container, .streaks-container, .streak {
            display: flex;
            flex-direction: row;
            gap: 10px;
            break-inside: avoid; /* Don't split items */
            page-break-inside: avoid; /* Older browser support */
        }
        .container .medals-container, .container .promotions-container, .container .streaks-container {
            margin-bottom: 20px;
        }
        .task-container, .streak-container {
            margin-bottom: 5px;
        }
        .task, .medal, .promotion, .campaign-medals {
            display: flex;
            flex-direction: column;
            width: 2in;
            gap: 2px;
        }
        .campaign-name {
            font-size: 22px;
            line-height: 1;
        }
        .campaign-medals {
            font-size: 12px;
        }
        .task-short-name {
            font-size: 14px;
        }
        .task-name {
            font-size: 9px;
        }
        .campaign-icon, .task-stats {
            width: 50px;
            text-align: center;
        }
        .task-stats {
            font-size: 9px;
        }
        .medal {
            width: 0.75in;
        }
        .promotion {
            width: 1in;
        }
        .medal-name, .promotion-name {
            font-size: 12px;
            margin-top: -10px;
            text-align: center;
        }
        .streak .campaign-icon {
            width: 75px;
        }
        .streak-text {
            weight: bold;
            text-align: center;
            font-size: 16px;
        }
        .streak-fill progress {
            height: 30px;
            margin-left: 5px;
        }
        .streak progress {
            width: 2in;
            margin-left: 0;
        }
        .besuros-tovos {
            margin: 20px auto;
            line-height: 2.5;
            text-align: center;
        }
        footer {
            bottom: 0;
            font-size: 26px;
            font-weight: bold;
            margin-top: 20px;
            text-align: center;
        }
        .pageFooter {
            width: 90% !important;
            padding-top: 5px;
            padding-bottom: 2px;
        }
    </style>
</head>

<body>
    <?php
        $pages = 0;
        // * Print the missions just like before
        foreach ( $objMissions as $obj ) {
            $obj->setDateDisplay( $dates_id );
            // $obj->setDblSided( $double_sided );

            $class = 'userDuch';
            if ($obj->lang_id == 2) $class .= ' he';
            if (in_array($obj->school_type_id , [4,5])) $class .= ' ds';

            $id = $obj->user_id;
            echo "<div class='$class' id='user-$id'";
            if ($obj->lang_id == 2) echo " dir='rtl' ";
            echo ">";

            // get streaks for the user
            $streaks = new Streaks($obj->user_id, $start, $end);
            $activeStreaks = $streaks->getStreaks();

            $debug = false;
            if (isset($_GET['debug'])) $debug = true;
            $pages += $obj->printDuch($activeStreaks);
            echo "</div>";
            echo "<div style='clear: both; page-break-after: always'></div>";
        }
    ?>
    
    <script src="/scripts/functions.js"></script>
    <script src="/jquery.js"></script>
    <script>
        $(function() {
            $(".container").each(function() {
                let container = $(this);
                // get container dimensions
                let width = container.width();
                let height = container.height();
                alert(width + 'x' + height);
            });
        });
    </script>
</body>
</html>