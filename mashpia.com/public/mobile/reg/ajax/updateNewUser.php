<?php
chdir('../../../');
require 'db.php';

$user_id = mysql_real_escape_string(  $_POST['user_id'] );

// auth
require 'encrypt.php';
$admin_id = mysql_real_escape_string( $_COOKIE['admin'] );
$admin_id = encrypt_decrypt('decrypt', $admin_id);

$first = ucwords(mysql_real_escape_string( $_POST['fname'] ));
$mothers_name = ucwords(mysql_real_escape_string( $_POST['mothers_name'] ));
$country = mysql_real_escape_string( $_POST['country'] );
$zip = mysql_real_escape_string( $_POST['zip'] );
$gender = strtoupper(mysql_real_escape_string( $_POST['gender'] ));
$photo = mysql_real_escape_string( $_POST['photo'] );
$lang = mysql_real_escape_string( $_POST['lang'] );
$school_type_id = mysql_real_escape_string( intval($_POST['type']) );

// make sure we have correct school type id
if ( $gender == 'F' ) {
	$school_type_id++;
}

mysql_query("set autocommit=0");
mysql_query("begin");
$success = true;

// update admin
$sql = "UPDATE admins SET 
		admin_country = '$country',
		admin_postal = '$zip'
		where admin_id = $admin_id";
if (!mysql_query($sql)) {
	$success = false;
} else {
	// update user
	$sql = "UPDATE users SET 
			first = '$first', 
			mothers_name = '$mothers_name', 
			lang_id = $lang, 
			gender = '$gender',   
			school_type_id = " . $school_type_id;
	if ($photo && strpos($photo, "img/") !== false && $photo != 'images/addphoto.png') {
		$sql .= ", mobile_pic = '" . $photo . "'";
	}
	$sql .= " WHERE user_id = " . $user_id;
	echo $sql; exit;
	if (!mysql_query($sql)) {
		$success = false;
	}
}

if ($success) {
	mysql_query("commit");
	mysql_query("set autocommit=1");
	echo 1;
} else {
	mysql_query("rollback");
	mysql_query("set autocommit=1");
	echo 0;
}
?>