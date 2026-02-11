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
$num_days = $end - $start + 1;
$days_flag = ceil( $num_days / 30 ); // number of times to multiply the number of users by to get the total number of users needed for the duch

// When check_tabs=1, return JSON: useTabs (true if 375+ registered children) and grades (grade label + class_ids per grade) so duch.php can open one page per grade.
define( 'DUCH_TABS_USER_THRESHOLD', 300 );
define( 'DUCH_GRADE_ORDER', [ 'Pre1a', '1', '2', '3', '4', '5', '6', '7', '8' ] );
if ( ! empty( $_POST['check_tabs'] ) ) {
    header( 'Content-Type: application/json; charset=utf-8' );
    global $MASHPIA_DB;
    $stmt = $MASHPIA_DB->prepare( 'SELECT COUNT(*) AS n FROM users WHERE school_id = ? AND user_registered > 0' );
    $stmt->execute( [ $school_id ] );
    $total_users = (int) $stmt->fetch( PDO::FETCH_ASSOC )['n'];
    if ( ($total_users * $days_flag) < DUCH_TABS_USER_THRESHOLD ) {
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
    $order = array_flip( DUCH_GRADE_ORDER );
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

// * Batch-load birthday mission IDs for all users (user_id => [mission_id => true]); user_track_for_duch reads from $GLOBALS['birthday_cache']
$GLOBALS['birthday_cache'] = [];
if ( ! empty( $user_ids ) ) {
	$ids = array_values( array_unique( array_filter( array_map( 'intval', $user_ids ) ) ) );
	$chunk = 500;
	foreach ( array_chunk( $ids, $chunk ) as $chunk_ids ) {
		$placeholders = implode( ',', array_fill( 0, count( $chunk_ids ), '?' ) );
		$stmt = $MASHPIA_DB->prepare( "SELECT user_id, date_tasks_mission_id FROM birthdays WHERE user_id IN ($placeholders)" );
		$stmt->execute( $chunk_ids );
		while ( $row = $stmt->fetch( PDO::FETCH_ASSOC ) ) {
			$uid = (int) $row['user_id'];
			$mid = (int) $row['date_tasks_mission_id'];
			$GLOBALS['birthday_cache'][ $uid ][ $mid ] = true;
		}
	}
}

// * Batch-load exception data (user_id => [task_id => true]); TaskExceptions reads from $GLOBALS['exception_cache']
/*
$GLOBALS['exception_cache'] = [];
if ( ! empty( $user_ids ) ) {
	$uids = array_values( array_unique( array_filter( array_map( 'intval', $user_ids ) ) ) );
	$mission_ids = [];
	foreach ( $all_date_tasks_missions as $by_subject ) {
		foreach ( $by_subject as $by_type ) {
			foreach ( $by_type as $by_lang ) {
				foreach ( $by_lang as $by_level ) {
					foreach ( $by_level as $missions ) {
						foreach ( $missions as $m ) {
							$mission_ids[] = (int) $m['date_tasks_mission_id'];
						}
					}
				}
			}
		}
	}
	$mission_ids = array_unique( $mission_ids );
	if ( ! empty( $mission_ids ) ) {
		$m_ph = implode( ',', array_fill( 0, count( $mission_ids ), '?' ) );
		$stmt = $MASHPIA_DB->prepare( "SELECT dt.date_task_id, m.subject_id FROM date_tasks dt JOIN date_tasks_missions m USING (date_tasks_mission_id) WHERE m.date_tasks_mission_id IN ($m_ph)" );
		$stmt->execute( $mission_ids );
		$task_subject = [];
		while ( $row = $stmt->fetch( PDO::FETCH_ASSOC ) ) {
			$task_subject[ (int) $row['date_task_id'] ] = (int) $row['subject_id'];
		}
		$task_ids = array_keys( $task_subject );

		$u_ph = implode( ',', array_fill( 0, count( $uids ), '?' ) );
		$stmt = $MASHPIA_DB->prepare( "SELECT user_id, school_id, class_id FROM users WHERE user_id IN ($u_ph)" );
		$stmt->execute( $uids );
		$user_school_class = [];
		$school_ids = [];
		$class_ids_ex = [];
		while ( $row = $stmt->fetch( PDO::FETCH_ASSOC ) ) {
			$uid = (int) $row['user_id'];
			$sid = (int) $row['school_id'];
			$cid = (int) $row['class_id'];
			$user_school_class[ $uid ] = [ 'school_id' => $sid, 'class_id' => $cid ];
			$school_ids[ $sid ] = true;
			$class_ids_ex[ $cid ] = true;
		}
		$school_ids = array_keys( $school_ids );
		$class_ids_ex = array_keys( $class_ids_ex );

		$school_subjects = [];
		if ( ! empty( $school_ids ) && ! empty( array_unique( array_values( $task_subject ) ) ) ) {
			$s_ph = implode( ',', array_fill( 0, count( $school_ids ), '?' ) );
			$subjects = array_unique( array_values( $task_subject ) );
			$sub_ph = implode( ',', array_fill( 0, count( $subjects ), '?' ) );
			$stmt = $MASHPIA_DB->prepare( "SELECT school_id, subject_id FROM school_subjects WHERE school_id IN ($s_ph) AND subject_id IN ($sub_ph)" );
			$stmt->execute( array_merge( $school_ids, $subjects ) );
			while ( $row = $stmt->fetch( PDO::FETCH_ASSOC ) ) {
				$school_subjects[ (int) $row['school_id'] ][ (int) $row['subject_id'] ] = true;
			}
		}

		$school_task_exc = [];
		if ( ! empty( $school_ids ) && ! empty( $task_ids ) ) {
			$s_ph = implode( ',', array_fill( 0, count( $school_ids ), '?' ) );
			$t_ph = implode( ',', array_fill( 0, count( $task_ids ), '?' ) );
			$stmt = $MASHPIA_DB->prepare( "SELECT school_id, date_task_id FROM school_task_exceptions WHERE school_id IN ($s_ph) AND date_task_id IN ($t_ph)" );
			$stmt->execute( array_merge( $school_ids, $task_ids ) );
			while ( $row = $stmt->fetch( PDO::FETCH_ASSOC ) ) {
				$school_task_exc[ (int) $row['school_id'] ][ (int) $row['date_task_id'] ] = true;
			}
		}

		$class_task_exc = [];
		if ( ! empty( $class_ids_ex ) && ! empty( $task_ids ) ) {
			$c_ph = implode( ',', array_fill( 0, count( $class_ids_ex ), '?' ) );
			$t_ph = implode( ',', array_fill( 0, count( $task_ids ), '?' ) );
			$stmt = $MASHPIA_DB->prepare( "SELECT class_id, date_task_id FROM class_task_exceptions WHERE class_id IN ($c_ph) AND date_task_id IN ($t_ph)" );
			$stmt->execute( array_merge( $class_ids_ex, $task_ids ) );
			while ( $row = $stmt->fetch( PDO::FETCH_ASSOC ) ) {
				$class_task_exc[ (int) $row['class_id'] ][ (int) $row['date_task_id'] ] = true;
			}
		}

		$user_task_exc = [];
		$u_ph = implode( ',', array_fill( 0, count( $uids ), '?' ) );
		$t_ph = implode( ',', array_fill( 0, count( $task_ids ), '?' ) );
		$stmt = $MASHPIA_DB->prepare( "SELECT user_id, date_task_id FROM user_task_exceptions WHERE user_id IN ($u_ph) AND date_task_id IN ($t_ph)" );
		$stmt->execute( array_merge( $uids, $task_ids ) );
		while ( $row = $stmt->fetch( PDO::FETCH_ASSOC ) ) {
			$user_task_exc[ (int) $row['user_id'] ][ (int) $row['date_task_id'] ] = true;
		}

		foreach ( $user_school_class as $uid => $sc ) {
			$sid = $sc['school_id'];
			$cid = $sc['class_id'];
			foreach ( $task_subject as $tid => $subj_id ) {
				$exc = false;
				if ( $sid && ! isset( $school_subjects[ $sid ][ $subj_id ] ) ) {
					$exc = true;
				}
				if ( ! $exc && $sid && isset( $school_task_exc[ $sid ][ $tid ] ) ) $exc = true;
				if ( ! $exc && $cid && isset( $class_task_exc[ $cid ][ $tid ] ) ) $exc = true;
				if ( ! $exc && isset( $user_task_exc[ $uid ][ $tid ] ) ) $exc = true;
				if ( $exc ) {
					$GLOBALS['exception_cache'][ $uid ][ $tid ] = true;
				}
			}
		}

		// * Batch-load defaults (user opted-in to task/mission when default_on=0); Defaults reads from $GLOBALS['defaults_cache']
		$GLOBALS['defaults_cache'] = [ 'task' => [], 'mission' => [] ];

		$user_task_def = [];
		$u_ph = implode( ',', array_fill( 0, count( $uids ), '?' ) );
		$t_ph = implode( ',', array_fill( 0, count( $task_ids ), '?' ) );
		$stmt = $MASHPIA_DB->prepare( "SELECT user_id, task_id FROM user_tasks WHERE user_id IN ($u_ph) AND task_id IN ($t_ph)" );
		$stmt->execute( array_merge( $uids, $task_ids ) );
		while ( $row = $stmt->fetch( PDO::FETCH_ASSOC ) ) {
			$user_task_def[ (int) $row['user_id'] ][ (int) $row['task_id'] ] = true;
		}

		$class_task_def = [];
		if ( ! empty( $class_ids_ex ) && ! empty( $task_ids ) ) {
			$c_ph = implode( ',', array_fill( 0, count( $class_ids_ex ), '?' ) );
			$t_ph = implode( ',', array_fill( 0, count( $task_ids ), '?' ) );
			$stmt = $MASHPIA_DB->prepare( "SELECT class_id, task_id FROM class_tasks WHERE class_id IN ($c_ph) AND task_id IN ($t_ph)" );
			$stmt->execute( array_merge( $class_ids_ex, $task_ids ) );
			while ( $row = $stmt->fetch( PDO::FETCH_ASSOC ) ) {
				$class_task_def[ (int) $row['class_id'] ][ (int) $row['task_id'] ] = true;
			}
		}

		$school_task_def = [];
		if ( ! empty( $school_ids ) && ! empty( $task_ids ) ) {
			$s_ph = implode( ',', array_fill( 0, count( $school_ids ), '?' ) );
			$t_ph = implode( ',', array_fill( 0, count( $task_ids ), '?' ) );
			$stmt = $MASHPIA_DB->prepare( "SELECT school_id, task_id FROM school_tasks WHERE school_id IN ($s_ph) AND task_id IN ($t_ph)" );
			$stmt->execute( array_merge( $school_ids, $task_ids ) );
			while ( $row = $stmt->fetch( PDO::FETCH_ASSOC ) ) {
				$school_task_def[ (int) $row['school_id'] ][ (int) $row['task_id'] ] = true;
			}
		}

		$m_ph = implode( ',', array_fill( 0, count( $mission_ids ), '?' ) );
		$user_mission_def = [];
		$u_ph = implode( ',', array_fill( 0, count( $uids ), '?' ) );
		$stmt = $MASHPIA_DB->prepare( "SELECT user_id, mission_id FROM user_missions WHERE user_id IN ($u_ph) AND mission_id IN ($m_ph)" );
		$stmt->execute( array_merge( $uids, $mission_ids ) );
		while ( $row = $stmt->fetch( PDO::FETCH_ASSOC ) ) {
			$user_mission_def[ (int) $row['user_id'] ][ (int) $row['mission_id'] ] = true;
		}

		$class_mission_def = [];
		if ( ! empty( $class_ids_ex ) && ! empty( $mission_ids ) ) {
			$c_ph = implode( ',', array_fill( 0, count( $class_ids_ex ), '?' ) );
			$stmt = $MASHPIA_DB->prepare( "SELECT class_id, mission_id FROM class_missions WHERE class_id IN ($c_ph) AND mission_id IN ($m_ph)" );
			$stmt->execute( array_merge( $class_ids_ex, $mission_ids ) );
			while ( $row = $stmt->fetch( PDO::FETCH_ASSOC ) ) {
				$class_mission_def[ (int) $row['class_id'] ][ (int) $row['mission_id'] ] = true;
			}
		}

		$school_mission_def = [];
		if ( ! empty( $school_ids ) && ! empty( $mission_ids ) ) {
			$s_ph = implode( ',', array_fill( 0, count( $school_ids ), '?' ) );
			$stmt = $MASHPIA_DB->prepare( "SELECT school_id, mission_id FROM school_missions WHERE school_id IN ($s_ph) AND mission_id IN ($m_ph)" );
			$stmt->execute( array_merge( $school_ids, $mission_ids ) );
			while ( $row = $stmt->fetch( PDO::FETCH_ASSOC ) ) {
				$school_mission_def[ (int) $row['school_id'] ][ (int) $row['mission_id'] ] = true;
			}
		}

		foreach ( $user_school_class as $uid => $sc ) {
			$sid = $sc['school_id'];
			$cid = $sc['class_id'];
			foreach ( $task_subject as $tid => $dummy ) {
				$on = isset( $user_task_def[ $uid ][ $tid ] ) || ( $cid && isset( $class_task_def[ $cid ][ $tid ] ) ) || ( $sid && isset( $school_task_def[ $sid ][ $tid ] ) );
				if ( $on ) {
					$GLOBALS['defaults_cache']['task'][ $uid ][ $tid ] = true;
				}
			}
			foreach ( $mission_ids as $mid ) {
				$on = isset( $user_mission_def[ $uid ][ $mid ] ) || ( $cid && isset( $class_mission_def[ $cid ][ $mid ] ) ) || ( $sid && isset( $school_mission_def[ $sid ][ $mid ] ) );
				if ( $on ) {
					$GLOBALS['defaults_cache']['mission'][ $uid ][ $mid ] = true;
				}
			}
		}
	}
}
*/
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
