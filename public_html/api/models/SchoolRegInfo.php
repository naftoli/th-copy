<?php
class SchoolRegInfo extends ActiveRecord\Model implements JsonSerializable {
    // relationships
    static $belongs_to = [ ['school'] ];
    // validations
    static $validates_uniqueness_of = [
        [ ['school_id', 'year'], 'message' => '- duplicate record' ]
    ];
    
    public function validate() {
        if ( $this->type == 2 && !$this->reg_deadline )
            $this->errors->add('registration_deadline', 'must be present on guaranteed bases');
    }

    public function jsonSerialize(){
        return $this->to_array();
    }
}