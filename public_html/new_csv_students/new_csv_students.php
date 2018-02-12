<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">

<HTML>
	<HEAD>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	</HEAD>
	
	<BODY>
	
<?
$debug = true; 
if (isset($_GET['debug']) && $_GET['debug'] == 'false') {
	$debug = false;
}

include("../db.php");

//get subjects adding ladder/year to child
$subjects = array();
$subject_sql = "select * from subjects WHERE subject_type = '' and subject_id != 91";
$subject_res = mysql_query($subject_sql);
while ($subject_row = mysql_fetch_assoc($subject_res)) {
	$subjects[] = $subject_row['subject_id'];
}

$new_students = fopen("students/Rockland.csv", "r");

$row_num = 0;
$school_id = 198;
$added = 0;
$success = 0;

$contents = stream_get_contents($new_students);
$arrRows = preg_split("/[\n\r]+/", $contents);
foreach ($arrRows as $strLine) {

	$data = split(",", $strLine);	
	$row_num++;
	if ($row_num > 0) {
	    if ($data[1] == 'Chabad') continue;
        //print_r( $data );	
		
		$i = 1;
		$class_grade = trim($data[$i++]);
		$class_sub = trim($data[$i++]);
		
		if ($school_id == 54) {
			$class_sub = substr($class_sub, strpos($class_sub, '-')+1);
		}
		
		$class_teacher = $data[$i++];
		
		$sql = "SELECT class_id FROM classes WHERE school_id=" . $school_id . " AND class_grade='" . $class_grade . "' and class_sub = '$class_sub'";					
		if ($debug) 
			echo $sql . "<br />";
		$query = mysql_query($sql) or die(mysql_error());
		$row = mysql_fetch_assoc($query);
		$class_id = $row["class_id"];		
		
		$last = $data[$i++];
		$first = $data[$i++];
		
		$username = get_username($first, $last);
		
		$first_he = $data[$i++];
		$last_he = $data[$i++];		
		
		$gender = $data[$i++];
		
		$dob = $data[$i++];
		//echo $dob;
		$date = explode('/', $dob);
		//print_r($date);
		//$pos = strpos($date[2], " ");
		if (!empty($dob)) {
			$yy = $date[2];
			$mm = $date[0];
			$dd = $date[1];
				
			if (strlen($mm) == 1) $mm = '0' . $mm;
			if (strlen($dd) == 1) $dd = '0' . $dd;
			
			$dob = $yy . "-" . $mm . "-" . $dd;
			$dob = $dob > 0 ? $dob : null;
		}		
		
		/*
		if (count($date) == 3) {
			$sql = "update users set dob = '$dob' where first = '$first' and last = '$last'";
			echo $sql . "<br />";
			if (mysql_query($sql)) $success++;
		}
		*/
		$i++;
		$user_address1 = $data[$i++];
		$user_address2 = $data[$i++];
		$user_city = $data[$i++];
		$user_state = $data[$i++];
		$user_postal = (int)$data[$i++];
		if ($user_postal == 0) $user_postal = '';
		$user_country = $data[$i++];
		$user_phone = $data[$i++];
		$user_email = $data[$i++];
		//see if there's a second email address and increment counter
		//if (strpos($data[$i], '@'))
		//	$i++;
		$type = $data[$i++];
        if (empty($type)) 
        	$type = 'chabad';
		
		switch ($type) {
			case 'chabad':
			case 'Chabad':
			case 'CHABAD':
				switch ($gender) {
					case 'male':
					case 'm':
					case 'M':
					case 'MALE':
					case 'Boy':
					case 'boy':
                    case 'BOY': 
						$type = 2;
						$child_type_id = 1;
						break;
					case 'female':
					case 'f':
					case 'F':
					case 'FEMALE':
					case 'Girl':
					case 'girl':
                    case 'GIRL':
						$type = 3;
						$child_type_id = 1;
						break;
					default:
						$type = 2;
						$child_type_id = 1;
						break;
				}
				break;
			case 'frum':
			case 'Frum':
			case 'FRUM':
				switch ($gender) {
					case 'male':
					case 'm':
					case 'M':
					case 'MALE':
					case 'Boy':
					case 'boy':
						$type = 12;
						$child_type_id = 2;
						break;
					case 'female':
					case 'f':
					case 'F':
					case 'FEMALE':
					case 'Girl':
					case 'girl':
						$type = 13;
						$child_type_id = 2;
						break;
					default:
						$type = 12;
						$child_type_id = 2;
						break;
				}
				break;
		}
		
		$user_code = get_user_code();
		$user_serial = get_user_serial();
		
		$sql = "INSERT INTO users SET child_type_id=" . $child_type_id . ", ";
		$sql = $sql . "lang='en', ";
		$sql = $sql . "username='" . $username . "', ";
		$sql = $sql . "class_id=" . $class_id . ", ";
		$sql = $sql . "user_code=" . $user_code . ", ";
		$sql = $sql . "first='" . mysql_real_escape_string($first) . "', ";
		$sql = $sql . "last='" . mysql_real_escape_string($last) . "', ";
		$sql = $sql . "school_id=" . $school_id . ", ";
		
		$sql = $sql . "first_he='" . mysql_real_escape_string($first_he) . "', ";
		$sql = $sql . "last_he='" . mysql_real_escape_string($last_he) . "', ";
		$sql = $sql . "gender='" . mysql_real_escape_string($gender) . "', ";
		$sql = $sql . "dob='" . mysql_real_escape_string($dob) . "', ";
		
		$sql = $sql . "user_address1='" . mysql_real_escape_string($user_address1) . "', ";
		$sql = $sql . "user_address2='" . mysql_real_escape_string($user_address2) . "', ";
		$sql = $sql . "user_city='" . mysql_real_escape_string($user_city) . "', ";
		$sql = $sql . "user_state='" . mysql_real_escape_string($user_state) . "', ";
		$sql = $sql . "user_postal='" . mysql_real_escape_string($user_postal) . "', ";
		$sql = $sql . "user_country='" . mysql_real_escape_string($user_country) . "', ";
		$sql = $sql . "email='" . mysql_real_escape_string($user_email) . "', ";
		
		$sql = $sql . "user_start_date='" . unixtojd() . "', ";				
		$sql = $sql . "school_type_id=" . $type . ", ";

		$sql = $sql . "user_serial=" . $user_serial;
		
		if ($debug) {
			echo $row_num . ": " . $sql . "<br /><br />";
		}
		else {
			$query = mysql_query($sql);
			if ($query) {
				$added++;
				$user_id = mysql_insert_id();
			} else {
				echo "*** ERROR->" . $sql . mysql_error() . "<br />";	
			}
			/*
			//insert user into ranks as private
			$sql = "INSERT IGNORE INTO rank_marks (rank_ord, user_id, date_promoted) 
				SELECT rank_ord, $user_id user_id, " . unixtojd() . ' date_promoted 
				FROM ranks WHERE medals_required <= 0';
			mysql_query($sql);

			//add ladders/years to db
			switch ($class_grade) {
			case '1':
				$level = 7;
				break;
			case '2':
				$level = 8;
				break;
			case '3':
				$level = 9;
				break;
			case '4':
				$level = 10;
				break;
			case '5':
				$level = 11;
				break;
			case '6':
				$level = 12;
				break;
			case '7':
				$level = 13;
				break;
			case '8':
				$level = 14;
				break;		
			}
			
			foreach ($subjects as $subject) {
				$insert_sql = "insert into user_tracks values( $user_id, $subject, 1, $level, 0 )";
				//echo $sql . "<br />";
				mysql_query($insert_sql);
			}
             * 
             */
		}
	}
}

fclose($new_students);

echo "Successfully updated: " . $success . "<br />";

echo "ADDED:" . $added . "<br />";
	
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
	</BODY>
</HTML>
