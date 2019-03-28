<?php
require '../db.php';
$user_id = $_POST['user_id'];

$sql = "select first, last from users where user_id = " . mysql_real_escape_string( $user_id );
$result = mysql_query( $sql );
$row = mysql_fetch_assoc( $result );
$name = $row['first'] . ' ' . $row['last'];

echo $name . " has now been marked as attended.";
?>