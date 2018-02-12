<?
$admin_auth = array('school'); 
require('header.php');
require_once 'class.posterOrders.php';
$type = "Shabbos Mevorchim";

if (isset($_POST['submit'])) {
	//echo "<pre>"; print_r($_POST); echo "</pre>"; 
	foreach ($_POST as $k => $v) {
		if (is_int($k)) {
			$qty = (int)trim(mysql_real_escape_string($v));
			if (is_numeric($qty)) {
				PosterOrders::updateClassOrders($k, $qty, $type);
			}
		}
	}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<link href="admin_styles.css" rel="stylesheet" type="text/css">
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>Order <?=$type?> Posters</title>
		<style>
			table {
                font-size: 11px;
            }
            th, td {
                padding: 3px 10px;
            }
		</style>
	</head>

	<body>
		<? include('admin_header.php');?>
	
		<h1>Order <?=$type?> Posters</h1>
		
		<?
		require_once 'class.adminSchools.php';      
        $as = new AdminSchools($admin_user['admin_id'], $admin_user['auth']);
        $schools = $as->getSchools();    
		
		require_once 'class.schoolClasses.php';
		
		echo "<form action='order_posters.php' method='post'>";
		foreach ($schools as $id => $name) {
			echo $name . "<br />";
			$p = new PosterOrders($id, $type);
			$c = new SchoolClasses($id);
			$classes = $c->getClasses();
			?>
			<table>
				<tr>
					<th>Platoon</th>
					<th>Amount to order</th>
				</tr>
			<?
			foreach ($classes as $class) {
				$qty = $p->getClassOrders($class['class_id'], $type);
				$className = $class['class_grade'] . (empty($class['class_sub']) ? '' : '-' . $class['class_sub']);
				echo "<tr><td>" . $className . 
				"</td><td><input size='4' type='text' name='" . $class['class_id'] . "' value='" . ($qty ? $qty : 0) . "' /></td></tr>";
			}
			echo "</table>";
		}
		echo "<input type='submit' name='submit' value='submit' />";
		echo "</form>";
		?>
	
	</body>
</html>