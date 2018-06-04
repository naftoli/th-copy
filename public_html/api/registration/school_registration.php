<?php
define( "MASHPIA_AUTH_REQUIRED", true );
include_once( __DIR__ . "/../header/header.php" );

class SchoolRegistrationRouter {
    // limit controller to HQ
    function authenticate(){
        global $current_user;
        return $current_user->isHQ();
    }

    // return all schools with their registration info
    function index(){
        $schools = School::find( 'all', [
            'include' => 'school_reg_infos', 'order' => 'school_name',
            'conditions' => "test_school = '0'"
        ] );
        json_response( array_map( function( $school ) {
            return $school->to_array([
                'only' => [ 'school_id', 'school_name' ],
                'include' => [ 'school_reg_infos' ]
            ]);
        }, $schools ) );
    }
}

rest_router( new SchoolRegistrationRouter );