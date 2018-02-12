<?php
require 'db.php';
require_once 'class.globalSettings.php';
$year = GlobalSettings::getRegistrationYear();

$info = array();
$sql = "select r.*, a.first, a.last from registration r 
		join admins a using (admin_id) 
		where r.year = $year";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$info[$row['school_id']][] = $row;
}
//echo "<pre>"; print_r($info); echo "</pre>"; exit;
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8" />
		<title>Registration Report</title>
		<style>
			table {
				padding-bottom: 20px;
			}
			th, td {
				padding: 5px 10px;
				font-size: 12px;
			}
			caption {
				border-bottom: 1px solid black;
			}
		</style>
	</head>
	
	<body>
		<h1>Registration Report</h1>
		
		<?
		if (empty($info)) echo "No Registrations have been done.";
		foreach ($info as $school => $other) {
			if ($school == 61) {
				echo "<table><caption>MyShliach</caption>";
			} else if ($school == 269) {
				echo "<table><caption>Anash Kinder</caption>";
			}
			echo "<tr><th>Date</th><th>Parent</th><th>Amount Charged</th><th>Description</th></tr>";
			foreach ($other as $row) {
				$info = explode(':', $row['approval']);
				$paid = $info[3];
				echo "<tr><td>" . $row['date'] . "</td><td>" . $row['first'] . ' ' . $row['last'] . "</td><td>" . 
					$paid . "</td><td>" . $row['description'] . "</td></tr>";
			}
			echo "</table>";
		}
		?>
	</body>
</html>