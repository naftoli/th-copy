<?php
require $_SERVER['DOCUMENT_ROOT'] . '/db.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonRegYear();

$admin = mysql_real_escape_string( $_POST['admin'] );
require 'encrypt.php';
$admin_id = encrypt_decrypt('decrypt', $admin);

$type_of_sweater = $_POST['type'];
$size = $_POST['size'];

$sql = "update th_chidon_parent_purchases 
        set sweater_{$type_of_sweater} =  '" . mysql_real_escape_string($size) . "' 
        where admin_id = $admin_id";
if (mysql_query($sql))
    echo json_encode([
        'success'   =>  true
    ]);
else
    echo json_encode([
        'success'   =>  false
    ]);
