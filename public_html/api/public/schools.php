<?php
include_once( __DIR__ . "/../header/header.php" );

class SchoolRouter {
    
    public function index() {
        $schools = School::find( 'all', [
            'order' => 'school_name', 'conditions' => "test_school = '0'"
        ]);

        json_response( array_map( function( $school ) {
            return $school->publicSerialize();
        }, $schools ) );
    }
}

rest_router( new SchoolRouter );