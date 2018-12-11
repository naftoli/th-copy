<?
require '../../db.php';
require 'vars.php';

$info = array();
$missing = array();
$sql = "select tc.*, u.first, u.last, u.gender, s.school_name, c.class_grade, c.class_sub
		from th_chidon tc
		join schools s using (school_id)
		join users u using (user_id)
		join classes c on c.class_id = u.class_id 
		where tc.year = " . $year . "  
		and tc.paid > 0";
if (isset($gender)) $sql .= " and u.gender = '" . $gender . "'";
$sql .= " order by school_name, class_grade, class_sub, last, first";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$grade = $row['class_grade'] . (empty($row['class_sub']) ? '' : '-' . $row['class_sub']);
	$type = $row['contestant'] ? 'representative' : 'contestant';
	$avg = ($row['test1a'] + $row['test1b'] + $row['test2a'] + $row['test2b'] + $row['test3a'] + $row['test3b']) / 6;
	$info[$row['gender']][$avg][][$type][$row['school_name']][$grade][$row['th_chidon_id']] = $row['first'] . ' ' . $row['last'];
}
foreach ($info as $gender => $other) {
	ksort($info[$gender]);
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
		<? foreach ($info as $gender => $other) : ?>
		<table>
			<caption><?=ucwords($gender)?></caption>
			<tr>
				<th>Type</th>
				<th>School</th>
				<th>Grade</th>
				<th>Name</th>
				<th>Avg</th>
				<th>ID</th>
				<th>Plaque</th>
				<th>Medal</th>
			</tr>
			<?
			$plaques = 0;
			$medals = 0;
			foreach ($other as $avg => $more) {
				foreach ($more as $arr) {
					foreach ($arr as $type => $more) {
						foreach ($more as $school => $other) {
							foreach ($other as $grade => $more) {
								foreach ($more as $id => $name) {
									echo "<tr><td>" . $type . "</td><td>" . $school . "</td><td>" . $grade . 
										"</td><td>" . $name . "</td><td>" . $avg . "</td><td>" . $id . "</td><td style='text-align: center'>";
									if ($avg >= 85) {
										$plaques++;
										echo "<span style='color: green'>&#x2713;</span>"; 
									}
									echo "</td><td style='text-align: center'>";
									if ($avg >= 95) {
										$medals++;
										echo "<span style='color: green'>&#x2713;</span>";
									}
									echo "</td></tr>";
								}
							}
						}
					}
				}
			}
			echo "<tr><th colspan='6' style='text-align: right'>Total:</th><th>" . $plaques . "</th><th>" . $medals . "</th></tr>";
			?>
		</table>
		<hr />
		<? endforeach; ?>
	</body>
</html>