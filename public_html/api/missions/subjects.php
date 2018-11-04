<?php
define( "MASHPIA_AUTH_REQUIRED", true );
include_once( __DIR__ . "/../header/header.php" );

class SubjectsRouter {

    public function index() {
        global $current_user; global $MASHPIA_DB;

        $subjects = Subject::all([
            'conditions' => [
                'inst_id IN ( 0, ? ) '
                .'AND subject_type IN ( "" , "WWTC", "Tanya" )', 
                $current_user->login->inst_id
            ]
        ]);

        json_response( $subjects, true, true );
    }
}

rest_router( new SubjectsRouter );
