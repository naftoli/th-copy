<?php
require '../db.php';
$email = mysql_real_escape_string( $_POST['email'] );

$sql = "select * from admins where admin_email = '" . $email . "'";
$result = mysql_query( $sql );
$num = mysql_num_rows($result);
if ( $num ) {
    echo 1;
} else {
    echo 0;
}
?>