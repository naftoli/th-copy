<?
require '../../db.php';
require 'vars.php';

$total['girls'] = array();
$total['boys'] = array();
$totals['boys'] = array();
$totals['girls'] = array();

$runnerUps['girls'] = array();
$runnerUps['boys'] = array();

$info = array();
$missing = array();
$sql = "select * from th_chidon tc  
		join schools s using (school_id)
		join users u using (user_id) 
		where tc.year = " . $year . "  
		and tc.paid > 0 
		order by school_name, grade, last, first";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	if ($row['contestant']) {
		$avg = number_format((intval($row['test1a']) + intval($row['test2a']) + intval($row['test3a']) + intval($row['test1b']) + intval($row['test2b']) + intval($row['test3b'])) / 6, 2);
	} else {
		$avg = number_format((intval($row['test1a']) + intval($row['test2a']) + intval($row['test3a'])) / 3, 2);
	}
	
	$grade = $row['grade'];
	$school = $row['school_name'];
	
	$childName = $row['first'] . ' ' . $row['last'];
	if ($row['gender'] == 'boys') {
		if (intval($row['contestant'])) {
			$info['boys']['winner'][$row['school_name']][$row['grade']][$row['th_chidon_id']][$childName] = $avg;
			
			if (isset($total['boys'][$school])) $total['boys'][$school]++;
			else $total['boys'][$school] = 1;
			if (isset($totals['boys'][$school][$grade])) $totals['boys'][$school][$grade]++;
			else $totals['boys'][$school][$grade] = 1;
		} else if (intval($row['shabbaton'])) {
			$info['boys']['runnerUp'][$row['school_name']][$row['grade']][$row['th_chidon_id']][$childName] = $avg;
			if (isset($runnerUps['boys'][$school][$grade])) {
				$runnerUps['boys'][$school][$grade]++;
			} else {
				$runnerUps['boys'][$school][$grade] = 1;
			}
		} else {
			$missing['boys']['contestant'][$row['school_name']][$row['grade']][$row['th_chidon_id']][$childName] = $avg;
		}
	} else {
		if (intval($row['contestant'])) {
			$info['girls']['winner'][$row['school_name']][$row['grade']][$row['th_chidon_id']][$childName] = $avg;

			if (isset($total['girls'][$school])) $total['girls'][$school]++;
			else $total['girls'][$school] = 1;
			if (isset($totals['girls'][$school][$grade])) $totals['girls'][$school][$grade]++;
			else $totals['girls'][$school][$grade] = 1;
			
		} else if (intval($row['shabbaton'])) {
			$info['girls']['runnerUp'][$row['school_name']][$row['grade']][$row['th_chidon_id']][$childName] = $avg;
			if (isset($runnerUps['girls'][$school][$grade])) {
				$runnerUps['girls'][$school][$grade]++;
			} else {
				$runnerUps['girls'][$school][$grade] = 1;
			}		
		} else {
			$missing['girls']['contestant'][$row['school_name']][$row['grade']][$row['th_chidon_id']][$childName] = $avg;
		}
	}
}
//echo "<pre>"; print_r($info); echo "</pre>"; exit;
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
		</style>
	</head>
	
	<body>
		<table>
			<caption>Total Winners By Gender / School</caption>
			<tr>
				<th>Gender</th>
				<th>School</th>
				<th>Total</th>
			</tr>
			<?
			foreach ($total as $gender => $other) {
				foreach ($other as $school => $total) {
					echo "<tr><td>" . $gender . "</td><td>" . $school . "</td><td>" . $total . "</td></tr>";
				}
			}
			?>
		</table>
		<hr />
		<table>
			<caption>Total Winners By Gender / School / Grade</caption>
			<tr>
				<th>Gender</th>
				<th>School</th>
				<th>Grade</th>
				<th>Total</th>
			</tr>
			<?
			foreach ($totals as $gender => $other) {
				foreach ($other as $school => $more) {
					foreach ($more as $grade => $total)
					echo "<tr><td>" . $gender . "</td><td>" . $school . "</td><td>" . 
						"</td><td>" . $grade . "</td><td>" . $total . "</td></tr>";
				}
			}
			?>
		</table>
		<hr />
		<table>
			<caption>Total Runner Ups By Gender / School / Grade</caption>
			<tr>
				<th>Gender</th>
				<th>School</th>
				<th>Grade</th>
				<th>Total</th>
			</tr>
			<?
			foreach ($runnerUps as $gender => $other) {
				foreach ($other as $school => $more) {
					foreach ($more as $grade => $total)
					echo "<tr><td>" . $gender . "</td><td>" . $school . "</td><td>" . 
						"</td><td>" . $grade . "</td><td>" . $total . "</td></tr>";
				}
			}
			?>
		</table>
		<hr />
		<table>
			<caption>Girls Winners</caption>
			<tr>
				<th>School</th>
				<th>Grade</th>
				<th>Name</th>
				<th>Avg</th>
				<th>ID</th>
			</tr>
			<?
			foreach ($info['girls']['winner'] as $school => $other) {
				foreach ($other as $grade => $more) {
					foreach ($more as $id => $names) {
						foreach ($names as $name => $avg) {
							echo "<tr><td>" . $school . "</td><td>" . $grade . "</td><td>" . 
								$name . "</td><td>" . $avg . "</td><td>" . $id . "</td></tr>";
						}
					}
				}
			}
			?>
		</table>
		<hr />
		<table>
			<caption>Girls Runner Ups</caption>
			<tr>
				<th>School</th>
				<th>Grade</th>
				<th>Name</th>
				<th>Avg</th>
				<th>ID</th>
			</tr>
			<?
			foreach ($info['girls']['runnerUp'] as $school => $other) {
				foreach ($other as $grade => $more) {
					foreach ($more as $id => $names) {
						foreach ($names as $name => $avg) {
							echo "<tr><td>" . $school . "</td><td>" . $grade . "</td><td>" . 
								$name . "</td><td>" . $avg . "</td><td>" . $id . "</td></tr>";
						}
					}
				}
			}
			?>
		</table>
		<hr />
		<table>
			<caption>Boys Winners</caption>
			<tr>
				<th>School</th>
				<th>Grade</th>
				<th>Name</th>
				<th>Avg</th>
				<th>ID</th>
			</tr>
			<?
			foreach ($info['boys']['winner'] as $school => $other) {
				foreach ($other as $grade => $more) {
					foreach ($more as $id => $names) {
						foreach ($names as $name => $avg) {
							echo "<tr><td>" . $school . "</td><td>" . $grade . "</td><td>" . 
								$name . "</td><td>" . $avg . "</td><td>" . $id . "</td></tr>";
						}
					}
				}
			}
			?>
		</table>
		<hr />
		<table>
			<caption>Boys Runner Ups</caption>
			<tr>
				<th>School</th>
				<th>Grade</th>
				<th>Name</th>
				<th>Avg</th>
				<th>ID</th>
			</tr>
			<?
			foreach ($info['boys']['runnerUp'] as $school => $other) {
				foreach ($other as $grade => $more) {
					foreach ($more as $id => $names) {
						foreach ($names as $name => $avg) {
							echo "<tr><td>" . $school . "</td><td>" . $grade . "</td><td>" . 
								$name . "</td><td>" . $avg . "</td><td>" . $id . "</td></tr>";
						}
					}
				}
			}
			?>
		</table>
	</body>
</html>