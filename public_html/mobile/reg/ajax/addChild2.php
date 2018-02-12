<?
require '../../../db.php';
$school = mysql_real_escape_string( $_POST['school'] );
$grade = mysql_real_escape_string( $_POST['grade'] );
$last = mysql_real_escape_string( $_POST['last'] );
$dob = mysql_real_escape_string( $_POST['dob'] );
$arrDob = explode('/', $dob);
$dob = $arrDob[2] . '-' . $arrDob[0] . '-' . $arrDob[1];
$admin_id = mysql_real_escape_string( $_POST['admin'] );
require 'encrypt.php';
$admin_id = encrypt_decrypt('decrypt', $admin_id);

$sql = "select user_id from users where 
		school_id = " . $school . " 
		and class_id = " . $grade . " 
		and last = '" . $last . "' 
		and dob = '" . $dob . "'";
$result = mysql_query( $sql );
if (mysql_num_rows($result) > 0) {
	$row = mysql_fetch_assoc($result);
	$id = $row['user_id'];
	
	//check if user is already connected with another admin
	$sql = "select * from admin_auths where id = " . $id . " and auth='user'";
	$result = mysql_query($sql);
	if (mysql_num_rows($result) > 0) {
		echo 1;
		exit;
	}
	
	$sql = "insert ignore into admin_auths set 
			admin_id = " . $admin_id . ", 
			id = " . $id . ", 
			auth = 'user', 
			role_id = 1";
	//echo $sql;
	if (mysql_query($sql)) {
		echo $id;
	} else {
		echo -1;
	}
} else {
	echo 0;
}
?>