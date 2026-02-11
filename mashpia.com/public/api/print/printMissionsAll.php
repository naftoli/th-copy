<?php
ini_set('max_execution_time', 600);
ini_set('memory_limit', '3072M');

require_once( '../header/header.php' );
require_once( $_SERVER['DOCUMENT_ROOT'] . '/mission_report/classes/missions.php' );
require_once( $_SERVER['DOCUMENT_ROOT'] . '/mission_report/classes/noPicMission.php' );
require_once( $_SERVER['DOCUMENT_ROOT'] . '/mission_report/classes/picMission.php' );
require_once( $_SERVER['DOCUMENT_ROOT'] . '/mission_report/classes/DSMission.php' );

$raw = file_get_contents( 'php://input' );
if ( $raw ) {
    $decoded = json_decode( $raw, true );
    if ( is_array( $decoded ) ) {
        $_POST = $decoded;
    }
}

if ( !isset( $_POST['school_id'] ) ) {
    echo 'error';
    die();
}

$school_id = (int) $_POST['school_id'];
$school = \School::find([ $school_id ]);

// When check_tabs=1, return JSON: useTabs (true if 300+ registered children) and grades
define( 'MISSIONS_TABS_USER_THRESHOLD', 300 );
define( 'MISSIONS_GRADE_ORDER', [ 'Pre1a', '1', '2', '3', '4', '5', '6', '7', '8' ] );
if ( ! empty( $_POST['check_tabs'] ) ) {
    header( 'Content-Type: application/json; charset=utf-8' );
    global $MASHPIA_DB;
    $stmt = $MASHPIA_DB->prepare( 'SELECT COUNT(*) AS n FROM users WHERE school_id = ? AND user_registered > 0' );
    $stmt->execute( [ $school_id ] );
    $total_users = (int) $stmt->fetch( PDO::FETCH_ASSOC )['n'];
    if ( $total_users < MISSIONS_TABS_USER_THRESHOLD ) {
        echo json_encode( [ 'useTabs' => false ] );
        exit;
    }
    $by_grade = [];
    foreach ( $school->platoons as $p ) {
        $grade = isset( $p->class_grade ) ? trim( (string) $p->class_grade ) : '';
        if ( $grade === '' ) continue;
        if ( ! isset( $by_grade[ $grade ] ) ) $by_grade[ $grade ] = [ 'grade' => $grade, 'label' => $grade, 'class_ids' => [] ];
        $by_grade[ $grade ]['class_ids'][] = (int) $p->class_id;
    }
    $order = array_flip( MISSIONS_GRADE_ORDER );
    uksort( $by_grade, function ( $a, $b ) use ( $order ) {
        $ia = isset( $order[ $a ] ) ? $order[ $a ] : 999;
        $ib = isset( $order[ $b ] ) ? $order[ $b ] : 999;
        if ( $ia !== $ib ) return $ia - $ib;
        return strcmp( $a, $b );
    } );
    $grades = array_values( array_map( function ( $g ) {
        return [ 'grade' => $g['grade'], 'label' => $g['label'], 'class_ids' => $g['class_ids'] ];
    }, $by_grade ) );
    echo json_encode( [ 'useTabs' => true, 'grades' => $grades ] );
    exit;
}

$user_ids = isset( $_POST['user_ids'] ) && $_POST['user_ids'] !== '' ? ( is_array( $_POST['user_ids'] ) ? $_POST['user_ids'] : explode( ',', $_POST['user_ids'] ) ) : null;
$class_ids = isset( $_POST['class_ids'] ) && $_POST['class_ids'] !== '' ? ( is_array( $_POST['class_ids'] ) ? $_POST['class_ids'] : explode( ',', $_POST['class_ids'] ) ) : null;
$parsha_ids = isset( $_POST['parsha_ids'] ) && $_POST['parsha_ids'] !== '' ? ( is_array( $_POST['parsha_ids'] ) ? $_POST['parsha_ids'] : explode( ',', $_POST['parsha_ids'] ) ) : null;

if ( $user_ids && ! is_array( $user_ids ) ) $user_ids = [ $user_ids ];

if ( ! $class_ids ) {
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
    foreach ( $class_ids as $class_id ) {
        $usersTmp = \Soldier::find_all_by_class_id( [ $class_id ] );
        $usersTmp = array_filter($usersTmp, function ($u) { return $u->user_registered; });
        usort( $usersTmp, function( $a, $b ) {
            return strcmp($a->last ?? '', $b->last ?? '');
        });
        $user_idsTmp = array_map(function ($u) { return $u->user_id; }, $usersTmp);
        $users = array_merge( $users, $usersTmp );
        $user_ids = array_merge( $user_ids, $user_idsTmp );
    }
} else if ( $user_ids ) {
    $users = \Soldier::find( $user_ids );
    $users = is_array( $users ) ? $users : [ $users ];
    $users = array_filter($users, function ($u) use ($class_ids) { return in_array( $u->class_id, $class_ids ); });
    usort( $users, function( $a, $b ) {
        return strcmp($a->last ?? '', $b->last ?? '');
    });
    $user_ids = array_map(function ($u) { return $u->user_id; }, $users);
}

if ( !$parsha_ids ) {
    echo 'Cannot Print 0 Parshos. Please select at least 1 parsha.';
    exit;
}

$parshos = \Parsha::find( $parsha_ids );
$parshos = is_array( $parshos ) ? $parshos : [ $parshos ];
$double_sided = isset( $_POST['double_sided'] ) && $_POST['double_sided'] === 'true';
$dates = $_POST['dates'] ?? 'hebrew';
$batchedPrinting = isset($_POST['batches']) ? 1 : 0;

$start_min = null;
$end_max = null;
foreach ( $parshos as $parsha ) {
    if ( $start_min === null || $parsha->start < $start_min ) $start_min = $parsha->start;
    if ( $end_max === null || $parsha->end > $end_max ) $end_max = $parsha->end;
}

$GLOBALS['mission_print_missions'] = [];
if ( $start_min !== null && $end_max !== null ) {
    global $MASHPIA_DB;
    $dtmSql = "
        SELECT * FROM date_tasks_missions dtm
        WHERE dtm.start_date >= :start AND dtm.end_date <= :end
        ORDER BY subject_id, school_type_id, lang_id, level, track_id, mission_number, start_date, mission_name";
    $dtmStmt = $MASHPIA_DB->prepare( $dtmSql );
    $dtmStmt->execute( [ 'start' => $start_min, 'end' => $end_max ] );
    $dtm = $dtmStmt->fetchAll( PDO::FETCH_ASSOC );
    $mission_ids = [];
    foreach ( $dtm as $dtmRow ) {
        $GLOBALS['mission_print_missions'][ $dtmRow['subject_id'] ][ $dtmRow['school_type_id'] ][ $dtmRow['lang_id'] ][ $dtmRow['level'] ][ $dtmRow['track_id'] ][] = $dtmRow;
        $mission_ids[] = (int) $dtmRow['date_tasks_mission_id'];
    }
    require_once __DIR__ . '/missions_print_cache.php';
    build_mission_print_task_cache( $mission_ids );
}

if ( ! empty( $user_ids ) ) {
    require_once __DIR__ . '/missions_print_cache.php';
    build_mission_print_caches( $user_ids, $start_min, $end_max );
}

$missions = [];
foreach( $user_ids as $user_id ) {
    foreach( $parshos as $parsha ) {
        $mission = new Missions( $parsha->start, $parsha->end, $user_id, 0, 0, true, true );
        $missions[] = $mission->getMissions();
    }
}

$objMissions = [];
foreach ( $missions as $info ) {
    foreach ( $info as $mission ) {
        $type = $mission->pic_mission_type;
        if (in_array($mission->school_type_id, [4,5])) $type = 4;
        $objMissions[] = MissionDisplay::getInstance( $type, $mission );
    }
}

$dates_id = 1;
if ( $dates == 'none' ) $dates_id = 0;
if ( $dates == 'english' ) $dates_id = 2;

ob_start();
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Print Missions</title>
    <link rel="stylesheet" href="/mission_report/newStyle.css?v=2.3" type="text/css" />
    <style>
        .mandStar { color: red; right: auto !important; padding-right: 5px; float: left !important; position: relative !important; }
        .he .mandStar { color: red; padding-left: 5px; float: right !important; right: 0px !important; }
    </style>
</head>
<body>
    <div id='stats'>
        <p>Soldiers Printed: <?= count( $user_ids ) ?> | Parshos Printed: <?= count( $parsha_ids ) ?></p>
        <p id='total'>Total Sheets: <?= count( $user_ids ) * count( $parsha_ids ) ?> | Total Pages: <span id='total-pages'>&#x25cc;</span></p>
    </div>
    <?php
    $pages = 0;
    foreach ( $objMissions as $obj ) {
        $obj->setDateDisplay( $dates_id );
        $obj->setDblSided( $double_sided );
        if ( ! empty( $_POST['pages'] ) ) $obj->setMinPages( $_POST['pages'] );
        $class = 'userMission';
        if ($obj->lang_id == 2) $class .= ' he';
        if (in_array($obj->school_type_id , [4,5])) $class .= ' ds';
        $id = $obj->user_id;
        echo "<div class='$class' id='user-$id'";
        if ($obj->lang_id == 2) echo " dir='rtl' ";
        echo ">";
        $debug = isset($_GET['debug']);
        $pages += $obj->printMission( $debug );
        echo "</div>";
        echo "<div style='clear: both; page-break-after: always'></div>";
    }
    ?>
    <input type='hidden' id='pages-printed' value='<?=$pages?>' />
    <script src="/scripts/functions.js"></script>
    <script src="/jquery.js"></script>
    <script src="missions.js"></script>
    <script>
        document.querySelector('#total-pages').innerText = document.querySelector('#pages-printed').value;
        document.addEventListener('DOMContentLoaded', function() {
            const batch = <?= $batchedPrinting ?>;
            if (batch) {
                $(".userMission").each(function () {
                    const id = $(this).attr('id');
                    const print_div = document.getElementById(id).innerHTML;
                    const print_area = window.open('', '', 'width=900,height=650');
                    print_area.document.write('<html><head><meta charset="utf8" /><link rel="stylesheet" href="/mission_report/newStyle.css?v=2.3" type="text/css" /></head><body>');
                    print_area.document.write(print_div + "</body></html>");
                    print_area.document.close();
                    print_area.focus();
                    setTimeout(() => { print_area.print(); print_area.close(); }, 500);
                });
            } else {
                window.print();
            }
        }, false);
    </script>
</body>
</html>
<?php
echo ob_get_clean();
