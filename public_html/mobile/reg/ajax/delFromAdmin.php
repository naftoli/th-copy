<?php 
require '../../../db.php';

$user_id = mysql_real_escape_string($_POST['child']);
$admin_id = mysql_real_escape_string( $_POST['admin'] );
require 'encrypt.php';
$admin_id = encrypt_decrypt('decrypt', $admin_id);

$sql = "delete from admin_auths where admin_id = " . $admin_id . " and id = " . $user_id . " and auth = 'user' and role_id = 1";
if (mysql_query($sql)) {
    echo 0;
} else {
    echo 1;
}
?>