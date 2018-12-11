<?php
require_once '../../../api/header/header.php';
require_once '../../../api/header/db.php';

$admin = $_POST['admin'];
require_once 'encrypt.php';
$admin_id = encrypt_decrypt('decrypt', $admin);

$qry = $MASHPIA_DB->prepare("SELECT authorize_customer_profile_id FROM admins WHERE admin_id = :admin");
$qry->execute( array(':admin'=>$admin_id) );
$row = $qry->fetch(PDO::FETCH_OBJ);
echo $row->authorize_customer_profile_id;