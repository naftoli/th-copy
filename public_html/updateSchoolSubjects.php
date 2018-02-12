<?
require 'db.php';
$subjects = array();
$sql = "select subject_id from subjects where subject_type not in ('school_points', 'Hakhel')";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$subjects[] = $row['subject_id'];
}

$school_id = 255;
foreach ($subjects as $subject) {
	$sql = "insert into school_subjects values($school_id, $subject)";
	mysql_query($sql);
}
?>