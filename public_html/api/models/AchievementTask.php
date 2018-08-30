<?php
/**
 * Due to how the DBS is structured, 
 * it is $this->class not $this->platoon ( which is the same as $this->class_id )
 */
include_once( __DIR__ . '/traits/BuildModel.php' );

class AchievementTask extends ActiveRecord\Model implements JsonSerializable {
    use \traits\BuildModel;

    static $belongs_to = [
        [ 'school', 'class_name' => 'School', 'foreign_key' => 'base' ],
        [ 'class', 'class_name' => 'Platoon', 'foreign_key' => 'platoon' ],
        [ 'subject' ]
    ];

    static $alias_attribute = [
        'class_id' => 'platoon', 'school_id' => 'base'
    ];

    public function platoonName() {
        return $this->class_id > 1 ? $this->class->name() : 'All Platoons';
    }

    public function baseName() {
        return $this->school_id > 1 ? $this->school->name() : 'All Bases';
    }

    // serialize to json
    public function jsonSerialize() {
        return $this->to_array([
            'include' => [
                'subject' => [ 'only' => [ 'subject_name', 'subject_id', 'subject_type' ] ]
            ],
            'methods' => [ 'platoonName', 'baseName' ]
        ]);
    }
}