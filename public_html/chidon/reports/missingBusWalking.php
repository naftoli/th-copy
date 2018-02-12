<?
echo 'needs updating';
exit;

require '../../db.php';
require 'vars.php';

$info = array();
$sql = "select * from th_chidon tc 
		join schools s using (school_id)
		join users u using (user_id) 
		where tc.year = " . $year . "  
		and tc.paid > 0 
		and (tc.bus_number is null or tc.walking_group is null)";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$info[$row['gender']][$row['school_name']][] = $row;
}
ksort($info);
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
				font-size: 20px;
				font-weight: bold;
			}
		</style>
	</head>
	
	<body>	
		<table>
			<caption>Missing Bus / Group Number</caption>
			<tr>
				<th>ID</th>
				<th>Type</th>
				<th>School Name</th>
				<th>Grade</th>
				<th>First Name</th>
				<th>Last Name</th>
				<th>Host Address</th>
				<th>Bus</th>
				<th>Walking Group</th>
			</tr>
			<?
			foreach ($info as $gender => $more) {
				foreach ($more as $other) {
					foreach ($other as $row) {
						$grade = $row['class_grade'] . (empty($row['class_sub']) ? '' : '-' . $row['class_sub']);
						echo "<tr><td>" . $row['th_chidon_id'] . "</td><td>" . $gender . "</td><td>" . 
							$row['school_name'] . "</td><td>" . $grade . "</td><td>" . 
							$row['first'] . "</td><td>" . $row['last'] . "</td><td>" . 
							$row['host_address1'] . ' ' . $row['host_address2'] . "</td><td>" . $row['bus_number'] . "</td><td>" . 
							$row['walking_group'] . "</td></tr>";
					}
				}
			}
			?>
		</table>
	</body>
</html>