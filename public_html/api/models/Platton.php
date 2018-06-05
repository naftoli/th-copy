<?php
class Platton extends ActiveRecord\Model implements JsonSerializable {
    static $table_name = 'classes';

    static $belongs_to = [
        [ 'school' ],
    ];

    public function name() {
        return $this->class_grade . ( $this->class_sub ? ' - ' . $this->class_sub : '' );
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
            'only' => [ 'class_id', 'class_grade', 'class_sub' ],
            'methods' => [ 'name' ],
            'include' => [ 'school' => [ 'only' => [ 'school_id', 'school_name' ] ] ]
        ]);
    }
}