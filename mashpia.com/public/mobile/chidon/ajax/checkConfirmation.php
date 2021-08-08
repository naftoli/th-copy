<?php
require $_SERVER['DOCUMENT_ROOT'] . '/db.php';
require 'encrypt.php';

$admin_id = encrypt_decrypt('decrypt', $_POST['admin']);
$field = $_POST['field'];

$sql = "select $field from admins where admin_id = " . $admin_id;
$result = mysql_query($sql);
$row = mysql_fetch_assoc($result);
echo $row[$field];