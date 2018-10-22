<?php
include_once( __DIR__ . '/traits/BuildModel.php' );

class Platoon extends ActiveRecord\Model implements JsonSerializable {
    use \traits\BuildModel;
    
    static $table_name = 'classes';
    // relationships
    static $belongs_to = [ [ 'school' ] ];
    static $has_many = [ [ 'soldiers', 'order' => 'first, last', 'foreign_key' => 'class_id' ] ];

    static $before_destroy = ['canDestroy'];

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
        return count( $this->users ) == 0;
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