<?php
/**
 * Due to how the DBS is structured, 
 * it is $this->class not $this->platoon ( which is the same as $this->class_id )
 */
include_once( __DIR__ . '/traits/BuildModel.php' );

class StorePrize extends ActiveRecord\Model implements JsonSerializable {
    use \traits\BuildModel;

    static $connection = 'pointsDB';
    static $table_name = 'prizes';
    static $primary_key = 'prize_id';

    const IMG_PATH = '/v2/images/imgsrepo/';

    static $alias_attribute = [
        'miles' => 'points',
        'stock' => 'prize_count'
    ];

    public function image() {
        return self::IMG_PATH . $this->image_id;
    }

    static $belongs_to = [
        [ 'school', 'class_name' => 'School', 'foreign_key' => 'institution_id' ],
        [ 'admin', 'class_name' => 'Admin', 'foreign_key' => 'created_by' ],
    //     [ 'subject', 'class_name' => 'Subject', 'foreign_key' => 'campaign_id' ],
    //     [ 'task', 'class_name' => 'AchievementTask', 'foreign_key' => 'task_id' ],
    ];

    // serialize to json
    public function jsonSerialize() {
        return $this->to_array([
            'only' => [
                'prize_id','prize_name', 'institution_id', 'points', 'prize_description', 
                'one_per_user', 'prize_count', 'is_active', 'modified'
            ],
            // 'include' => [
            //     'school', 'class', 'task', 
            //     'subject' => [ 'only' => [ 'subject_name', 'subject_id', 'subject_type' ] ]
            // ],
            'methods' => [ 'image' ]
        ]);
    }
}