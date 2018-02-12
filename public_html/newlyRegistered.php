<?
$admin_auth = array('school'); 
require('header.php');

$users = array();
$start = 2456893; //August 24, 2014
$sql = "select * from users u 
		join schools s using (school_id) 
		join classes c on (u.class_id = c.class_id) 
		where user_registered > 0 
		and user_start_date > $start 
		order by school_name, class_grade, class_sub, last, first";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$users[] = $row;
}

$usersWithMedal = array();
$usersWithoutMedal = array();
foreach ($users as $user) {
	$sql = "select * from medal_marks where user_id = " . $user['user_id'];
	$result = mysql_query($sql);
	if (mysql_num_rows($result) > 0) {
		$usersWithMedal[] = $user;
	} else {
		$usersWithoutMedal[] = $user;
	}
}

function createTable( array $users ) {
	?>
	<table>
		<tr>
			<th>Name</th>
			<th>Grade</th>
			<th>School</th>
			<th>Registered</th>
		</tr>
		<?
		$school_id = 0;
		$total = 0;
		$grandTotal = 0;
		foreach ($users as $user) {

			if ($school_id != $user['school_id'] && $total) {
				echo "<tr><th colspan='2'>Total</th><th>" . $school . "</th><th>" . $total . "</th></tr>";
				$grandTotal += $total;
				$school_id = $user['school_id'];
				$total = 1;
			} else {
				$school_id = $user['school_id'];
				$total++;
			}
			
			$name = $user['first'] . ' ' . $user['last'];
			$grade = $user['class_grade'] . (empty($user['class_sub']) ? '' : '-' . $user['class_sub']);
			$school = $user['school_name'];
			$reg = $user['user_registered'];
			
			echo "<tr><td>" . $name . "</td><td>" . $grade . "</td><td>" . $school . "</td><td>" . 
				$reg . "</td></tr>";
		}
		echo "<tr><th colspan='2'>Total</th><th>" . $school . "</th><th>" . $total . "</th></tr>";
		echo "<tr><th colspan='3'>Grand Total</th><th>" . $grandTotal . "</th></tr>";
		?>
	</table>
	<?
}
?>
<!doctype html>
	<head>
		<link href="admin_styles.css" rel="stylesheet" type="text/css">
		<style>
			tr, th, td {
				font-size: 12px;
				padding: 5px;
				border: 1px solid black;
			}
		</style>
	</head>
	
	<body>
		<? include('admin_header.php'); ?>
		<h1>Newly Registered Users</h1>
		
		<h2>New Users with Medal</h2>
		<? createTable( $usersWithMedal ); ?>
		
		<h2>New Users without Medal</h2>
		<? createTable( $usersWithoutMedal ); ?>
	</body>
</html>