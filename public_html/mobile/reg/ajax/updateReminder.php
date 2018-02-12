<?php
require '../../../db.php';
$admin = mysql_real_escape_string($_POST['admin']);

require 'encrypt.php';
$admin = encrypt_decrypt('decrypt', $admin);

$sql = "update admins
        set rebbe_story_forget = 1 
        where admin_id = " . $admin;
mysql_query($sql) or die($sql);
?>