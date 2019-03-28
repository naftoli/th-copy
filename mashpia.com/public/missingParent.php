<?
require 'db.php';

$users = array();
$sql = "select first, last, class_grade, class_sub, school_name from users u 
		join schools s using (school_id) 
		join classes c on c.class_id = u.class_id 
		left join admin_auths aa on aa.id = u.user_id 
		where u.user_registered > 0  
		and aa.id is null 
		order by school_name, c.class_grade, c.class_sub, last, first";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$users[] = $row;
}
//echo "<pre>"; print_r($users); echo "</pre>"; exit;
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8" />
		<style>
			body {
				font-family: Arial, Helvetica, sans-serif;
			}
			tr, td {
				padding: 5px;
				font-size: 12px;
			}
		</style>
	</head>
	
	<body>
		<table>
			<tr>
				<td><strong>School</strong></th>
				<td><strong>Grade</strong></th>
				<td><strong>Student</strong></th>
			</tr>
			<tr>
				<?php
				foreach ($users as $user) {
					$school = $user['school_name'];
					$grade = $user['class_grade'] . (empty($user['class_sub']) ? '' : '-' . $user['class_sub']);
					$name = $user['first'] . ' ' . $user['last'];
					echo "<tr><td>" . $school . "</td><td>" . $grade . "</td><td>" . $name . "</td></tr>";
				}
				?>
			</tr>
		</table>
	</body>
</html>