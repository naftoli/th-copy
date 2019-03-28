<?php
$admin_auth = array(); 	
require('header.php');
include('admin_header.php');
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<link href="admin_styles.css" rel="stylesheet" type="text/css">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Medal Shipping Report</title>
<style type='text/css'>
.indent {
	margin-left: 5%;
}
.indent2 {
	margin-left: 10%;
}
.indent3 {
	margin-left: 15%;
}
</style>
<body>
<h1>Medal Shipping Report</h1>
<? 
if ($admin->auth == 'super') { 

	//get all users by school, grade
	$sql = "
		select u.user_id, u.last, u.first, c.class_grade, c.class_sub, s.school_name, mm.*, su.subject_name, m.medal_name from users as u 
		join schools as s using (school_id) 
		join classes as c using (class_id) 
		join medal_marks as mm using (user_id) 
		join medals as m using (medal_ord) 
		join subjects as su using (subject_id) 
		where u.user_registered > 0 
		and mm.date_awarded is not null 
		and mm.date_received is null 
		order by s.school_name, su.subject_name, m.medal_ord, c.class_grade, c.class_sub, u.last, u.first
	";
	$result = mysql_query($sql);
	
	$schools = array();
	while ($row = mysql_fetch_assoc($result)) {
		$schools["$row[school_name]"]["$row[subject_name]"]["$row[medal_name]"] = $row['last'] . ", " . $row['first'];
	}
	
	foreach ($schools as $key => $school) {	
		echo $key . "<br />";
		foreach ($school as $a => $subject) { 
			echo "<div class='indent'>$a</div>";
			foreach ($subject as $b => $medal) {
				echo "<div class='indent2'>$b</div>";
				echo "<div class='indent3'>$medal</div>";
			}				
			//$grade = $v['class_sub'] == '' ? $v['class_grade'] : $v['class_grade'] . "-" . $v['class_sub'];
			//echo $grade . " " . $v['last'] . ", " . $v['first'] . " " . $v['subject_name'] . "-" . $v['medal_name'] . "<br />";
		}
		echo "<br />";
	}
} else { 
	echo "<p>you have no permission to view this page.</p>";
} 
?>
</body>

</html>