<?
require '../../db.php';

$info = array();
$sql = "select * from chidon_reg cr 
		join chidon_schools cs using (chidon_schools_id) 
		where cs.year = 5776 
		and cr.paid > 0 
		and cs.gender = 'girls' 
		order by school_name, last_name, name";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	// if (empty($row['bus_number']) || empty($row['walking_group'])) continue;
	$info[] = $row;
}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8" />
	</head>
	
	<body>
		<table>
			<tr>
				<th>Name</th>
				<th>Host Address</th>
				<th>Host Number</th>
				<th>Chaperone Number(s)</th>
				<th>Walking Group #</th>
				<th>Thursday Bus #</th>
			</tr>
			<? 
			foreach ($info as $row) { 
				$number = $row['chaperone_phone'];
				if (!empty($row['chaperone_phone2'])) {
					$number .= ', ' . $row['chaperone_phone2'];
				}
				if (!empty($row['chaperone_phone3'])) {
					$number .= ', ' . $row['chaperone_phone3'];
				}
				
				echo "<tr><td>" . $row['name'] . ' ' . $row['last_name'] . "</td><td>" . 
					$row['address'] . "</td><td>" . $row['phone'] . "</td><td>" . $number . "</td><td>" . 
					$row['walking_group'] . "</td><td>" . $row['bus_number'] . "</td></tr>";
			} 
			?>
		</table>	
	</body>
</html>	