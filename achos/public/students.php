<?
$admin_auth = array('school'); 
require 'header.php';

$users = array();
$sql = "select * from users u 
		join user_tracks ut using (user_id) 
		join classes c on c.class_id = u.class_id 
		where ut.subject_id = 1 
		order by c.class_grade, c.class_sub, u.last, u.first";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$grade = $row['class_grade'] . (empty($row['class_sub']) ? '' : '-' . $row['class_sub']);
	$users[$grade][] = $row;
}
?>
<!DOCTYPE html>
<html>
	<head>
		<link href="admin_styles.css" rel="stylesheet" type="text/css">
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>Student Levels Report</title>
		<style>
		th, td {
			font-size: 12px;
			padding: 5px;
		}
		</style>
	</head>

	<body>
	<? include 'admin_header.php';?>
	<h1>Student Levels Report</h1>

	<table>
		<tr>
			<th>Grade</th>
			<th>Student</th>
			<th>Level</th>
		</tr>
		<?
		foreach ($users as $grade => $info) {
			foreach ($info as $user) {
				echo "<tr><td>" . $grade . "</td><td>" . $user['first'] . ' ' . $user['last'] . "</td><td>
					<select class='level' id=" . $user['user_id'] . ">";
				for ($i = 1; $i < 5; $i++) {
					echo "<option value='" . $i . "' ";
					if ($i == $user['level']) {
						echo "selected='selected' ";
					}
					echo ">" . $i . "</option>";
				}
				echo "</select></td></tr>";
			}
		}
		?>
	</table>
	
	<script type="text/javascript" src="js/jquery-1.8.1.min.js"></script>
	<script>
		$(".level").change( function() {
			var id = $(this).attr('id');
			var level = $(this).val();
			$.post('ajax/updateLevel.php', { user_id : id, level : level });
		});
	</script>
	</body>
</html>