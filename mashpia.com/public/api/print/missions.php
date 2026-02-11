<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
ini_set('max_execution_time', 600);
ini_set('memory_limit', '3072M');

require_once( '../header/header.php' );
require_once( $_SERVER['DOCUMENT_ROOT'] . '/mission_report/classes/missions.php' );
require_once( $_SERVER['DOCUMENT_ROOT'] . '/mission_report/classes/noPicMission.php' );
require_once( $_SERVER['DOCUMENT_ROOT'] . '/mission_report/classes/picMission.php' );
require_once( $_SERVER['DOCUMENT_ROOT'] . '/mission_report/classes/DSMission.php' );

if ( !isset( $_POST['school_id'] ) ) {
    header('Location: /new/missions/print' ); die();
}

$school = \School::find([ $_POST['school_id'] ]);
$user_ids = $_POST['user_ids'] ? explode( ',', $_POST['user_ids'] ) : false;
$class_ids = $_POST['class_ids'] ? explode( ',', $_POST['class_ids'] ) : false;
$parsha_ids = $_POST['parsha_ids'] ? explode( ',', $_POST['parsha_ids'] ) : false;

$double_sided = isset( $_POST['double_sided'] ) && $_POST['double_sided'] === 'true';
$dates = $_POST['dates'];
$batchedPrinting = isset($_POST['batches']) ? 1 : 0;

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

if ( !$parsha_ids ) {
    echo 'Cannot Print 0 Parshos. Please select at least 1 parsha.'; die();
}

$parshos = \Parsha::find( $parsha_ids );
$parshos = is_array( $parshos ) ? $parshos : [ $parshos ]; // make sure it is an array of objects.

// * Pre-load missions for mission printing (user_track reads from mission_print_missions when set)
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

// * Generate the missions using the legacy code
$missions = [];
foreach( $user_ids as $user_id ) {
    foreach( $parshos as $parsha ) {
        $mission = new Missions( $parsha->start, $parsha->end, $user_id, 0, 0, true, true );
        $missions[] = $mission->getMissions();
    }
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
        /* Mandatory star marks*/
        .mandStar {
            color: red;
            right: auto !important;
            padding-right: 5px;
            float: left !important;
            position: relative !important;
        }

        .he .mandStar {
            color: red;
            padding-left: 5px;
            float: right !important;
            right: 0px !important;
        }
    </style>
</head>

<body>
    <div id='stats'>
        <p>Soldiers Printed: <?= count( $user_ids ) ?> | Parshos Printed: <?= count( $parsha_ids ) ?></p>
        <p id='total'>
            Total Sheets: <?= count( $user_ids ) * count( $parsha_ids ) ?> |
            Total Pages: <span id='total-pages'>&#x25cc;</span> 
        </p>
    </div>

    <?php
        $pages = 0;
        // * Print the missions just like before
        foreach ( $objMissions as $obj ) {
            $obj->setDateDisplay( $dates_id );
            $obj->setDblSided( $double_sided );

            if ( $_POST['pages'] )
                $obj->setMinPages( $_POST['pages'] );

            $class = 'userMission';
            if ($obj->lang_id == 2) $class .= ' he';
            if (in_array($obj->school_type_id , [4,5])) $class .= ' ds';

            $id = $obj->user_id;
            echo "<div class='$class' id='user-$id'";
            if ($obj->lang_id == 2) echo " dir='rtl' ";
            echo ">";

            $debug = false;
            if (isset($_GET['debug'])) $debug = true;
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
            // your code here
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
                    setTimeout(() => {
                        print_area.print();
                        print_area.close();
                    }, 500);
                });
            } else {
                window.print();
            }
        }, false);
        /*
            (function () {
            let currentIndex = 0;

            // Collect all sections based on the break divs
            const allNodes = Array.from(document.body.children);
            const sections = [];
            let temp = [];

            allNodes.forEach(node => {
                temp.push(node);
                if (
                node.tagName === 'DIV' &&
                node.style &&
                node.style.pageBreakAfter === 'always'
                ) {
                sections.push([...temp]); // save this section
                temp = []; // reset for next
                }
            });

            // Add CSS to hide everything initially
            allNodes.forEach(node => {
                if (node.style) node.style.display = 'none';
            });

            // Define the print function globally so you can also call it from AHK
            window.printNextSection = function () {
                if (currentIndex < sections.length) {
                // Hide all
                allNodes.forEach(node => {
                    if (node.style) node.style.display = 'none';
                });

                // Show current section
                sections[currentIndex].forEach(node => {
                    if (node.style) node.style.display = '';
                });

                // Print and go to next
                window.print();
                currentIndex++;
                } else {
                sendCtrlBacktick();
                alert('All sections printed!'); 
                }
            };
                
            function sendCtrlBacktick() {
            document.title = 'PRINT_COMPLETE_SIGNAL';
            }

            // Initially hide everything
            allNodes.forEach(node => {
                if (node.style) node.style.display = 'none';
            });
            // Add keyboard shortcut: press "n" to trigger
            window.addEventListener('keydown', function (e) {
                if (e.key === 'n' && !e.ctrlKey && !e.altKey && !e.metaKey) {
                e.preventDefault();
                printNextSection();
                }
            });
            })();
        */
    </script>
    <?php // ! *************************** Debug *************************** ?>
    <!-- <details id='debug'>
        <summary>Debug</summary>
        <pre>
        <?php
            // print_r([
            //     'post' => $_POST,
            //     'vars' => [
            //         'dates' => $dates,
            //         'pages' => $pages,
            //         'school_id' => $school->school_id,
            //         'user_ids' => $user_ids,
            //         'class_ids' => $class_ids,
            //         'parsha_ids' => $parsha_ids,
            //         'double_sided' => $double_sided,
            //     ]
            // ]);
        ?>
        </pre>
    </details> -->
</body>
</html>