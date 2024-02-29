<?php
require '../../../db.php';
require 'encrypt.php';
require '../../../class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

$user = mysql_real_escape_string( $_POST['user'] );
$admin_id = mysql_real_escape_string( $_COOKIE['admin'] );
$admin_id = encrypt_decrypt('decrypt', $admin_id);

$sql = "select * from users u 
        left join th_chidon tc on tc.user_id = u.user_id 
		left join thumbs t on t.file_id = u.user_photo_id 
		join admin_auths aa on aa.id = u.user_id && aa.auth = 'user'
		where u.user_id = " . $user ."
		and aa.admin_id = " . $admin_id . "
		and tc.year = " . $year;
$result = mysql_query( $sql );
if ( mysql_num_rows($result) > 0 ) {
	$row = mysql_fetch_assoc($result);
	echo json_encode( $row );
} else {
	echo 0;
}
?>