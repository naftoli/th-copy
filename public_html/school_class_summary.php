<?
$admin_auth = array('school'); 
require('header.php');

require_once 'class.adminSchools.php';
require_once 'class.schoolClasses.php';

$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
$schools = $as->getSchools();
?>
<!DOCTYPE html>
<html>
	<head>
		<title>School Grade Summary</title>
		<link href="admin_styles.css" rel="stylesheet" type="text/css">
		<style type='text/css'>
            table {
                font-size: 12px;
            }
            th, td {
                padding: 3px 10px;
            }
        </style>
	</head>
	
	<body>
		<? include('admin_header.php'); ?>
		<h1>School Grade Summary</h1>
		
		<table>
			<tr>
				<th>School</th>
				<th>Number of Grades</th>
			</tr>
		<?
		$total = 0;
		foreach ($schools as $id => $name) {
			$sc = new SchoolClasses( $id );
			$classes = $sc->getClasses();
			echo "<tr><td>" . $name . "</td><td>" . count($classes) . "</td></tr>";
			$total += count($classes);
		}
		echo "<tr><th>Total</th><th>" . $total . "</th></tr></table>";
		?>
	</body>
</html>