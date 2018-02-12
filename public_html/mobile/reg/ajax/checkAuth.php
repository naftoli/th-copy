<?php
require '../../../db.php';
$admin_id = mysql_real_escape_string( $_POST['admin_id'] );
$user_id = mysql_real_escape_string( $_POST['user_id'] );

require 'encrypt.php';
$admin_id = encrypt_decrypt('decrypt', $admin_id);

$sql = "select * from admin_auths where admin_id = " . $admin_id . " and id = " . $user_id;
$result = mysql_query( $sql );
if (mysql_num_rows($result) > 0) {
	echo 1;
} else {
	echo 0;
}
?>