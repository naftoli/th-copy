<?
require '../db.php';

$username = strtolower(mysql_real_escape_string(trim($_POST['username'])));
$school = mysql_real_escape_string(trim($_POST['school']));
$year = mysql_real_escape_string($_POST['year']);
$gender = mysql_real_escape_string($_POST['gender']);

$sql = "select * from chidon_schools 
		where year = $year 
		and chidon_schools_id = $school  
		and username = '" . $username . "' 
		and gender = '" . $gender . "'";
$result = mysql_query($sql);
if (mysql_num_rows($result) > 0) {
	$row = mysql_fetch_assoc($result);
	$schoolID = $row['chidon_schools_id'];	
	echo $schoolID;
} else {
	echo 0;
}