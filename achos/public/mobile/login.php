<?php
session_start();

require_once '../db.php';
require_once '../class.achosStudent.php';

$username = mysql_real_escape_string(trim($_POST['username']));
$password = mysql_real_escape_string(trim($_POST['password']));

if ($username == '' || $password == '') {
	echo "Username and Password cannot be blank.";
	exit;
}

$sql = "select * from admins where username = '" . $username . "' and password = '" . $password . "'";
$result = mysql_query($sql);
if (mysql_num_rows($result) == 1) {
	$row = mysql_fetch_assoc($result);
	$admin_id = $row['admin_id'];
	
	$as = new AchosStudent($admin_id);
	$_SESSION['admin_id'] = $admin_id;
	$_SESSION['user_id'] = $as->getStudentID();
	$_SESSION['school'] = $as->getSchoolName();
	$_SESSION['name'] = $as->getName();
	$_SESSION['grade'] = $as->getGrade();
	$_SESSION['photo'] = $as->getPhoto();
	$_SESSION['subject'] = $as->getSubject();
	
	echo 1;
} else {
	echo 0;
}
?>