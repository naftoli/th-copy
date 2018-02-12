<?
$admin_auth = array('school'); 
require('header.php');

require_once 'class.adminSchools.php';
$exception = array(79,82,198,199);      
$schools = AdminSchools::getAllSchools($exception);
$schoolIDs = array();
foreach ($schools as $id => $school) {
	$schoolIDs[] = $id;
}

$campaigns = array(
	3 => 'tanya',
	4 => 'mishna'
	); //tanya, mishna yud alef nissan 5774
require_once 'class.lineCampaigns.php';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<HTML>

    <HEAD>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Tanya / Mishna Report</title>
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <script type="text/javascript" src="scripts/jquery-1.8.3.js"></script>
        <script type="text/javascript" src="scripts/jquery.styleselect.js"></script>
        <style type='text/css'>
            p {
                font-size: 24px;
            }
            table {
                font-size: 11px;
            }
            th, td {
                padding: 6px 10px;
                border-bottom: 1px solid #C0C0C0;
                border-right: 1px solid #C0C0C0;
            }
            td:first-child, th:first-child {
            	border-left: 1px solid #C0C0C0;
            }
            tr:first-child {
            	border-top: 1px solid #C0C0C0;
            }
            @media print{
	            .no-print {
	            	display: none;
	            }
	        }
        </style>
    </head>
    
    <body>
    	<? include('admin_header.php'); ?>
        <h1 class="no-print">Tanya / Mishna Report</h1>
        
        <?
        $results = array();
        foreach ($campaigns as $id => $campaign) {
        	$l = new LineCampaigns($id);
        	$results['pledged'][$campaign] = $l->getLinesPledged($schoolIDs);
			$results['learned'][$campaign] = $l->getLinesLearned($schoolIDs);
        }
				
		//echo "<pre>"; print_r($results); echo "</pre>";
        ?>       
        <div align="center">
        	<p>Tanya / Mishna Lines Report</p>
        	
	        <table>
	        	<tr>
	        		<th>School</th>
	        		<th>Tanya Lines Pledged</th>
	        		<th>Tanya Lines Learned</th>
	        		<th>Mishna Lines Pledged</th>
	        		<th>Mishna Lines Learned</th>
	        	</tr>
	        	
	        	<?
	        	$totals = array();
				$types = array('pledged', 'learned');
	        	foreach ($schools as $id => $school) {
	        		echo "<tr><td>" . $school . "</td>";
	        		foreach ($campaigns as $campaign) {
		        		foreach ($types as $type) {
		        			if (isset($results[$type][$campaign][$id])) {
		        				echo "<td>" . number_format($results[$type][$campaign][$id]) . "</td>";
								if (isset($totals[$type][$campaign])) {
									$totals[$type][$campaign] += $results[$type][$campaign][$id];
								} else {
									$totals[$type][$campaign] = $results[$type][$campaign][$id];
								}
							} else {
								echo "<td>&nbsp;</td>";
								if (isset($totals[$type][$campaign])) {
									$totals[$type][$campaign] += 0;
								} else {
									$totals[$type][$campaign] = 0;
								}
							}
		        		} 
					}
					echo "</tr>";
	        	}
				
				echo "<tr><th align='right'>Totals</th>";
				foreach ($campaigns as $campaign) {
					foreach ($types as $type) {
						echo "<th>" . number_format($totals[$type][$campaign]) . "</th>";
					}
				}
				echo "</tr>";
	        	?>
	        </table>
	    </div>
    </body>
</html>