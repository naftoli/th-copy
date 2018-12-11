<?php
/**
 * Due to how the DBS is structured, 
 * it is $this->class not $this->platoon ( which is the same as $this->class_id )
 */
include_once( __DIR__ . '/traits/BuildModel.php' );

class AchievementCard extends ActiveRecord\Model implements JsonSerializable {
    use \traits\BuildModel;

    static $connection = 'pointsDB';

    static $belongs_to = [
        [ 'school', 'class_name' => 'School', 'foreign_key' => 'institution_id' ],
        [ 'class', 'class_name' => 'Platoon', 'foreign_key' => 'class_id' ],
        [ 'subject', 'class_name' => 'Subject', 'foreign_key' => 'campaign_id' ],
        [ 'task', 'class_name' => 'AchievementTask', 'foreign_key' => 'task_id' ],
    ];

    // serialize to json
    public function jsonSerialize() {
        return $this->to_array([
            // 'include' => [
            //     'school', 'class', 'task', 
            //     'subject' => [ 'only' => [ 'subject_name', 'subject_id', 'subject_type' ] ]
            // ],
            // 'methods' => [ 'platoonName', 'baseName', 'editable' ]
        ]);
    }
}