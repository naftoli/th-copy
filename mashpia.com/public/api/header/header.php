<?php
error_reporting(0);
if ( isset( $_GET['debug'] ) ) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}
date_default_timezone_set( 'UTC' );
define( "API_ROOT", __DIR__ . '/..' );

// include composer dependancies and custom scripts
require_once( API_ROOT . "/../../vendor/autoload.php" ); // composer install must be run
include_once( __DIR__ . "/json-functions.php" );
include_once( __DIR__ . "/rest-router.php" );
include_once( __DIR__ . "/db.php" );
include_once( __DIR__ . '/getallheaders.php');
// Import Authorize.net API functions into global space to be used in models
require_once( __DIR__ . "/../../classes/authorize/CustomerProfile.php" );
require_once( __DIR__ . "/../../classes/authorize/PaymentProfile.php" );
// GlobalSettings
include_once( __DIR__ . '/../../class.globalSettings.php');

// set headers
header('Access-Control-Allow-Origin: '. ( isset( $_SERVER['HTTP_ORIGIN'] ) ? $_SERVER['HTTP_ORIGIN'] : "*" ) ); // CORS
header('Access-Control-Allow-Credentials: true');
header("Access-Control-Allow-Methods: GET, POST, PATCH, OPTIONS, DELETE");
header('Access-Control-Allow-Headers: mobile, Content-Type, Authorization, login');
header("Content-Type: text/html; charset=utf-8;");

if ( isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == "OPTIONS")
    json_response( false );

$raw_data = file_get_contents('php://input');
$data = json_decode( $raw_data, true );
if ( is_array( $data ) ) {
    $_POST = $data;
}

if ( $development && !defined('AUTHORIZE_NET_SANDBOX') ) {
    define('AUTHORIZE_NET_SANDBOX', true);
}

// authenticate user if authentication is required
if ( defined( "MASHPIA_AUTH_REQUIRED" ) && MASHPIA_AUTH_REQUIRED ){
    include_once( __DIR__ . "/setCurrentUser.php" );
    // set the current user
    $current_user = setCurrentUser();
    // Return 401 Unauthorized if we cannot login user
    if ( !$current_user ){
        json_error( "Invalid Credentials", false, 401 );
    }
// no auth required
} else {
    $current_user = false;
}
