<?php

class AdminAuth extends ActiveRecord\Model implements JsonSerializable {
    static $before_create = ['setDefaultRole', 'setDefaultPosition'];
    
    static $belongs_to = [
        [ 'admin' ]
    ];

    static $validates_uniqueness_of = [
        [['admin_id','auth','id'], 'message' => 'Auth already Exists']
    ];

    public static function findAuth( $admin_id, $auth, $auth_id ) {
        return self::find_by_admin_id_and_auth_and_id( $admin_id, $auth, $auth_id );
    }
    // set the default role for the connetion
    public function setDefaultRole() {
        if ( $this->role_id ) return true;
        if ( $this->auth === 'user' ) {
            $this->role_id = 1;
        } else if ( $this->auth === 'class' ) {
            $this->role_id = 13;
        } else if ( $this->auth === 'school' ) {
            $this->role_id = 18;
        }
    }
    // set the default position for the auth
    public function setDefaultPosition() {
        if ( $this->position ) return true;
        if ( $this->auth === 'user' ) {
            $this->position = 'Parent';
        } else if ( $this->auth === 'class' ) {
            $this->position = 'Teacher';
        } else if ( $this->auth === 'school' ) {
            $this->position = 'Base Commander';
        }
    }

    public function jsonSerialize() {
        return $this->to_array();
    }
}
