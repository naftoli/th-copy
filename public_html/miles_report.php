<?
require('db.php');

function dateThisYear($month, $day, $starting = 0, $year_offset = 0) {
		if(!$starting) 
			$starting = unixtojd();
			
		$today = cal_from_jd($starting, CAL_JEWISH);
		
		return cal_to_jd(CAL_JEWISH, $month, $day, $today['year']+$year_offset-(cal_to_jd(CAL_JEWISH, $month, $day, $today['year']) >= $starting ? 1 : 0));
}
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>
<body>
<?
$totals = array();

$sql = "select u.user_id, u.first, u.last, s.school_name, c.class_grade, c.class_sub 
		from users as u
		join schools as s using (school_id) 
		join classes as c using (class_id) 
		where user_registered > 0 
		order by s.school_name, c.class_grade, c.class_sub, u.last, u.first";
$result = mysql_query($sql);

echo "<table><tr>";
echo "<th>School</th>";
echo "<th>Grade</th>";
echo "<th>Last Name</th>";
echo "<th>First Name</th>";
echo "<th>Army Miles</th>";
echo "<th>Base Miles</th>";
echo "<th>Total Miles</th></tr>";

while ($row = mysql_fetch_assoc($result)) {
	$id = $row['user_id'];
	
	$mission_miles = "SELECT SUM(mark_points) mark_points 
	FROM date_tasks_marks JOIN ord ON (mark_inactive = 0 AND ord.num = 1) 
	WHERE user_id = $id AND mark_date >= " . dateThisYear(13, 18);
	
	$school_miles = "SELECT SUM(award_points) mark_points 
	FROM points WHERE user_id = $id AND award_date >= " . dateThisYear(13, 18);	
	
	$mMiles = ceil(mysql_result(mq($mission_miles), 0));
	$bMiles = ceil(mysql_result(mq($school_miles), 0));
	$gMiles = ceil(mysql_result(mq(totalMarks("WHERE user_id = {$row['user_id']} AND mark_date >= " . dateThisYear(13, 18))), 0));
	
	echo "<tr><td>" . $row['school_name'] . "</td><td>" . $row['class_grade'] . $row['class_sub'] . "</td><td>" . $row['last'] . "</td><td>" 
	. $row['first'] . "</td><td align='right'>" . $mMiles . "</td><td align='right'>" . $bMiles . "</td><td align='right'>" . $gMiles . "</td></tr>";
	
	$totals[$row['school_name']]['total_army'] += $mMiles;
	$totals[$row['school_name']]['total_base'] += $bMiles;
	$totals[$row['school_name']]['grand_total'] += $gMiles;
}
echo "</table>";
echo "<h3>Totals</h3>";
echo "<table><tr>";
echo "<th>School Name</th>";
echo "<th>Army Miles</th>";
echo "<th>Base Miles</th>";
echo "<th>Total Miles</th></tr>";

foreach ($totals as $k => $v) {
	echo "<td align='left'>" . $k . "</td>";
	echo "<td align='right'>" . number_format($v['total_army'], ',') . "</td>";
	echo "<td align='right'>" . number_format($v['total_base'], ',') . "</td>";
	echo "<td align='right'>" . number_format($v['grand_total'], ',') . "</td></tr>";
}
echo "</table>";
?>
</body>
</html>