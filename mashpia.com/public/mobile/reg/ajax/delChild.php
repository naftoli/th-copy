<?
require '../../../db.php';

$admin = mysql_real_escape_string( $_POST['parent'] );
$user = mysql_real_escape_string( $_POST['child'] );

require 'encrypt.php';
$admin = encrypt_decrypt('decrypt', $admin);

$sql = "delete from admin_auths where auth = 'user' and role_id = 1 and admin_id = " . $admin . " and id = " . $user;
//echo $sql;
echo mysql_query( $sql );
?>