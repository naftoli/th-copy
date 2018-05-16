<?php
require_once( dirname(__FILE__) . "/../../../db.php" ); // database
include_once( dirname(__FILE__) . "/functions/primary_functions.php" ); // functions for all pages
require_once( dirname(__FILE__) . '/../../reg/ajax/encrypt.php'); // encryption scheme

/** AUTHENTICATION */
$admin_id = false;
if ( isset( $_COOKIE['admin'] ) )
    $admin_id = encrypt_decrypt('decrypt', $_COOKIE['admin']);
