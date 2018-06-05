<?php
class User extends ActiveRecord\Model implements JsonSerializable {
    // relationships
    static $belongs_to = [
        [ 'school' ], [ 'platton', 'foreign_key' => 'class_id' ]
    ];

    // ******************************* SERIALIZERS *******************************
    /**
     * jsonSerialize
     * 
     * serialize object to array
     * 
     * @return array
     */
    public function jsonSerialize(){
        return $this->to_array();
    }

    /**
     * publicSerialize
     * 
     * serialze school for public endpoints
     *
     * @return array
     */
    public function publicSerialize(){
        return $this->to_array([
            'only' => [ 'user_serial', 'first', 'last' ],
            'include' => [ 
                'school' => [ 'only' => [ 'school_id', 'school_name' ] ],
                'platton' => [ 'only' => [ 'class_id', 'class_grade', 'class_sub' ] ]
            ]
        ]);
    }
}