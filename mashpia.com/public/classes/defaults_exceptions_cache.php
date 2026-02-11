<?php
/**
 * Request-scoped cache for Defaults and TaskExceptions lookups.
 * Reduces repeated DB queries when checking mission/task defaults and exceptions
 * in user_track.php, date_tasks_mission.php, etc.
 */
class DefaultsAndExceptionsCache {

    private static $defaults = [];
    private static $exception_user_context = [];
    private static $exception_school_tasks = [];
    private static $exception_school_subjects = [];
    private static $exception_class_tasks = [];
    private static $exception_user_tasks = [];
    private static $task_subject = [];
    private static $birthday_mission_ids = [];

    /**
     * Cache key for Defaults data (user, class, or school).
     * @param string $type 'user'|'class'|'school'
     * @param int $id
     * @return string
     */
    public static function defaultsKey( $type, $id ) {
        return 'defaults:' . $type . ':' . (int) $id;
    }

    /** @param array|null $data [ 'school' => int, 'class' => int, 'user_missions' => [], 'class_missions' => [], 'school_missions' => [], 'user_tasks' => [], 'class_tasks' => [], 'school_tasks' => [] ] */
    public static function getDefaults( $type, $id ) {
        $key = self::defaultsKey( $type, $id );
        return isset( self::$defaults[ $key ] ) ? self::$defaults[ $key ] : null;
    }

    public static function setDefaults( $type, $id, array $data ) {
        self::$defaults[ self::defaultsKey( $type, $id ) ] = $data;
    }

    /** @return array|null [ 'school_id' => int, 'class_id' => int ] */
    public static function getExceptionUserContext( $user_id ) {
        $id = (int) $user_id;
        return isset( self::$exception_user_context[ $id ] ) ? self::$exception_user_context[ $id ] : null;
    }

    public static function setExceptionUserContext( $user_id, $school_id, $class_id ) {
        self::$exception_user_context[ (int) $user_id ] = [ 'school_id' => (int) $school_id, 'class_id' => (int) $class_id ];
    }

    /** @return int[]|null date_task_id list */
    public static function getExceptionSchoolTasks( $school_id ) {
        $id = (int) $school_id;
        return isset( self::$exception_school_tasks[ $id ] ) ? self::$exception_school_tasks[ $id ] : null;
    }

    public static function setExceptionSchoolTasks( $school_id, array $date_task_ids ) {
        self::$exception_school_tasks[ (int) $school_id ] = $date_task_ids;
    }

    /** @return int[]|null subject_id list (enrolled) */
    public static function getExceptionSchoolSubjects( $school_id ) {
        $id = (int) $school_id;
        return isset( self::$exception_school_subjects[ $id ] ) ? self::$exception_school_subjects[ $id ] : null;
    }

    public static function setExceptionSchoolSubjects( $school_id, array $subject_ids ) {
        self::$exception_school_subjects[ (int) $school_id ] = $subject_ids;
    }

    /** @return int[]|null date_task_id list */
    public static function getExceptionClassTasks( $class_id ) {
        $id = (int) $class_id;
        return isset( self::$exception_class_tasks[ $id ] ) ? self::$exception_class_tasks[ $id ] : null;
    }

    public static function setExceptionClassTasks( $class_id, array $date_task_ids ) {
        self::$exception_class_tasks[ (int) $class_id ] = $date_task_ids;
    }

    /** @return int[]|null date_task_id list */
    public static function getExceptionUserTasks( $user_id ) {
        $id = (int) $user_id;
        return isset( self::$exception_user_tasks[ $id ] ) ? self::$exception_user_tasks[ $id ] : null;
    }

    public static function setExceptionUserTasks( $user_id, array $date_task_ids ) {
        self::$exception_user_tasks[ (int) $user_id ] = $date_task_ids;
    }

    /** @return int|null subject_id for date_task_id */
    public static function getTaskSubject( $date_task_id ) {
        $id = (int) $date_task_id;
        return isset( self::$task_subject[ $id ] ) ? self::$task_subject[ $id ] : null;
    }

    public static function setTaskSubject( $date_task_id, $subject_id ) {
        self::$task_subject[ (int) $date_task_id ] = (int) $subject_id;
    }

    /** @return int[]|null date_tasks_mission_ids this user has in birthdays (for birthday-mission filter) */
    public static function getBirthdayMissionIds( $user_id ) {
        $id = (int) $user_id;
        return isset( self::$birthday_mission_ids[ $id ] ) ? self::$birthday_mission_ids[ $id ] : null;
    }

    public static function setBirthdayMissionIds( $user_id, array $date_tasks_mission_ids ) {
        self::$birthday_mission_ids[ (int) $user_id ] = $date_tasks_mission_ids;
    }

    /** Call after mutating defaults/exceptions (e.g. addOn/deleteOn) so next read is fresh. */
    public static function clearDefaults( $type = null, $id = null ) {
        if ( $type === null ) {
            self::$defaults = [];
            return;
        }
        $key = self::defaultsKey( $type, $id );
        unset( self::$defaults[ $key ] );
    }

    /** Call after mutating exceptions so next read is fresh. */
    public static function clearExceptions( $user_id = null ) {
        if ( $user_id === null ) {
            self::$exception_user_context = [];
            self::$exception_school_tasks = [];
            self::$exception_school_subjects = [];
            self::$exception_class_tasks = [];
            self::$exception_user_tasks = [];
            self::$task_subject = [];
            return;
        }
        $uid = (int) $user_id;
        unset( self::$exception_user_context[ $uid ], self::$exception_user_tasks[ $uid ] );
    }

    /**
     * Preload birthday mission IDs for all given users (for birthday-mission filter in user_track).
     *
     * @param int[] $user_ids
     */
    public static function warmBirthdayMissionIdsForUsers( array $user_ids ) {
        $user_ids = array_filter( array_map( 'intval', $user_ids ) );
        if ( empty( $user_ids ) || ! function_exists( 'mysql_query' ) ) {
            return;
        }
        $ids = implode( ',', $user_ids );
        $by_user = [];
        $r = mysql_query( "SELECT user_id, date_tasks_mission_id FROM birthdays WHERE user_id IN ($ids)" );
        if ( $r ) {
            while ( $row = mysql_fetch_assoc( $r ) ) {
                $uid = (int) $row['user_id'];
                if ( ! isset( $by_user[ $uid ] ) ) $by_user[ $uid ] = [];
                $by_user[ $uid ][] = (int) $row['date_tasks_mission_id'];
            }
        }
        foreach ( $user_ids as $uid ) {
            self::setBirthdayMissionIds( $uid, isset( $by_user[ $uid ] ) ? $by_user[ $uid ] : [] );
        }
    }

    /**
     * Preload defaults for all given users (school + class + user missions/tasks) in one batch.
     * Call before looping over users (e.g. at start of printDuchAll) to avoid per-user queries.
     * Uses mysql_* to match Defaults/TaskExceptions. No-op if mysql_query not available.
     *
     * @param int[] $user_ids
     */
    public static function warmDefaultsForUsers( array $user_ids ) {
        $user_ids = array_filter( array_map( 'intval', $user_ids ) );
        if ( empty( $user_ids ) || ! function_exists( 'mysql_query' ) ) {
            return;
        }
        $ids = implode( ',', $user_ids );
        $r = mysql_query( "SELECT user_id, school_id, class_id FROM users WHERE user_id IN ($ids)" );
        if ( ! $r ) return;
        $user_ctx = [];
        $school_ids = [];
        $class_ids = [];
        while ( $row = mysql_fetch_assoc( $r ) ) {
            $uid = (int) $row['user_id'];
            $sid = isset( $row['school_id'] ) ? (int) $row['school_id'] : 0;
            $cid = isset( $row['class_id'] ) && $row['class_id'] ? (int) $row['class_id'] : 0;
            $user_ctx[ $uid ] = [ 'school' => $sid, 'class' => $cid ];
            if ( $sid ) $school_ids[ $sid ] = true;
            if ( $cid ) $class_ids[ $cid ] = true;
        }
        $school_ids = array_keys( $school_ids );
        $class_ids = array_keys( $class_ids );

        $school_missions = [];
        $school_tasks = [];
        if ( ! empty( $school_ids ) ) {
            $sids = implode( ',', $school_ids );
            $r = mysql_query( "SELECT school_id, mission_id FROM school_missions WHERE school_id IN ($sids)" );
            if ( $r ) while ( $row = mysql_fetch_assoc( $r ) ) {
                $school_missions[ (int) $row['school_id'] ][] = (int) $row['mission_id'];
            }
            $r = mysql_query( "SELECT school_id, task_id FROM school_tasks WHERE school_id IN ($sids)" );
            if ( $r ) while ( $row = mysql_fetch_assoc( $r ) ) {
                $school_tasks[ (int) $row['school_id'] ][] = (int) $row['task_id'];
            }
        }
        $class_missions = [];
        $class_tasks = [];
        if ( ! empty( $class_ids ) ) {
            $cids = implode( ',', $class_ids );
            $r = mysql_query( "SELECT class_id, mission_id FROM class_missions WHERE class_id IN ($cids)" );
            if ( $r ) while ( $row = mysql_fetch_assoc( $r ) ) {
                $class_missions[ (int) $row['class_id'] ][] = (int) $row['mission_id'];
            }
            $r = mysql_query( "SELECT class_id, task_id FROM class_tasks WHERE class_id IN ($cids)" );
            if ( $r ) while ( $row = mysql_fetch_assoc( $r ) ) {
                $class_tasks[ (int) $row['class_id'] ][] = (int) $row['task_id'];
            }
        }
        $user_missions = [];
        $user_tasks = [];
        $r = mysql_query( "SELECT user_id, mission_id FROM user_missions WHERE user_id IN ($ids)" );
        if ( $r ) while ( $row = mysql_fetch_assoc( $r ) ) {
            $user_missions[ (int) $row['user_id'] ][] = (int) $row['mission_id'];
        }
        $r = mysql_query( "SELECT user_id, task_id FROM user_tasks WHERE user_id IN ($ids)" );
        if ( $r ) while ( $row = mysql_fetch_assoc( $r ) ) {
            $user_tasks[ (int) $row['user_id'] ][] = (int) $row['task_id'];
        }

        foreach ( $user_ids as $uid ) {
            $ctx = isset( $user_ctx[ $uid ] ) ? $user_ctx[ $uid ] : [ 'school' => 0, 'class' => 0 ];
            $sid = $ctx['school'];
            $cid = $ctx['class'];
            $data = [
                'school' => $sid,
                'class'  => $cid,
                'user_missions' => isset( $user_missions[ $uid ] ) ? $user_missions[ $uid ] : [],
                'class_missions' => $cid && isset( $class_missions[ $cid ] ) ? $class_missions[ $cid ] : [],
                'school_missions' => $sid && isset( $school_missions[ $sid ] ) ? $school_missions[ $sid ] : [],
                'user_tasks' => isset( $user_tasks[ $uid ] ) ? $user_tasks[ $uid ] : [],
                'class_tasks' => $cid && isset( $class_tasks[ $cid ] ) ? $class_tasks[ $cid ] : [],
                'school_tasks' => $sid && isset( $school_tasks[ $sid ] ) ? $school_tasks[ $sid ] : [],
            ];
            self::setDefaults( 'user', $uid, $data );
        }
    }

    /**
     * Preload exception data for all given users (school/class/user task exceptions + school subjects).
     * Call before looping over users (e.g. at start of printDuchAll). Uses mysql_*.
     *
     * @param int[] $user_ids
     */
    public static function warmExceptionCacheForUsers( array $user_ids ) {
        $user_ids = array_filter( array_map( 'intval', $user_ids ) );
        if ( empty( $user_ids ) || ! function_exists( 'mysql_query' ) ) {
            return;
        }
        $ids = implode( ',', $user_ids );
        $r = mysql_query( "SELECT user_id, school_id, class_id FROM users WHERE user_id IN ($ids)" );
        if ( ! $r ) return;
        $school_ids = [];
        $class_ids = [];
        while ( $row = mysql_fetch_assoc( $r ) ) {
            $uid = (int) $row['user_id'];
            $sid = isset( $row['school_id'] ) ? (int) $row['school_id'] : 0;
            $cid = isset( $row['class_id'] ) && $row['class_id'] ? (int) $row['class_id'] : 0;
            self::setExceptionUserContext( $uid, $sid, $cid );
            if ( $sid ) $school_ids[ $sid ] = true;
            if ( $cid ) $class_ids[ $cid ] = true;
        }
        $school_ids = array_keys( $school_ids );
        $class_ids = array_keys( $class_ids );

        if ( ! empty( $school_ids ) ) {
            $sids = implode( ',', $school_ids );
            $taskIds = [];
            $r = mysql_query( "SELECT school_id, date_task_id FROM school_task_exceptions WHERE school_id IN ($sids)" );
            if ( $r ) while ( $row = mysql_fetch_assoc( $r ) ) {
                $taskIds[ (int) $row['school_id'] ][] = (int) $row['date_task_id'];
            }
            foreach ( $school_ids as $sid ) {
                self::setExceptionSchoolTasks( $sid, isset( $taskIds[ $sid ] ) ? $taskIds[ $sid ] : [] );
            }
            $subjects = [];
            $r = mysql_query( "SELECT school_id, subject_id FROM school_subjects WHERE school_id IN ($sids)" );
            if ( $r ) while ( $row = mysql_fetch_assoc( $r ) ) {
                $subjects[ (int) $row['school_id'] ][] = (int) $row['subject_id'];
            }
            foreach ( $school_ids as $sid ) {
                self::setExceptionSchoolSubjects( $sid, isset( $subjects[ $sid ] ) ? $subjects[ $sid ] : [] );
            }
        }
        if ( ! empty( $class_ids ) ) {
            $cids = implode( ',', $class_ids );
            $taskIds = [];
            $r = mysql_query( "SELECT class_id, date_task_id FROM class_task_exceptions WHERE class_id IN ($cids)" );
            if ( $r ) while ( $row = mysql_fetch_assoc( $r ) ) {
                $taskIds[ (int) $row['class_id'] ][] = (int) $row['date_task_id'];
            }
            foreach ( $class_ids as $cid ) {
                self::setExceptionClassTasks( $cid, isset( $taskIds[ $cid ] ) ? $taskIds[ $cid ] : [] );
            }
        }
        $user_task_ids = [];
        $r = mysql_query( "SELECT user_id, date_task_id FROM user_task_exceptions WHERE user_id IN ($ids)" );
        if ( $r ) while ( $row = mysql_fetch_assoc( $r ) ) {
            $user_task_ids[ (int) $row['user_id'] ][] = (int) $row['date_task_id'];
        }
        foreach ( $user_ids as $uid ) {
            self::setExceptionUserTasks( $uid, isset( $user_task_ids[ $uid ] ) ? $user_task_ids[ $uid ] : [] );
        }
    }
}
