<?
require 'db.php';

$info = array();
$sql = "select * from classes c 
		join schools s using (school_id) 
		where c.class_era = 0";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$info[$row['school_id']][] = $row;
}
?>
<html>
	<head>
		<style>
			th, td {
				padding: 3px;
				font-size: 12px;
				text-align: left;
			}
		</style>
	</head>
	<body>
		<table>
			<tr>
				<th>School</th>
				<th>Grade</th>
				<th>Teacher</th>
				<th>Email</th>
			</tr>
			<? 
			foreach ($info as $school => $other) {
				foreach ($other as $row) {
					echo "<tr><td>" . $row['school_name'] . "</td><td>" . $row['class_grade'] . 
						(empty($row['class_sub']) ? '' : '-' . $row['class_sub']) . "</td><td>" . 
						$row['class_teacher'] . "</td><td>" . $row['email'] . "</td></tr>";
				}
			}
			?>
		</table>
		<br />
		<br />
		<?
		$emails = array();
		foreach ($info as $school => $other) {
			foreach ($other as $row) {
				if (filter_var($row['email'], FILTER_VALIDATE_EMAIL)) {
					if (!in_array($row['email'], $emails)) {
						$emails[] = $row['email'];
					}
				}
			}
		}
		foreach ($emails as $email) {
			echo $email . ",<br />";
		}
		?>
	</body>
</html>