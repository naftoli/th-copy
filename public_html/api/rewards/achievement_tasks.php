<?php
define( "MASHPIA_AUTH_REQUIRED", true );
include_once( __DIR__ . "/../header/header.php" );

class TasksRouter {

    public function index() {
        global $current_user;
        $login = $current_user->login;

        $inst_id = 2; // default institution id.

        $base_filter = false;
        $platoon_filter = false;
        try {
            if ( $login['code'] == 'HQ' ) {
                $base_filter = 'base IN ( SELECT school_id FROM schools WHERE test_school = 0 )';
            } else if ( $login['code'] == 'INST' ) {
                $inst_id = $login['id'];
                $base_filter .= "base IN ( SELECT school_id FROM schools WHERE inst_id = $inst_id ) ";
            } else if ( $login['code'] == 'BC' ) {
                $inst_id = School::find( $login['id'] )->inst_id;
                $base_filter .= 'base = ' . $login['id'];
                $platoon_filter .= 'platoon IN ( SELECT class_id FROM classes WHERE school_id = "' . $login['id'] . '") ';
            } else if ( $login['code'] == 'TEACHER' ) {
                $platoon = Platoon::find( $login['id'] );
                $inst_id = $platoon->school->inst_id;
                $base_filter .= 'base = "' . $platoon->school->school_id . '"';
                $platoon_filter .= 'platoon = "' . $platoon->class_id . '"';
            } 
        } catch ( Exception $e ) { return json_error( 'Could not get Achivement Filters.' ); };

        $tasks = AchievementTask::all([
            'joins' => 'JOIN subjects s USING ( subject_id )',
            'conditions' => 
                "inst_id IN (0, $inst_id) "
                ." AND s.subject_type IN ('', 'WWTC', 'Tanya', 'achievement')"
                .( $base_filter ? " AND ( base = 1 OR $base_filter ) " : '' )
                .( $platoon_filter ? " AND ( platoon = 1 OR $platoon_filter ) " : '' ),
            'include' => [ 'subject', 'school', 'class' ],
            'order' => 'base, platoon'
        ]);

        json_response( $tasks );
    }

    public function create() {
        global $current_user;
        $login = $current_user->login;

        try {
            $task = new AchievementTask( $_POST );
        } catch ( Exception $e ) { json_error( 'Invalid Request' ); }

        if ( $login['code'] == 'BC' ) {
            $task->base = $login['id'];
        } else if ( $login['code'] == 'TEACHER') {
            $task->base = Platoon::find( $login['id'] )->school->school_id;
            $task->platoon = $login['id'];
        }

        if ( !$task->save() )
            json_error( 'Could not create Task.' );
        json_response( $task );
    }

    public function update( $id ) {
        $task = AchievementTask::find( $id );

        $keys = ['subject_id', 'task', 'points'];
        foreach( $keys as $key ){
            if ( isset( $_POST[$key] ) )
                $task->{$key} = $_POST[$key];
        }
        
        if ( !$task->save() )
            json_error( 'Could not update Task.' );
        json_response( $task );
    }
}

rest_router( new TasksRouter );
