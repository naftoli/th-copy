<?
require '../../db.php';
require 'vars.php';

$info = array();
$registered = array();
$winners = array();
$byGrade = array();

$sql = "select * from th_chidon tc
		join users u using (user_id) 
		join schools s on s.school_id = tc.school_id 
		join classes c on c.class_id = u.class_id 
		where tc.year = " . $year . "  
		and tc.paid > 0 
		order by school_name, class_grade, class_sub, last, first";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$info[] = $row;
	$type = $row['contestant'] ? 'school rep' : 'contestant';
	
	if (isset($registered[$row['gender']][$row['class_grade']])) {
		$registered[$row['gender']][$row['class_grade']]++;
	} else {
		$registered[$row['gender']][$row['class_grade']] = 1;
	}
	
	if (isset($winners[$type][$row['gender']])) {
		$winners[$type][$row['gender']]++;
	} else {
		$winners[$type][$row['gender']] = 1;
	}
	
	if (isset($byGrade[$type][$row['gender']][$row['class_grade']])) {
		$byGrade[$type][$row['gender']][$row['class_grade']]++;
	} else {
		$byGrade[$type][$row['gender']][$row['class_grade']] = 1;
	}
}
foreach ($registered as $gender => $other) {
	ksort($registered[$gender]);
}
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
				font-size: 20px;
				font-weight: bold;
			}
		</style>
	</head>
	
	<body>	
		<table>
			<caption>School Rep. Totals</caption>
			<tr>
				<th>Boys</th>
				<th>Girls</th>
			</tr>
			<tr>
				<td><?=$winners['school rep']['M']?></td>
				<td><?=$winners['school rep']['F']?></td>
			</tr>
		</table>
		
		<br />
		<hr />
		<br />
		
		<table>
			<caption>Contestant Totals</caption>
			<tr>
				<th>Boys</th>
				<th>Girls</th>
			</tr>
			<tr>
				<td><?=$winners['contestant']['M']?></td>
				<td><?=$winners['contestant']['F']?></td>
			</tr>
		</table>
		
		<br />
		<hr />
		<br />
		
		<table>
			<caption>Registered Totals</caption>
			<tr>
				<th>Gender</th>
				<th>Grade</th>
				<th>Total</th>
			</tr>
			<?
			$gtotal = 0;
			foreach ($registered as $gender => $other) {
				foreach ($other as $grade => $total) {
					echo "<tr><td>" . $gender . "</td><td>" . $grade . "</td><td>" . $total . "</td></tr>";
					$gtotal += $total;
				}
			}
			echo "<tr><th colspan='2'>Grand Total:</th><th>" . $gtotal . "</th></tr>";
			?>
		</table>
		
		<br />
		<hr />
		<br />
		
		<table>
			<caption>Registration Info</caption>
			<tr>
				<th>User ID</th>
				<th>Gender</th>
				<th>School Name</th>
				<th>Grade</th>
				<th>English Name</th>
				<th>Hebrew Name</th>
				<th>Type</th>
				<th>Avg Mark</th>
			</tr>
			<?
			foreach ($info as $row) {
				if ($row['contestant']) {
					$type = 'school rep.';
					$avg = round((intval($row['test1a']) + intval($row['test1b']) + intval($row['test2a']) + intval($row['test2b']) + intval($row['test3a']) + intval($row['test3b'])) / 6, 2);
				} else {
					$type = 'contestant';
					$avg = round((intval($row['test1a']) + intval($row['test2a']) + intval($row['test3a'])) / 3, 2);
				}
				echo "<tr><td>" . $row['user_id'] . "</td><td>" . $row['gender'] . "</td><td>" . $row['school_name'] . "</td><td>" . $row['class_grade'] . 
					"</td><td>" . $row['first'] . ' ' . $row['last'] . "</td><td>" . $row['first_he'] . ' ' . $row['last_he'] .
					"</td><td>" . $type . "</td><td>" . $avg . "</td></tr>";
			}
			?>
		</table>
	</body>
</html>