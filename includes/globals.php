<?php
// detect if the server is running on 192.168., if so connect to the remote server, else use localhost.
// use triple equals since it should return 0 which is falsy
$development = isset( $_SERVER['HTTP_HOST'] ) ? strpos($_SERVER['HTTP_HOST'], "mashpia.com") === false : true;

$global_db_host = $development ? "mashpia.com" : "localhost";
// DBS credentials
$global_db_user = 'mashpia_cth';
$global_db_pass = 'UlqKsfnTUq2A';

$domain = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'];

// if ( $development )
//     $domain = 'http://localhost:3000';

if ( !defined( 'MASHPIA_ABS_URL' ) )
    define( 'MASHPIA_ABS_URL', $domain );
