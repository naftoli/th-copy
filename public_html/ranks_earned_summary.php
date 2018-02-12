<?
$admin_auth = array('school'); 
require('db.php');

require_once 'ranksEarned.class.php';
$m = new RanksEarned;
$report = $m->getReport();
$ranks = $m->getRanks();
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
			foreach ($ranks as $ord => $rank) {
				echo "<tr><td>" . $rank . "</td>";
                echo "<td>" . $report[$year][$ord] . "</td></tr>";
			} 
			echo "</table><br /><br />";
    	}
    	?>
	</body>
</html>