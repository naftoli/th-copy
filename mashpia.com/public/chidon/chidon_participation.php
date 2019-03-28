<?
require '../db.php';

$avgs = array();
$schools = array();
$byGender = array();

$sql = "select school_name, chidon_schools_id, gender  
		from chidon_schools 
		join chidon_reg using (chidon_schools_id) 
		where year = 5776 
		order by gender";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$schools[$row['chidon_schools_id']] = $row['school_name'];
	$byGender[$row['gender']][] = $row['chidon_schools_id'];
	$avgs[$row['chidon_schools_id']][65] = array();
	$avgs[$row['chidon_schools_id']][70] = array();
	$avgs[$row['chidon_schools_id']][85] = array();
}

$info = array();
foreach ($schools as $id => $name) {
	$sql = "select grade, name, last_name, mark1, mark2, mark3, bonus, type from chidon_reg where chidon_schools_id = " . $id;
	$result = mysql_query($sql);
	while ($row = mysql_fetch_assoc($result)) {
		$info[$id][] = $row;
	}
}
//echo "<pre>"; print_r($info); echo "</pre>";
$grandTotals = array();
$onStage = array();
for ($i = 4; $i < 9; $i++) {
	$grandTotals[65]['b'][$i] = 0;
	$grandTotals[65]['g'][$i] = 0;
	$grandTotals[70]['b'][$i] = 0;
	$grandTotals[70]['g'][$i] = 0;
	$grandTotals[85]['b'][$i] = 0;
	$grandTotals[85]['g'][$i] = 0;
}

$byGrade = array();
$totals = array();
foreach ($info as $id => $other) {
	//find out gender of school
	if (array_search($id, $byGender['boys']) !== false) $gender = 'b';
	else if (array_search($id, $byGender['girls']) !== false) $gender = 'g';
	else {
		echo "we have a problem.";
		exit;
	}
	$stage = 0; 
	foreach ($other as $row) {
		if ($row['mark3'] > 0) {
			$avg = round(($row['mark1'] + $row['mark2'] + $row['bonus'] + $row['mark3']) / 3);
		} else {
			$avg = round(($row['mark1'] + $row['mark2'] + $row['bonus']) / 2);
		}
		$name = $row['name'] . ' ' . $row['last_name'];
		if ($avg >= 85) {
			$avgs[$id][85][] = array($name, $row['grade']);
			$grandTotals[85][$gender][$row['grade']]++;
			if ($stage < 2) $stage++;
		} else if ($avg >= 70) {
			$avgs[$id][70][] = array($name, $row['grade']);
			$grandTotals[70][$gender][$row['grade']]++;
		} else if ($avg >= 65) {
			$avgs[$id][65][] = array($name, $row['grade']);
			$grandTotals[65][$gender][$row['grade']]++;
		}
		$byGrade[$id][$row['grade']][] = array($name, $avg);
		if (isset($totals[$id])) $totals[$id]++;
		else $totals[$id] = 1;
	}
	if (isset($onStage[$gender][$row['grade']])) $onStage[$gender][$row['grade']] += $stage;
	else $onStage[$gender][$row['grade']] = $stage;
	ksort($byGrade[$id]);
}

//echo "<pre>"; print_r($byGrade); echo "</pre>";
?>
<!DOCTYPE html>
<html>
	<head>
		<title>Chidon Participants Averages</title>
		<style>
			body, table {
				font-family: 'Arial', 'Verdana';
				font-size: 12px;
			}
			.page-break {
				page-break-after: always;
			}
		</style>
	</head>
	
	<body>
		<? 
		foreach ($avgs as $school => $other) {
			echo "<h3>" . $schools[$school] . "</h3>";
			echo "Children Learning: " . $totals[$school] . "<br />";
			foreach ($other as $percent => $names) {
				echo "Over " . $percent . "% Avg: " . count($names) . "<br />";
				
				if ($percent == 85) {
					$grades = array();
					foreach ($names as $row) {
						$grades[$row[1]][] = $row[0];
					}
					ksort($grades);
					echo "<blockquote>";
					foreach ($grades as $grade => $names) {
						echo "Grade " . $grade . ": " . count($names) . "<br />";
					}
					echo "</blockquote>";
				}
				
			}
			
			echo "<table><tr><th>Grade</th><th>Name</th><th>Avg</th></tr>";
			foreach ($byGrade[$school] as $grade => $rest) {
				foreach ($rest as $row) {
					echo "<tr><td>". $grade . "</td><td>" . $row[0] . "</td><td>" . $row[1] . "</td></tr>";
				}
			}
			echo "</table>";
			echo "<hr /><div class='page-break'></div>";
		}
		
		echo "<h3>Grand Totals</h3>";
		echo "<table><tr><th>Mark</th>";
		for ($i = 4; $i < 9; $i++) {
			echo "<th colspan='2'>Grade " . $i . "</th>";
		}
		echo "<th>Total</th></tr>";
		echo "<tr><td>&nbsp;</td>";
		for ($i = 4; $i < 9; $i++) {
			echo "<th>B</th>";
			echo "<th>G</th>";
		}
		echo "<th>&nbsp;</th></tr>";
		
		foreach ($grandTotals as $mark => $rest) {
			$num = 0;
			echo "<tr><td>" . $mark . "+</td>";
			foreach ($rest as $other) {
				foreach ($other as $gender => $total) {
					$num += $total;
					echo "<td>" . $total . "</td>";
				}
			}
			echo "<td>" . $num . "</td></tr>";
		}
		
		$num = 0;
		echo "<tr><td>On Stage</td>";
		foreach ($onStage as $other) {
			foreach ($other as $gender => $total) {
				$num += $total;
				echo "<td>" . $total . "</td>";
			}
		}
		echo "<td>" . $num . "</td></tr></table>";
		//echo "<pre>"; print_r($grandTotals); echo "</pre>";
		?>
	</body>
</html>