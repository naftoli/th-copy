<?
$admin_auth = array('school');
require_once 'header.php';
?>
<html>
	<head>
		<meta charset="UTF-8" />
		<link href="admin_styles.css" rel="stylesheet" type="text/css">
        <link href="styles/achosCustomization.css" rel="stylesheet" type="text/css">
	</head>
	
	<body>
		<? require 'admin_header.php'; ?>
        <h1>Student List</h1>
		<?
		$admins = array();
		$sql = "select * from admins";
		$result = mysql_query($sql);
		while ($row = mysql_fetch_assoc($result)) {
			if ($row['auth'] == 'super') continue;
			$admins[] = $row;
		}
		?>
		
		<table>
			<tr>
				<th>Username</th>
				<th>Password</th>
				<th>Name</th>
			</tr>
			<?
			foreach ($admins as $admin) {
				echo "<tr><td>" . $admin['username'] . "</td><td>" . $admin['password'] . "</td><td>" . 
					$admin['first'] . ' ' . $admin['last'] . "</td></tr>";
			}
			?>
		</table>
	</body>
</html>
