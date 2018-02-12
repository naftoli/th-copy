<?
include '../db.php';
$grade = mysql_real_escape_string($_POST['grade']);
$school = mysql_real_escape_string($_POST['school']);
$type = mysql_real_escape_string($_POST['type']);
$pledge = mysql_real_escape_string($_POST['val']);

if ($school > 0) {
	$grades = array();
	if ($grade == -1) {
		$grades = array();
		$sql = "select * from classes where school_id = " . $school . " and class_era = 0 order by class_grade, class_sub";
		$result = mysql_query($sql);
		while ($row = mysql_fetch_assoc($result)) {
			$grades[] = $row['class_id'];
		}
	} else {
		$grades[] = $grade;
	}
	
	$sqls = array();
	foreach ($grades as $class_id) {
		$users = array();
		$sql = "select * from users where class_id = " . $class_id . " and user_registered > 0 order by last, first";
		$result = mysql_query($sql);
		if (mysql_num_rows($result) > 0) {
			while ($row = mysql_fetch_assoc($result))	{
				$users[] = $row['user_id'];
			}
		}
		
		foreach ($users as $user) {
			$sqls[] = "update lines_pledged 
					set lines_pledged = " . $pledge . " 
					where campaign_id = " . $type . "  
					and school_id = " . $school . "  
					and class_id = " . $class_id . " 
					and user_id = " . $user;					
		}
	}
	
	foreach ($sqls as $sql) {
		mysql_query($sql);
	}
	echo 1;
} else {
	echo 0;
}
?>