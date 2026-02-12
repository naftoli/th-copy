<?php
/**
 * Pre-load task rows for mission printing.
 * Sets $GLOBALS['mission_print_tasks'][mission_id][type] = array of rows.
 * Used by date_tasks_mission::get_*_tasks when mission_print_tasks is set.
 */
function build_mission_print_task_cache( $mission_ids ) {
	global $MASHPIA_DB;
	$GLOBALS['mission_print_tasks'] = [];
	if ( empty( $mission_ids ) ) return;
	$mids = array_values( array_unique( array_filter( array_map( 'intval', $mission_ids ) ) ) );
	$placeholders = implode( ',', array_fill( 0, count( $mids ), '?' ) );

	$queries = [
		'daily_tasks'   => "SELECT l.label_name, l.frequency_id, f.frequency_name, fp.frequency_period_name, dt.* FROM date_tasks dt JOIN labels l ON dt.label_id = l.label_id JOIN frequencies f ON l.frequency_id = f.frequency_id JOIN frequency_periods fp ON f.frequency_period_id = fp.frequency_period_id WHERE dt.date_tasks_mission_id IN ($placeholders) AND f.frequency_name = 'Daily' AND dt.mission_marking = 1 ORDER BY dt.label_ord, dt.grid_id",
		'weekly_tasks'  => "SELECT l.label_name, l.frequency_id, f.frequency_name, fp.frequency_period_name, dt.* FROM date_tasks dt JOIN labels l ON dt.label_id = l.label_id JOIN frequencies f ON l.frequency_id = f.frequency_id JOIN frequency_periods fp ON f.frequency_period_id = fp.frequency_period_id WHERE dt.date_tasks_mission_id IN ($placeholders) AND f.frequency_name = 'Weekly' AND dt.mission_marking = 1 ORDER BY dt.label_ord, dt.grid_id",
		'shabbos_tasks' => "SELECT l.label_name, l.frequency_id, f.frequency_name, fp.frequency_period_name, dt.* FROM date_tasks dt JOIN labels l ON dt.label_id = l.label_id JOIN frequencies f ON l.frequency_id = f.frequency_id JOIN frequency_periods fp ON f.frequency_period_id = fp.frequency_period_id WHERE dt.date_tasks_mission_id IN ($placeholders) AND f.frequency_name = 'Shabbos' AND dt.mission_marking = 1 ORDER BY dt.label_ord, dt.grid_id",
		'no_label_tasks'=> "SELECT * FROM date_tasks dt WHERE dt.date_tasks_mission_id IN ($placeholders) AND (dt.label_id IS NULL OR dt.label_id = 0) AND dt.mission_marking = 1 ORDER BY dt.grid_id, dt.ord, dt.date_task_id",
		'pesukim_tasks' => "SELECT l.label_name, l.frequency_id, f.frequency_name, fp.frequency_period_name, dt.* FROM date_tasks dt JOIN labels l ON dt.label_id = l.label_id JOIN frequencies f ON l.frequency_id = f.frequency_id JOIN frequency_periods fp ON f.frequency_period_id = fp.frequency_period_id WHERE dt.date_tasks_mission_id IN ($placeholders) AND f.frequency_name = 'Pesukim' AND dt.mission_marking = 1 ORDER BY dt.label_ord, dt.grid_id",
	];
	foreach ( $queries as $type => $sql ) {
		$stmt = $MASHPIA_DB->prepare( $sql );
		$stmt->execute( $mids );
		while ( $row = $stmt->fetch( PDO::FETCH_ASSOC ) ) {
			$mid = (int) $row['date_tasks_mission_id'];
			if ( ! isset( $GLOBALS['mission_print_tasks'][ $mid ][ $type ] ) ) {
				$GLOBALS['mission_print_tasks'][ $mid ][ $type ] = [];
			}
			$GLOBALS['mission_print_tasks'][ $mid ][ $type ][] = $row;
		}
	}
}

/**
 * Batch-load date_tasks_marks for duch printing.
 * One query per user chunk instead of per day per task.
 * Sets $GLOBALS['duch_marks_cache'][user_id][lookup_key][mark_date] = row (or empty array when no mark).
 * lookup_key: date_task_id for normal tasks, 'grid_' . grid_id for grid-based tasks.
 *
 * @param array $user_ids
 * @param int   $start  Julian day (inclusive)
 * @param int   $end    Julian day (inclusive)
 */
function build_duch_marks_cache( $user_ids, $start, $end ) {
	global $MASHPIA_DB;
	$GLOBALS['duch_marks_cache'] = [];
	if ( empty( $user_ids ) ) return;

	$uids = array_values( array_unique( array_filter( array_map( 'intval', $user_ids ) ) ) );
	$chunk = 500;
	foreach ( array_chunk( $uids, $chunk ) as $chunk_ids ) {
		$placeholders = implode( ',', array_fill( 0, count( $chunk_ids ), '?' ) );
		$params = array_merge( $chunk_ids, [ $start, $end ] );
		$sql = "SELECT dtm.*, dt.grid_id
			FROM date_tasks_marks dtm
			JOIN date_tasks dt ON dtm.date_task_id = dt.date_task_id
			WHERE dtm.user_id IN ($placeholders)
			AND dtm.mark_date >= ? AND dtm.mark_date <= ?";
		$stmt = $MASHPIA_DB->prepare( $sql );
		$stmt->execute( $params );
		while ( $row = $stmt->fetch( PDO::FETCH_ASSOC ) ) {
			$uid = (int) $row['user_id'];
			$dtid = (int) $row['date_task_id'];
			$mid = (int) $row['mark_date'];
			$grid_id = isset( $row['grid_id'] ) && $row['grid_id'] !== '' ? (int) $row['grid_id'] : 0;

			if ( ! isset( $GLOBALS['duch_marks_cache'][ $uid ] ) ) {
				$GLOBALS['duch_marks_cache'][ $uid ] = [];
			}
			$GLOBALS['duch_marks_cache'][ $uid ][ $dtid ][ $mid ] = $row;
			if ( $grid_id ) {
				$gk = 'grid_' . $grid_id;
				$GLOBALS['duch_marks_cache'][ $uid ][ $gk ][ $mid ] = $row;
			}
		}
	}
}

/**
 * Batch-load birthday cache for mission printing.
 * Used by missions.php and printDuchAll.php.
 * Sets $GLOBALS['birthday_cache'] (user_id => [mission_id => true]).
 * Requires $GLOBALS['MASHPIA_DB'] (PDO).
 *
 * @param array $user_ids
 * @param int   $start  Julian day (unused, kept for API compatibility)
 * @param int   $end    Julian day (unused, kept for API compatibility)
 * @param mixed $all_date_tasks_missions  Unused, kept for API compatibility
 */
function build_mission_print_caches( $user_ids, $start, $end, $all_date_tasks_missions = null ) {
	global $MASHPIA_DB;
	$GLOBALS['birthday_cache'] = [];
	if ( empty( $user_ids ) ) {
		return;
	}

	$uids = array_values( array_unique( array_filter( array_map( 'intval', $user_ids ) ) ) );
	$chunk = 500;
	foreach ( array_chunk( $uids, $chunk ) as $chunk_ids ) {
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
