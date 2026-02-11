<?php
require_once dirname(__FILE__) . '/classes/defaults_exceptions_cache.php';

class Defaults {   
    private $school;
    private $class;
    private $user;
    private $type;

    public function __construct($id, $type='user') {
        $this->class = 0;
        $this->user = 0; 
        $this->type = $type;
        if ($type == 'user') {
            $this->user = (int) $id;
            $cached = DefaultsAndExceptionsCache::getDefaults( 'user', $this->user );
            if ( $cached !== null ) {
                $this->school = $cached['school'];
                $this->class = $cached['class'];
            } else {
                $sql = "select school_id, class_id from users where user_id = " . $this->user;
                $result = mysql_query( $sql );
                $row = mysql_fetch_assoc( $result );
                $this->school = $row ? (int) $row['school_id'] : 0;
                $this->class = ( $row && $row['class_id'] ) ? (int) $row['class_id'] : 0;
            }
            $this->ensureCache();
        } else if ($type == 'class') {
            $this->class = (int) $id;
            $cached = DefaultsAndExceptionsCache::getDefaults( 'class', $this->class );
            if ( $cached !== null ) {
                $this->school = $cached['school'];
            } else {
                $sql = "select school_id from classes where class_id = " . $this->class;
                $result = mysql_query($sql);
                $row = mysql_fetch_assoc($result);
                $this->school = $row ? (int) $row['school_id'] : 0;
            }
            $this->ensureCache();
        } else {
            $this->school = (int) $id;
            $this->ensureCache();
        }
    }

    private function ensureCache() {
        $entityId = $this->{$this->type};
        if ( DefaultsAndExceptionsCache::getDefaults( $this->type, $entityId ) !== null ) {
            return;
        }
        $data = [
            'school' => $this->school,
            'class'  => $this->class,
            'user_missions' => [], 'class_missions' => [], 'school_missions' => [],
            'user_tasks' => [], 'class_tasks' => [], 'school_tasks' => [],
        ];
        if ( $this->type === 'user' && $this->user ) {
            $r = mysql_query( "SELECT mission_id FROM user_missions WHERE user_id = " . $this->user );
            while ( $row = mysql_fetch_assoc( $r ) ) $data['user_missions'][] = (int) $row['mission_id'];
            $r = mysql_query( "SELECT task_id FROM user_tasks WHERE user_id = " . $this->user );
            while ( $row = mysql_fetch_assoc( $r ) ) $data['user_tasks'][] = (int) $row['task_id'];
        }
        if ( $this->class ) {
            $r = mysql_query( "SELECT mission_id FROM class_missions WHERE class_id = " . $this->class );
            while ( $row = mysql_fetch_assoc( $r ) ) $data['class_missions'][] = (int) $row['mission_id'];
            $r = mysql_query( "SELECT task_id FROM class_tasks WHERE class_id = " . $this->class );
            while ( $row = mysql_fetch_assoc( $r ) ) $data['class_tasks'][] = (int) $row['task_id'];
        }
        if ( $this->school ) {
            $r = mysql_query( "SELECT mission_id FROM school_missions WHERE school_id = " . $this->school );
            while ( $row = mysql_fetch_assoc( $r ) ) $data['school_missions'][] = (int) $row['mission_id'];
            $r = mysql_query( "SELECT task_id FROM school_tasks WHERE school_id = " . $this->school );
            while ( $row = mysql_fetch_assoc( $r ) ) $data['school_tasks'][] = (int) $row['task_id'];
        }
        DefaultsAndExceptionsCache::setDefaults( $this->type, $entityId, $data );
    }

    public function addOn($id, $table) {
        $typeID = $this->{$this->type};
        $sql = "INSERT ignore into {$this->type}_{$table}s values ($typeID, $id)";
        mysql_query($sql);
        $sql = "SELECT date_tasks_mission_id FROM date_tasks WHERE date_task_id = " . (int) $id;
        $result = mysql_query($sql);
        if (mysql_num_rows($result) > 0) {
            $row = mysql_fetch_assoc($result);
            $missionID = $row['date_tasks_mission_id'];
            mysql_query("INSERT ignore INTO {$this->type}_missions VALUES ($typeID, $missionID)");
        }
        DefaultsAndExceptionsCache::clearDefaults( $this->type, $typeID );
    }

    public function deleteOn($id, $table) {
        $typeID = $this->{$this->type};
        $sql = "delete from {$this->type}_{$table}s where {$this->type}_id = $typeID and task_id = " . (int) $id;
        mysql_query($sql);
        $sql = "select date_tasks_mission_id from date_tasks where date_task_id = " . (int) $id;
        $result = mysql_query($sql);
        if (mysql_num_rows($result) > 0) {
            $row = mysql_fetch_assoc($result);
            $missionID = $row['date_tasks_mission_id'];
            mysql_query("delete from {$this->type}_missions where {$this->type}_id = $typeID and mission_id = $missionID");
        }
        DefaultsAndExceptionsCache::clearDefaults( $this->type, $typeID );
    }

    private function isOnFromCache( $id, $table ) {
        $entityId = $this->{$this->type};
        $data = DefaultsAndExceptionsCache::getDefaults( $this->type, $entityId );
        if ( $data === null ) return null;
        $col = $table === 'mission' ? 'missions' : 'tasks';
        $sets = [];
        if ( ! empty( $data['user_' . $col ] ) ) $sets[] = $data['user_' . $col ];
        if ( ! empty( $data['class_' . $col ] ) ) $sets[] = $data['class_' . $col ];
        if ( ! empty( $data['school_' . $col ] ) ) $sets[] = $data['school_' . $col ];
        $merged = $sets ? array_merge( ...$sets ) : [];
        if ( is_array( $id ) ) {
            foreach ( $id as $one ) {
                if ( in_array( (int) $one, $merged, true ) ) return true;
            }
            return false;
        }
        return in_array( (int) $id, $merged, true );
    }

    public function isOn($id, $table) {
        $fromCache = $this->isOnFromCache( $id, $table );
        if ( $fromCache !== null ) return $fromCache;
        if (!$this->class && !$this->user) {
            if (is_array( $id )) {
                $sql3 = "select * from school_{$table}s where school_id = $this->school and {$table}_id in (" . implode(',', array_map('intval', $id)) . ")";
            } else {
                $sql3 = "select * from school_{$table}s where school_id = $this->school and {$table}_id = " . (int) $id;
            }
            $result3 = mysql_query($sql3);
            return mysql_num_rows($result3) > 0;
        } else if (!$this->user) {
            if (is_array( $id )) {
                $sql2 = "SELECT * from class_{$table}s where class_id = $this->class and {$table}_id in (" . implode(',', array_map('intval', $id)) . ")";
                $sql3 = "SELECT * from school_{$table}s where school_id = $this->school and {$table}_id in (" . implode(',', array_map('intval', $id)) . ")";
            } else {
                $sql2 = "SELECT * from class_{$table}s where class_id = $this->class and {$table}_id = " . (int) $id;
                $sql3 = "SELECT * from school_{$table}s where school_id = $this->school and {$table}_id = " . (int) $id;
            }
            $result2 = mysql_query($sql2);
            $result3 = mysql_query($sql3);
            return ( $result2 && mysql_num_rows( $result2 ) > 0 ) || ( $result3 && mysql_num_rows( $result3 ) > 0 );
        } else {
            if (is_array( $id )) {
                $sql1 = "select * from user_{$table}s where user_id = $this->user and {$table}_id in (" . implode(',', array_map('intval', $id)) . ")";
                $sql2 = "select * from class_{$table}s where class_id = $this->class and {$table}_id in (" . implode(',', array_map('intval', $id)) . ")";
                $sql3 = "select * from school_{$table}s where school_id = $this->school and {$table}_id in (" . implode(',', array_map('intval', $id)) . ")";
            } else {
                $sql1 = "select * from user_{$table}s where user_id = $this->user and {$table}_id = " . (int) $id;
                $sql2 = "select * from class_{$table}s where class_id = $this->class and {$table}_id = " . (int) $id;
                $sql3 = "select * from school_{$table}s where school_id = $this->school and {$table}_id = " . (int) $id;
            }
            $result1 = mysql_query($sql1);
            $result2 = mysql_query($sql2);
            $result3 = mysql_query($sql3);
            return mysql_num_rows($result1) > 0 || mysql_num_rows($result2) > 0 || mysql_num_rows($result3) > 0;
        }
    }
}
?>