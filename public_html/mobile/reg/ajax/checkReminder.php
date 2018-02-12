<?php
require '../../../db.php';
$admin = mysql_real_escape_string($_POST['admin']);

require 'encrypt.php';
$admin = encrypt_decrypt('decrypt', $admin);

$sql = "select rebbe_story_forget from admins where admin_id = " . $admin;
$result = mysql_query($sql);
$row = mysql_fetch_assoc($result);

echo (int)$row['rebbe_story_forget'];
?>