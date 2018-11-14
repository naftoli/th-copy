<?php
require_once( __DIR__ . '/../header/header.php' );
require_once( __DIR__ . '/classes/Auth.php' );
// validate the request method
if ( $_SERVER['REQUEST_METHOD'] !== 'POST' )
    json_error( "Invalid Request" );

// * clear all cookies
$past = time() - 3600;
foreach ( $_COOKIE as $key => $value ) {
    setcookie( $key, $value, $past, '/' );
}

$login = false;

// * try to use the username and password first
if ( isset($_POST['username']) && isset($_POST['password']) )
    $login = \mashpia\api\auth\Auth::login(
        $_POST['username'], $_POST['password']
    );
else if ( isset( $_POST['chabad_key'] ) )
    $login = \mashpia\api\auth\Auth::chabadLogin(
        $_POST['chabad_key']
    );

if ( !$login )
    json_error( "Invalid Username and/or Password");

json_response( $login );
