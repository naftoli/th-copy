<?php
include_once( __DIR__ . "/../header/header.php" );
require_once( __DIR__ . "/../../class.tasksCustomizationNew.php" );

class PersonalizeRouter {

    public function __construct(){
        $this->tc = new TasksCustomizationNew;
    }

    /**
     * ?action=getCampaigns
     * Returns a list of campaigns
     * 
     * takes school_id, class_id, and user_id filters
     */
    public function getCampaigns() {

        extract( $this->getParams() );

        if ( $user_id > 0 ) {
            extract( $this->tc->getCampaignsForChild( $user_id, true ) );
        } else if ( $school_id > 0 ) {
            $campaigns = $this->tc->getCampaigns( $school_id );
            $enrolled = $this->tc->getCampaignsEnrolled( $school_id, $class_id, $user_id );
        } else
            return json_error( 'School ID Required' );
        
        $response = [];
        foreach( $campaigns as $id => $campaign ) {
            $response[] = [
                'subject_name'  => $campaign,
                'subject_id'    => $id,
                'enrolled'      => in_array( $id, $enrolled )
            ];
        }

        json_response( $response );
    }

    /**
     * ?action=getTasks
     * Returns a list tasks for a single campaign
     * 
     * takes school_id, class_id, user_id, $parsha_id and subject_id filters
     */
    public function getTasks() {
        // see below for variables available in each call
        extract( $this->getParams() );

        $this->tc->setEnd( $end );
        $this->tc->setLang( $lang );
        $this->tc->setStart( $start );
        $this->tc->setType( $user_id, $class_id, $school_id );

        $tasks = $this->tc->getTasks( $subject_id, false );

        // * Format the response to something sane and consistent
        $response = [];
        // go through each cat
        foreach( $tasks as $cat => $labels ) {
            $task = [ 'task' => $cat ];
            // missions is weird, but the array key is a 1 or 0 for if it is checked,
            // then that has an array with all the details as key values again...
            $task['enrolled'] = !!array_keys( $labels )[0];
            // array_values just skips another foreach which is somehow more confusing
            foreach( $labels[ array_keys( $labels )[0] ] as $label => $school_types ) {

                $label_school_types = [];
                foreach( $school_types as $school_type => $grades ) {
                    $label_school_type = [ 'type' => $school_type ];

                    foreach( $grades as $grade => $quota ) {
                        $label_school_type['grades'][] = [ 'grade' => $grade, 'quota' => $quota ];
                    } // end grade-quota level

                    $label_school_types[] = $label_school_type;
                } // end type-grade level

                $task['labels'][] = [
                    'label' => $label,
                    'school_types' => $label_school_types,
                    'quota' => $quota
                ];
            } // end label-type level
            // add to resposne
            $response[] = $task;
        } // and finish task-label level

        json_response( $response, true, true );
    }

    /**
     * Get the individual missions for each "week" to turn them on/off
     */
    public function getMissions() {
        
        extract( $this->getParams() );

        $this->tc->setEnd( $end );
        $this->tc->setLang( $lang );
        $this->tc->setStart( $start );
        $this->tc->setType( $user_id, $class_id, $school_id );

        $missions = $this->tc->getMissions( $task, $subject_id );

        // * Format the response to something sane and consistent
        $response = [];
        // go through each cat
        foreach( $missions as $name => $mission ) {
            $mission['enrolled'] = !!$mission['enrolled'];
            $mission['start_date']  = date( SQL_DATE_FORMAT, jdtounix( $mission['start_date'] ) );
            $mission['end_date']    = date( SQL_DATE_FORMAT, jdtounix( $mission['end_date'] ) );
            $response[] = array_merge(
                [ 'name' => $name ], $mission
            );
        } // and finish task-label level

        json_response( $response, true, true );
    }

    /**
     * POST /missions/personalize
     */
    public function create(){
        extract( $this->getParams() );

        $this->tc->setEnd( $end );
        $this->tc->setLang( $lang );
        $this->tc->setStart( $start );
        $this->tc->setType( $user_id, $class_id, $school_id );

        if ( !isset( $_POST['updates'] ) )
            return json_error( 'Update not provided' );
        $updates = $_POST['updates'];

        // * enroll and unenroll from campaigns
        if ( $updates['level'] === 'campaign' ) {
            // * get all the user_ids that we need to update
            if ( $user_id )
                $user_ids = [ $user_id ];
            else if ( $class_id )
                $user_ids = $this->tc->getUsersInGrade( $class_id );
            else if ( $school_id )
                $user_ids = $this->tc->getAllUsers( $school_id );

            // * enroll or unenroll the soldiers from the campaign
            if ( $updates['enrolled'] )
                $status = $this->tc->enroll( $user_ids, [ $updates['subject_id'] ] );
            else
                $status = $this->tc->unenroll( $user_ids, [ $updates['subject_id'] ] );

        // * enable and disable missions
        } else if ( $updates['level'] === 'task' ) {
            $task = $updates['subject_id'].'|'.$updates['task'];

            if ( $updates['enrolled'] )
                $status = $this->tc->enrollIntoTasks( [ $task ] );
            else
                $status = $this->tc->setTaskExceptions( [ $task ] );

        } else if ( $updates['level'] === 'mission' ) {
            $mission = $updates['subject_id'].'|'.$updates['task'].'~'.$updates['mission'];

            if ( $updates['enrolled'] )
                $status = $this->tc->enrollIntoMissions( [ $mission ] );
            else
                $status = $this->tc->setMissionExceptions( [ $mission ] );
        }

        return json_response( $status );
    }

    /**
     * get the params that we support to extract them and turn them into varialbes.
     * 
     * Params:
     * subject_id,  school_id,
     * class_id,    user_id,
     * task,        parsha_id (optional, becomes start and end julian dates),
     */
    private function getParams () {
        $params = [];
        // parse params
        $params['subject_id'] = isset( $_POST['subject_id'] ) && $_POST['subject_id'] ? $_POST['subject_id'] : 0;
        $params['school_id']  = isset( $_POST['school_id'] )  && $_POST['school_id']  ? $_POST['school_id']  : 0;
        $params['class_id']   = isset( $_POST['class_id'] )   && $_POST['class_id']   ? $_POST['class_id']   : 0;
        $params['user_id']    = isset( $_POST['user_id'] )    && $_POST['user_id']    ? $_POST['user_id']    : 0;
        $params['lang']       = isset( $_POST['lang'] )       && $_POST['lang']       ? $_POST['lang']       : 1;
        $params['task']       = isset( $_POST['task'] )       && $_POST['task']       ? $_POST['task']       : false;

        if ( isset( $_POST['parsha_id'] ) && $_POST['parsha_id'] > 0 ) {
            $parsha = \Parsha::find([ $_POST['parsha_id'] ]);
            $params['start'] = $parsha->start;
            $params['end']   = $parsha->end;
        } else
            $params = array_merge( $params, GlobalSettings::getCurYearDates() );

        return $params;
    }

}

rest_router( new PersonalizeRouter );

die();