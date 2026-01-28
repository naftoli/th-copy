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

// Enable profiling if ?profile=1 is in URL or profile is in POST
$profile = isset($_GET['profile']) || isset($_POST['profile']);
$timings = [];
$start_time = microtime(true);

function profile_timing($label, $start_time) {
    global $timings, $profile;
    if ($profile) {
        $elapsed = microtime(true) - $start_time;
        $timings[] = ['label' => $label, 'time' => round($elapsed * 1000, 2), 'memory' => round(memory_get_usage(true) / 1024 / 1024, 2)];
        return microtime(true);
    }
    return $start_time;
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
// echo "Start: " . $start . "<br />";
// echo "End: " . $end . "<br />";
// exit;

// * Set class_ids and user_ids if not set by client
$section_start = microtime(true);
if ( !$class_ids ) {
    $class_ids = array_map( function ($p) { return $p->class_id; }, $school->platoons );
}
$section_start = profile_timing('Get class_ids', $section_start);

if ($user_ids) {
    foreach ($user_ids as $i => $user_id) {
        if ($i == 0 && !$user_id) {
            $user_ids = false;
            break;
        }
    }
}

$section_start = microtime(true);
if ( !$user_ids ) {
    $users = [];
    $user_ids = [];
    // do each class separately so that we can order the children alphabetically
    foreach ( $class_ids as $class_id ) {
        $class_start = microtime(true);
        $usersTmp = \Soldier::find_all_by_class_id( [ $class_id ] );
        if ($profile) profile_timing("Load users for class_id=$class_id", $class_start);
        
        $usersTmp = array_filter($usersTmp, function ($u) { return $u->user_registered; });
        // order users alphabetically
        usort( $usersTmp, function( $a, $b ) {
            return $a->last > $b->last;
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
        return $a->last > $b->last;
    });
    $user_ids = array_map(function ($u) { return $u->user_id; }, $users);
}
$section_start = profile_timing('Load and sort users (' . count($user_ids) . ' users)', $section_start);
// echo "<pre>"; print_r($user_ids); echo "</pre>"; exit;
// * Convert the dates for the legacy system
$dates_id = 1;
if ( $dates == 'none' ) $dates_id = 0;
if ( $dates == 'english' ) $dates_id = 2;

// * Generate the missions using the legacy code
$section_start = microtime(true);
$missions = [];
foreach( $user_ids as $user_id ) {
    $user_start = microtime(true);
    $mission = new Missions( $start, $end, $user_id, 0, 0, true, true, true );
    $missions[] = $mission->getMissions();
    if ($profile) profile_timing("Missions for user_id=$user_id", $user_start);
}
$section_start = profile_timing('Generate missions for all users', $section_start);

// * Generate the printed sheets using the legacy code
$section_start = microtime(true);
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
$section_start = profile_timing('Create MissionDisplay objects', $section_start);

// * Print the buttons to print and email the Duch
echo "
    <div style='display: flex; justify-content: center; gap: 10px; margin-top: 10px;'>
    <button class='no-print btn btn-primary' id='print-button' style='display: none;' onclick='javascript:window.print()'>Print</button>
    <button class='no-print btn btn-primary' id='email-button' style='display: none;' onclick='javascript:emailToOhel()'>Email to the Ohel</button>
    </div>";

$pages = 0;
// * Print the missions just like before
$section_start = microtime(true);
foreach ( $objMissions as $obj ) {
    $user_start = microtime(true);
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
    $streak_start = microtime(true);
    $streaks = new Streaks($obj->user_id, $start, $end);
    $activeStreaks = $streaks->getStreaks();
    if ($profile) profile_timing("Streaks for user_id=$id", $streak_start);

    $debug = false;
    if (isset($_GET['debug'])) $debug = true;
    $print_start = microtime(true);
    $pages += $obj->printDuch($activeStreaks);
    if ($profile) profile_timing("Print duch for user_id=$id", $print_start);
    echo "</div>";
    // echo "<div style='clear: both; page-break-after: always'></div>";
    if ($profile) profile_timing("Total processing for user_id=$id", $user_start);
}
$section_start = profile_timing('Print all duches', $section_start);

function isRTL($lang_id) {
    return in_array($lang_id, [2, 4]);
}

// Display profiling information if enabled
if ($profile) {
    $total_time = microtime(true) - $start_time;
    profile_timing('TOTAL', $start_time);
    
    echo "<div class='no-print' style='background: #f0f0f0; padding: 20px; margin: 20px; border: 2px solid #333; font-family: monospace;'>";
    echo "<h2>Performance Profile</h2>";
    echo "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Section</th><th>Time (ms)</th><th>Memory (MB)</th><th>% of Total</th></tr>";
    
    $total_ms = $total_time * 1000;
    foreach ($timings as $timing) {
        $percent = $total_ms > 0 ? round(($timing['time'] / $total_ms) * 100, 1) : 0;
        $color = $timing['time'] > 1000 ? '#ffcccc' : ($timing['time'] > 500 ? '#ffffcc' : '#ccffcc');
        echo "<tr style='background: $color;'>";
        echo "<td>" . htmlspecialchars($timing['label']) . "</td>";
        echo "<td style='text-align: right;'>" . number_format($timing['time'], 2) . "</td>";
        echo "<td style='text-align: right;'>" . number_format($timing['memory'], 2) . "</td>";
        echo "<td style='text-align: right;'>" . $percent . "%</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "<p><strong>Total execution time:</strong> " . round($total_time, 2) . " seconds</p>";
    echo "<p><strong>Peak memory usage:</strong> " . round(memory_get_peak_usage(true) / 1024 / 1024, 2) . " MB</p>";
    echo "</div>";
}