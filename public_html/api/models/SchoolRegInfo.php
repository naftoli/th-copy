<?php
class SchoolRegInfo extends ActiveRecord\Model implements JsonSerializable {
    // relationships
    static $belongs_to = [ ['school'] ];
    // validations
    static $validates_uniqueness_of = [
        [ ['school_id', 'year'], 'message' => '- duplicate record' ]
    ];
    
    public $default = false;
    /**
     * validate() ( custom validation )
     *
     * Validate that the registration deadline is set for type 2 schools
     */
    public function validate() {
        if ( $this->type == 2 && !$this->reg_deadline )
            $this->errors->add('registration_deadline', 'must be present on guaranteed bases');
    }

    public static function getDefault( $school_id, $year ) {
        $instance = new self([
            'school_id' => $school_id, 'year' => $year, 'type' => 3,
            'fee' => 770, 'balance' => 0, 'early_bird' => new DateTime( '2018-09-07 00:00:00' )
        ]);
        $instance->default = true;
        return $instance;
    }

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
}