<?php
ini_set('display_errors', 1);
require 'db.php';

require_once 'class.globalSettings.php';
$year = GlobalSettings::getRegistrationYear();

$info = array();
$sql = "select * from user_registration r 
        join users u using (user_id) 
		join schools s on u.school_id = s.school_id  
		where r.year = " . $year;
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$info[] = $row;
}
//echo "<pre>"; print_r($info); echo "</pre>"; exit;
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8" />
		<title>Registration Report</title>
		<style>
			th, td {
				padding: 5px 10px;
				font-size: 12px;
			}
		</style>
	</head>
	
	<body>
		<h1>Registration Report</h1>
		
		<? if (empty($info)) { echo "No Registrations have been done."; exit; } ?>
        <table>
            <tr>
				<th>School</th>
				<th>User ID</th>
                <th>Child</th>
                <th>Registered</th>
                <th>Paid</th>
            </tr>
            <?
            foreach ($info as $row) {
                echo "<tr><td>" . $row['school_name'] . "</td><td>" . $row['user_id'] . "</td><td>" . $row['first'] . ' ' . $row['last'] . "</td><td>" .
					$row['reg_date'] . "</td><td>" . $row['paid'] . "</td></tr>";
            }
            ?>
        </table>
	</body>
</html>