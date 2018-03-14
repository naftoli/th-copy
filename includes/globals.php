<?php
// detect if the server is running on 192.168., if so connect to the remote server, else use localhost.
$global_db_host = strpos($_SERVER['HTTP_HOST'], "192.168.") === false ? // use triple equals since it should return 0 which is falsy
    "localhost" : 
    "mashpia.com";
// DBS credentials
$global_db_user = 'mashpia_cth';
$global_db_pass = 'UlqKsfnTUq2A';

// Deployment Tokens
define( "DEPLOYMENT_ACCESS_TOKEN", "183HXNi6q4Zcl7z0Z8uiJrEgihkklnsz" );