<?php
require '../../../db.php';
require 'encrypt.php';
require '../../../class.globalSettings.php';
$year = GlobalSettings::getRegistrationYear();

$user = mysql_real_escape_string( $_POST['user'] );
$admin_id = mysql_real_escape_string( $_COOKIE['admin'] );
$admin_id = encrypt_decrypt('decrypt', $admin_id);

$sql = "SELECT 
            u.*
        FROM
            users u 
                LEFT JOIN
            th_chidon tc ON tc.user_id = u.user_id
                LEFT JOIN
            thumbs t ON t.file_id = u.user_photo_id 
                LEFT JOIN 
            admin_auths aa ON aa.id = u.user_id 
        WHERE
            u.user_id = " . $user . " AND aa.admin_id = " . $admin_id;
// echo $sql;
$result = mysql_query( $sql );
if ( mysql_num_rows($result) > 0 ) {
	$row = mysql_fetch_assoc($result);
	echo json_encode( $row );
} else {
	echo 0;
}
?>