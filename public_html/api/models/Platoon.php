<?php

class Platoon extends ActiveRecord\Model implements JsonSerializable {
    
    static $table_name = 'classes';
    // relationships
    static $belongs_to = [ [ 'school' ] ];
    static $has_many = [ [ 'users', 'foreign_key' => 'class_id' ] ];

    // ******************************* HELPER FUNCTIONS *******************************
    public function name() {
        return $this->class_grade . ( $this->class_sub ? ' - ' . $this->class_sub : '' );
    }
    public function staff() {
        global $pdo;
        $staff_query = $pdo->prepare(
            'SELECT a.first, a.last, a.username, a.admin_email, a.admin_id FROM admins a '
            .'JOIN admin_auths aa USING( admin_id ) WHERE aa.auth="class" AND aa.id=?;'
        );
        $staff_query->execute([ $this->class_id ]);
        return $staff_query->fetchAll();
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