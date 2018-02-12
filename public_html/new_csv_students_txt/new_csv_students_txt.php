<? 
include("../db.php");

$file = fopen("School_30.txt", "r") or exit("Unable to open file!");

while (!feof($file)) {
	$buffer = fgets($file, 4096); 
	echo $buffer . "<br />";
}

fclose($file);

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
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	</HEAD>
	
	<BODY>
	</BODY>
</HTML>
