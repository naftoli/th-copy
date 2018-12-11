<?
require '../../db.php';
require 'vars.php';

$info = array();
$sql = "select * from th_chidon tc  
		join schools s using (school_id) 
		join users u using (user_id) 
		where tc.year = " . $year . "  
		and tc.paid > 0";
if (isset($id)) $sql .= " and th_chidon_id = " . $id;
$sql .= " order by school_name, last, first";
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
			<caption>Chaperones</caption>
			<tr>
				<th>ID</th>
				<th>Gender</th>
				<th>School</th>
				<th>Chaperone Name</th>
				<th>Chaperone Cell</th>
				<th>English Name</th>
				<th>Avg Mark</th>
				<th>Host Family</th>
				<th>Host Number</th>
				<th>Host Address</th>
				<th>Between Streets</th>
				<th>Allowed to walk alone by DAY</th>
				<th>Allowed to walk alone by NIGHT</th>
				<th>Walking group #</th>
				<th>Walking counselor name</th>
				<th>Counselor Name</th>
				<th>Team</th>
				<th>Thursday Bus Number</th>
				<th>Allergies</th>
				<th>Notes</th>
			</tr>
			<?
			foreach ($info as $gender => $other) {
				foreach ($other as $row) {
					if ($row['contestant']) {
						$avg = number_format(($row['test1a'] + $row['test1b'] + $row['test2a'] + $row['test2b'] + $row['test3a'] + $row['test3b']) / 6, 2);
					} else {
						$avg = number_format(($row['test1a'] + $row['test2a'] + $row['test3a']) / 3, 2);
					}
					
					echo "<tr><td>" . $row['th_chidon_id'] . "</td><td>" . $gender . "</td><td>" . $row['school_name'] . 
						"</td><td>" . $row['chaperone_name'] . "</td><td>" . $row['chaperone_phone'] . "</td><td>" . 
						$row['first'] . ' ' . $row['last'] . "</td><td>" . $avg . "</td><td>" . $row['host'] . 
						"</td><td>" . $row['host_number'] . "</td><td>" . $row['host_address1'] . ' ' . $row['host_address2']. "</td><td>" .
						$row['between_streets'] . "</td><td>" . ($row['walk_day'] ? 'yes' : 'no') . "</td><td>" . ($row['walk_night'] ? 'yes' : 'no') . "</td><td>" .
						"</td><td>" . "</td><td>" . "</td><td>" . $row['team'] . "</td><td>" . "</td><td>" . $row['allergies'] . "</td><td>" .
						$row['notes'] . "</td></tr>";
				}
			}
			?>
		</table>
	</body>
</html>