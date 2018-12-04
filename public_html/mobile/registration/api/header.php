<?php
define( "MASHPIA_AUTH_REQUIRED", true );
include_once( __DIR__ . '/../../../api/header/header.php' );
include_once( dirname(__FILE__) . "/functions/primary_functions.php" ); // functions for all pages

$admin_id = $current_user->admin_id;
