<?php
header('Access-Control-Allow-Origin: *'); // CORS
require '../db.php';
$email = mysql_real_escape_string( $_REQUEST['email'] );

$sql = "select * from admins where admin_email = '" . $email . "'";
$result = mysql_query( $sql );
echo mysql_num_rows( $result );
?>