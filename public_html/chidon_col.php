<?
require 'db.php';

$info = array();
$sql = "select * from chidon_reg cr 
		join chidon_schools cs using (chidon_schools_id) 
		where cs.year = 5776 
		order by school_name, last_name, name";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$mark1 = $row['mark1'];
	$mark2 = $row['mark2'];
	$mark3 = $row['mark3'];
	$bonus = $row['bonus'];
	$avg = round(($mark1 + $bonus + $mark2 + $mark3) / 3);
	if ($avg >= 70)
		$info[$row['school_name']][] = $row['name'] . ' ' . $row['last_name'];
}
//echo "<pre>"; print_r($info); echo "</pre>"; exit;
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8" />
		<style>
			body {
				font-family: Arial, Helvetica, sans-serif;
				font-size: 14px;
			}
		</style>
	</head>
	
	<body>
		<h3>Chidon Participants</h3>
		<? 
		foreach ($info as $school => $names) {
			echo "<b>" . $school . ":</b><br />";
			foreach ($names as $name) {
				echo $name . "<br />";
			}
			echo "<br /><br />";
		}
		?>
	</body>
</html>