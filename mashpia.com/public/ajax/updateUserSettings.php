<?php
require '../db.php';

$user = mysql_real_escape_string( $_POST['user'] );
$type = mysql_real_escape_string( $_POST['type'] );
$value = mysql_real_escape_string( $_POST['value'] == 'false' ? 0 : 1 );

$sql = "update users set " . $type . " = " . $value . " where user_id = " . $user;
if (mysql_query( $sql )) {
    echo 0;
} else {
    echo mysql_error();
}