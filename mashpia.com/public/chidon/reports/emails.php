<? 
require '../../db.php';
require 'vars.php';

$emails = array();
$sql = "select tc.th_chidon_id, u.first as u_first, u.last as u_last, a.*, a.first as a_first, a.last as a_last from th_chidon tc
		join users u using (user_id)
		join admins a on a.admin_id = tc.paid_by  
		where tc.year = " . $year . "
		and tc.paid > 0";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$emails[] = $row;
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
		</style>
	</head>
	
	<body>
		<h1>List of Parent Emails</h1>
		<table>
			<tr>
				<th>ID</th>
				<th>Name</th>
				<th>Parent</th>
				<th>Email</th>
				<th>Cell</th>
				<th>City, State</th>
			</tr>
			<?
			foreach ($emails as $row) {
				$city = $row['admin_city'];
				$good = false;
				if (filter_var($row['admin_email'], FILTER_VALIDATE_EMAIL)) $good = true;
				echo "<tr><td>" . $row['th_chidon_id'] . "</td><td>" . $row['u_first'] . ' ' . $row['u_last'] . 
					"</td><td>" . $row['a_first'] . ' ' . $row['a_last'] . "</td><td>" . $row['admin_email'] . "</td><td>" . 
					$row['admin_phone_mobile'] . ', ' . $row['admin_phone_mobile2'] . "</td><td>" . $city . "</td></tr>";
			}
			?>
		</table>
	</body>
</html>