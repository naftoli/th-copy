<?
$admin_auth = array('school'); 
require('header.php');

require_once 'class.adminSchools.php';
$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth']);
$schools = $as->getSchools();

require_once 'class.schoolClasses.php';
$classes = array();
foreach ($schools as $id => $name) {
	$c = new SchoolClasses($id);
	$cs = $c->getClasses();
	foreach ($cs as $class) {
		$classID = $class['class_id'];
		$grade = $class['class_grade'];
		$sub = $class['class_sub'];
		$classes[$id][$classID] = $grade . (empty($sub) ? '' : '-' . $sub);
	}
}

require_once 'class.posterOrders.php';
$type = "Shabbos Mevorchim";
$orders = array();
foreach ($schools as $id => $name) {
	$p = new PosterOrders($id);
	$orders[$id] = $p->getOrders($type);
}

//echo "<pre>"; print_r($orders); echo "</pre>"; exit;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<HTML>

    <HEAD>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Poster Orders Report</title>
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <style type='text/css'>
            table {
                font-size: 12px;
            }
            th, td {
                padding: 3px 10px;
            }
            .page-break {
                page-break-after: always;
            }
        </style>
    </HEAD>

    <BODY>
        <? include('admin_header.php'); ?>
        <h1>Poster Orders Report</h1>
		
		<?
        $totals = array();
        foreach ($orders as $school => $info) {
        	$totals[$school] = 0;
			?>
	        <table>
	            <tr>
	                <th>School</th>
	                <th>Grade</th>
	                <th>Posters Ordered</th>
	            </tr>
		        <?
	        	foreach ($info as $id => $qty) {
	        		$totals[$school] += $qty;
	        		echo "<tr><td>" . $schools[$school] . "</td><td>" . $classes[$school][$id] . "</td><td>" . $qty . "</td></tr>";
	        	}
		        ?>
	        </table>
	        <br />
	        <div class="page-break"></div>
		<? } ?>	
		
		<h2>Totals</h2>
		<table>
			<tr>
				<th>School</th>
				<th>Total</th>
			</tr>
			<?
			$grandTotal = 0;
			foreach ($totals as $school => $total) {
				$grandTotal += $total;
				echo "<tr><td>" . $schools[$school] . "</td><td>" . $total . "</td></tr>";
			}
			?>
		</table> 
		
		<?
		if (count($schools) > 1) {
			echo "<br />Grand Total: " . $grandTotal;
		}
		?>  
    </body>
</html>