<?php
define( "MASHPIA_AUTH_REQUIRED", true );
include_once( __DIR__ . "/../header/header.php" );
require_once( __DIR__ . "/../../class.tasksCustomizationNew.php" );

class PersonalizeRouter {

    public function __construct(){
        $this->tc = new TasksCustomizationNew;
    }

    /**
     * POST /missions/personalize
     */
    public function create(){
        global $current_user;
        // * Get the params from POST
        $subject_id = isset( $_POST['subject_id'] ) && $_POST['subject_id'] ? $_POST['subject_id'] : 0;
        $short_name = isset( $_POST['short_name'] ) && $_POST['short_name'] ? $_POST['short_name'] : '';

        $mission_marking = isset( $_POST['mission_marking'] ) ? $_POST['mission_marking'] : false;
        $grid_marking = isset( $_POST['grid_marking'] ) ? $_POST['grid_marking'] : false;

        $school_type_id = isset( $_POST['school_type_id'] ) && $_POST['school_type_id'] ? $_POST['school_type_id'] : 0;
        $label_id = isset( $_POST['label_id'] ) && $_POST['label_id'] ? $_POST['label_id'] : 0;
        $lang_id = isset( $_POST['lang'] ) && $_POST['lang'] ? $_POST['lang'] : 1;

        $parsha_ids = isset( $_POST['parsha_ids'] ) && $_POST['parsha_ids'] ? $_POST['parsha_ids'] : [];
        $grades = isset( $_POST['grades'] ) && $_POST['grades'] ? $_POST['grades'] : [];
        
        $task = isset( $_POST['task'] ) && $_POST['task'] ? $_POST['task'] : '';

        require_once( __DIR__ . '/../tools/functions/create_task.php' );
        // create the task
        json_response( create_task(
            $subject_id,    $short_name,    $task,
            $lang_id,       $label_id,      $mission_marking,
            $grid_marking,  $grades,        $parsha_ids,
            $current_user->login->school_id,
            $school_type_id
        ) );
    }

}

rest_router( new PersonalizeRouter );

die();