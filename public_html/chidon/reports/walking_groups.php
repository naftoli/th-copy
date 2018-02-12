<?
require '../../db.php';
require 'vars.php';

$info = array();
$sql = "select * from th_chidon tc
		join schools s using (school_id)
		join users u using (user_id)
		join th_chidon_chaps tcc on tcc.school_id = tc.school_id 
		where tc.year = " . $year . "  
		and tc.paid > 0 
		order by school_name, last, first";
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
			<caption>Walking Groups</caption>
			<tr>     
				<th>ID</th>
				<th>Gender</th>
				<th>School</th>
				<th>Chaperone Name</th>
				<th>Chaperone Cell</th>
				<th>Walking Group #</th>
				<th>Walking Counselor Name</th>
				<th>Walking Counselor Cell</th>
				<th>First Name</th>
				<th>Last Name</th>
				<th>Host Family</th>
				<th>Host Cell</th>
				<th>Host Address Number</th>
				<th>Host Address</th>
				<th>Between Streets</th>
				<th>Permission to walk alone by DAY</th>
				<th>Permission to walk alone by NIGHT</th>
				<th>Thursday Bus Number</th>
			</tr>
			<?
			foreach ($info as $gender => $other) {
				foreach ($other as $row) {
					echo "<tr><td>" . $row['th_chidon_id'] . "</td><td>" . $gender . "</td><td>" . 
						$row['school_name'] . "</td><td>" . $row['name'] . "</td><td>" . $row['phone'] . 
						"</td><td>" . "</td><td>" . "</td><td>" . "</td><td>" . $row['first'] . "</td><td>" . 
						$row['last'] . "</td><td>" . $row['host'] . "</td><td>" . $row['host_number'] . "</td><td>" . 
						$row['host_address1'] . "</td><td>" . $row['host_address2'] . "</td><td>" . $row['between_streets'] . "</td><td>" . 
						($row['walk_day'] ? 'yes' : 'no') . "</td><td>" .
						($row['walk_night'] ? 'yes' : 'no') . "</td><td>" . "</td></tr>";
				}
			}
			?>
		</table>
	</body>
</html>