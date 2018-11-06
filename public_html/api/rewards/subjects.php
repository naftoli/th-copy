<?php
define( "MASHPIA_AUTH_REQUIRED", true );
include_once( __DIR__ . "/../header/header.php" );

class SubjectsRouter {

    public function index() {
        global $current_user; global $MASHPIA_DB;

        $subjects = Subject::all([
            'conditions' => [
                'subject_type IN ( "" , "WWTC", "Tanya", "achievement" )', 
                // .'AND inst_id IN ( 0, ? ) '
                // $current_user->login->inst_id
            ],
            'order' => 'subject_name'
        ]);

        $subjects = array_map( function( $subject ) use ( $current_user ) {
            return $subject->includeTasksJSON( $current_user->login );
        }, $subjects);

        json_response( $subjects, true, true );
    }
}

rest_router( new SubjectsRouter );
