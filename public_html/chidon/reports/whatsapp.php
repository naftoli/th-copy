<?
require '../../db.php';
require 'vars.php';

$info = array();
$sql = "select *, a.first as a_first, a.last as a_last, u.first as u_first, u.last as u_last from th_chidon tc  
		join schools s using (school_id)
		join users u using (user_id)
		join admins a on a.admin_id = tc.paid_by 
		where tc.year = " . $year . "  
		and tc.paid > 0 
		order by school_name, grade, u.last, u.first";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$name = $row['a_first'] . ' ' . $row['a_last'];
	$child = $row['u_first'] . ' ' . $row['u_last'];
	$email = $row['admin_email'];
	$cell1 = $row['admin_phone_mobile'];
	$cell2 = $row['admin_phone_mobile2'];
	$school = $row['school_name'];
	
	if ($row['gender'] == 'M') {
		$info['boys'][] = array(
			'admin' =>	$name, 
			'email'	=>	$email, 
			'cell1'	=>	$cell1,
			'cell2' =>	$cell2,
			'child' =>  $child,
			'school' => $school
		);
	} else if ($row['gender'] == 'F') {
		$info['girls'][] = array(
			'admin' 	=>	$name, 
			'email'	=>	$email, 
			'cell1'	=>	$cell1,
			'cell2' =>	$cell2,
			'child' =>  $child,
			'school' => $school
		);
	}
}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8" />
		<style>
			body {
				font-family: Arial, Helvetica, sans-serif;
				font-size: 14px;
			}
			caption {
				font-size: 20px;
				font-weight: bold;
			}
		</style>
	</head>
	
	<body>
		<?php foreach ($info as $gender => $other) : ?>
			<table>
				<caption><?=ucwords($gender)?> Whatsapp Info</caption>
				<tr>
					<th>School</th>
					<th>Parent Name</th>
					<th>Cell 1</th>
					<th>Celll 2</th>
					<th>Email</th>
					<th>Student</th>
				</tr>
				<?
				foreach ($other as $row) {
					echo "<tr><td>" . $row['school'] . "</td><td>" . $row['admin'] . "</td><td>" . $row['cell1'] . "</td><td>" . $row['cell2'] .
						"</td><td>" . $row['email'] . "</td><td>" . $row['child'] . "</td></tr>";
				}
				?>
			</table>
			
			<br />
			<hr />
			<br />
		<?php endforeach; ?>
	</body>
</html>