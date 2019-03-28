<?php
ini_set('display_errors', 1);
require '../../../db.php';

define('SUCCESSINDEX', 0);
define('ERRORINDEX', 3);

$users = $_POST['users'];
$amount = (int)mysql_real_escape_string( $_POST['amount'] );
$card_num = mysql_real_escape_string( $_POST['number'] );
$exp_date = mysql_real_escape_string( $_POST['expiry'] );
$zip = mysql_real_escape_string( $_POST['zip'] );
$first_name = '';
$last_name = '';
$address = '';
$state = '';
$year = mysql_real_escape_string( $_POST['year'] );
$description = "Re-registration for student(s): " . implode(',', $users) . " for year " . $year;
$admin_id = mysql_real_escape_string( $_POST['admin_id'] );
$shipFee = mysql_real_escape_string( $_POST['shipFee'] );
$shipOption = mysql_real_escape_string( $_POST['shipOpt'] );
$shipDest = mysql_real_escape_string( $_POST['dest'] );

require 'encrypt.php';
$admin_id = encrypt_decrypt('decrypt', $admin_id);

// get school IDs
$anash = false;
$myshliach = false;
$kinder = array();
$schools = array();
$sql = "select user_id, school_id from users where user_id in (" . implode(',', $users) . ")";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$schools[$row['user_id']] = $row['school_id'];
	if ($row['school_id'] == 269) {
		$anash = true;
		$kinder[] = $row['user_id'];
	}
	if ($row['school_id'] == 61) {
		$myshliach = true;
		$kinder[] = $row['user_id'];
	}
}

chdir('../../../');
require 'authorize.php';
chdir('mobile/reg/ajax/');
if ($response_array[SUCCESSINDEX] == 1) { // success
	// update user_registration
	require_once 'regFeeSchools.php';
	foreach ($users as $user_id) {
		$user_id = mysql_real_escape_string($user_id);
		$userAmount = $userFee;
		if (in_array($schools[$user_id], $tuitionSchools)) $userAmount = 45;
		if (in_array($schools[$user_id], $tuitionSchoolsNoPay)) $userAmount = 0;
		$sql = "insert into user_registration 
				set user_id = " . $user_id . ", 
				admin_id = " . $admin_id . ", 
				year = " . $year . ", 
				reg_date = now(), 
				paid = " . $userAmount . ",
				school_id = " . $schools[$user_id];
		//echo $sql;
		if (!@mysql_query( $sql )) {
			$to = "naftolir@gmail.com";
			$subject = "Error in mobile registration.";
			$msg = $sql . " - " . mysql_error();
			@mail($to, $subject, $msg);
		} 
		@mysql_query("update users set user_registered = now() where user_id = " . $user_id);
		@mysql_query("update users set user_start_date = " . unixtojd() . " where user_start_date is null and user_id = " . $user_id);
		
		//create private rank for soldier if no rank exists
		$sql = "select * from rank_marks where user_id = " . $user_id;
		$result = mysql_query($sql);
		if (mysql_num_rows($result) == 0) {
			$jd = unixtojd();
			$sql = "insert into rank_marks 
					set rank_ord = 1, 
					user_id = " . $user_id . ",  
					date_promoted = " . $jd;
			@mysql_query($sql);
		}		
	}
	
	$strUsers = implode(',', $users);
	$strResponse =  $response_array[3] . ':' . 
					$response_array[4] . ':' . 
					$response_array[6] . ':' . 
					$response_array[9];	
	
	// update registration table
	if ($anash || $myshliach) {
		$schoolID = 269;
		if ($myshliach) $schoolID = 61;
		$sql = "insert into registration 
				set description = \"User Registration for $year : $strUsers\",  
				approval = '" . $strResponse . "', 
				year = " . $year . ", 
				school_id = " . $schoolID . ", 
				admin_id = " . $admin_id . ", 
				ship_option = " . $shipOption . ", 
				ship_dest = '" . $shipDest . "', 
				users = '" . implode(',', $kinder) . "'";
		//echo $sql;
		@mysql_query($sql);
	}
	
	// update transactions table
	$sql = "insert into transactions 
			set trans_date = now(),
			admin_id = " . $admin_id . ", 
			description = \"User Registration for $year : $strUsers\", 
			amount = '" . $amount . "', 
			reg_amount = " . intval($amount - $shipfee) . ", 
			ship_amount = " . intval($shipFee) . ", 
			zip = '" . $zip . "',
			users_registered = '" . $strUsers . "', 
			response = \"" . $strResponse . "\"";
	//echo $sql;
	@mysql_query($sql);
					
	echo 1;
} else {
	echo $response_array[ERRORINDEX];
	//echo 1;
}
?>