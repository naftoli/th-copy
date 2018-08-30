<?php

include_once( __DIR__ . '/traits/BuildModel.php' );

class Subject extends ActiveRecord\Model implements JsonSerializable {
    use \traits\BuildModel;

    static $has_many = [ 'achievement_tasks' ];

    // serialize to json
    public function jsonSerialize() {
        return $this->to_array([
            'only' => [ 'subject_name', 'subject_id', 'subject_type' ],
            'include' => [ 'achievement_tasks' ]
        ]);
    }
}