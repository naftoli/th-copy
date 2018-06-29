<?php
include_once( __DIR__ . '/traits/BuildModel.php' );

class Platoon extends ActiveRecord\Model implements JsonSerializable {
    use traits\BuildModel;
    
    static $table_name = 'classes';
    // relationships
    static $belongs_to = [ [ 'school' ] ];
    static $has_many = [ [ 'users', 'foreign_key' => 'class_id' ] ];

    // ******************************* HELPER FUNCTIONS *******************************
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