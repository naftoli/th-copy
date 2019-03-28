<?php
require '../../../db.php';

$admin = mysql_real_escape_string( $_POST['admin'] );
$year = mysql_real_escape_string( $_POST['year'] );

require 'encrypt.php';
$admin = encrypt_decrypt('decrypt', $admin);

require 'regFeeSchools.php';

$users = array();
$sql = "select id from admin_auths where admin_id = " . $admin . " and role_id = 1 and auth = 'user'";
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc($result) ) {
	$users[] = $row['id'];
}

$children = array();
$sql = "select * from users 
		where user_id in (" . implode(',', $users) . ")";
//echo $sql;
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc($result) ) {
    
    if (!in_array($row['school_id'], $showRegister)) continue;
	
	$regSql = "select * from user_registration where year = " . $year . " and user_id = " . $row['user_id'];
    $regRes = mysql_query( $regSql );
    if ( mysql_num_rows( $regRes ) != 0 ) continue;
    
	$children[] = $row['user_id'];
}
echo json_encode( $children );
?>