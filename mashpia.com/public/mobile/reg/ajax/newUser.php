<?php
ini_set('display_errors',1);
chdir('../../../');
require 'db.php';

$admin_id = mysql_real_escape_string( $_POST['admin'] );
$first = mysql_real_escape_string( $_POST['first'] );
$last = mysql_real_escape_string( $_POST['last'] );
$firstHe = mysql_real_escape_string( $_POST['firsthe'] );
$lastHe = mysql_real_escape_string( $_POST['lasthe'] );
$address = isset( $_POST['address'] ) ? mysql_real_escape_string( $_POST['address'] ) : '';
$city = isset( $_POST['city'] ) ? mysql_real_escape_string( $_POST['city'] ) : '';
$state = isset( $_POST['state'] ) ? mysql_real_escape_string( $_POST['state'] ) : '';
$zip = isset( $_POST['zip'] ) ? mysql_real_escape_string( $_POST['zip'] ) : '';
$school = mysql_real_escape_string( $_POST['school_id'] );
$grade = mysql_real_escape_string( $_POST['class_id'] );
$dobArr = explode('/', mysql_real_escape_string( $_POST['dob'] ) );
$dob = $dobArr[2] . '-' . $dobArr[0] . '-' . $dobArr[1];
$gender = mysql_real_escape_string( $_POST['gender'] );
$photo = mysql_real_escape_string( $_POST['photo'] );
$lang = mysql_real_escape_string( $_POST['lang'] );

require 'encrypt.php';
$admin_id = encrypt_decrypt('decrypt', $admin_id);

require 'classes/admin.php';
$sql = "select * from admins where admin_id = " . $admin_id;
$result = mysql_query( $sql );
$row = mysql_fetch_assoc( $result );
$parent = new \classes\admin( $row );

require 'newClasses/newSoldier.php';
if (!empty($photo)) 
	$u = new NewSoldier( $parent, $first, $last, $dob, $gender, $school, $grade, $firstHe, $lastHe, $photo, true, false );
else 
	$u = new NewSoldier( $parent, $first, $last, $dob, $gender, $school, $grade, $firstHe, $lastHe );

if ($lang > 1) $u->setLang( $lang );

//echo $u->create();
if ($u->create())
	echo $u->getUserID();
else 
	echo 0;
?>