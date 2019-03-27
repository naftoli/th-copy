<?
require '../../db.php';
require 'vars.php';

$info = array();
$sql = "select a.*, s.school_name, tc.grade, tc.th_chidon_id, tc.allergies, u.first as u_first, u.last as u_last, a.first as a_first, a.last as a_last from th_chidon tc
		join schools s using (school_id) 
		join users u using (user_id)
		join admins a on a.admin_id = tc.paid_by 
		where tc.year = " . $year . "  
		and tc.date_paid > 0
		order by school_name, tc.grade, u_last, u_first";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$info[$row['school_name']][] = $row;
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
		<?php	foreach ( $info as $school => $other ) : ?>
		<h3><?= $school ?></h3><hr /><br />
		<table>
			<caption>Emergency Info</caption>
			<tr>
				<th>Grade</th>
				<th>First Name</th>
				<th>Last Name</th>
				<th>Allergies</th>
				<th>Parent's Name</th>
				<th>Father's Cell</th>
				<th>Mother's Cell</th>
				<th>Parent's Email</th>
			</tr>
			<?
			foreach ($other as $row) {
				echo "<tr><td>" . $row['grade'] . "</td><td>" . $row['u_first'] . "</td><td>" . $row['u_last'] . "</td><td>" . 
					$row['allergies'] . "</td><td>" . $row['a_first'] . ' ' . $row['a_last'] . "</td><td>" . $row['admin_phone_mobile'] . "</td><td>" . 
					$row['admin_phone_mobile2'] . "</td><td>" . $row['admin_email'] . "</td></tr>";
			}
			?>
		</table>
		<div style="page-break-after: always;"></div>
		<?php endforeach; ?>
	</body>
</html>