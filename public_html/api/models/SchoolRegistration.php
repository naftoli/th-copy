<?php

class SchoolRegistration extends ActiveRecord\Model implements JsonSerializable {

    static $belongs_to = [ ['school'], ['admin'], ['user'] ];
    // validations
    static $validates_uniqueness_of = [
        [ ['school_id', 'year'], 'message' => '- duplicate record' ]
    ];
    public function validate() {
        if ( $this->type == 2 && !$this->reg_deadline )
            $this->errors->add('registration_deadline', 'must be present on guaranteed bases');
        if ( !in_array( $this->type, [ 1, 2, 3 ] ) )
            $this->errors->add('type', 'must be a valid option');
    }
    // not a default instance
    public $default = false;

    public static function getDefault( $school_id, $type, $year ) {
        $instance = new self([
            'school_id' => $school_id, 'year' => $year, 'type' => $type, 'fee' => 770, 
            'balance' => 0, 'early_bird' => new DateTime( '2018-09-07 00:00:00' )
        ]);
        $instance->default = true;
        return $instance;
    }

    public function jsonSerialize(){
        return array_merge(
            $this->to_array(),
            [ 'default' => $this->default ]
        );
    }
}

?>