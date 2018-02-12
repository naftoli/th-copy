<?php
require '../../../db.php';
$admin_id = mysql_real_escape_string( $_POST['admin_id'] );
$pwd = mysql_real_escape_string( $_POST['pwd'] );

require 'encrypt.php';
$admin_id = encrypt_decrypt('decrypt', $admin_id);

$sql = "select * from admins where admin_id = " . $admin_id;
$result = mysql_fetch_assoc(mysql_query( $sql ));
if ($result['password'] == $pwd) {
	echo 1;
} else {
	echo 0;
}
?>