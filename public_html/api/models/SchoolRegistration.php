<?php

class SchoolRegistration extends ActiveRecord\Model implements JsonSerializable {

    static $belongs_to = [ ['school'], ['admin'], ['soldier'] ];
    // validations
    static $validates_uniqueness_of = [
        [ ['school_id', 'year'], 'message' => '- duplicate record' ]
    ];

    public function validate() {
        if ( !in_array( $this->type, [ 1, 2, 3 ] ) )
            $this->errors->add('type', 'must be a valid option');
    }

    public function jsonSerialize(){
        return $this->to_array();
    }
}
?>