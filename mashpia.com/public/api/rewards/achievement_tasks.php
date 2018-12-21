<?php
define( "MASHPIA_AUTH_REQUIRED", true );
include_once( __DIR__ . "/../header/header.php" );

class TasksRouter {

    public function index() {
        global $current_user;

        $subjects = Subject::all([
            'conditions' => [
                "subject_type IN ( '', 'WWTC', 'Tanya', 'achievement' )", 
            ],
            'order' => 'subject_name'
        ]);

        $tasks = array_reduce( $subjects, function( $combined, $subject ) use ( $current_user ) {
            return array_merge( $combined, $subject->getAchievementTasks( $current_user->login ) );
        }, []);

        json_response( $tasks );
    }

    public function create() {
        global $current_user;
        $login = $current_user->login;

        try {
            $task = new AchievementTask( $_POST );
        } catch ( Exception $e ) { json_error( 'Invalid Request' ); }

        if ( $login->code == 'BC' ) {
            $task->base = $login->id;
        } else if ( $login->code == 'TEACHER') {
            $task->base = $login->model->school_id;
            $task->platoon = $login->id;
        }

        if ( !$task->subject_id )
            json_error( 'Cannot create a Task without a Campaign' );
        
        if ( $login->code == 'INST' ) {
            $subject = \Subject::find([ $task->subject_id ]);
            if ( $subject->inst_id != $login->id )
                return json_error('You do not have permission to create Tasks for this Campaign. Please select another one.');
        }

        if ( !$task->save() )
            json_error( 'Could not create Task.' );
        json_response( $task );
    }

    public function update( $id ) {
        $task = \AchievementTask::find([ $id ]);

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
