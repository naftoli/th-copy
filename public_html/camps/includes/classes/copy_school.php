<?php
include ("../db.php");

$start_date = gregoriantojd(date("n"), date("j"), date("Y"));
$end_date = $start_date + 364;

$school_id = $_GET['school_id'];
$sql = "SELECT * FROM schools WHERE school_id=" . $school_id;
$query = mysql_query($sql);
$row = mysql_fetch_assoc($query);

$sql = "INSERT INTO camps SET camp_name='" . $row['school_name'] . "', "; 
$sql = $sql . "camp_gender='" . $row['school_gender'] . "', ";
if ($row['school_logo_id'] > 0)
	$sql = $sql . "camp_logo_id=" . $row['school_logo_id'] . ", ";
$sql = $sql . "camp_address1='" . $row['school_address1'] . "', ";
$sql = $sql . "camp_address2='" . $row['school_address2'] . "', ";
$sql = $sql . "camp_city='" . $row['school_city'] . "', ";
$sql = $sql . "camp_state='" . $row['school_state'] . "', ";
$sql = $sql . "camp_country='" . $row['school_country'] . "', ";
$sql = $sql . "camp_postal='" . $row['school_postal'] . "', ";
$sql = $sql . "camp_phone='" . $row['school_phone'] . "', ";
$sql = $sql . "kiosk_print='" . $row['kiosk_print'] . "', ";
$sql = $sql . "camp_era='" . $row[''] . "', ";
$sql = $sql . "shipping_method='" . $row['shipping_method'] . "', ";
$sql = $sql . "shipping_first='" . $row['shipping_first'] . "', ";
$sql = $sql . "shipping_last='" . $row['shipping_last'] . "', ";
$sql = $sql . "shipping_phone='" . $row['shipping_phone'] . "', ";
$sql = $sql . "shipping_address1='" . $row['shipping_address1'] . "', ";
$sql = $sql . "shipping_address2='" . $row['shipping_address2'] . "', ";
$sql = $sql . "shipping_city='" . $row['shipping_city'] . "', ";
$sql = $sql . "shipping_state='" . $row['shipping_state'] . "', ";
$sql = $sql . "shipping_postal='" . $row['shipping_postal'] . "', ";
$sql = $sql . "shipping_country='" . $row['shipping_country'] . "', "; 
$sql = $sql . "start_date=" . $start_date . ", "; 	
$sql = $sql . "end_date=" . $end_date . ", "; 		
$sql = $sql . "school_id=" . $school_id;
echo "1) " . $sql . "<br />";
$query = mysql_query($sql);

if ($query) {	
	echo "1) WORKED<br />";
	$camp_id = mysql_insert_id();
	
	// ***** UPDATE STUDENTS ***** //
	$sql = "UPDATE users SET camp_id=" . $camp_id . " WHERE school_id=" . $school_id;
	$query = mysql_query($sql);
	if (!$query) {
		echo "Failed to update users<br />";
		die();
	}
	// ***** UPDATE STUDENTS ***** //
	
	// ***** UPDATE ADMINS ***** //
	$sql = "SELECT admin_id FROM admins AS a JOIN admin_auths AS aa USING (admin_id) WHERE aa.id=" . $school_id . " AND aa.auth='school'";
	echo $sql . "<br />";
	$query = mysql_query($sql);
	while ($row = mysql_fetch_assoc($query)) {
		$admin_id = $row['admin_id'];
		$update = "UPDATE admins SET camp_id=" . $camp_id . " WHERE admin_id=" . $admin_id;
		$update_query = mysql_query($update);
		if (!$update_query) {
			echo "Failed to update admins<br />";
			die();
		}
	}
	// ***** UPDATE STUDENTS ***** //

	
	$sql = "INSERT INTO group_types SET camp_id=" . $camp_id . ", group_type_name='Classes', has_divisions=1";
	echo "2) " . $sql . "<br />";
	$query = mysql_query($sql);
	
	if ($query) {
		echo "2) WORKED<br />";
		$group_type_id = mysql_insert_id();

		$sql = "INSERT INTO divisions SET group_type_id=" . $group_type_id . ", division_name='Classes'";
		echo "3) " . $sql . "<br />";
		$query = mysql_query($sql);
		
		if ($query) {
			echo "3) WORKED<br />";
			$division_id = mysql_insert_id();

			$sql = "SELECT c.class_grade, c.class_sub FROM users AS u JOIN classes AS c USING (class_id) WHERE u.school_id=" . $school_id . " GROUP BY c.class_grade, c.class_sub";
			//echo "4) " . $sql . "<br />";
			$query = mysql_query($sql);
			while ($row = mysql_fetch_assoc($query)) {
				$group_name = $row['class_grade'] . " " . $row['class_sub'];
				$insert = "INSERT INTO groups SET division_id=" . $division_id . ", group_name='" . $group_name . "'";
				echo "5) " . $insert . "<br />";
				$insert_query = mysql_query($insert);
				if ($insert_query) {
					echo "5) WORKED<br />";
				}
				else {
					$delete = "DELETE FROM camps WHERE camp_id=" . $camp_id;
					$delete_query = mysql_query($delete);
			
					$delete = "DELETE FROM group_types WHERE group_type_id=" . $group_type_id;
					$delete_query = mysql_query($delete);

					$delete = "DELETE FROM divisions WHERE division_id=" . $division_id;
					$delete_query = mysql_query($delete);
					
					echo "5) DID NOT WORK<br />";
				}
			}
			
			$sql1 = "SELECT u.user_id, c.class_grade, c.class_sub FROM users AS u JOIN classes AS c USING (class_id) WHERE u.school_id=" . $school_id . " GROUP BY u.user_id";
			//echo "6) " . $sql1 . "<br />";
			$query1 = mysql_query($sql1);
			while ($row1 = mysql_fetch_assoc($query1)) {
				$group_name = $row1['class_grade'] . " " . $row1['class_sub'];
				$sql2 = "SELECT group_id FROM groups WHERE group_name='" . $group_name . "'";
				//echo "7) " . $sql2 . "<br />";
				$query2 = mysql_query($sql2);
				$row2 = mysql_fetch_assoc($query2);
				$group_id = $row2['group_id'];

				$insert = "INSERT INTO member_groups SET camp_id=" . $camp_id . ", user_id=" . $row1['user_id'] . ", group_type_id=" . $group_type_id . ", division_id=". $division_id . ", group_id=" . $row2['group_id'] . ", start_date=NOW(), end_date=0";
				echo "8) " . $insert . "<br />";
				$insert_query = mysql_query($insert);
				if ($insert_query) {
					echo "8) WORKED<br />";
				}
				else {
					$delete = "DELETE FROM camps WHERE camp_id=" . $camp_id;
					$delete_query = mysql_query($delete);
			
					$delete = "DELETE FROM group_types WHERE group_type_id=" . $group_type_id;
					$delete_query = mysql_query($delete);

					$delete = "DELETE FROM divisions WHERE division_id=" . $division_id;
					$delete_query = mysql_query($delete);
				
					$delete = "DELETE FROM groups WHERE group_id=" . $group_id;
					$delete_query = mysql_query($delete);
				
					echo "8) DID NOT WORK<br />";
				}

			}
			
			
		}
		else {
			$delete = "DELETE FROM camps WHERE camp_id=" . $camp_id;
			$delete_query = mysql_query($delete);
			
			$delete = "DELETE FROM group_types WHERE group_type_id=" . $group_type_id;
			$delete_query = mysql_query($delete);
		
			echo "3) DID NOT WORK<br />";
		}
	}
	else {
		$delete = "DELETE FROM camps WHERE camp_id=" . $camp_id;
		$delete_query = mysql_query($delete);
		echo "2) DID NOT WORK<br />";
	}
	
}
else {
	echo "1) DID NOT WORK<br />";
}
?>