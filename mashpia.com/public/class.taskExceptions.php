<?php
if ( ! class_exists( 'DefaultsAndExceptionsCache' ) ) {
    require_once dirname(__FILE__) . '/classes/defaults_exceptions_cache.php';
}

class TaskExceptions {

    public function __construct() {
    }

    private function ensureExceptionCache( $userID ) {
        $userID = (int) $userID;
        if ( DefaultsAndExceptionsCache::getExceptionUserContext( $userID ) !== null ) {
            return;
        }
        $sql = "SELECT school_id, class_id FROM users WHERE user_id = " . $userID;
        $result = mysql_query( $sql );
        $row = mysql_fetch_assoc( $result );
        $schoolID = $row ? (int) $row['school_id'] : null;
        $classID = $row && $row['class_id'] ? (int) $row['class_id'] : null;
        DefaultsAndExceptionsCache::setExceptionUserContext( $userID, $schoolID ?: 0, $classID ?: 0 );

        if ( $schoolID ) {
            $taskIds = [];
            $r = mysql_query( "SELECT date_task_id FROM school_task_exceptions WHERE school_id = " . $schoolID );
            while ( $row = mysql_fetch_assoc( $r ) ) $taskIds[] = (int) $row['date_task_id'];
            DefaultsAndExceptionsCache::setExceptionSchoolTasks( $schoolID, $taskIds );
            $subjects = [];
            $r = mysql_query( "SELECT subject_id FROM school_subjects WHERE school_id = " . $schoolID );
            while ( $row = mysql_fetch_assoc( $r ) ) $subjects[] = (int) $row['subject_id'];
            DefaultsAndExceptionsCache::setExceptionSchoolSubjects( $schoolID, $subjects );
        }
        if ( $classID ) {
            $taskIds = [];
            $r = mysql_query( "SELECT date_task_id FROM class_task_exceptions WHERE class_id = " . $classID );
            while ( $row = mysql_fetch_assoc( $r ) ) $taskIds[] = (int) $row['date_task_id'];
            DefaultsAndExceptionsCache::setExceptionClassTasks( $classID, $taskIds );
        }
        $taskIds = [];
        $r = mysql_query( "SELECT date_task_id FROM user_task_exceptions WHERE user_id = " . $userID );
        while ( $row = mysql_fetch_assoc( $r ) ) $taskIds[] = (int) $row['date_task_id'];
        DefaultsAndExceptionsCache::setExceptionUserTasks( $userID, $taskIds );
    }

    private function getTaskSubjectId( $taskID ) {
        $taskID = (int) $taskID;
        $cached = DefaultsAndExceptionsCache::getTaskSubject( $taskID );
        if ( $cached !== null ) return $cached;
        $sql = "SELECT subject_id FROM date_tasks dt JOIN date_tasks_missions dtm USING (date_tasks_mission_id) WHERE dt.date_task_id = " . $taskID;
        $result = mysql_query( $sql );
        $row = mysql_fetch_assoc( $result );
        $sid = $row ? (int) $row['subject_id'] : null;
        if ( $sid !== null ) DefaultsAndExceptionsCache::setTaskSubject( $taskID, $sid );
        return $sid;
    }

    public function isException( $taskID, $userID ) {
        $taskID = (int) $taskID;
        $userID = (int) $userID;
        $this->ensureExceptionCache( $userID );

        $ctx = DefaultsAndExceptionsCache::getExceptionUserContext( $userID );
        $schoolID = $ctx['school_id'];
        $classID = $ctx['class_id'];

        if ( $schoolID ) {
            $subjectId = $this->getTaskSubjectId( $taskID );
            if ( $subjectId !== null ) {
                $enrolled = DefaultsAndExceptionsCache::getExceptionSchoolSubjects( $schoolID );
                if ( $enrolled !== null && ! in_array( $subjectId, $enrolled, true ) ) {
                    return true;
                }
            }
            $schoolTasks = DefaultsAndExceptionsCache::getExceptionSchoolTasks( $schoolID );
            if ( $schoolTasks !== null && in_array( $taskID, $schoolTasks, true ) ) {
                return true;
            }
        }

        if ( $classID ) {
            $classTasks = DefaultsAndExceptionsCache::getExceptionClassTasks( $classID );
            if ( $classTasks !== null && in_array( $taskID, $classTasks, true ) ) {
                return true;
            }
        }

        $userTasks = DefaultsAndExceptionsCache::getExceptionUserTasks( $userID );
        if ( $userTasks !== null && in_array( $taskID, $userTasks, true ) ) {
            return true;
        }

        return false;
    }
}