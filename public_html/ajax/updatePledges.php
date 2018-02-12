<?
require_once '../db.php';

$campaign = $_POST['id'];
$amount = $_POST['val'];
$school_id = isset( $_POST['school'] ) ? $_POST['school'] : 0;
$class_id = isset( $_POST['grade'] ) ? $_POST['grade'] : 0;
$user_id = isset( $_POST['user'] ) ? $_POST['user'] : 0;

//make sure that class has school id as well
//make sure user has school and class id
if ($class_id && !$school_id) {
	echo 0;
	exit;
} else if ($user_id && !($school_id && $class_id)) {
	echo 0;
	exit;
}

require_once '../class.balPehCampaign.php';
$bp = BalPehCampaign::getInstance( $campaign );

$insert = true;
if ($user_id > 0) {
	$sql = "select * from lines_pledged where user_id = " . $user_id . " and campaign_id = " . $campaign;
	$result = mysql_query($sql);
	if (mysql_num_rows($result) > 0) {
		$insert = false;
		$sql = "update lines_pledged set lines_pledged = " . $amount . " 
				where user_id = " . $user_id . " and campaign_id = " . $campaign;
		if (mysql_query($sql)) {
			echo 1;
		} else {
			echo 0;
		}
	}
} else if ($class_id > 0) {
	$sql = "select * from lines_pledged where class_id = " . $class_id . " and campaign_id = " . $campaign . " and user_id = 0";
	$result = mysql_query($sql);
	if (mysql_num_rows($result) > 0) {
		$insert = false;
		$sql = "update lines_pledged set lines_pledged = " . $amount . " 
				where class_id = " . $class_id . " and campaign_id = " . $campaign . " and user_id = 0";
		if (mysql_query($sql)) {
			echo 1;
		} else {
			echo 0;
		}
	}
} else if ($school_id > 0) {
	$sql = "select * from lines_pledged where school_id = " . $school_id . " and campaign_id = " . $campaign . " and class_id = 0 
			and user_id = 0";
	$result = mysql_query($sql);
	if (mysql_num_rows($result) > 0) {
		$insert = false;
		$sql = "update lines_pledged set lines_pledged = " . $amount . " 
				where school_id = " . $school_id . " and campaign_id = " . $campaign . " and class_id = 0 and user_id = 0";
		if (mysql_query($sql)) {
			echo 1;
		} else {
			echo 0;
		}
	}
}

if ($insert) {
	$sql = "insert into lines_pledged  
			set campaign_id = " . $campaign . ", 
			lines_pledged = " . $amount . ", 
			school_id = " . $school_id . ", 
			class_id = " . $class_id . ", 
			user_id = " . $user_id;
	if (mysql_query($sql)) {
		echo 1;
	} else {
		echo 0;
	}
}
?>