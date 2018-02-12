<?
require_once 'db.php';
$users = array();
$sql = "select * from users where school_id = 61";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$users[] = $row;
}

$admins = array();
$parents = array();
foreach ($users as $user) {
	$sql = "select * from admins 
			join admin_auths aa using (admin_id) 
			where aa.id = " . $user['user_id'] . " 
			and aa.auth = 'user'";
	$result = mysql_query($sql);
	$row = mysql_fetch_assoc($result);
	$admins[$row['admin_id']] = $row;
	$parents[$row['admin_id']][] = $user;
}

echo "<pre>";
//print_r($parents);
echo "</pre>";
?>
<html>
	<head>
		<meta charset="UTF-8" />
		<style>
			tr, th, td {
				vertical-align: top;
				border: 1px solid black;
				text-align: left;
				padding: 3px;
				font-size: 10px;
			}
		</style>
	</head>
	
	<body>
		<table>
			<tr>
				<th>Parent Account ID</th>
				<th>Parent Name</th>
				<th>Parent Email</th>
				<th>Parent Phone</th>
				<th>Parent Address</th>
				<th>Child Account ID</th>
				<th>Child Name</th>
				<th>Child Phone</th>
				<th>Child Address</th>
			</tr>
			<?
			foreach ($parents as $admin_id => $children) {
				$adminName = $admins[$admin_id]['title'] . ' ' . $admins[$admin_id]['first'] . ' ' . $admins[$admin_id]['last'];
				$adminPhone = "W: " . $admins[$admin_id]['admin_phone_work'] . " H: " . $admins[$admin_id]['admin_phone_home'] . 
				" C: " . $admins[$admin_id]['admin_phone_mobile'];
				$adminAddress = $admins[$admin_id]['admin_address1'] . " " . $admins[$admin_id]['admin_city'] . 
						", " . $admins[$admin_id]['admin_state'] . " " . $admins[$admin_id]['admin_postal'] . " " . 
						$admins[$admin_id]['admin_country'];
				foreach ($children as $child) {
					echo "<tr><td>&nbsp;" . $admin_id . "</td><td>&nbsp;" . $adminName . "</td><td>&nbsp;" . $admins[$admin_id]['admin_email'] . 
						"</td><td>&nbsp;" . $adminPhone . "</td><td>&nbsp;" . $adminAddress . "</td><td>&nbsp;" . $child['user_id'] . 
						"</td><td>&nbsp;" . $child['first'] . ' ' . $child['last'] . "</td><td>&nbsp;" . $child['user_phone'] . 
						"</td><td>&nbsp;" . $child['user_address1'] . " " . $child['user_city'] . ', ' . 
						$child['user_state'] . ' ' . $child['user_postal'] . " " . $child['user_country'] . "</td></tr>";
				}
			}
			?>
		</table>
	</body>
</html>