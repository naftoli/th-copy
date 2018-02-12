<?
require 'db.php';

$info = array();
$sql = "select * from chidon_schools where year = 5776 and chidon_schools_id not in (112,134)";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$info[$row['gender']][] = $row;
}
//echo "<pre>"; print_r($info); echo "</pre>"; exit;
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8" />
		<style>
			tr, th, td {
				padding: 5px;
				font-family: "sans-serif";
				font-size: 12px;
			}
		</style>
	</head>
	
	<body>
		<table>
			<tr>
				<th>Gender</th>
				<th>School</th>
				<th>Chaperone Name</th>
				<th>Chaperone Phone</th>
				<th>Paid for Full Program</th>
				<th>Paid for Sweater</th>
				<th>Sweater Size</th>
			</tr>
			<?
			foreach ($info as $gender => $other) {
				foreach ($other as $row) {
					echo "<tr><td>" . $gender . "</td><td>" . $row['school_name'] . "</td><td>" . 
					$row['chaperone_name'] . "</td><td>" . $row['chaperone_phone'] . "</td><td>" . 
					($row['full_program'] ? 'yes' : 'no') . "</td><td>" . 
					($row['sweater'] ? 'yes' : 'no') . "</td><td>" . 
					($row['s_size'] ? 'Adult ' . $row['s_size'] : '') . "</td></tr>";
				}
			}
			?>
		</table>
	</body>
</html>