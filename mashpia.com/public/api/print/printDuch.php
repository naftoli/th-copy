<?php
ini_set('max_execution_time', 600);
ini_set('memory_limit', '3072M');

require_once( '../header/header.php' );
require_once( $_SERVER['DOCUMENT_ROOT'] . '/mission_report/classes/missions.php' );
require_once( $_SERVER['DOCUMENT_ROOT'] . '/mission_report/classes/noPicMission.php' );
require_once( $_SERVER['DOCUMENT_ROOT'] . '/mission_report/classes/picMission.php' );
require_once( $_SERVER['DOCUMENT_ROOT'] . '/mission_report/classes/DSMission.php' );
require_once $_SERVER['DOCUMENT_ROOT'] . '/mobile/streaks/classes/class.streaks.php';
require_once( $_SERVER['DOCUMENT_ROOT'] . '/mobile/streaks/classes/class.accomplished.php' );
require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/duch_task.php' );

$_POST = json_decode(file_get_contents('php://input'), true);
if ( !isset( $_POST['school_id'] ) ) {
    echo 'error';
    die();
}

$school = \School::find([ $_POST['school_id'] ]);
$user_ids = $_POST['user_ids'] ? explode( ',', $_POST['user_ids'] ) : false;
$class_ids = $_POST['class_ids'] ? explode( ',', $_POST['class_ids'] ) : false;
if (!is_array($user_ids)) $user_ids = [ $user_ids ];

// $double_sided = isset( $_POST['double_sided'] ) && $_POST['double_sided'] === 'true';
$dates = $_POST['dates'];
$start_date = $_POST['start'];
$end_date = $_POST['end'];
$date_range = $_POST['date_range'];
$selectedMonth = $_POST['selectedMonth'];
$besuros_tovos = $_POST['besuros_tovos'] ?? '';

if ( $selectedMonth ) {
    // selected month - convert the dates to unix timestamps
    $dates = explode( ' - ', $selectedMonth );
    $start = unixtojd(strtotime($dates[0])) + 1;
    $end = unixtojd(strtotime($dates[1])) + 1;
} else if ( $start_date && $end_date ) {
    // convert the dates to unix timestamps
    if (! is_numeric($start_date)) $start = unixtojd(strtotime($start_date)) + 1;
    if (! is_numeric($end_date)) $end = unixtojd(strtotime($end_date)) + 1;
} else {
    // date range
    $end = unixtojd();
    $start = $end - $date_range + 1; // one less b/c we include start and end date in total number of days
} 

// * Set class_ids and user_ids if not set by client
if ( !$class_ids ) {
    $class_ids = array_map( function ($p) { return $p->class_id; }, $school->platoons );
}

if ($user_ids) {
    foreach ($user_ids as $i => $user_id) {
        if ($i == 0 && !$user_id) {
            $user_ids = false;
            break;
        }
    }
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
            return strcmp($a->last ?? '', $b->last ?? '');
        });
        $user_idsTmp = array_map(function ($u) { return $u->user_id; }, $usersTmp);

        $users = array_merge( $users, $usersTmp );
        $user_ids = array_merge( $user_ids, $user_idsTmp );
    }
// make sure the soldiers are in the selected platoons if provided with an array of soldiers.
} else if ( $user_ids ) {
    $users = \Soldier::find( $user_ids );
    $users = is_array( $users ) ? $users : [ $users ]; // make sure it is an array so we can filter it
    $users = array_filter($users, function ($u) use ($class_ids) { return in_array( $u->class_id, $class_ids ); });
    // order users alphabetically
    usort( $users, function( $a, $b ) {
        return strcmp($a->last ?? '', $b->last ?? '');
    });
    $user_ids = array_map(function ($u) { return $u->user_id; }, $users);
}

// * Convert the dates for the legacy system
$dates_id = 1;
if ( $dates == 'none' ) $dates_id = 0;
if ( $dates == 'english' ) $dates_id = 2;

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

// * Print the buttons to print and email the Duch
echo "
    <div style='display: flex; justify-content: center; gap: 10px; margin-top: 10px;'>
    <button class='no-print btn btn-primary' id='print-button' style='display: none;' onclick='javascript:window.print()'>Print</button>
    <button class='no-print btn btn-primary' id='email-button' style='display: none;' onclick='javascript:emailToOhel()'>Email to the Ohel</button>
    </div>";

$pages = 0;
// * Print the missions just like before
foreach ( $objMissions as $obj ) {
    $obj->setDateDisplay( $dates_id );
    // $obj->setDblSided( $double_sided );

    $class = 'userDuch';
    if (isRTL($obj->lang_id)) $class .= ' he';
    if (in_array($obj->school_type_id , [4,5])) $class .= ' ds';

    $id = $obj->user_id;
    echo "<div class='$class' id='user-$id'";
    if (in_array($obj->lang_id, [2, 4])) echo " dir='rtl' ";
    echo ">";

    // get streaks for the user
    $streaks = new Streaks($obj->user_id, $start, $end);
    $activeStreaks = $streaks->getStreaks();

    $debug = false;
    if (isset($_GET['debug'])) $debug = true;
    $pages += $obj->printDuch($activeStreaks);
    echo "</div>";
}

function isRTL($lang_id) {
    return in_array($lang_id, [2, 4]);
}
