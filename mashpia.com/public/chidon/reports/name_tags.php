<?php
require '../../db.php';
require 'vars.php';

$info = array();
$sql = "select * from th_chidon tc  
		join schools s using (school_id)
		join users u using (user_id) 
		where tc.year = " . $year . "  
		and tc.paid > 0";
if (isset($gender)) {
	$sql .= " and u.gender = '" . $gender . "'";
}
$sql .= " order by school_name, class_grade, class_sub, last, first";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$info[$row['gender']][] = $row;
}
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
			caption {
				font-size: 16px;
				font-weight: bold;
				border-bottom: 1px solid grey;
			}
		</style>
	</head>
	
	<body>	
		<table>
			<caption>Name Tags</caption>
			<tr>
				<th>ID</th>
				<th>Gender</th>
				<th>School</th>
				<th>Grade</th>
				<th>First Name</th>
				<th>Last Name</th>
				<th>Team</th>
			</tr>
			<?
			foreach ($info as $gender => $other) {
				foreach ($other as $row) {
					$grade = $row['class_grade'] . (empty($row['class_sub']) ? '' : '-' . $row['class_sub']);
					echo "<tr><td>" . $row['th_chidon_id'] . "</td><td>" . $gender . "</td><td>" . 
						$row['school_name'] . "</td><td>" . $grade . "</td><td>" . $row['first'] . 
						"</td><td>" . $row['last'] . "</td><td>" . $row['team'] . "</td></tr>";
				}
			}
			?>
		</table>
	</body>
</html>