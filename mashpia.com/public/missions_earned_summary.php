<?php
ini_set('display_errors',1);
$admin_auth = array('school'); 
require('db.php');

require_once 'missionsEarned.class.php';
$m = new MissionsEarned;
$report = $m->getReport();
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
            echo $year;
    		echo "<table>";
			foreach ($subjects as $subject => $subject_id) {
				echo "<tr><td>" . $subject . "</td>";
                echo "<td>" . $report[$year][$subject] . "</td></tr>";
			} 
			echo "</table><br /><br />";
    	}
    	?>
	</body>
</html>