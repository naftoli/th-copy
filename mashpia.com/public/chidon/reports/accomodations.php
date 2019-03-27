<?php
//echo "Needs update for this report.";
//exit;

require '../../db.php';
require 'vars.php';

$info = array();
$sql = "select *, u.first as u_first, u.last as u_last from th_chidon tc 
		join schools s using (school_id)
		join users u using (user_id)
		join admins a on a.admin_id = tc.paid_by 
		where tc.year = " . $year . "
		and tc.paid > 0 
		order by school_name, u.last, u.first";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$info[$row['gender']][] = $row;
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
			<caption>Accomodations Info</caption>
			<tr>
				<th>Gender</th>
				<th>School Name</th>
				<th>First Name</th>
				<th>Last Name</th>
				<th>Grade</th>
				<th>Host Family</th>
				<th>Host Address</th>
				<th>Host Number</th>
				<th>Emergency Number</th>
			</tr>
			<?
			foreach ($info as $gender => $other) {
				foreach ($other as $row) {
					echo "<tr><td>" . $gender . "</td><td>" . $row['school_name'] . "</td><td>" . 
						$row['u_first'] . "</td><td>" . $row['u_last'] . "</td><td>" . $row['host'] . 
						"</td><td>" . $row['host_street_num'] . $row['host_street_num_suffix'] . ' ' . $row['host_street'] . ' ' . $row['host_street_apt'] . "</td><td>" . 
						$row['host_number'] . "</td><td>" . $row['admin_phone_mobile'] . ', ' . $row['admin_phone_mobile2'] . "</td></tr>";
				}
			}
			?>
		</table>
	</body>
</html>