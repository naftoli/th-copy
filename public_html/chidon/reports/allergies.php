<?
require '../../db.php';
require 'vars.php';

$info = array();
$sql = "select * from th_chidon tc
		join schools s using (school_id)
		join users u using (user_id)
		join classes c on c.class_id = u.class_id 
		where tc.year = " . $year . "  
		and tc.shabbaton = 1 
		order by school_name, class_grade, class_sub, last, first";
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
				<th>Allergies</th>
				<th>Notes</th>
			</tr>
			<?
			foreach ($info as $gender => $other) {
				foreach ($other as $row) {
					$grade = $row['class_grade'] . (empty($row['class_sub']) ? '' : '-' . $row['class_sub']);
					echo "<tr><td>" . $row['chidon_reg_id'] . "</td><td>" . $gender . "</td><td>" . 
						$row['school_name'] . "</td><td>" . $grade . "</td><td>" . $row['first'] . 
						"</td><td>" . $row['last'] . "</td><td>" . $row['allergies'] . "</td><td>" . $row['notes'] . "</td></tr>";
				}
			}
			?>
		</table>
	</body>
</html>