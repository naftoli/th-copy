<?
$admin_auth = array('school'); 
require('header.php');

$users = array();
$sql = "select * from users u 
		join schools s using (school_id) 
		join classes c on (c.class_id = u.class_id) 
		join rank_marks using (user_id) 
		where rank_ord = 1 
		and date_book_received is null 
		and u.user_registered > 0 
		and s.school_id != 82 
		order by s.school_name, c.class_grade, c.class_sub, u.last, u.first";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$users[] = $row;
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
		<h1>Users without Rank Book</h1>
		
		<h2>Users without Rank Book</h2>
		<? createTable( $users ); ?>
	</body>
</html>