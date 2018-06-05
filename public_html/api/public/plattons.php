<?php
include_once( __DIR__ . "/../header/header.php" );

class PlattonRouter {
    
    public function index() {
        if ( !isset( $_GET['school_id'] ) )
            json_error( "Please provide a school to list it's plattons", "Missing 'school_id' GET paramater, get school ids at /api/public/schools.php");

        try {
            $school = School::find( $_GET['school_id'] );
            $plattons = $school->plattons;
        } catch ( Exception $e ) {
            print_r( $e );
            json_error( "Server Error: Could not load plattons", false, 500 );
        }

        json_response( array_map( function( $platton ) {
            return $platton->publicSerialize();
        }, $plattons ) );
    }
}

rest_router( new PlattonRouter );