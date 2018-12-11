<?php
define( "MASHPIA_AUTH_REQUIRED", true );
include_once( __DIR__ . "/../header/header.php" );

class InstRouter {

    public function index(){
        $institutions = \Institution::all();
        json_response( $institutions, true, true );
    }
}

rest_router( new InstRouter );
