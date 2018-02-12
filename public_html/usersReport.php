<?
require 'db.php';
$users = array();
$sql = "select user_id, last, first, school_name 
		from users u 
		join schools using (school_id) 
		order by user_id";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$users[] = $row;
}
?>
<!DOCTYPE html>
<html>
	<head>
		<style>
			th, td {
				padding: 3px;
				font-size: 12px;
				text-align: left;
			}
		</style>
	</head>
	
	<body>
		<table>
			<tr>
				<th>User ID</th>
				<th>Name</th>
				<th>School</th>
			</tr>
			<?
			foreach ($users as $user) {
				echo "<tr><td>" . $user['user_id'] . "</td><td>" . $user['first'] . ' ' . $user['last'] . "</td><td>" . 
				$user['school_name'] . "</td></tr>";
			}
			?>
		</table>
	</body>
</html>