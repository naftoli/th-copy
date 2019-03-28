<?php

class SchoolRegistration extends ActiveRecord\Model implements JsonSerializable {
    use traits\BuildModel;
    
    static $belongs_to = [ ['school'], ['admin'], ['soldier'] ];
    // validations
    static $validates_uniqueness_of = [
        [ ['school_id', 'year'], 'message' => '- duplicate record' ]
    ];
    // callbacks
    static $before_save = [ 'cleanup' ];

    public function validate() {
        if ( !in_array( $this->type, [ 1, 2, 3 ] ) )
            $this->errors->add('type', 'must be a valid option');
    }

    public function jsonSerialize(){
        $array = $this->to_array();
        $array['modules'] = json_decode( $array['modules'] );
        return $array;
    }

    public function cleanup(){
        // encode the modules to JSON
        if ( is_array( $this->modules ) )
            $this->modules = json_encode( $this->modules );
    }
}
?>