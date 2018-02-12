<?php
require_once '../db.php';

$campaign = mysql_real_escape_string($_POST['id']);
$amount = mysql_real_escape_string($_POST['val']);
if ($amount == '') $amount = 0;
$table = mysql_real_escape_string($_POST['table']);
$school_id = isset( $_POST['school'] ) ? mysql_real_escape_string($_POST['school']) : 0;
$class_id = isset( $_POST['grade'] ) ? mysql_real_escape_string($_POST['grade']) : 0;
$user_id = isset( $_POST['user'] ) ? mysql_real_escape_string($_POST['user']) : 0;
$updateSumary = isset( $_POST['updateSummary'] ) ? 1 : 0;

function updateSummary() {
    require_once '../class.bpSummary.php';
    if ($school_id) {
        $bps = new BpSummary( $campaign, 'school' );
        $bps->updateSummary( $school_id );
    }
    if ($class_id) {
        $bps = new BpSummary( $campaign, 'class' );
        $bps->updateSummary( $class_id );
    }
    if ($user_id) {
        $bps = new BpSummary( $campaign, 'user' );
        $bps->updateSummary( $user_id );
    }
}

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
	$sql = "select * from $table where user_id = " . $user_id . " and campaign_id = " . $campaign;
	$result = mysql_query($sql);
	if (mysql_num_rows($result) > 0 && $amount > 0) {
		$insert = false;
		$sql = "update $table set $table = " . $amount;
		$sql .= " where user_id = " . $user_id . " and campaign_id = " . $campaign;
		if (mysql_query($sql)) {
		    if ($updateSumary) updateSummary();
			echo 1;
		} else {
			echo 0;
		}
	} elseif (mysql_num_rows($result) > 0 && $amount == 0) {
		$insert = false;
		$sql = "DELETE FROM $table where user_id = " . $user_id . " and campaign_id = " . $campaign;
		if (mysql_query($sql)) {
		    if ($updateSumary) updateSummary();
			echo 1;
		} else {
			echo 0;
		}
	}
} else if ($class_id > 0) {
	$sql = "select * from $table where class_id = " . $class_id . " and campaign_id = " . $campaign . " and user_id = 0";
	$result = mysql_query($sql);
	if (mysql_num_rows($result) > 0) {
		$insert = false;
		$sql = "update $table set $table = " . $amount . " 
				where class_id = " . $class_id . " and campaign_id = " . $campaign . " and user_id = 0";
		if (mysql_query($sql)) {
		    if ($updateSumary) updateSummary();
			echo 1;
		} else {
			echo 0;
		}
	}
} else if ($school_id > 0) {
	$sql = "select * from $table where school_id = " . $school_id . " and campaign_id = " . $campaign . " and class_id = 0 
			and user_id = 0";
	$result = mysql_query($sql);
	if (mysql_num_rows($result) > 0) {
		$insert = false;
		$sql = "update $table set $table = " . $amount . " 
				where school_id = " . $school_id . " and campaign_id = " . $campaign . " and class_id = 0 and user_id = 0";
		if (mysql_query($sql)) {
		    if ($updateSumary) updateSummary();
			echo 1;
		} else {
			echo 0;
		}
	}
}

if ($insert && $amount > 0) {
	$sql = "insert into $table  
			set campaign_id = " . $campaign . ", 
			$table = " . $amount . ", 
			school_id = " . $school_id . ", 
			class_id = " . $class_id . ", 
			user_id = " . $user_id;
	//echo $sql;
	if (mysql_query($sql)) {
	    if ($updateSumary) updateSummary();
		echo 1;
	} else {
		echo 0;
	}
} elseif ( $insert && $amount == 0 ) { // if we want to insert nothing
	echo 1; // say all is good...
}
?>