<?php
if ( isset( $_GET['debug'] ) ) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

date_default_timezone_set( 'UTC' );
define( "API_ROOT", __DIR__ . '/..' );
// TODO set to false in production
define("AUTHORIZE_NET_SANDBOX", true);

// include composer dependancies and custom scripts
require_once( API_ROOT . "/vendor/autoload.php" ); // composer install must be run
include_once( __DIR__ . "/json-functions.php" );
include_once( __DIR__ . "/rest-router.php" );
include_once( __DIR__ . "/db.php" );
// Import Authorize.net API functions into global space to be used in models
require_once( dirname(__FILE__) . "/../../classes/authorize/CustomerProfile.php" );
require_once( dirname(__FILE__) . "/../../classes/authorize/PaymentProfile.php" );
// GlobalSettings
include_once( __DIR__ . '/../../class.globalSettings.php');

// set headers
header('Access-Control-Allow-Origin: *'); // CORS
header("Content-Type: text/html; charset=utf-8;");

$data = json_decode( file_get_contents('php://input'), true );
if ( is_array( $data ) ) {
    $_POST = $data;
}

// authenticate user if authentication is required
if ( defined( "MASHPIA_AUTH_REQUIRED" ) && MASHPIA_AUTH_REQUIRED ){
    include_once( API_ROOT . "/auth/classes/Auth.php" );

    // detect if we are on mobile
    $mobile = false;
    if ( // check if we have the proper header set or are coming from /mobile
        ( isset( getallheaders()['mobile'] ) && getallheaders()['mobile'] === 'true' ) || 
        ( isset( $_SERVER['HTTP_REFERER'] ) && strpos( $_SERVER['HTTP_REFERER'], '/mobile' ) > 0 )
    ) $mobile = true;

    if ( $mobile && $_COOKIE['admin'] ) {
        $admin_id = \mashpia\api\auth\Auth::authenticate(
            [ "key" => $_COOKIE['admin'] ], "mobile"
        );
    } else if ( isset( $_COOKIE['admin_auth'] ) && isset( $_COOKIE['admin_id'] ) ) {
        $admin_id = \mashpia\api\auth\Auth::authenticate(
            [ "key" => $_COOKIE['admin_auth'], "admin_id" => $_COOKIE['admin_id'] ],
            "legacy"
        );
    }

    $current_user = $admin_id ? Admin::find( $admin_id ) : false;
} else {
    $current_user = false;
}
