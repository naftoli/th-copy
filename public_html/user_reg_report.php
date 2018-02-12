<?php
require 'db.php';
$info = array();

require_once 'class.globalSettings.php';
$year = GlobalSettings::getRegistrationYear();

$sql = "select r.*, u.first, u.last, s.school_name, a.first as afirst, a.last as alast 
		from user_registration r 
		join admins a using (admin_id) 
		join users u using (user_id) 
		join schools s on s.school_id = u.school_id 
		where r.year = $year 
		order by school_name, u.last, u.first";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$info[] = $row;
}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8" />
		<title>Registration Report</title>
		<style type="text/css">
			th, td {
				padding: 5px;
				font-size: 12px;
				border-bottom: 1px solid red;
			}
		</style>
	</head>
	
	<body>
		<h1>Registration Report</h1>
		
		<?
		if (empty($info)) echo "No Registrations have been done.";
		else {
			?>
			<table>
				<tr>
					<th>School</th>
					<th>Student</th>
					<th>Amount Paid</th>
					<th>Date</th>
					<th colspan="2">Paid By</th>
				</tr>
				<?
				foreach ($info as $row) {
					echo "<tr><td>" . $row['school_name'] . "</td><td>" . $row['first'] . ' ' . $row['last'] . 
						"</td><td>" . $row['paid'] . "</td><td>" . $row['reg_date'] . "</td><td>" . 
						$row['afirst'] . "</td><td>" . $row['alast'] . "</td></tr>";
				}
				echo "</table>";
			}
			?>
	</body>
</html>