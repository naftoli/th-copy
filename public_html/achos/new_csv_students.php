<? 
include("db.php");

$labels_file = fopen("new_csv_students/Morristown_Girls.csv", "r");

$row_num = 0;
$school_id = 0;
$child_type_id = 0;
while ($data = fgetcsv($labels_file, 1000, ",")) {
	$row_num++;
	
	if ($row_num > 1) {	
		if ($row_num == 2) {
			$school_name = $data[0];
			$sql = "SELECT school_id FROM schools WHERE school_name='" . $school_name . "'";
			$query = mysql_query($sql);
			$row = mysql_fetch_assoc($query);
			$school_id = $row["school_id"];
			
			$sql = "SELECT child_type_id FROM school_child_types WHERE school_id=" . $school_id . " AND is_default=1";
			$query = mysql_query($sql);
			$row = mysql_fetch_assoc($query);
			$child_type_id = $row["child_type_id"];	

			echo "SCHOOL ID:$school_id CHILD TYPE ID:$child_type_id<br />";
		}		
		
		if ($school_id > 0 && $child_type_id > 0) {
			$class_grade = $data[1];
			$sql = "SELECT class_id FROM classes WHERE school_id=" . $school_id . " AND class_grade='" . $class_grade . "'";
			$query = mysql_query($sql);
			$row = mysql_fetch_assoc($query);
			$class_id = $row["class_id"];		
			
			$last = $data[4];
			$first = $data[5];

			$username = get_username($first, $last);
			
			$first_he = $data[6];
			$last_he = $data[7];
			$gender = $data[8];
			$dob = $data[9];
			
			$user_address1 = $data[11];
			$user_address2 = $data[12];
			
			$user_city = $data[13];
			$user_state = $data[14];
			$user_postal = $data[15];
			$user_country = $data[16];
			
			$user_code = get_user_code();
			$user_serial = get_user_serial();
			
			$sql = "INSERT INTO users SET child_type_id=" . $child_type_id . ", ";
			$sql = $sql . "lang='en', ";
			$sql = $sql . "username='" . $username . "', ";
			$sql = $sql . "class_id=" . $class_id . ", ";
			$sql = $sql . "user_code=" . $user_code . ", ";
			$sql = $sql . "first='" . $first . "', ";
			$sql = $sql . "last='" . $last . "', ";
			$sql = $sql . "school_id=" . $school_id . ", ";
			$sql = $sql . "first_he='" . $first_he . "', ";
			$sql = $sql . "last_he='" . $last_he . "', ";
			$sql = $sql . "gender='" . $gender . "', ";
			$sql = $sql . "dob='" . $dob . "', ";
			$sql = $sql . "user_address1='" . $user_address1 . "', ";
			$sql = $sql . "user_address2='" . $user_address2 . "', ";
			$sql = $sql . "user_city='" . $user_city . "', ";
			$sql = $sql . "user_state='" . $user_state . "', ";
			$sql = $sql . "user_postal='" . $user_postal . "', ";
			$sql = $sql . "user_country='" . $user_country . "', ";
			$sql = $sql . "user_serial=" . $user_serial;
			
			$query = mysql_query($sql);
			if (!$query) {
				echo mysql_error() . "<br />";
			}
		}		
		
	}
	
}
fclose($labels_file);

function get_user_serial() {
	$user_serial = 0;
	
	$sql = "SELECT IFNULL(MAX(user_serial), 0)+1 AS user_serial FROM users";
	$query = mysql_query($sql);
	$row = mysql_fetch_assoc($query);
	$user_serial = $row["user_serial"];
	
	return $user_serial;
	
}

function get_username($first, $last) {
	$counter = 0;
	$username = strtolower(mysql_real_escape_string($first) . mysql_real_escape_string($last));
	do { 
		$counter++;
		$sql = "SELECT COUNT(*) AS number_of_usernames FROM users WHERE username='" . mysql_real_escape_string($username) . "'";
		$query = mysql_query($sql);
		$row = mysql_fetch_assoc($query);
		$number_of_usernames = $row['number_of_usernames'];
		if ($number_of_usernames > 0)
			$username = $username . $counter;
	} while ($number_of_usernames > 0);
	

	return $username;
}

function get_user_code() {
	$user_code = 0;
	
    if (mysql_result(mq("SELECT GET_LOCK('users', 30)"),0) != 1) 
		trigger_error('could not get lock', E_USER_ERROR);
		
    $count = 0;
    do {
		if ($count++ > 100000) 
			trigger_error('could not get ID', E_USER_ERROR);
			
		$user_code = mysql_result(mq('SELECT FLOOR(RAND() * 9223372036854775807)'),0);
		
    } while (mysql_result(mq("SELECT COUNT(*) FROM users WHERE user_code = $user_code"),0) != 0);

	return $user_code;
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">

<HTML>
	<HEAD>
	</HEAD>
	
	<BODY>
	</BODY>
</HTML>
