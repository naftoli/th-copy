<?php
include_once( __DIR__ . '/traits/BuildModel.php' );

class Platoon extends ActiveRecord\Model implements JsonSerializable {
    use \traits\BuildModel;
    
    static $table_name = 'classes';
    // relationships
    static $belongs_to = [ [ 'school' ] ];
    static $has_many = [ [ 'soldiers', 'order' => 'last, first', 'foreign_key' => 'class_id' ] ];

    static $before_destroy = ['canDestroy'];
    static $before_update = [ 'updateSoldiers' ];

    // ******************************* HELPER FUNCTIONS *******************************
    public function name() {
        return self::generateName( $this->class_grade, $this->class_sub );
    }

    public static function generateName( $grade, $sub ) {
        return $grade . ( $sub ? ' - ' . $sub : ' ' );
    }

    public function staff() {
        global $MASHPIA_DB;
        $staff_query = $MASHPIA_DB->prepare(
            'SELECT a.first, a.last, a.username, a.admin_email as email, a.admin_id FROM admins a '
            .'JOIN admin_auths aa USING( admin_id ) WHERE aa.auth="class" AND aa.id=?;'
        );
        $staff_query->execute([ $this->class_id ]);
        return $staff_query->fetchAll();
    }

    public function validateAccess( $login ){
        if ( $login->code === 'HQ' ) return true;
        if ( $login->code === 'INST' ) return !!$this->school->inst_id == $login->id;
        if ( $login->code === 'BC' ) return $this->school->school_id == $login->id;
        // if ( $login->code === 'TEACHER' ) return $this->class_id == $login->id;
        return false;
    }

    // ******************************* ONDELETE FUNCTIONS *******************************
    public function canDestroy(){
        return count( $this->soldiers ) == 0;
    }
    
    public function updateSoldiers() {
        global $MASHPIA_DB;
        // allow_parent_tasks
        if ( $this->attribute_is_dirty('allow_parent_tasks') ){
            $update = 'UPDATE users SET allow_parent_tasks = ? WHERE class_id = ?';
            $update = $MASHPIA_DB->prepare( $update );
            $update->execute([ $this->allow_parent_tasks, $this->class_id ]);
        }
        // print_parent_tasks
        if ( $this->attribute_is_dirty('print_parent_tasks') ){
            $update = 'UPDATE users SET print_parent_tasks = ? WHERE class_id = ?';
            $update = $MASHPIA_DB->prepare( $update );
            $update->execute([ $this->print_parent_tasks, $this->class_id ]);
        }
        // pic_mission_type
        if ( $this->attribute_is_dirty('pic_mission_type') ){
            $update = 'UPDATE users SET pic_mission_type = ? WHERE class_id = ?';
            $update = $MASHPIA_DB->prepare( $update );
            $update->execute([ $this->pic_mission_type, $this->class_id ]);
        }
        // save the platoon to the dbs
        return true;
    }

    // ******************************* SERIALIZERS *******************************
    /**
     * jsonSerialize
     * 
     * serialize object to array
     * 
     * @return array
     */
    public function jsonSerialize(){
        return $this->to_array([
            // 'only' => [ 'class_id' ],
            'methods' => [ 'name', 'staff' ],
            'include' => [ 'soldiers' => [
                'only' => [ 'user_id', 'first', 'last', 'user_serial' ],
                'methods' => [ 'rank', 'profilePicture' ]
            ]]
        ]);
    }
}