<?
require 'db.php';

$gender = 'boys';
if (isset($_GET['girls'])) {
	$gender = 'girls';
}

$info = array();
$sql = "select * from chidon_reg cr 
		join chidon_schools cs using (chidon_schools_id) 
		where cs.year = 5776 
		and cr.paid > 0 
		and gender = '" . $gender . "' 
		order by school_name, size";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	//$school = $row['school_name'];
	$type = $row['gender'];
	$size = strtolower($row['size']);
	if (isset($info[$type][$size]))
		$info[$type][$size]++;
	else 
		$info[$type][$size] = 1;	
}

$sql = "select * from chidon_schools where year = 5776 and gender = '" . $gender . "'";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	if ($row['sweater']) {
		$size = 'adult ' . $row['s_size'];
		$info[$row['gender']][$size]++;
	}
}

foreach ($info as $type => $rest) {
	ksort($info[$type]);
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
				font-family: Arial, Helvetica, sans-serif;
				font-size: 12px;
			}
		</style>
	</head>
	
	<body>
		<table>
			<caption>Totals</caption>
			<tr>
				<th>Type</th>
				<th>Size</th>
				<th>Total</th>
			</tr>
			<?
			$gtotal = 0;
			foreach ($info as $type => $rest) {
				foreach ($rest as $size => $total) {
					$gtotal += $total;
					echo "<tr><td>" . $type . "</td><td>" . $size . "</td><td>" . $total . "</td></tr>";
				}
			}
			echo "<tr><th colspan='2'>Grand Total:</th><th>" . $gtotal . "</th></tr>";
			?>
		</table>
		<br /><hr /><br />
		<?
		$info = array();
		$sql = "select * from chidon_reg cr 
				join chidon_schools cs using (chidon_schools_id) 
				where cs.year = 5776 
				and cr.paid > 0 
				and gender = '" . $gender . "' 
				order by school_name, grade, size, last_name, name";
		$result = mysql_query($sql);
		while ($row = mysql_fetch_assoc($result)) {
			$info[$row['gender']][$row['school_name']][$row['grade']][strtolower($row['size'])][] = $row['name'] . ' ' . $row['last_name'];
		}
		
		$sql = "select * from chidon_schools where year = 5776 and gender = '" . $gender . "'";
		$result = mysql_query($sql);
		while ($row = mysql_fetch_assoc($result)) {
			if ($row['sweater']) {
				$size = 'adult ' . $row['s_size'];
				$info[$row['gender']][$row['school_name']]['chaperone'][$size][] = $row['chaperone_name'] . ' (' . $row['school_name'] . ')';
			}
		}
		/*
		ksort($info);
		foreach ($info as $gender => $other) {
			ksort($info[$gender]);
		}
		foreach ($info as $gender => $other) {
			foreach ($other as $grade => $more) {
				ksort($info[$gender][$grade]);
			}
		}
		 * 
		 */
		?>
		<table>
			<caption>Details</caption>
			<tr>
				<th>Type</th>
				<th>School</th>
				<th>Grade</th>
				<th>Size</th>
				<th>Name</th>
			</tr>
			<?
			$totals = array();
			foreach ($info as $gender => $other) {
				foreach ($other as $school => $rest) {
					foreach ($rest as $grade => $more) {
						foreach ($more as $size => $names) {
							foreach ($names as $name) {
								echo "<tr><td>" . $gender . "</td><td>" . $school . "</td><td>" . $grade . 
									"</td><td>" . $size . "</td><td>" . $name . "</td></tr>";
								if (isset($totals[$school][$size])) $totals[$school][$size]++;
								else $totals[$school][$size] = 1;
							}
						}
					}
				}
			}
			?>
		</table>
		<br />
		<hr />
		<br />
		<table>
			<caption>Totals</caption>
			<tr>
				<th>School</th>
				<th>Size</th>
				<th>Total</th>
			</tr>
			<?
			foreach ($totals as $school => $other) {
				foreach ($other as $size => $total) {
					echo "<tr><td>" . $school . "</td><td>" . $size . "</td><td>" . $total . "</td></tr>";
				}
			}
			?>
		</table>
	</body>
</html>