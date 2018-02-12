<?
echo 'needs updating';
exit;

require '../../db.php';

$flightInfo = array(
	'aa'	=> 'AA 1064', 
	'delta1'=> 'Delta 1644', 
	'delta2'=> 'Delta 2147', 
	'jb'	=> 'JetBlue 1201', 
	'd1'	=> 'Delta 1852', 
	'd2'	=> 'Delta 2057'
);

$info = array();
$missing = array();
$sql = "select * from chidon_reg 
		where chidon_schools_id = 115 
		and paid > 0 
		order by last_name, name";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$flight = $row['arr_number'];
	if (strpos($flight, 'AA') !== false) {
		$info['arr']['aa'][] = $row;
	} else if (strpos($flight, '1644') !== false) {
		$info['arr']['delta1'][] = $row;
	} else if (strpos($flight, '2147') !== false) {
		$info['arr']['delta2'][] = $row;
	} 
	
	
	$flightD = $row['dep_number'];
	if (strpos($flightD, '1201') !== false) {
		$info['dep']['jb'][] = $row;
	} else if (strpos($flightD, '1852') !== false) {
		$info['dep']['d1'][] = $row;
	} else if (strpos($flightD, '2057') !== false) {
		$info['dep']['d2'][] = $row;
	} 
}
ksort($info['arr']);
ksort($info['dep']);
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
		<h1>LEC Florida Girls</h1>
		<table>
			<caption>Arrivals</caption>
			<tr>
				<th>First Name</th>
				<th>Last Name</th>
				<th>Airport</th>
				<th>Flight Number</th>
				<th>Time</th>
			</tr>
			<?
			foreach ($info['arr'] as $flight => $other) {
				foreach ($other as $row) {
					echo "<tr><td>" . $row['name'] . "</td><td>" . $row['last_name'] . "</td><td>" . 
						strtoupper($row['arr_airport']) . "</td><td>" . $flightInfo[$flight] . "</td><td>" . 
						$row['arr_time'] . "</td></tr>";
 				}
			}
			?>
		</table>
		
		<br /><br />
		
		<table>
			<caption>Departures</caption>
			<tr>
				<th>First Name</th>
				<th>Last Name</th>
				<th>Airport</th>
				<th>Flight Number</th>
				<th>Time</th>
			</tr>
			<?
			foreach ($info['dep'] as $flight => $other) {
				foreach ($other as $row) {
					echo "<tr><td>" . $row['name'] . "</td><td>" . $row['last_name'] . "</td><td>" . 
						strtoupper($row['dep_airport']) . "</td><td>" . $flightInfo[$flight] . "</td><td>" . 
						$row['dep_time'] . "</td></tr>";
 				}
			}
			?>
		</table>
	</body>
</html>