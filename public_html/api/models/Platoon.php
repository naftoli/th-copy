<?php
include_once( __DIR__ . '/traits/BuildModel.php' );

class Platoon extends ActiveRecord\Model implements JsonSerializable {
    use \traits\BuildModel;
    
    static $table_name = 'classes';
    // relationships
    static $belongs_to = [ [ 'school' ] ];
    static $has_many = [ [ 'users', 'foreign_key' => 'class_id' ] ];

    // ******************************* HELPER FUNCTIONS *******************************
    public function name() {
        return $this->class_grade . ( $this->class_sub ? ' - ' . $this->class_sub : ' ' );
    }
    public function staff() {
        global $pdo;
        $staff_query = $pdo->prepare(
            'SELECT a.first, a.last, a.username, a.admin_email as email, a.admin_id FROM admins a '
            .'JOIN admin_auths aa USING( admin_id ) WHERE aa.auth="class" AND aa.id=?;'
        );
        $staff_query->execute([ $this->class_id ]);
        return $staff_query->fetchAll();
    }

    public function validateAccess( $login ){
        if ( $login['code'] === 'HQ' ) return true;
        if ( $login['code'] === 'CKIDS-ADMIN' ) return !!$this->school->ckids;
        if ( $login['code'] === 'BC' ) return $this->school->school_id == $login['id'];
        // if ( $login['code'] === 'TEACHER' ) return $this->class_id == $login['id'];
        return false;
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
        ]);
    }
}