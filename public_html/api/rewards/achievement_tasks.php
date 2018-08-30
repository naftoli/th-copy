<?php
define( "MASHPIA_AUTH_REQUIRED", true );
include_once( __DIR__ . "/../header/header.php" );

class TasksRouter {

    public function index() {
        global $current_user;

        $inst_id = 2; // default institution id.

        $base_filter = 'base = 1';
        $platoon_filter = 'platoon = 1';
        try {
            if ( $current_user->login['code'] == 'INST' ) {
                $inst_id = $current_user->login['id'];
                $base_filter .= " OR base IN ( SELECT school_id FROM schools WHERE inst_id = $inst_id ) ";
            } else if ( $current_user->login['code'] == 'BC' ) {
                $inst_id = School::find( $current_user->login['id'] )->inst_id;
                $base_filter .= ' OR base = ' . $current_user->login['id'];
            } else if ( $current_user->login['code'] == 'TEACHER' ) {
                $platoon = Platoon::find( $current_user->login['id'] );
                $inst_id = $platoon->school->inst_id;
                $base_filter .= ' OR base = ' . $school->school_id;
                $platoon_filter .= ' OR platoon = ' . $platoon->class_id;
            } 
        } catch ( Exception $e ) {};

        $tasks = AchievementTask::all([
            'joins' => 'JOIN subjects s USING ( subject_id )',
            'conditions' => 
                "inst_id IN (0, $inst_id) "
                ." AND s.subject_type IN ('', 'WWTC', 'Tanya', 'achievement')"
                ." AND ( $base_filter ) AND ( $platoon_filter )",
            'include' => [ 'subject' ],
        ]);

        json_response( $tasks );
    }

    public function create() {
        json_response( $_POST );
    }
}

rest_router( new TasksRouter );
