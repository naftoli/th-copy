<?php 
include_once( __DIR__ . "/../../classes/user.php" );
include_once( __DIR__ . "/../../classes/user_track.php" );
include_once( __DIR__ . "/../../classes/school_class.php" );
include_once( __DIR__ . "/../../class.taskExceptions.php" );
include_once( __DIR__ . "/../../classes/date_tasks_mission.php" );
include_once( __DIR__ . "/../../classes/date_tasks_mission_for_duch.php" );
include_once( __DIR__ . "/../../classes/daily_task.php" );
include_once( __DIR__ . "/../../classes/weekly_task.php" );
include_once( __DIR__ . "/../../classes/shabbos_task.php" );
include_once( __DIR__ . "/../../classes/no_label_task.php" );
include_once( __DIR__ . "/../../classes/task.php" );
include_once( __DIR__ . "/../../classes/date_tasks_mark.php" );
include_once( __DIR__ . "/../../classes/pesukim_task.php" );
include_once( __DIR__ . "/../../class.defaults.php" );

class Missions {
	const MAX_USER_IDS_IN_QUERY = 500;

	protected $school;
	protected $grade;
	protected $user;
	protected $start;
	protected $end;
	protected $school_type_id;
	protected $missions;

	public function __construct( $start, $end, $user = 0, $school = 0, $grade = 0, $allowPersonalization = true, $printing_mode = false, $by_date_range = false, $for_duch = false ) {
		$this->school = $school;
		$this->grade = $grade;
		$this->user = $user;
		$this->start = $start;
		$this->end = $end;
		$this->missions = array();
		$this->createMissions( $allowPersonalization, $printing_mode, $by_date_range, $for_duch );
	}

	private function createMissions( $allowPersonalization, $printing_mode = false, $by_date_range = false, $for_duch = false ) {
		$sql = "SELECT u.* FROM users u"
				." JOIN classes c ON u.class_id = c.class_id"
				." WHERE u.user_registered > 0";
		if ( $this->school ) {
			$sql .= " AND u.school_id = " . (int) $this->school;
		}
		if ( $this->grade ) {
			$sql .= " AND u.class_id = " . (int) $this->grade;
		}
		// Avoid huge IN / FIELD() for entire school: use school filter and class order when list is too long
		$use_school_instead = is_array( $this->user ) && count( $this->user ) > self::MAX_USER_IDS_IN_QUERY && $this->school;
		if ( $use_school_instead ) {
			$sql .= " ORDER BY c.class_grade, c.class_sub, u.last, u.first";
		} elseif ( is_array( $this->user ) && ! empty( $this->user ) ) {
			$ids = array_map( 'intval', $this->user );
			$sql .= " AND u.user_id IN (" . implode( ',', $ids ) . ")";
			$sql .= " ORDER BY FIELD(u.user_id, " . implode( ',', $ids ) . ")";
		} elseif ( $this->user ) {
			$sql .= " AND u.user_id = " . (int) $this->user;
			$sql .= " ORDER BY c.class_grade, c.class_sub, u.last, u.first";
		} else {
			$sql .= " ORDER BY c.class_grade, c.class_sub, u.last, u.first";
		}

		$query = mysql_query( $sql );
		$rows = array();
		while ( $row = mysql_fetch_assoc($query) ) {
			$rows[] = $row;
		}

		// When loading multiple users, batch-load ranks and classes to reduce queries
		$ranks_by_user = array();
		$classes_by_id = array();
		if ( ( is_array( $this->user ) || count( $rows ) > 1 ) && count( $rows ) > 0 ) {
			$user_ids = array_unique( array_column( $rows, 'user_id' ) );
			$class_ids = array_unique( array_filter( array_column( $rows, 'class_id' ) ) );

			if ( ! empty( $user_ids ) ) {
				$rids = array_map( 'intval', $user_ids );
				$chunks = array_chunk( $rids, self::MAX_USER_IDS_IN_QUERY );
				foreach ( $chunks as $chunk ) {
					$rq = mysql_query( "SELECT rm.user_id, r.rank_ord, r.rank_name, r.rank_image_id, rm.date_promoted
						FROM rank_marks rm
						JOIN ranks r USING(rank_ord)
						WHERE rm.user_id IN (" . implode( ',', $chunk ) . ")
						ORDER BY rm.user_id, r.rank_ord DESC" );
					while ( $r = mysql_fetch_assoc( $rq ) ) {
						if ( ! isset( $ranks_by_user[ $r['user_id'] ] ) ) {
							$ranks_by_user[ $r['user_id'] ] = $r;
						}
					}
				}
			}
			if ( ! empty( $class_ids ) ) {
				$cids = array_map( 'intval', $class_ids );
				$cq = mysql_query( "SELECT * FROM classes WHERE class_id IN (" . implode( ',', $cids ) . ")" );
				while ( $c = mysql_fetch_assoc( $cq ) ) {
					$classes_by_id[ $c['class_id'] ] = new school_class( $c );
				}
			}
		}

		foreach ( $rows as $row ) {
		    if ( ! $this->school_type_id ) $this->school_type_id = $row['school_type_id'];
		    $user = new user( $row );
		    if ( isset( $ranks_by_user[ $row['user_id'] ] ) ) {
				$r = $ranks_by_user[ $row['user_id'] ];
				$user->rank_ord = $r['rank_ord'];
				$user->rank_name = $r['rank_name'];
				$user->rank_image_id = $r['rank_image_id'];
				$user->date_promoted = $r['date_promoted'];
			} else {
				$user->get_rank();
			}
			if ( isset( $classes_by_id[ $row['class_id'] ] ) ) {
				$user->school_class = $classes_by_id[ $row['class_id'] ];
			} else {
				$user->get_school_class();
			}
			if ( $for_duch ) {
				$user->for_duch = true;
			}
			if ( ! $allowPersonalization && $row['school_id'] == 255 ) $user->disablePersonalization();
			$lang_id = $row['school_id'] == 255 ? ( $user->lang_id == 1 ? $user->lang_id : 2 ) : $user->lang_id;
		    $user->get_user_tracks( -1, $this->start, $this->end, array(), $lang_id, $printing_mode, $by_date_range );
		    array_push( $this->missions, $user );
		}
	}
	
	public function getMissions() {
		return $this->missions;
	}
}
?>