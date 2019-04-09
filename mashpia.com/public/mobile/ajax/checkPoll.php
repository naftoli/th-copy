<?php
require_once $_SERVER['DOCUMENT_ROOT']."/db.php"; // connect to the database....

$user_id = mysql_real_escape_string($_POST['user']);
$result = mysql_query("select * from rally_poll where user_id = " . $user_id);
if ( mysql_num_rows( $result ) > 0 ) $notPolled = false;
else $notPolled = true;

echo json_encode( $notPolled );