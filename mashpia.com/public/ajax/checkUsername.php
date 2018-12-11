<?php
require '../db.php';
$username = mysql_real_escape_string( $_POST['user'] );

$sql = "select * from admins where username = '" . $username . "'";
$result = mysql_query( $sql );
$num = mysql_num_rows($result);
if ( $num ) {
    echo 1;
} else {
    echo 0;
}
?>