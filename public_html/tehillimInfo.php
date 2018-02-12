<?
$admin_auth = array('school');
require_once 'header.php';

$sm = calculateSM( 5775 );

$months = array(
    1   =>  'Cheshvon', 
    2   =>  'Kisleiv', 
    3   =>  'Teves', 
    4   =>  'Shevat', 
    5   =>  'Adar', 
    6   =>  'Adar II', 
    7   =>  'Nissan',  
    8   =>  'Iyar', 
    9   =>  'Sivan', 
    10  =>  'Tamuz', 
    11  =>  'Av', 
    12  =>  'Elul', 
    13  =>  'Tishrei'
);

$info = array();
foreach ($sm as $date) {
	$sql = "select * from date_tasks_missions dtm 
			join date_tasks dt using (date_tasks_mission_id) 
			where subject_id = 1 
			and ord in (1,2) 
			and start_date = " . $date . " 
			and end_date = " . $date;
	echo $sql . "<br />";
	$result = mysql_query( $sql );
	while ($row = mysql_fetch_assoc($result)) {
		$info[$date][] = $row;
	}
}

$sorted = array();
foreach ($info as $date => $arr) {
	foreach ($arr as $row) {
		$year = $row['level'];
		$ladder = $row['track_id'];
		$speed = $row['speed'];
		$ord = $row['ord'];
		$desc = $row['description'];
		$missionID = $row['date_tasks_mission_id'];
		$sorted[$year][$ladder][$date][$speed][$ord] = $desc . ':' . $missionID;
	}
}

//echo "<pre>"; print_r( $sorted ); echo "</pre>";
?>
<html>
	<head>
		<meta charset="UTF-8" />
	</head>
	
	<body>
		<table>
			<tr>
				<th>Year</th>
				<th>Ladder</th>
				<th>Date</th>
				<th>Kapitelach</th>
				<th>Minutes</th>
				<th>Speed</th>
				<th>Mission ID</th>
			</tr>
			<?
			foreach ($sorted as $year => $arr) {
				foreach ($arr as $ladder => $rest) {
					foreach ($rest as $date => $arr) {
						foreach ($arr as $speed => $desc) {
							$key = array_search($date, $sm);
							$arrMinutes = explode(':', $desc[1]);
							$arrKapitalech = explode(':', $desc[2]);
							echo "<tr><td>" . $year . "</td><td>" . $ladder . "</td><td>" . $months[$key] . "</td><td>" . 
								$arrMinutes[0] . "</td><td>" . $arrKapitalech[0] . "</td><td>" . $speed . "</td><td>" . 
								$arrMinutes[1] . "</td></tr>";
						}
					}
				}
			}
			?>
		</table>
	</body>
</html>