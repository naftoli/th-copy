<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	</head>
	
<body>
<?
require_once 'db.php';

$winners = array();
$sql = "select u.first, u.last, u.user_id, c.class_grade, c.class_sub, s.school_name, p.prize_name 
		from auction_winners aw 
		join users u using (user_id) 
		join schools s using (school_id) 
		join classes c using (class_id) 
		join prizes_auction p on (p.prize_number = aw.prize_id) 
		where aw.auction_id = 57 
		order by school_name, class_grade, class_sub, last, first";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$school = $row['school_name'];
	$class = $row['class_grade'] . (empty($row['class_sub']) ? '' : '-' . $row['class_sub']);
	$user = $row['first'] . ' ' . $row['last'];
	$winners[$school][$class][$user] = $row['prize_name'];	
}

echo "<table>";
echo "<tr><th>School</th><th>Grade</th><th>Student</th><th>Prize</th></tr>";
foreach ($winners as $school => $info) {
	foreach ($info as $class => $arr) {
		foreach ($arr as $user => $prize) {
			echo "<tr><td>" . $school . "</td><td>" . $class . "</td><td>" . $user . "</td><td>" . $prize . "</td></tr>";
		}
	}
}
echo "</table>";
?>
</body>
</html>