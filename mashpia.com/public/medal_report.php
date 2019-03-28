<? 
$admin_auth = array('school','user'); 
require('header.php'); 
function ranks($arr, $type) {
	foreach ($arr as $v) {
		echo "<div class='rank'>";
		echo "___";
		if (!empty($v["date_{$type}_received"])) 
			echo "(&#x2713;) ";
		else 
			echo "(&#x2717;) ";
		echo $v['rank_name'];
		echo "</div><br />";
	}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<link href="admin_styles.css" rel="stylesheet" type="text/css">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Medal and Rank Statement</title>
<style type='text/css'>
@media all {
	.page-break {
		display: none;
	}
	.print {
		display: none;
	}
	.no-print {
		display: block;
	}
}
@media print {
	.page-break {
		display: block;
		page-break-before: always;
	}
	.print {
		display: block;
	}
	.no-print {
		display: none;
	}	
}
.report {
	line-height: 120%;
	font-size: medium;
}
.subject, .medal, .rank {
	margin-left: 5%;
}
.ranks {
	width: 50%;
	float: right;
}
.next_child {
	clear: both;
}
</style>
</head>

<body>
<? include('admin_header.php');?>
<? if ($admin_auth[0] == 'school') : ?>
<? include_once('db.php'); ?>

<h1 class='no-print'>Medal and Rank Statement</h1>

<?
//if (isset($_POST['submit'])) { 
?>
<div class='report'>
<?
$users = array();
/*
$sql = "
	SELECT * 
	FROM users AS u
	JOIN classes AS c
	USING ( class_id ) 
	WHERE c.class_id = " . $_POST['class'] . " 
	ORDER BY c.class_grade, c.class_sub, u.last, u.first
";
*/
$school = "= $admin->school_id ";
if ($admin->admin_id == 20) {
	$school = "in (19, 42) ";
}
$sql = "
	SELECT * 
	FROM users AS u
	JOIN classes AS c
	USING ( class_id ) 
	WHERE u.school_id $school  
	ORDER BY u.school_id, c.class_grade, c.class_sub, u.last, u.first
";
//echo $sql;
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$users[] = $row;
}

if (count($users) == 0)
	echo "No students in this class.";

foreach ($users as $user) {
	echo "Name: " . $user['first'] . " " . $user['last'] . "<br />";
	$grade = $user['class_sub'] == '' ? $user['class_grade'] : $user['class_grade'] . "-" . $user['class_sub'];
	echo "Grade: " . $grade . "<br />";
	echo "Teacher: " . $user['class_teacher'] . "<br /><br />";
?>
	<div class='print'>
	Below please find a list of all the medals and Rank books/cards you have earned.<br />
	If it has an (&#x2713) next to it, it means you received it from your base commander.<br />
	If it has an (&#x2717) next to it, it means, we have not received record that you have received it yet.<br />
	If you have received any of the medals or rank books below please add a (&#x2713) and return this paper to<br />
	your base commander so that s/he can update the system.<br /><br />
	</div>
<?		
	//find users rank
	$info = array();
	$sql4 = "select * from rank_marks join ranks using(rank_ord) where user_id = " . $user['user_id'];
	$result4 = mysql_query($sql4);
	if (mysql_num_rows($result4) > 0) {
		while ($row4 = mysql_fetch_assoc($result4)) {
			$info[] = $row4;
		}
	}
	
	echo "<div class='ranks'>";
	echo "Rank Book<br /><br />";
	ranks($info, 'book');
	echo "Rank Card<br /><br />";
	ranks($info, 'card');
	echo "</div>";
	
	echo "Medals<br /><br />";
	echo "<div class='subject'>";
	//find all campaigns child is assigned to
	$sql2 = "
		SELECT *
		FROM user_tracks
		JOIN subjects
		USING ( subject_id )
		WHERE user_id = " . $user['user_id'] . " 
		AND enrolled =1
		AND subject_id !=91
		AND subject_id
		IN (
		SELECT subject_id
		FROM medal_marks
		WHERE user_id = " . $user['user_id'] . "
		)
	";
	$result2 = mysql_query($sql2);
	while ($row2 = mysql_fetch_assoc($result2)) {
		echo $row2['subject_name'] . "<br />";
		
		echo "<div class='medal'>";
		//get medals given for this subject
		$sql3 = "select * from medal_marks
				join medals using (medal_ord) 
				where user_id = " . $user['user_id'] . " and subject_id = " . $row2['subject_id'] . " order by medal_ord";
		//echo $sql3 . "<br />";
		$result3 = mysql_query($sql3);
		while ($row3 = mysql_fetch_assoc($result3)) {
			echo "___";
			if (!empty($row3['date_received'])) 
				echo "(&#x2713;) ";
			else 
				echo "(&#x2717;) ";
			echo $row3['medal_name'] . "<br />";
		}
		echo "</div>";
	}
	echo "</div><br />";
	echo "<div class='next_child'></div>";
	echo "<div class='page-break'></div>";
}
?>
</div>
<?
//} else {
if (1==2) {
?>
<p>Please choose a grade:</p>
<form action='medal_report.php' method='post'>
<select name='class'>
<?
$sql = "select * from classes where school_id = " . $admin->school_id . " and class_era = 0 order by class_grade";
$result = mysql_query($sql);
while ($row = mysql_fetch_array($result)) {
	$grade = $row['class_sub'] == '' ? $row['class_grade'] : $row['class_grade'] . "-" . $row['class_sub'];
	echo "<option value='" . $row['class_id'] . "'>" . $grade;
}
?>
</select><br />
<input type='submit' value='submit' name='submit'>
</form>
<? } ?>
<? else : ?>
no permission to view this page
<? endif; ?>
</body>
</html>