<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">

<HTML>
	<HEAD>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	</HEAD>
	
	<BODY>
	
<? 
include("../db.php");
/*
$school_id = 79;
$new_students = array( array( 'Brett', 'Carnival' ), array( 'Aiden', 'McKee' ) );
foreach ( $new_students as $student ) {
    $first = $student[0];
    $last = $student[1];
    $user_code = get_user_code();
    $user_serial = get_user_serial();
    
    $sql = "INSERT INTO users SET lang='en', ";
    $sql = $sql . "user_code=" . $user_code . ", ";
    $sql = $sql . "first='" . mysql_real_escape_string($first) . "', ";
    $sql = $sql . "last='" . mysql_real_escape_string($last) . "', ";
    $sql = $sql . "user_start_date='" . unixtojd() . "', ";
    $sql = $sql . "school_id=" . $school_id . ", ";             
    $sql = $sql . "user_serial=" . $user_serial;
    
    if ( mysql_query( $sql ) ) {
        echo "added " . mysql_insert_id() . "<br />";
    }
}
*/

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
		
		//print_r($data);
		$first = trim($data[0]);		
		$last = trim($data[1]);
		
		$username = get_username($first, $last);
		
		$user_code = get_user_code();
		$user_serial = get_user_serial();
		
		$sql = "INSERT INTO users SET lang='en', ";
		$sql = $sql . "username='" . $username . "', ";
		$sql = $sql . "user_code=" . $user_code . ", ";
		$sql = $sql . "first='" . mysql_real_escape_string($first) . "', ";
		$sql = $sql . "last='" . mysql_real_escape_string($last) . "', ";
		$sql = $sql . "user_start_date='" . unixtojd() . "', ";
		$sql = $sql . "school_id=" . $school_id . ", "; 			
		$sql = $sql . "user_serial=" . $user_serial;
		
		//echo $row_num . ": " . $sql . "<br /><br />";
		
		$query = mysql_query($sql);
		if ($query) {
			$added++;
			$user_id = mysql_insert_id();
		} else {
			echo "*** ERROR->" . $sql . mysql_error() . "<br />";	
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
