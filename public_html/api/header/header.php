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
include_once( __DIR__ . '/getallheaders.php');
// Import Authorize.net API functions into global space to be used in models
require_once( dirname(__FILE__) . "/../../classes/authorize/CustomerProfile.php" );
require_once( dirname(__FILE__) . "/../../classes/authorize/PaymentProfile.php" );
// GlobalSettings
include_once( __DIR__ . '/../../class.globalSettings.php');

// set headers
header('Access-Control-Allow-Origin: '. ( isset( $_SERVER['HTTP_ORIGIN'] ) ? $_SERVER['HTTP_ORIGIN'] : "*" ) ); // CORS
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Headers: mobile, Content-Type, Authorization, login');
header("Content-Type: text/html; charset=utf-8;");

if ($_SERVER['REQUEST_METHOD'] == "OPTIONS") json_response( false );

$data = json_decode( file_get_contents('php://input'), true );
if ( is_array( $data ) ) {
    $_POST = $data;
}

// authenticate user if authentication is required
if ( defined( "MASHPIA_AUTH_REQUIRED" ) && MASHPIA_AUTH_REQUIRED ){
    include_once( API_ROOT . "/auth/classes/Auth.php" );
    $headers = getallheaders();
    // detect if we are on mobile
    $mobile = false;
    if ( // check if we have the proper header set or are coming from /mobile
        ( isset( $headers['mobile'] ) && $headers['mobile'] === 'true' ) || 
        ( isset( $_SERVER['HTTP_REFERER'] ) && strpos( $_SERVER['HTTP_REFERER'], '/mobile' ) > 0 )
    ) $mobile = true;

    $token = false;
    $have_cookies = (isset($_COOKIE['admin_auth']) && isset($_COOKIE['admin_id'])) || isset($_COOKIE['admin']);
    if ( !$have_cookies && isset( $headers['Authorization'] ) ) {
        $token = explode( ' ',  $headers['Authorization'] )[1];
    } else if ( !$have_cookies && isset( $headers['authorization'] ) ) {
        $token = explode( ' ',  $headers['authorization'] )[1];
    }

    if ( $token ) {
        if ( $mobile ) {
            $_COOKIE['admin'] = $token;
        } else {
            $_COOKIE['admin_id'] = explode( '-', $token )[0];
            $_COOKIE['admin_auth'] = explode( '-', $token )[1];
        }
    }
    // get the current user
    $admin_id = false;
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
    
    // Return 401 Unauthorized if we cannot login user
    if ( !$current_user ){
        json_error( "EH1: Invalid Credentials", $_COOKIE, 401 );
    }

    // get the current login
    if ( isset( $_COOKIE['login'] ) ) {
        $login_parts = explode('-', $_COOKIE['login']);
        if ( count($login_parts) == 2 ) $current_user->setLogin( $login_parts[0], $login_parts[1] );
    } else if ( $headers['login'] ) {
        $login_parts = explode('-', $headers['login']);
        if ( count($login_parts) == 2 ) $current_user->setLogin( $login_parts[0], $login_parts[1] );
    }
    // make sure we always have a login
    if ( !$current_user->login ) {
        $current_user->setLogin();
    }
// no auth required
} else {
    $current_user = false;
}
