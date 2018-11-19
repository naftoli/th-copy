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

$error = 'Unknown Login Error';

// * try to use the username and password first
if ( isset($_POST['username']) && isset($_POST['password']) ) {
    $login = \mashpia\api\auth\Auth::login(
        $_POST['username'], $_POST['password']
    );
    $error = 'Invalid Username and/or Password';
// sign-in with Chabad.org
} else if ( isset( $_POST['chabad_key'] ) ) {
    $login = \mashpia\api\auth\Auth::chabadLogin(
        $_POST['chabad_key']
    );
    $error = 'No Connected TH Account Found. Please login and connect your accounts or create a new account below.';
// sign in with Google
} else if ( isset( $_POST['google_key'] ) ) {
    $login = \mashpia\api\auth\Auth::googleLogin(
        $_POST['google_key']
    );
    $error = 'No Connected TH Account Found. Please login and connect your accounts or create a new account below.';
}

if ( !$login )
    json_error( $error );

json_response( $login );
