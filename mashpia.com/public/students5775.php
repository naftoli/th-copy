
<?
ini_set('display_errors', TRUE);
$admin_auth = array('school'); 
require('header.php');
//mysql_query("use mashpia5775");

require_once 'class.adminSchools.php';
$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
$schools = $as->getSchools();
$school_ids = array();
foreach ($schools as $id => $name) {
	if ($id == 82) continue;
	$school_ids[] = $id;
}

$totals = array();
$teachers = array();
$users = array();
$schoolStudents = array();
/*
$sql = "select u.first, u.last, c.class_grade, c.class_sub, c.class_teacher, s.school_id, s.school_name 
		from mashpia5775.users u 
		left join classes c on c.class_id = u.class_id 
		join schools s on u.school_id = s.school_id 
		where u.user_registered > 0 
		and s.school_id in (" . implode(',', $school_ids) . ") 
		order by s.school_name, c.class_grade, c.class_sub, u.last, u.first";
		*/
$sql = "select u.first, u.last, c.class_grade, c.class_sub, c.class_teacher, s.school_id, s.school_name 
		from mashpia5775.users u 
		left join classes c on c.class_id = u.class_id 
		join schools s on u.school_id = s.school_id 
		where u.user_registered > 0 
		and s.school_id != 82 
		order by s.school_name, c.class_grade, c.class_sub, u.last, u.first";
echo "<input type='hidden' name='sql' value='$sql' />";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$name = $row['first'] . ' ' . $row['last'];
	$grade = $row['class_grade'] . (empty($row['class_sub']) ? '' : '-' . $row['class_sub']);
	$school = $row['school_name'];
	$users[$school][$grade][] = $name;
	$teachers[$school][$grade] = $row['class_teacher'];
	$totals[$school][] = 1;
	if ( isset( $schoolStudents[$school] ) ) $schoolStudents[$school]++;
	else $schoolStudents[$school] = 1;
}

//find out teachers for current year's grades
$grades = array();
$sql = "select school_name, count(class_id) as total from classes c 
		join schools s on s.school_id = c.school_id 
		where class_era = 0 and c.school_id in (" . implode(',', $school_ids) . ") 
		group by c.school_id";
//echo $sql; exit;
$result = mysql_query( $sql );
while ($row = mysql_fetch_assoc($result)) {
	$grades[$row['school_name']] = $row['total'];
}
//echo "<pre>"; print_r( $users ); echo "</pre>";
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8" />
		<link href="admin_styles.css" rel="stylesheet" type="text/css">
		<style>
			tr, td {
				padding: 5px;
				font-size: 12px;
			}
			caption {
				border-bottom: 2px solid blue;
				margin-bottom: 10px;
			}
		</style>
	</head>
	
	<body>
		<? include('admin_header.php'); ?>
		<h1>Students from 5775</h1>
		<? foreach ($users as $school => $info) { ?>
			<p>
				School: <?=$school?><br />
				Base Commander: 1<br />
				Principal: 1<br />
				Teachers: <?= isset($grades[$school]) ? $grades[$school] : '' ?><br />
				Students: <?= $schoolStudents[$school] ?><br />
				Total: <?= (isset($grades[$school]) ? $grades[$school] : 0) + $schoolStudents[$school] + 2 ?>
			</p>
			<table>
				<caption><?=$school?></caption>
				<tr>
					<th>Grade</th>
					<th>Teacher</th>
					<th>Student</th>
				</tr>
				<?
				foreach ($info as $grade => $other) {
					foreach ($other as $student) {
						echo "<tr><td>" . $grade . "</td><td>" . 
						$teachers[$school][$grade] . "</td><td>" . $student . "</td></tr>";
					}
				}
				?>
			</table>
			<h2></h2>
		<? } ?>
		
		<br />
		<h2>Totals</h2>
		<table>
			<tr>
				<th>School</th>
				<th>Total</th>
			</tr>
			<? foreach ($totals as $school => $info) { ?>
				<tr><td><?= $school?></td><td><?=$grades[$school] + $schoolStudents[$school] + 2 ?></td></tr>
			<? } ?>
		</table>			
	</body>
</html>