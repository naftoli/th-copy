<?php
define("AUTHORIZE_NET_SANDBOX", true);

require_once( dirname(__FILE__) . "/../../../db.php" ); // database
include_once( dirname(__FILE__) . "/functions/primary_functions.php" ); // functions for all pages
require_once( dirname(__FILE__) . '/../../reg/ajax/encrypt.php'); // encryption scheme

/** AUTHENTICATION */
if( !isset( $_COOKIE['admin'] ) )
    render_json_error( "Invalid Authentication" );
// decrypt the token
$admin_id = encrypt_decrypt('decrypt', $_COOKIE['admin']);
if ( !$admin_id )
    render_json_error( "Invalid Authentication" );