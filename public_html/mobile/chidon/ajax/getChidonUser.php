<?php
require '../../../db.php';

$info = array();
$user = mysql_real_escape_string( $_POST['user'] );
$year = mysql_real_escape_string( $_POST['year'] );

$sql = "select a.* from admins a
        join admin_auths aa using (admin_id)
        where aa.id = " . $user . "
        and aa.auth='user'";
//echo $sql;
$result = mysql_query($sql);
if ( mysql_num_rows($result) > 0 ) {
	$row = mysql_fetch_assoc($result);
	$info['admin'] = $row;
}

$sql = "select * from users u
        left join classes c on (u.class_id = c.class_id) 
		left join thumbs t on t.file_id = u.user_photo_id 
		where u.user_id = " . $user;
$result = mysql_query( $sql );
if ( mysql_num_rows($result) > 0 ) {
	$row = mysql_fetch_assoc($result);
	$info['user'] = $row;
}

$sql = "select * from th_chidon where year = " . $year . " and user_id = " . $user;
$result = mysql_query( $sql );
if (mysql_num_rows($result) > 0) {
	$row = mysql_fetch_assoc($result);
	$info['chidon'] = $row;
} else {
	$info['chidon'] = array();
}

if (empty($info)) echo 0;
else echo json_encode($info);
?>