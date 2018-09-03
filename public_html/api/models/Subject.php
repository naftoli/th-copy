<?php

include_once( __DIR__ . '/traits/BuildModel.php' );

class Subject extends ActiveRecord\Model implements JsonSerializable {
    use \traits\BuildModel;

    static $has_many = [ 'achievement_tasks' ];

    public function logoPath() {
        if ( $this->subject_logo )
            return '/mobile/img_new/campaign-logos-bw/'.$this->subject_logo;
        if ( $this->subject_image_id )
            return '/file_view.php?id='.$this->subject_image_id;
        return '/mobile/img_new/campaign-logos-bw/Footsteps.gif';
    }

    // serialize to json
    public function jsonSerialize() {
        return $this->to_array([
            'only' => [ 'subject_name', 'subject_id', 'subject_type' ],
            'include' => [ 'achievement_tasks' ]
        ]);
    }
}