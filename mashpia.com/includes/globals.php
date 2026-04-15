<?php
// detect if the server is running on 192.168., if so connect to the remote server, else use localhost.
// use triple equals since it should return 0 which is falsy
$development = isset( $_SERVER['HTTP_HOST'] ) ? strpos($_SERVER['HTTP_HOST'], "mashpia.com") === false : true;

$global_db_host = $development ? "50.28.66.228" : "localhost"; // mashpia.com was pointing to old ip address so i just put in new ip address
// DBS credentials
$global_db_user = 'mashpia_cth';
$global_db_pass = 'UlqKnfsTUq2A';

$domain = '';
if ( isset( $_SERVER['REQUEST_SCHEME'] ) &&  isset( $_SERVER['HTTP_HOST'] ) ) {
    $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'];
}
// define the absolute url
if ( !defined( 'MASHPIA_ABS_URL' ) )
    define( 'MASHPIA_ABS_URL', $domain );

// set the google client id
if ( !defined( 'GOOGLE_CLIENT_ID' ) )
    define( 'GOOGLE_CLIENT_ID', '356394568289-o9uqieb96qevc8a1plmm5voa6so0l2fd.apps.googleusercontent.com' );

// set the google client id
if ( !defined( 'GOOGLE_CLIENT_SECRET' ) )
    define( 'GOOGLE_CLIENT_SECRET', 'ytFUARhMG-2cpJblFjHBy6m9' );

// if development, change the domain to localhost:3000 for the redirects
if ( $development )
    $domain = 'http://localhost:3000';

// encryption key
if ( !defined( 'ENCRYPTION_KEY' ) )
    define( 'ENCRYPTION_KEY', 'tzivos-hashem-5786' );

// Encryption functions
// Encryption functions
if (!function_exists('encryptPassword')) {
    function encryptPassword($password, $key) {
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        $encrypted = openssl_encrypt($password, 'aes-256-cbc', $key, 0, $iv);
        return base64_encode($encrypted . '::' . $iv);
    }
}

if (!function_exists('decryptPassword')) {
    function decryptPassword($encryptedPassword, $key) {
        // Check if $encryptedPassword is valid base64
        if (base64_encode(base64_decode($encryptedPassword, true)) !== $encryptedPassword) {
            // Not base64, return original password
            return $encryptedPassword;
        }
        list($encrypted_data, $iv) = explode('::', base64_decode($encryptedPassword), 2);
        return openssl_decrypt($encrypted_data, 'aes-256-cbc', $key, 0, $iv);
    }
}

// initialize the $logger global variable
// for usage see the monolog docs at https://github.com/Seldaek/monolog/blob/main/doc/01-usage.md
require_once( __DIR__ . "/../utils/logger.php" );

// Redis credentials
$global_redis_host = 'localhost';
$global_redis_port = 6379;
$global_redis_password = 'Naftoli@5783!';
$global_redis_db = 0;

// if server is not mashpia.com
if ( $development ) {
    $global_redis_host = '127.0.0.1';
    $global_redis_port = 6380;
}