<?
require '../db.php';

$school = mysql_real_escape_string($_POST['school']);
$grade = mysql_real_escape_string($_POST['grade']);
$user = mysql_real_escape_string($_POST['user']);

$sql = "select * from mishna_ppl where school_id = " . $school . " and class_id = " . $grade;
if ($user > 0) $sql .= " and user_id = " . $user;
//echo $sql;
$result = mysql_query($sql);
if (mysql_num_rows($result) > 0) echo 1;
else {
	if ($user > 0) {
		$sql = "select * from mishna_ppl where school_id = " . $school . " and class_id = " . $grade;
		$result = mysql_query($sql);
		if (mysql_num_rows($result) > 0) echo 1;
		else {
			$sql = "select * from mishna_ppl where school_id = " . $school;
			$result = mysql_query($sql);
			if (mysql_num_rows($result) > 0) echo 1;
			else echo 0;
		}
	} else {
		$sql = "select * from mishna_ppl where school_id = " . $school;
		$result = mysql_query($sql);
		if (mysql_num_rows($result) > 0) echo 1;
		else echo 0;
	}
}
?>