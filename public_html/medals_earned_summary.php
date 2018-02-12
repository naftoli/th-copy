<?
$admin_auth = array('school'); 
require('db.php');

require_once 'medalsEarned.class.php';
$m = new MedalsEarned;
$report = $m->getReport();
$medals = $m->getMedals();
$subjects = $m->getSubjects();

//echo "<pre>"; print_r($report); echo "</pre>"; exit;
?>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    </head>
    <style type='text/css'>
        table {
            font-size: 12px;
        }
        td {
            padding: 3px 10px;
        }
    </style>
    <body>
    	<?
    	$totals = array();
    	foreach ($report as $year => $arr) {
    		echo "<table>";
			echo "<tr><td>" . $year . "</td>";
			foreach ($medals as $medal) {
				echo "<td>" . $medal . "</td>";
			} 
			echo "</tr>";
			
			echo "<tr>";
			foreach ($subjects as $subject => $id) {
				echo "<td>" . $subject . "</td>";
				foreach ($medals as $ord => $medal) {
					if (is_array($id)) {
						$s = implode(',', $id);
					} else {
						$s = (string)$id;
					}
					echo "<td>" . $report[$year][$s][$ord] . "</td>";
				}
				echo "</tr>";
			}
			echo "</table><br /><br />";
    	}
    	?>
	</body>
</html>