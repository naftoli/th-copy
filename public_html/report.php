<?
$admin_auth = array('school');
require_once 'header.php';
$pledges = array(
	'Pre1a'	=>	11, 
	'1'		=>	22,
	'2'		=>	44,
	'3'		=>	66,
	'4'		=>	77,
	'5'		=>	88,
	'6'		=>	100, 
	'7'		=>	113,
	'8'		=>	113
);
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8" />
		<style>
			th, td {
				font-size: 12px;
				padding: 5px 10px;
			}
		</style>
	</head>
	
	<body>
		<?
		$sql = "select s.school_name, u.user_id, u.first, u.last, u.dob, u.gender, c.class_grade, c.class_sub, a.admin_id, a.username, a.password, a.title, a.first as pfirst, a.last as plast, a.admin_address1, a.admin_city, 
				a.admin_state, a.admin_postal, a.admin_country, a.admin_phone_work, a.admin_phone_home, a.admin_phone_mobile, a.admin_email, lp.lines_pledged as mishna_pledged  
				from users u 
				join schools s on (u.school_id = s.school_id) 
				join classes c on (u.class_id = c.class_id) 
				join lines_pledged lp using (user_id) 
				join admin_auths aa on (u.user_id = aa.id) 
				join admins a on (a.admin_id = aa.admin_id) 
				where aa.auth = 'user' 
				and u.school_id in (61, 269) 
				and lp.campaign_id = 5 
				order by u.gender, a.admin_country, a.admin_state, a.admin_city, a.admin_postal, a.admin_id, u.last, u.first";
		$result = mysql_query($sql);	
		?>
		<table>
			<tr>
				<th>School</th>
				<th>User ID</th>
				<th>First</th>
				<th>Last</th>
				<th>Gender</th>
				<th>Age</th>
				<th>Grade</th>
				<th>Admin ID</th>
				<th>Username</th>
				<th>Password</th>
				<th>Parent</th>
				<th>Address</th>
				<th>City</th>
				<th>State</th>
				<th>Zip</th>
				<th>Country</th>
				<th>Work Phone</th>
				<th>Home Phone</th>
				<th>Cell Phone</th>
				<th>Email</th>
				<th>Mishna Pledged</th>
				<th>Tanya Pledged</th>
			</tr>
			<?
			while ($row = mysql_fetch_assoc($result)) {
				$arrSchool = explode(' ' , $row['school_name']);
				$grade = $row['class_grade'] . (empty($row['class_sub']) ? '' : '-' . $row['class_sub']);
				$parent = $row['title'] . ' ' . $row['pfirst'] . ' ' . $row['plast'];
				$datetime1 = new DateTime($row['dob']);
				$datetime2 = new DateTime();
				$difference = $datetime1->diff($datetime2);
				echo "<tr><td>" . $arrSchool[0] . "</td><td>" . $row['user_id'] . "</td><td>" . $row['first'] . 
					"</td><td>" . $row['last'] . "</td><td>" . $row['gender'] . "</td><td>" . $difference->y . 
					"</td><td>" . $grade . "</td><td>" . $row['admin_id'] . "</td><td>" . $row['username'] . 
					"</td><td>" . $row['password'] . "</td><td>" . $parent . "</td><td>" . $row['admin_address1'] . 
					"</td><td>" . $row['admin_city'] . "</td><td>" . $row['admin_state'] . 
					"</td><td>" . $row['admin_postal'] . "</td><td>" . $row['admin_country'] . 
					"</td><td>" . $row['admin_phone_work'] . "</td><td>" . $row['admin_phone_home'] . 
					"</td><td>" . $row['admin_phone_mobile'] . "</td><td>" . $row['admin_email'] . 
					"</td><td>" . $row['mishna_pledged'] . "</td><td>" . $pledges[$row['class_grade']] . "</td></tr>";
			}
			?>
		</table>
	</body>
</html>