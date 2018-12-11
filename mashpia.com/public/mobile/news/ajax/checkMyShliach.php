<?php
require '../../../db.php';
$admin = $_POST['admin'];
require "../../reg/ajax/encrypt.php";

$admin_id = encrypt_decrypt('decrypt', $admin);
$sql = "select is_shliach from admins where admin_id = " . $admin_id;
$result = mysql_query($sql);
$row = mysql_fetch_assoc($result);
echo $row['is_shliach'];