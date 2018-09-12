<?php
include_once( __DIR__ . '/../auth/classes/Auth.php' );
include_once( __DIR__ . '/../tools/emails/index.php' );
// This class uses the Authorize.net gateway

class Institution extends ActiveRecord\Model implements JsonSerializable {
    // relationships 
    static $has_many = [ [ 'schools' ] ];
    // validations
    static $validates_uniqueness_of = [ [ 'inst_name' ] ];
    // prop mapping
    static $alias_attribute = [
        'name' => 'inst_name',
    ];

    public function logo() {
        return '/schoolLogos/institutions/'.$this->inst_logo;
    }

    //********************************** SERIALIZERS **********************************/
    public function jsonSerialize() {
        return $this->to_array([
            'only' => [
                'inst_id', 'name'
            ],
            'methods' => [ 'logo' ]
        ]);
    }
}