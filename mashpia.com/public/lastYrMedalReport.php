<?
require 'db.php';

$info = array();
$sql = "select mm.date_awarded, s.subject_name, m.medal_name, sc.school_name, c.class_grade, c.class_sub, u.last, u.first  
		from medal_marks mm 
		join users u using (user_id) 
		join subjects s using (subject_id) 
		join medals m using (medal_ord) 
		join classes c on (u.class_id = c.class_id) 
		join schools sc on (sc.school_id = u.school_id) 
		where date_awarded > 2456474 
		and date_awarded < 2456841
		and date_shipped is null 
		and date_received is null 
		order by subject_id, medal_ord, school_name, class_grade, class_sub, last, first";
//echo $sql;
$result = mysql_query($sql) or die(mysql_error());
while ($row = mysql_fetch_assoc($result)) {
	$info[$row['school_name']][] = $row;
}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8" />
		<title>Medal Report</title>
		<style>
			tr, th, td {
				padding: 5px;
				font-size: 11px;
				border: 1px solid black;
			}
		</style>
	</head>
	
	<body>
		<?
		$grandtotals = array();
		foreach ($info as $school => $arr) {
			echo "<h3>" . $school . "</h3><hr />";
			?>
			<table>
				<tr>
					<th>Subject</th>
					<th>Medal</th>
					<th>Date Earned</th>
					<th>Class</th>
					<th>First Name</th>
					<th>Last Name</th>
				</tr>
				<?
				$medals = array();
				foreach ($arr as $row) {
					$date = jdtogregorian($row['date_awarded']);
					echo "<tr><td>" . $row['subject_name'] . "</td><td>" . $row['medal_name'] . "</td><td>" . 
						$date . "</td><td>" . $row['class_grade'] . 
						(empty($row['class_grade']) ? '' : '-' . $row['class_sub']) . "</td><td>" . 
						$row['first'] . "</td><td>" . $row['last'] . "</td></tr>";
					if (isset($medals[$row['subject_name']][$row['medal_name']])) {
						$medals[$row['subject_name']][$row['medal_name']]++;
					} else {
						$medals[$row['subject_name']][$row['medal_name']] = 1;
					}
					
					if (isset($grandtotals[$row['subject_name']][$row['medal_name']])) {
						$grandtotals[$row['subject_name']][$row['medal_name']]++;
					} else {
						$grandtotals[$row['subject_name']][$row['medal_name']] = 1;
					}
				}
				?>
			</table>
			
			<h4>Totals</h4>
			<hr />
			<table>
				<tr>
					<th>Subject</th>
					<th>Medal</th>
					<th>Total</th>
				</tr>
				<?
				foreach ($medals as $subject => $arr) {
					foreach ($arr as $medal => $total) {
						echo "<tr><td>" . $subject . "</td><td>" . 
							$medal . "</td><td>" . $total . "</td></tr>";
					}
				}
				?>
			</table>
		<? } ?>
		<br />
		<h4>Grand Totals</h4>
		<hr />
		<table>
			<tr>
				<th>Subject</th>
				<th>Medal</th>
				<th>Total</th>
			</tr>
			<?
			foreach ($grandtotals as $subject => $info) {
				foreach ($info as $medal => $total) {
					echo "<tr><td>" . $subject . "</td><td>" . $medal . "</td><td>" . $total . "</td></tr>";
				}
			}
			?>
		</table>
	</body>
</html>