<?php
define( "MASHPIA_AUTH_REQUIRED", true );
include_once( __DIR__ . "/../header/header.php" );
require_once( __DIR__ . "/../../class.tasksCustomizationNew.php" );

class PersonalizeRouter {

    public function __construct(){
        $this->tc = new TasksCustomizationNew;
    }

    /**
     * GET /missions/tasks
     */
    public function index() {
        global $MASHPIA_DB; global $current_user;

        extract( GlobalSettings::getCurYearDates() );

        $inst_id = $current_user->login->inst_id;
        $school_id = $current_user->login->school_id;

        $tasks_sql = 'SELECT dt.grid_id, dtm.lang_id, s.subject_name, '
            .'dt.short_name, dt.name, dt.points, dt.cat, dt.mission_marking, '
            .'dt.grid_marking, l.label_name, dt.label_id, dtm.created_by_school, b.school_name '
            .'FROM date_tasks dt '
            .'JOIN date_tasks_missions dtm USING (date_tasks_mission_id) '
            .'JOIN labels l USING (label_id) '
            .'LEFT JOIN schools b ON b.school_id = dtm.created_by_school '
            .'LEFT JOIN subjects s USING (subject_id) '
            .'WHERE grid_id IS NOT NULL '
            .'AND created_by_parent IS NULL '
            .( $school_id || $inst_id ?
                'AND ( '
                    .( $school_id ? 'created_by_school = :school_id' : '' )
                    .( !$school_id && $inst_id ? 'b.inst_id = :inst_id' : '' )
                .')'
                : ''
            )
            .'AND grid_id >= :min_grid_id '
            .'AND start_date >= :start AND end_date <= :end '
            .'GROUP BY grid_id, lang_id '
            .'ORDER BY grid_id , name';

        // echo $tasks_sql;
        
        $tasks_query = $MASHPIA_DB->prepare( $tasks_sql );

        if ( $school_id )
            $school_filter = [ ':school_id' => $school_id ];
        else if ( $inst_id )
            $school_filter = [ ':inst_id' => $current_user->login->inst_id ];
        else
            $school_filter = [];

        $tasks_query->execute( array_merge (
            $school_filter,
            [ ':min_grid_id' =>  1, ':start' => $start, ':end' => $end ]
        ));
        $tasks = $tasks_query->fetchAll();

        json_response( $tasks, true, true );
    }

    /**
     * POST /missions/tasks
     */
    public function create() {
        global $current_user;
        // * Get the params from POST
        $subject_id = isset( $_POST['subject_id'] ) && $_POST['subject_id'] ? $_POST['subject_id'] : 0;
        $short_name = isset( $_POST['short_name'] ) && $_POST['short_name'] ? $_POST['short_name'] : '';

        $school_id = isset( $_POST['school_id'] ) && $_POST['school_id'] ? $_POST['school_id'] : $current_user->login->school_id;
        // make sure we have a school_id
        if ( !$school_id ) return json_error( 'Invalid Base' );

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
            $school_id,     $school_type_id
        ), true, true );
    }
}

rest_router( new PersonalizeRouter );

die();