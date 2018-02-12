<?
echo 'needs updating.';
exit;

require '../../db.php';
require 'vars.php';

$info = array();
$sql = "select * from th_chidon tc 
		join schools s using (school_id)
		join users u using (user_id)
		join th_chidon_chaps tcc on tc.school_id = tcc.school_id 
		where tc.year = " . $year . "  
		and tc.paid > 0 
		order by school_name, grade, last, first";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$info[$row['gender']][] = $row;
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
				font-size: 16px;
				font-weight: bold;
				border-bottom: 1px solid grey;
			}
		</style>
	</head>
	
	<body>	
		<table>
			<caption>Bunk List</caption>
			<tr>   
				<th>ID</th>
				<th>Gender</th>
				<th>Team Name</th>
				<th>Team HC Name</th>
				<th>Team HC Cell</th>
				<th>Team Grade</th>
				<th>Counselor Name</th>
				<th>Counselor Cell</th>
				<th>First Name</th>
				<th>Last Name</th>
				<th>Sweater Size</th>
				<th>Shoe Size</th>
				<th>Allergy</th>
				<th>Notes</th>
			</tr>
			<?
			foreach ($info as $gender => $other) {
				foreach ($other as $row) {
					echo "<tr><td>" . $row['th_chidon_id'] . "</td><td>" . $gender . "</td><td>" . 
						 "</td><td>" . "</td><td>" . "</td><td>" . "</td><td>" . "</td><td>" . "</td><td>" . 
						 $row['first'] . "</td><td>" . $row['last'] . "</td><td>" . $row['size'] . "</td><td>" . 
						 $row['shoe_size'] . "</td><td>" . $row['allergies'] . "</td><td>" . $row['notes'] . "</td></tr>";
				}
			}
			?>
		</table>
	</body>
</html>