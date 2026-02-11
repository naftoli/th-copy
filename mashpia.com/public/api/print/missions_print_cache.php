<?php
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
