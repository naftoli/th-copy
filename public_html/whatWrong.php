<?php
require 'db.php';

$info = array();
$year = 5777;
$sql = "select * from user_registration r 
        join users u using (user_id) 
		join schools s on u.school_id = s.school_id  
		where r.year = " . $year . " 
		order by s.school_name, u.last, u.first";
$result = mysql_query($sql);
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
        
	</body>
</html>