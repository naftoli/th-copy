<?php
// detect if the server is running on 192.168., if so connect to the remote server, else use localhost.
$global_db_host = strpos($_SERVER['HTTP_HOST'], "mashpia.com") === false ? // use triple equals since it should return 0 which is falsy
    "mashpia.com" : 
    "localhost" ;
// DBS credentials
$global_db_user = 'mashpia_cth';
$global_db_pass = 'UlqKsfnTUq2A';

// Deployment Tokens
$deployment_access_token = "183HXNi6q4Zcl7z0Z8uiJrEgihkklnsz";