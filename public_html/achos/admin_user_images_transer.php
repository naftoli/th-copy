<?php
include "db.php";

$new_link = mysql_connect('mashpia.icorpa.com', 'mashpia_devel','q65u7sZVsnGb');
mysql_query('SET NAMES utf8', $new_link);
mysql_query('SET CHARACTER_SET utf8', $new_link);
mysql_select_db('mashpia_devel', $new_link);

$today = date("Y-m-d H:i:S");

// ********** INSERT USERS FROM OLD DATABASE INTO NEW DATABASE ********** //
/*$sql = "SELECT * FROM users WHERE first <> '' AND last <> ''";
$query = mysql_query($sql, $link);
while ($row = mysql_fetch_assoc($query)) {

	if ($row['user_registered'] > 0)
		$is_active = 1;
	else
		$is_active = 0;
		
	$email = substr($row['first'], 0, 1) . $row['last'];	
	$email_found = true;
	$counter = 0;		
	do {
			if ($counter > 0)
				$new_email = str_replace("'", "\'", $email) . $counter;
			else
				$new_email = str_replace("'", "\'", $email);
				
			$sql2 = "SELECT count(*) AS no_of_emails FROM users WHERE email='" . mysql_real_escape_string($new_email) . "'";
			$query2 = mysql_query($sql2, $new_link);
			$row2 = mysql_fetch_assoc($query2);
			
			if ($row2['no_of_emails'] == 0)
				$email_found = false;
			
			$counter++;
	} while ($email_found == true && $counter < 50);
	
	//echo "USER ID:" . $row['user_id'] . "<br />";
	
	$insert_sql = "INSERT INTO users SET user_id=" . $row['user_id'] . ", ";
	$insert_sql = $insert_sql . "email='" . mysql_real_escape_string($new_email) . "', ";
	$insert_sql = $insert_sql . "password='" . md5(mysql_real_escape_string($row['user_code'])) . "', ";
	$insert_sql = $insert_sql . "first_name='" . mysql_real_escape_string($row['first']) . "', ";	
	$insert_sql = $insert_sql . "last_name='" . mysql_real_escape_string($row['last']) . "', ";		
	$insert_sql = $insert_sql . "hebrew_first_name='" . mysql_real_escape_string($row['first_he']) . "', ";	
	$insert_sql = $insert_sql . "hebrew_last_name='" . mysql_real_escape_string($row['last_he']) . "', ";	
	$insert_sql = $insert_sql . "is_active=" . $is_active . ", ";	
	$insert_sql = $insert_sql . "address='" . mysql_real_escape_string($row['address1']) . "', ";	
	$insert_sql = $insert_sql . "city='" . mysql_real_escape_string($row['user_city']) . "', ";	
	$insert_sql = $insert_sql . "state='" . mysql_real_escape_string($row['state']) . "', ";	
	$insert_sql = $insert_sql . "country='" . mysql_real_escape_string($row['user_country']) . "', ";	
	$insert_sql = $insert_sql . "postal='" . mysql_real_escape_string($row['user_postal']) . "', ";	
	$insert_sql = $insert_sql . "phone='" . mysql_real_escape_string($row['user_phone']) . "', ";	
	$insert_sql = $insert_sql . "created='" . $today . "'";
	
	$insert_query = mysql_query($insert_sql, $new_link);

	//echo $insert_sql . "<br /><br />";
	
	if (!$insert_query) {
		echo "1) " . mysql_error() . "<br />";
	}
}

echo "DONE<br />";
*/
// ********** INSERT USERS FROM OLD DATABASE INTO NEW DATABASE ********** //

// ********** INSERT IMAGES FROM OLD SYSTEM INTO NEW SYSTEM *********** //
$sql = "SELECT u.*, f.file_content_type AS photo_type, f.file_data AS photo FROM users AS u JOIN files AS f ON (u.user_photo_id=f.file_id) WHERE u.image_added=0 LIMIT 1";
$query = mysql_query($sql, $link);
while ($row = mysql_fetch_assoc($query)) {
	$insert_sql = "INSERT INTO images SET photo='" . mysql_real_escape_string($row['photo']) . "', photo_type='" . mysql_real_escape_string($row['photo_type']) . "'";
	$insert_query = mysql_query($insert_sql, $new_link);
	
	if ($insert_query) {
		$image_id = mysql_insert_id(); 
		
		$update_sql = "UPDATE users SET image_id=" . $image_id . " WHERE user_id=" . $row['user_id'];
		$update_query = mysql_query($update_sql, $new_link);
		
		if ($update_query) {
			$update_sql2 = "UPDATE users SET image_added=1 WHERE user_id=" . $row['user_id'];
			$update_query2 = mysql_query($update_sql2, $link);			
		}
		else {
			echo "2) " . mysql_error() . "<br />";
		}
	}
	else {
		echo "1) " . mysql_error() . "<br />";
	}	
}
echo "DONE<br />";
// ********** INSERT IMAGES FROM OLD SYSTEM INTO NEW SYSTEM *********** //

// ********** INSERT THE ADMINS INTO THE NEW DATABASE ********** //
/*$sql = "SELECT * FROM admins WHERE been_added=0 AND admin_email <> ''";
$query = mysql_query($sql, $link);
while ($row = mysql_fetch_assoc($query)) {
	$insert_sql = "INSERT INTO users SET email='" . mysql_real_escape_string($row['admin_email']) . "', ";
	$insert_sql = $insert_sql . "password='" . md5(mysql_real_escape_string($row['password'])) . "', ";
	$insert_sql = $insert_sql . "first_name='" . mysql_real_escape_string($row['first']) . "', ";	
	$insert_sql = $insert_sql . "last_name='" . mysql_real_escape_string($row['last']) . "', ";		
	$insert_sql = $insert_sql . "is_active=1, ";	
	$insert_sql = $insert_sql . "address='" . mysql_real_escape_string($row['admin_address1']) . "', ";	
	$insert_sql = $insert_sql . "city='" . mysql_real_escape_string($row['admin_city']) . "', ";	
	$insert_sql = $insert_sql . "state='" . mysql_real_escape_string($row['admin_state']) . "', ";	
	$insert_sql = $insert_sql . "country='" . mysql_real_escape_string($row['admin_country']) . "', ";	
	$insert_sql = $insert_sql . "postal='" . mysql_real_escape_string($row['admin_postal']) . "', ";	
	$insert_sql = $insert_sql . "phone='" . mysql_real_escape_string($row['admin_phone']) . "', ";	
	$insert_sql = $insert_sql . "created='" . $today . "'";
	
	$insert_query = mysql_query($insert_sql, $new_link);
	
	if (!$insert_query) {
		echo $insert_sql . "<br /><br />";
		echo "1) " . mysql_error() . "<br />";
	}	
	else {
		$update_sql2 = "UPDATE admins SET benn_added=1 WHERE admin_id=" . $row['admin_id'];
		$update_query2 = mysql_query($update_sql2, $link);				
	}
}
echo "DONE<br />";*/
// ********** INSERT THE ADMINS INTO THE NEW DATABASE ********** //
?>