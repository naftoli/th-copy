<?php
// DBS connection.....
require_once( $_SERVER['DOCUMENT_ROOT'].'/db.php' );
require_once(dirname(__FILE__)."/functions/header.php");

// Authentication scheme
require_once( $_SERVER['DOCUMENT_ROOT'].'/mobile/reg/ajax/encrypt.php' );
$login = encrypt_decrypt('decrypt', $_POST['login']);
if(!$login) render_json_error("Invalid Login");

require_once __DIR__ . '/../classes/staffManager.php';
$sm = new StaffManager();
if ( !$sm->setStaffById( $login ) ) render_json_error("Invalid Login");

echo json_encode([
    "success"   => true,
    "user"      => $sm->getPersonalInfo(), 
    "info"      => $sm->getInfo()
]);