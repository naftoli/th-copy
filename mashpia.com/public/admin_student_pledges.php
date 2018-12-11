<?
$admin_auth = array('user'); 
require('header.php');

$campaigns = array(
	3 => 'tanya',
	4 => 'mishna'
	); //tanya, mishna yud alef nissan 5774
require_once 'class.lineCampaigns.php';

require_once 'class.schoolsUsers.php';
$s = new SchoolsUsers(61);
$s->setClasses('all');
$users = $s->getUsers();
$userIDs = $s->getUserIDs();
$userNames = $s->getUserNames();

require_once 'class.maosChittim.php';
$m = new MaosChittim(5774);
 
require_once 'class.schoolClasses.php';
$sc = new SchoolClasses(61);
$grades = $sc->getClasses();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<HTML>

    <HEAD>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Yud Alef Nissan Report</title>
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <script type="text/javascript" src="scripts/jquery-1.8.3.js"></script>
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
                text-align: center;
            }
            td:first-child, th:first-child {
            	border-left: 1px solid #C0C0C0;
            }
            tr:first-child {
            	border-top: 1px solid #C0C0C0;
            }
            #mainTable td:nth-child(2) {
            	text-align: left;
            }
            @media print{
	            .no-print {
	            	display: none;
	            }
	        }
        </style>
        <script>
        	$(function() {
        		$('#grade').change(function() {
        			var grade = $(this).val();
        			if (grade > 0) {
        				window.location = 'admin_student_pledges.php?grade=' + grade;
        			} else {
        				window.location = 'admin_student_pledges.php';
        			}
        		});
        	});
        </script>
    </head>
    
    <body>
    	<? include('admin_header.php'); ?>
        <h1 class="no-print">Yud Alef Nissan Report</h1> 
        
        Filter by Platoon: 
        <select name='grade' id='grade'>
        	<option value='0'>All</option>
        	<?
        	foreach ($grades as $grade) {
        		echo "<option value='" . $grade['class_grade'];
				if (isset($_GET['grade']) && $grade['class_grade'] == $_GET['grade'])
					echo "' selected='selected";
				 echo "'>" . $grade['class_grade'] . "</option>";
        	} 
        	?>
        </select><br />
        
        <?
        $results = array();
		$mChitim = array();
    	foreach ($userIDs as $grade => $ids) {
    		foreach ($ids as $user_id) {
    			foreach ($campaigns as $id => $campaign) {
		        	$l = new LineCampaigns($id);
        			$results[$grade][$user_id][$campaign]['pledged'] = $l->getLinesPledgedByStudent($user_id);
					$learned = $l->getLinesLearned(array($user_id), 'user');
					$results[$grade][$user_id][$campaign]['learned'] = $learned[$user_id];
				}
				$pledged = $m->getStudentPledges($user_id);
				$mChitim[$grade][$user_id] = $pledged[$user_id]; 
        	}
        }
		
		//echo "<pre>"; print_r($results); print_r($mChitim); echo "</pre>"; exit;
        ?>       
        <div align="center">
        	<p>Yud Alef Nissan Report</p>
        	
	        <table id='mainTable'>
	        	<tr>
	        		<th>Grade</th>
	        		<th>Chayol</th>
	        		<th>Tanya Pledged</th>
	        		<th>Tanya Learned</th>
	        		<th>Mishna Pledged</th>
	        		<th>Mishna Learned</th>
	        		<th>Mo'os Chitim Pledged</th>
	        	</tr>
	        	
	        	<?
	        	$totals = array();
				$mcTotal = array();
				$types = array('pledged', 'learned');
	        	foreach ($results as $grade => $info) {
	        		if (isset($_GET['grade']) && $grade != $_GET['grade']) continue;
	        		foreach ($info as $user_id => $arr) {
		        		echo "<tr><td>" . $grade . "</td>";
						echo "<td>" . $userNames[$grade][$user_id] . "</td>";
		        		foreach ($arr as $campaign => $info) {
		        			foreach ($types as $type) {
		        				echo "<td>" . number_format($results[$grade][$user_id][$campaign][$type]) . "</td>";
								if (isset($totals[$grade][$campaign][$type])) {
									$totals[$grade][$campaign][$type] += $results[$grade][$user_id][$campaign][$type];
								} else {
									$totals[$grade][$campaign][$type] = $results[$grade][$user_id][$campaign][$type];
								}
							}
						}
						echo "<td>" . number_format($mChitim[$grade][$user_id]) . "</td>";
						if (isset($mcTotal[$grade])) {
							$mcTotal[$grade] += $mChitim[$grade][$user_id];
						} else {
							$mcTotal[$grade] = $mChitim[$grade][$user_id];
						}
						echo "</tr>";
					}
	        	}
			echo "</table>";
			
			$grandTotal = array();
			$mcGrandTotal = 0;
			echo "<h2>Totals per Grade</h2>";
			echo "<table>";	
			echo "<tr><th>Grade</th><th>Tanya Pledges</th><th>Tanya Learned</th>
				<th>Mishna Pledges</th><th>Mishna Learned</th><th>Mo'os Chitim Pledges</th></tr>";			
			foreach ($totals as $grade => $info) {
				echo "<tr><td>" . $grade . "</td>";
				foreach ($info as $campaign => $arr) {
					foreach ($arr as $type => $total) {
						echo "<td>" . number_format($total) . "</td>";
						if (isset($grandTotal[$campaign][$type])) {
							$grandTotal[$campaign][$type] += $total;
						} else {
							$grandTotal[$campaign][$type] = $total;
						}
					}
				}
				echo "<td>" . number_format($mcTotal[$grade]) . "</td>";
				$mcGrandTotal += $mcTotal[$grade];
				echo "</tr>";
			}
	        ?>
	        </table>
	        
	        <h2>Grand Totals</h2>
	        <table>
	        	<tr>
	        		<th>Tanya Pledges</th>
	        		<th>Tanya Learned</th>
					<th>Mishna Pledges</th>
					<th>Mishna Learned</th>
					<th>Mo'os Chitim Pledges</th>
	        	</tr>
	        	<tr>
		        	<?
		        	foreach ($grandTotal as $campaign => $info) {
		        		foreach ($info as $type => $total) {
		        			echo "<td>" . number_format($total) . "</td>";
		        		}
		        	}
					echo "<td>" . number_format($mcGrandTotal) . "</td>";
		        	?>
		        </tr>
	        </table>
	    </div>
	</body>
</html>