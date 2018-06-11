<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once( __DIR__ . '/../header/header.php' );
require_once( __DIR__ . '/classes/Auth.php' );
// validate the request method
if ( $_SERVER['REQUEST_METHOD'] !== 'POST' )
    json_error( "Invalid Request" );

// validate the presence of the correct paramaters
if ( !isset($_POST['username']) || !isset($_POST['password']) )
    json_error( "Invalid Request");

$login = \mashpia\api\auth\Auth::login(
    $_POST['username'], $_POST['password']
);

if ( !$login ) json_error( "Invalid Login" );

json_response( $login );
