<?php
require '../../../db.php';

$user = mysql_real_escape_string( $_POST['user'] );
$sql = "select * from users u 
		left join thumbs t on t.file_id = u.user_photo_id 
		where u.user_id = " . $user;
$result = mysql_query( $sql );
if ( mysql_num_rows($result) > 0 ) {
	$row = mysql_fetch_assoc($result);
	echo json_encode( $row );
} else {
	echo 0;
}
?>