<?php
define( "MASHPIA_AUTH_REQUIRED", true );
require_once( __DIR__ . '/../header/header.php' );

if ( $current_user )
    json_response( $current_user );
else
    json_error( "Invalid Credentials" );