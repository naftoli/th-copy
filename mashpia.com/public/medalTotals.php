<?
require 'db.php';

$years = array('5769', '5770', '5771', '5772', '5773', '5774', '5775', '5776', '5777', '5778', '5779'); 	
$dates = array(2454832, 2455075, 2455440, 2455805, 2456171, 2456536, 2456901, 2457266, 2457632, 2457997, 2458242);

$medals = array();
$sql = "select medal_ord, medal_name from medals order by medal_ord";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$medals[$row['medal_ord']] = $row['medal_name'];
}

$info = array();
$num = count($dates);
for ($i = 0; $i < $num; $i++) {
	$start = $dates[$i];
	if ($i < ($num-1))	$end = $dates[$i+1];
	else $end = unixtojd();
	foreach ($medals as $medal_ord => $medal_name) {
		$sql = "select subject_name, count(*) as total from medal_marks 
				join subjects using (subject_id) 
				where date_awarded >= " . $start . " 
				and date_awarded < " . $end . " 
				and medal_ord = " . $medal_ord . " 
				group by subject_id, medal_ord";
		//echo $sql . "<br />";
		$result = mysql_query($sql);
		while ($row = mysql_fetch_assoc($result)) {
			$info[$row['subject_name']][$medal_name][$years[$i]] = $row['total'];
		}
	}
}
?>
<!DOCTYPE html>
<html>
	<head>
		<title>Campaigns Medals Totals</title>
		<meta charset="UTF-8" />
		<style>
			caption {
				width: 200px;
			}
			th, td {
				font-size: 14px;
				padding: 5px;
			}
		</style>
	</head>
	
	<body>
		<table>
			<caption>Campaign Medal Totals</caption>
			<tr>
				<th>Campaign</th>
				<th>Medal</th>
				<? 
				foreach ($years as $year) {
					echo "<th>" . $year . "</th>";
				}
				?>
			</tr>
			<?
			$totals = array();
			foreach ($years as $year) {
				$totals[$year] = 0;
			}
			foreach ($info as $subject => $other) {
				foreach ($other as $medal => $more) {
					echo "<tr><td>" . $subject . "</td><td>" . $medal . "</td>";
					foreach ($years as $year) {
						if (isset($more[$year])) {
							$totals[$year] += $more[$year];
							echo "<td>" . $more[$year] . "</td>";
						} else {
							echo "<td></td>";
						}
					}
					echo "</tr>";
				} 
			}
			echo "<tr><td colspan='2'>Grand Total:</td>";
			foreach ($totals as $total) {
				echo "<td>" . $total . "</td>";
			}
			echo "</tr>";
			?>
		</table>
	</body>
</html>