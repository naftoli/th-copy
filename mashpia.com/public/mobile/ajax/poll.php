<?php
require_once $_SERVER['DOCUMENT_ROOT']."/db.php"; // connect to the database....

$user_id = mysql_real_escape_string($_POST['user']);
$rally_number = mysql_real_escape_string($_POST['poll_number']);
$result = mysql_query("insert into rally_poll set user_id = " . intval( $user_id ) . ", rally_number = " . intval( $rally_number ));

echo json_encode( $result );