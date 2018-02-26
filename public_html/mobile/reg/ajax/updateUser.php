<?php
chdir('../../../');
require 'db.php';
$user_id = mysql_real_escape_string(  $_POST['user_id'] );
$first = mysql_real_escape_string( $_POST['fname'] );
$last = mysql_real_escape_string( $_POST['last'] );
$firstHe = mysql_real_escape_string( $_POST['fhname'] );
$lastHe = mysql_real_escape_string( $_POST['lhname'] );
//$address = mysql_real_escape_string( $_POST['address'] );
//$city = mysql_real_escape_string( $_POST['city'] );
//$state = mysql_real_escape_string( $_POST['state'] );
//$zip = mysql_real_escape_string( $_POST['zip'] );
$school = mysql_real_escape_string( $_POST['school'] );
$grade = mysql_real_escape_string( $_POST['grade'] ? $_POST['grade'] : 0 );
$dobArr = explode('/', mysql_real_escape_string( $_POST['dob'] ) );
$dob = $dobArr[2] . '-' . $dobArr[0] . '-' . $dobArr[1];
$gender = mysql_real_escape_string( $_POST['gender'] );
$photo = mysql_real_escape_string( $_POST['photo'] );
$lang = mysql_real_escape_string( $_POST['lang'] );

// need to check if dob changed
$sql = "select dob from users where user_id = " . $user_id;
$result = mysql_query($sql);
$row = mysql_fetch_assoc($result);
if ($dob != $row['dob']) $dobChanged = true;
else $dobChanged = false;

/*
$sql = "update users set 
		first = '$first', 
		last = '$last', 
		first_he = '$firstHe', 
		last_he = '$lastHe',
		user_address1 = '$address',
		user_city = '$city', 
		user_state = '$state',
		user_postal = '$zip', 
		school_id = $school,
		class_id = $grade,
		dob = '$dob', 
		user_photo_id = '$photo'";
 *
 */
 $sql = "UPDATE users SET " 
	." first = '$first', "
	." last = '$last', "
	." first_he = '$firstHe', "
	." last_he = '$lastHe', "
	." he_name = '$firstHe $lastHe', "
	." school_id = $school, "
	." dob = '$dob', "
	." lang_id = " . $lang;
if ($grade > 0) {
	$sql .= ", class_id = $grade";
}
if ($gender == 'm' || $gender == 'f') {
	$sql .= ", gender = '" . $gender . "'";
}
if (strpos($photo, "img/") !== false) {
	$sql .= ", mobile_pic = '" . $photo . "'";
}
$sql .= " where user_id = " . $user_id;
$success = mysql_query( $sql );

//need to run birthday mission creator if dob changed
if ($success && $dobChanged) {
	// delete all existing birthday tasks
	$sql = "delete from birthdays where user_id = " . $user_id;
	mysql_query($sql);
	
	//update birthday
	require 'class.birthday.php';
	$b = new Birthday( $user_id );
	$b->setBirthday();
	require 'class.birthdayYi.php';
	$b = new BirthdayYi( $user_id );
	$b->setBirthday();
	
	//set dob for syncing with wp
	require 'class.heDob.php';
	$hdob = new HeDob( $user_id );
	$hdob->setHeDob();
}
echo $success;
?>