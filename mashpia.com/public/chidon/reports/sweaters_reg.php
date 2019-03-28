<?php
require '../../db.php';
require 'vars.php';

$info = array();
$sql = "select * from th_chidon tc 
		join users u using (user_id)
		join schools s on s.school_id = tc.school_id 
		where tc.year = " . $year . "
        and tc.school_id in (39,185,42,66,112,471) 
		order by s.school_name, tc.size";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	//$school = $row['school_name'];
	$size = strtolower($row['size']);
	if (isset($info[$row['gender']][$size]))
		$info[$row['gender']][$size]++;
	else 
		$info[$row['gender']][$size] = 1;	
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
		<?php foreach ($info as $gender => $other) { ?>
			<table>
				<caption>Totals for <?=$gender?></caption>
				<tr>
					<th>Size</th>
					<th>Total</th>
				</tr>
				<?
				$gtotal = 0;
				foreach ($other as $size => $total) {
					$gtotal += $total;
					echo "<tr><td>" . $type . "</td><td>" . $size . "</td><td>" . $total . "</td></tr>";
				}
				echo "<tr><th colspan='2'>Grand Total:</th><th>" . $gtotal . "</th></tr>";
				?>
			</table>
			<br />
			<?php } ?>
		<hr /><br />
		<?
		$info = array();
		$sql = "select * from th_chidon tc  
				join users u using (user_id)
				join schools s on s.school_id = u.school_id 
				join classes c on c.class_id = u.class_id 
				where tc.year = $year 
				order by school_name, class_grade, class_sub, size, last, first";
		$result = mysql_query($sql);
		while ($row = mysql_fetch_assoc($result)) {
			$grade = $row['class_grade'] . (empty($row['class_sub']) ? '' : '-' . $row['class_sub']);
			$info[$row['school_name']][$grade][strtolower($row['size'])][] = $row['first'] . ' ' . $row['last'];
		}
		?>
		<table>
			<caption>Details</caption>
			<tr>
				<th>School</th>
				<th>Grade</th>
				<th>Size</th>
				<th>Name</th>
			</tr>
			<?
			$totals = array();
			foreach ($info as $school => $rest) {
				foreach ($rest as $grade => $more) {
					foreach ($more as $size => $names) {
						foreach ($names as $name) {
							echo "<tr><td>" . $school . "</td><td>" . $grade . 
								"</td><td>" . $size . "</td><td>" . $name . "</td></tr>";
							if (isset($totals[$school][$size])) $totals[$school][$size]++;
							else $totals[$school][$size] = 1;
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