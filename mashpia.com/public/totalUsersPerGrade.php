<?
require 'db.php';

$users = array();
$grades = array();
$sql = "select * from users u 
		join classes c on c.class_id = u.class_id 
		where user_registered > 0";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$users[$row['class_grade']][] = $row['user_id'];
}

$totals = array();
foreach ($users as $grade => $info) {
	$totals[$grade] = count($info);
}
ksort($totals);
?>
<!DOCTYPE html>
<html>
	<head>
		<style>
			th, td {
				font-size: 14px;
				padding: 3px;
			}
		</style>
	</head>
	<body>
		<table>
			<tr>
				<th>Grade</th>
				<th>Total</th>
			</tr>
			<?
			$sum = 0;
			foreach ($totals as $grade => $total) {
				echo "<tr><td>" . $grade . "</td><td>" . $total . "</td></tr>";
				$sum += $total;
			}
			echo "<tr><td>&nbsp;</td><td>" . $sum . "</td></tr>";
			?>
		</table>
	</body>
</html>