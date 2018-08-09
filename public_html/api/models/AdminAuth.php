<?php

class AdminAuth extends ActiveRecord\Model implements JsonSerializable {
    static $belongs_to = [
        [ 'admin' ]
    ];

    static $validates_uniqueness_of = [
        [['admin_id','auth','id'], 'message' => 'Auth already Exists']
    ];

    public static function findAuth( $admin_id, $auth, $auth_id ) {
        return self::find_by_admin_id_and_auth_and_id( $admin_id, $auth, $auth_id );
    }

    public function jsonSerialize() {
        return $this->to_array();
    }
}
