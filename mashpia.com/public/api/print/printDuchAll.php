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

$school_id = $_POST['school_id'];
$school = \School::find([ $school_id ]);

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
    $date_range = ( isset( $date_range ) && is_numeric( $date_range ) && $date_range > 0 ) ? (int) $date_range : 30;
    $start = $end - $date_range + 1; // one less b/c we include start and end date in total number of days
}
// $num_days = $end - $start + 1;
// $days_flag = ceil( $num_days / 30 ); // number of times to multiply the number of users by to get the total number of users needed for the duch

// When check_tabs=1, return JSON: useTabs (true if over threshold) and batches (user_ids per batch of DUCH_TABS_USER_THRESHOLD) so duch.php can open one page per batch.
define( 'DUCH_TABS_USER_THRESHOLD', 360 );
define( 'DUCH_GRADE_ORDER', [ 'Pre1a', '1', '2', '3', '4', '5', '6', '7', '8' ] );
if ( ! empty( $_POST['check_tabs'] ) ) {
    header( 'Content-Type: application/json; charset=utf-8' );
    global $MASHPIA_DB;
    $tab_class_ids = isset( $_POST['class_ids'] ) && $_POST['class_ids'] !== ''
        ? ( is_array( $_POST['class_ids'] ) ? $_POST['class_ids'] : explode( ',', $_POST['class_ids'] ) )
        : array_map( function ( $p ) { return $p->class_id; }, $school->platoons );
    $tab_class_ids = array_filter( array_map( 'intval', $tab_class_ids ) );
    $users = [];
    foreach ( $tab_class_ids as $class_id ) {
        $usersTmp = \Soldier::find_all_by_class_id( [ $class_id ] );
        $usersTmp = array_filter( $usersTmp, function ( $u ) { return $u->user_registered; } );
        $users = array_merge( $users, $usersTmp );
    }
    $order = array_flip( DUCH_GRADE_ORDER );
    usort( $users, function ( $a, $b ) use ( $order ) {
        $ga = isset( $a->platoon ) && isset( $a->platoon->class_grade ) ? trim( (string) $a->platoon->class_grade ) : '';
        $gb = isset( $b->platoon ) && isset( $b->platoon->class_grade ) ? trim( (string) $b->platoon->class_grade ) : '';
        $ia = isset( $order[ $ga ] ) ? $order[ $ga ] : 999;
        $ib = isset( $order[ $gb ] ) ? $order[ $gb ] : 999;
        if ( $ia !== $ib ) return $ia - $ib;
        return strcmp( $a->last ?? '', $b->last ?? '' );
    } );
    $user_ids = array_map( function ( $u ) { return $u->user_id; }, $users );
    $total = count( $user_ids );
    if ( $total < DUCH_TABS_USER_THRESHOLD ) {
        echo json_encode( [ 'useTabs' => false ] );
        exit;
    }
    $batches = [];
    $chunk_size = DUCH_TABS_USER_THRESHOLD;
    $chunks = array_chunk( $user_ids, $chunk_size );
    foreach ( $chunks as $i => $chunk ) {
        $from = $i * $chunk_size + 1;
        $to = $i * $chunk_size + count( $chunk );
        $batches[] = [
            'user_ids' => $chunk,
            'label'    => 'Batch ' . ( $i + 1 ) . ' (' . $from . '-' . $to . ')',
        ];
    }
    echo json_encode( [ 'useTabs' => true, 'batches' => $batches ] );
    exit;
}

$user_ids = isset( $_POST['user_ids'] ) && $_POST['user_ids'] !== '' ? ( is_array( $_POST['user_ids'] ) ? $_POST['user_ids'] : explode( ',', $_POST['user_ids'] ) ) : null;
$class_ids = isset( $_POST['class_ids'] ) && $_POST['class_ids'] !== '' ? ( is_array( $_POST['class_ids'] ) ? $_POST['class_ids'] : explode( ',', $_POST['class_ids'] ) ) : null;
if ( $user_ids && ! is_array( $user_ids ) ) $user_ids = [ $user_ids ];

// $double_sided = isset( $_POST['double_sided'] ) && $_POST['double_sided'] === 'true';

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

$all_date_tasks = [];
$all_date_tasks_missions = [];
$dtmSql = "
    SELECT 
        *
    FROM
        date_tasks_missions dtm 
    WHERE
        dtm.start_date >= :start 
            AND dtm.end_date <= :end 
            AND subject_id NOT IN (12 , 15, 40, 93, 94, 136)
            AND mission_name NOT LIKE '%Chidon Limmud%' 
            AND created_by_parent IS NULL 
            ORDER BY subject_id, school_type_id, lang_id, level, track_id, mission_number, start_date, mission_name";
$dtmStmt = $MASHPIA_DB->prepare($dtmSql);
$dtmStmt->execute([
    'start' => $start,
    'end' => $end
]);
$dtm = $dtmStmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($dtm as $dtmRow) {
    $all_date_tasks_missions[$dtmRow['subject_id']][$dtmRow['school_type_id']][$dtmRow['lang_id']][$dtmRow['level']][$dtmRow['track_id']][] = $dtmRow;
}
// echo "<pre>"; print_r($all_date_tasks_missions); echo "</pre>"; exit;

// * Batch-load caches (birthdays, exceptions, defaults, date_tasks_marks); user_track_for_duch / user_track / TaskExceptions / Defaults read from globals
require_once __DIR__ . '/missions_print_cache.php';
build_mission_print_caches( $user_ids, $start, $end, $all_date_tasks_missions );
build_duch_marks_cache( $user_ids, $start, $end );
build_duch_medals_ranks_cache( $user_ids, $start, $end );
// * Generate the missions: use school filter when list is huge to avoid giant IN/FIELD in SQL
$user_ids_to_pass = $user_ids;
$school_for_query = 0;
if ( is_array( $user_ids ) && count( $user_ids ) > Missions::MAX_USER_IDS_IN_QUERY && $school_id ) {
	$user_ids_to_pass = [];
	$school_for_query = (int) $school_id;
}
$mission = new Missions( $start, $end, $user_ids_to_pass, $school_for_query, 0, true, true, true, true );
$missions = [ $mission->getMissions() ];
if ( $school_for_query && empty( $user_ids_to_pass ) ) {
	$user_ids = array_map( function ( $u ) { return $u->user_id; }, $missions[0] );
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

ob_start();
// * Print the buttons to print and email the Duch
echo "
    <div style='display: flex; justify-content: center; gap: 10px; margin-top: 10px;'>
    <button class='no-print btn btn-primary' id='print-button' style='display: none;' onclick='javascript:window.print()'>Print</button>
    <button class='no-print btn btn-primary' id='email-button' style='display: none;' onclick='javascript:emailToOhel()'>Email to the Ohel</button>
    </div>";

$pages = 0;
// * Load streaks for all users in one batch
$allStreaks = Streaks::getStreaksForUsers( $user_ids, $start, $end );

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

    $activeStreaks = isset( $allStreaks[ $id ] ) ? $allStreaks[ $id ] : [];

    $debug = false;
    if (isset($_GET['debug'])) $debug = true;
    $pages += $obj->printDuch($activeStreaks);
    echo "</div>|";
    // Flush so client receives output progressively and doesn't timeout
    if ( ob_get_level() ) {
        ob_flush();
        flush();
    }
}
$html = ob_get_flush();
echo $html;

function isRTL($lang_id) {
    return in_array($lang_id, [2, 4]);
}
