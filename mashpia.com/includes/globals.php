<?php
// detect if the server is running on 192.168., if so connect to the remote server, else use localhost.
// use triple equals since it should return 0 which is falsy
$development = isset( $_SERVER['HTTP_HOST'] ) ? strpos($_SERVER['HTTP_HOST'], "mashpia.com") === false : true;

$global_db_host = $development ? "mashpia.com" : "localhost";
// DBS credentials
$global_db_user = 'mashpia_cth';
$global_db_pass = 'UlqKsfnTUq2A';

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
