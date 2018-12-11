<?
require 'db.php';

$users = array();
$sql = "select * from users where user_registered > '2016-01-31 11:00:00'";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$users[] = $row;
}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8" />
		<style>
			th, td {
				padding: 5px 10px;
				font-size: 12px;
			}
		</style>
	</head>
	
	<body>
		<table>
			<tr>
				<th>Registered</th>
				<th>Name</th>
				<th>Hebrew Name</th>
				<th>Personalized Pushka</th>
				<th>Plain Pushka</th>
			</tr>
			<?
			foreach ($users as $user) {
				if ($user['pushka'] == 1) {
					$pp = 1;
					$plain = 0;
				} else if ($user['pushka'] == 2) {
					$pp = 0;
					$plain = 1;
				}
				$heName = empty($user['he_name']) ? $user['first_he'] . ' ' . $user['last_he'] : $user['he_name'];
				echo "<tr><td>" . $user['user_registered'] . "</td><td>" . $user['first'] . ' ' . $user['last'] . 
					"</td><td>" . $heName . "</td><td>" . $pp . "</td><td>" . $plain . "</td></tr>";
			}
			?>
		</table>
	</body>
</html>