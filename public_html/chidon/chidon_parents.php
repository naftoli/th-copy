<?php
require '../db.php';

$parents = array();
$sql = "select school_name, name, last_name, parent_email, parent_name, parent_cell, parent_cell2 
		from chidon_reg cr
		join chidon_schools cs using (chidon_schools_id)
		where cs.year = 5776 
		group by parent_email 
		order by school_name, parent_name";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$parents['chidon'][] = $row;	
}

$emails = array();
$sql = "select a.first, a.last, a.admin_email, a.admin_phone_work, a.admin_phone_home, a.admin_phone_mobile,
			u.first as ufirst, u.last as ulast, u.user_registered, s.school_name 
		from admins a
		join admin_auths aa using (admin_id)
		join users u on u.user_id = aa.id
		join schools s on s.school_id = u.school_id 
		where aa.role_id = 1
		group by a.admin_email 
		order by s.school_name, a.last, a.first";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$parents['chayolei'][] = $row;
}
//echo "<pre>"; print_r($parents); echo "</pre>";
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8" />
		<style>
			tr, th, td {
				padding: 5px;
				font-family: Arial;
				font-size: 12px;
			}
		</style>
	</head>
	
	<body>
		<h2>Chidon Parents</h2>
		<table>
			<tr>
				<th>School</th>
				<th>Child</th>
				<th>Parent</th>
				<th>Email</th>
				<th>Number</th>
			</tr>
			<?php
			foreach ($parents['chidon'] as $parent) {
				echo "<tr><td>" . $parent['school_name'] . "</td><td>" . $parent['first'] . ' ' . $parent['last'] . "</td><td>" .
					$parent['parent_name'] . "</td><td>" . $parent['parent_email'] . "</td><td>" .
					$parent['parent_cell'] . "<br />" .	$parent['parent_cell2'] . "</td></tr>";
			}
			?>
		</table>
		
		<h2>Chayolei Parents</h2>
		<table>
			<tr>
				<th>School</th>
				<th>Child</th>
				<th>Parent</th>
				<th>Email</th>
				<th>Number</th>
			</tr>
			<?php
			foreach ($parents['chayolei'] as $parent) {
				echo "<tr><td>" . $parent['school_name'] . "</td><td>" . $parent['ufirst'] . ' ' . $parent['ulast'] . "</td><td>" .
					$parent['first'] . ' ' . $aprent['last'] . "</td><td>" . $parent['admin_email'] . "</td><td>" .
					$parent['admin_phone_work'] . "<br />" .	$parent['admin_phone_home'] . "<br />" .
					$parent['admin_phone_mobile'] . "</td></tr>";
			}
			?>
		</table>
	</body>
</html>