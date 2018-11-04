<?php

include_once( __DIR__ . '/traits/BuildModel.php' );

class Subject extends ActiveRecord\Model implements JsonSerializable {
    use \traits\BuildModel;

    private $achievement_tasks = [];

    public function logoPath() {
        if ( $this->subject_logo )
            return '/mobile/img_new/campaign-logos-bw/'.$this->subject_logo;
        if ( $this->subject_image_id )
            return '/file_view.php?id='.$this->subject_image_id;
        return '/mobile/img_new/campaign-logos-bw/Footsteps.gif';
    }

    public function getAchievementTasks( $login ) {
        $inst_id = 2; // default institution id.
        $base_filter = 'base = 1 ';
        $platoon_filter = 'platoon = 1 ';
        // generate the filters
        if ( $login->code == 'INST' ) {
            $base_filter .= 'OR base IN ( SELECT school_id FROM schools WHERE inst_id = '.$login->inst_id.' ) ';
        } else if ( $login->code == 'BC' ) {
            $base_filter .= 'OR base = ' . $login->school_id;
        } else if ( $login->code == 'TEACHER' ) {
            $base_filter .= 'OR base = ' . $login->school_id;
            $platoon_filter .= 'OR platoon = ' . $login->class_id;
        }
        
        return $this->achievement_tasks = AchievementTask::all([
            'conditions' => [
                "subject_id = ? AND ( $base_filter ) AND ( $platoon_filter )",
                $this->subject_id
            ]
        ]);
    }

    // serialize to json
    public function jsonSerialize() {
        return $this->to_array([
            'only' => [ 'subject_name', 'subject_id', 'subject_type' ],
        ]);
    }

    public function includeTasksJSON( $login ) {
        $res = $this->to_array([
            'only' => [ 'subject_name', 'subject_id', 'subject_type' ],
        ]);

        $res['achievement_tasks'] = $this->getAchievementTasks( $login );

        return $res;
    }
}