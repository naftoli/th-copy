<?
$admin_auth = array('school'); 
require('header.php');

$users = array();
$sql = "select user_id, first, last from users where school_id = 1 order by last, first";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$id = $row['user_id'];
	$name = $row['first'] . ' ' . $row['last'];
	$users[$id] = $name;
}

require 'class.achosPoints.php';
?>
<!DOCTYPE html>
<html>
	<head>
		<link href="admin_styles.css" rel="stylesheet" type="text/css">
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>Points Report</title>
		<style>
			tr, td {
				font-size: 12px;
				padding: 5px;
			}
		</style>
	</head>
	
	<body>
		<? include('admin_header.php');?>
		<h1>Points Report</h1>
		
		<table>
			<tr>
				<th>Student</th>
				<th>Points Today</th>
				<th>Points this Week</th>
				<th>Total Points</th>
			</tr>
			<?
			foreach ($users as $id => $name) {
				$p = new AchosPoints($id);
				echo "<tr><td>" . $name . "</td><td>" . $p->calcDailyPoints() . 
					"</td><td>" . $p->calcWeeklyPoints() . "</td><td>" . 
					$p->calcPoints() . "</td></tr>";
			}
			?>
		</table>
	</body>
</html>
