<?php
include_once( __DIR__ . "/../header/header.php" );

class UserRouter {

    public function index() {
        if ( !isset( $_GET['school_id'] ) && !isset( $_GET['class_id'] ) )
            json_error( "Please provide a school or platton to list it's users");
        // limit the users to the school/platton provided
        try {
            if ( isset( $_GET['school_id'] ) ) {
                $zone = School::find( $_GET['school_id'] );
            } else if ( isset( $_GET['class_id'] ) ) {
                $zone = Platton::find( $_GET['class_id'] );
            }
            $users = $zone->users;
        } catch ( Exception $e ) {
            json_error( "Server Error: Could not load users", false, 500 );
        }
        // return the users
        json_response( array_map( function( $user ) {
            return $user->publicSerialize();
        }, $users ) );
    }

}

rest_router( new UserRouter );