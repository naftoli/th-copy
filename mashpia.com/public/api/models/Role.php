<?php

class Role extends ActiveRecord\Model implements JsonSerializable  {
    static $has_many = [ [ 'admin_auths' ] ];
    static $alias_attribute = [ 'role' => 'role_name' ];

    public function jsonSerialize() {
        return $this->to_array();
    }
}