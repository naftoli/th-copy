<?php

class SchoolRegistration extends ActiveRecord\Model implements JsonSerializable {

    static $belongs_to = [ ['school'], ['admin'], ['user'] ];
    // validations
    static $validates_uniqueness_of = [
        [ ['school_id', 'year'], 'message' => '- duplicate record' ]
    ];

    public function validate() {
        if ( !in_array( $this->type, [ 1, 2, 3 ] ) )
            $this->errors->add('type', 'must be a valid option');
    }
    // not a default instance
    public $default = false;

    public static function getDefaultEarlyBird() {
        return new DateTime( '2018-09-07 00:00:00' );
    }

    public static function getDefault( $school_id, $type, $year ) {
        $instance = new self([
            'school_id' => $school_id, 'year' => $year, 'type' => $type, 'fee' => 770, 
            'balance' => 0, 'early_bird' => self::getDefaultEarlyBird()
        ]);
        $instance->default = true;
        return $instance;
    }

    public function getChildFee( $is_school = false, $for_type = false, $no_discount = false ){
        if ( !$for_type ) $for_type = $this->type;
        // if we have a custom fee...
        if ( $this->child_fee > 0 ) {
            $fee = $this->child_fee;
        // if we do not. get the default rates
        } else {
            $fee = 0;
        }
        // is the early bird done...
        $early_bird = $this->early_bird > new DateTime();
        return GlobalSettings::calculateChildFee( $for_type, $fee, $is_school, $early_bird, $no_discount );
    }

    public function jsonSerialize(){
        return array_merge(
            $this->to_array(),
            [ 'default' => $this->default ]
        );
    }
}
?>