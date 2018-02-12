<?
$admin_auth = array('school', 'user'); 
require('header.php');

$campaign_id = 2;

$schoolsInfo = array();
$schoolsSql = "select * from schools where school_era is null and school_id not in (82) order by school_name";
$schoolResult = mysql_query($schoolsSql);
while ($schoolRow = mysql_fetch_assoc($schoolResult)) {
	$schoolsInfo[$schoolRow['school_id']] = array(
		'name'		=> $schoolRow['school_name'], 
		'city'		=> $schoolRow['school_city'], 
		'state'		=> $schoolRow['school_state'], 
		'postal'	=> $schoolRow['school_postal'], 
		'country'	=> $schoolRow['school_country']
	);
}

foreach ($schoolsInfo as $id => $school) {
	$rSql = "select count(*) as total from users where school_id = $id and user_registered > 0";
	$rResult = mysql_query($rSql);
	$rRow = mysql_fetch_assoc($rResult);
	$registered = $rRow['total'];
	if ($registered) {
		$schoolsInfo[$id]['registered'] = $registered;
		$pSql = "select lines_pledged from lines_pledged where school_id = $id and campaign_id = $campaign_id";
		$pResult = mysql_query($pSql);
		$pRow = mysql_fetch_assoc($pResult);
		$schoolsInfo[$id]['pledged'] = empty($pRow['lines_pledged']) ? 0 : $pRow['lines_pledged'];
	} else {
		unset($schoolsInfo[$id]);
	}
}

$schools = array();
if ($admin_user['auths']['school']) {
	require_once 'class.adminSchools.php';	
	$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
	$schools = $as->getSchools();
} else {
	include("classes/admin.php");
	$sql = "SELECT * FROM admins WHERE admin_id=" . $admin_user['admin_id'];
	$query = mysql_query($sql);
	$row = mysql_fetch_assoc($query);
	$admin = new admin($row);
	$admin->get_children();
	foreach ($admin->children as $child) {
		$schools[$child->school_id] = $child->school_name;
	}
}

require_once 'class.tanya.php';
$tanya = new Tanya;

require_once 'class.mishna.php';
$mishna = new Mishna;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<HTML>

    <HEAD>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Tanya Report</title>
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <script type="text/javascript" src="scripts/jquery-1.8.3.js"></script>
        <script type="text/javascript" src="scripts/jquery.styleselect.js"></script>
        <style type='text/css'>
            p {
                font-size: 12px;
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
            .page-break {
                page-break-after: always;
            }
            @media print {
                .no-print {
                    display: none;
                }
            }
        </style>
        <script type="text/javascript">
            
        </script> 
    </HEAD>

    <BODY>
        <? include('admin_header.php'); ?>
        <h1 class="no-print">Tanya Report</h1>
        
        <?
        //if (isset($_POST['submit'])) {
        	echo "<pre>";
			//print_r($_POST);
			echo "</pre>";
			//exit;
			
			//$selectedSchool = $_POST['school'];
			//$selectedGrade = $_POST['grade'];
			//$user = $_POST['user'];
			
			$totalRegistered = 0;
			$totalPledged = 0;
			$totalLearned = 0;
			$allInfo = array();
			foreach ($schoolsInfo as $id => $school) {
				$total = $tanya->getTotal('school', $id);
				$totalRegistered += $school['registered'];
				$totalPledged += $school['pledged'];
				$totalLearned += $total; 
				
				//add all info to array
				$allInfo[$id]['name'] = $school['name'];
				$allInfo[$id]['city'] = $school['city'];
				$allInfo[$id]['state'] = $school['state'];
				$allInfo[$id]['postal'] = $school['postal'];
				$allInfo[$id]['country'] = $school['country'];
				$allInfo[$id]['registered'] = $school['registered'];
				$allInfo[$id]['pledged'] = $school['pledged'];
				$allInfo[$id]['learned'] = $total;
			}
			
			
			if (isset($_GET['sort'])) {
				//sort array by sort option
				$registered = array();
				$pledged = array();
				$learned = array();
				foreach ($allInfo as $id => $info) {
					$registered[$id] = $info['registered'];
					$pledged[$id] = $info['pledged'];
					$learned[$id] = $info['learned'];
				}
				switch ($_GET['sort']) {
					case 'registered':
						array_multisort($registered, SORT_DESC, $allInfo);
						break;
					case 'pledged':
						array_multisort($pledged, SORT_DESC, $allInfo);
						break;
					case 'learned':
						array_multisort($learned, SORT_DESC, $allInfo);
						break;
				}
			}
			             
	        echo "<div align='center' class='no-print'>";
	        echo "<input type='button' value='Print' onclick='window.print()' />";
	        echo "</div>";
			
			//after first 22 schools show new page
			$i = 0;
			
			echo "<h2>Army Wide Tanya Report</h2>";
			echo "<table>";
			echo "<tr><th>School Name</th><th>Address</th>
				<th><a href='new_tanya_report.php?sort=registered'>Registered Chayolim</a><br /><span class='no-print'>(click to sort by)</span></th>
				<th><a href='new_tanya_report.php?sort=tanya'>Tanya Bal Peh Lines Learned</a><br /><span class='no-print'>(click to sort by)</span></th>
				<th><a href='new_tanya_report.php?sort=mishna'>Mishanyos Bal Peh Lines Learned</a><br /><span class='no-print'>(click to sort by)</span></th>
				<th><a href='new_tanya_report.php?sort=maos'>Maos Chitim Pledges</a><br /><span class='no-print'>(click to sort by)</span></th>				
				<th>Average per Registered Chayol</th></tr>";
			
			foreach ($allInfo as $school) {
				if ($i++ == 22) {
					echo "</table><div class='page-break'></div>";
					echo "<h2>Army Wide Tanya Report</h2>";
					echo "<table>";
					echo "<tr><th>School Name</th><th>Address</th>
						<th><a href='new_tanya_report.php?sort=registered'>Registered Chayolim</a><br /><span class='no-print'>(click to sort by)</span></th>
						<th><a href='new_tanya_report.php?sort=tanya'>Tanya Bal Peh Lines Learned</a><br /><span class='no-print'>(click to sort by)</span></th>
						<th><a href='new_tanya_report.php?sort=mishna'>Mishanyos Bal Peh Lines Learned</a><br /><span class='no-print'>(click to sort by)</span></th>
						<th><a href='new_tanya_report.php?sort=maos'>Maos Chitim Pledges</a><br /><span class='no-print'>(click to sort by)</span></th>				
						<th>Average per Registered Chayol</th></tr>";
				}
				echo "<tr><td>" . $school['name'] . "</td><td>" . $school['city'] . ", " . $school['state'] . "<br />" . 
						$school['postal'] . "  " . $school['country'] . "</td><td>" . 
						number_format($school['registered'], 0, '', ',') . "</td><td>" . 
						number_format($school['pledged'], 0, '', ',') . "</td><td>" . 
						number_format($school['learned'], 0, '', ',') . "</td><td>" . 
						number_format($school['learned'] / $school['registered'], 2) . "</td></tr>";
			}
					
			echo "<tr><th colspan='2' align='right'>Total:</th><th>" . number_format($totalRegistered, 0, '', ',') . 
				"</th><th>" . number_format($totalPledged, 0, '', ',') . "</th><th>" . 
				number_format($totalLearned, 0, '', ',') . "</th><th>" . 
				number_format($totalLearned / $totalRegistered, 2) . "</th></tr>";
			echo "</table>";
			echo "<div class='page-break'></div>";
			
			foreach ($schools as $id => $school) {
				//if ($selectedSchool > 0 && $selectedSchool != $id) continue;
				$users = array();
				$uSql = "select u.user_id, u.first, u.last, c.class_id, c.class_grade, c.class_sub 
						from users u 
						join classes c using (class_id) 
						where u. school_id = $id 
						and u.user_registered > 0 
						order by class_grade, class_sub, last, first";
				$uResult = mysql_query($uSql);
				while ($uRow = mysql_fetch_assoc($uResult)) {
					$grade = $uRow['class_id'] . ":" . $uRow['class_grade'] . (!empty($uRow['class_sub']) ? '-' . $uRow['class_sub'] : '');
					$users[$id][$grade][$uRow['user_id']] = $uRow['first'] . ' ' . $uRow['last'];
				}
			}
			
			$totalRegistered = 0;
			$totalLearned = 0;	
			foreach ($users as $id => $users) {
				echo "<h2>School Wide Tanya Report</h2>";
				echo "<table>";	
				echo "<tr><th>Teacher</th><th>Class Grade</th><th>Registered Chayolim</th><th>Total Lines Learned</th>";
				echo "<th>Average per Registered Chayol</th></tr>";			
				foreach ($users as $grade => $students) {
					$gradeInfo = explode(":", $grade);
					//if ($selectedGrade > 0 && $selectedGrade != $gradeInfo[0]) continue;
										
					$cSql = "select * from classes where class_id = " . $gradeInfo[0];
					$cResult = mysql_query($cSql);
					$cRow = mysql_fetch_assoc($cResult);
					
					$rcSql = "select count(*) as total from users where user_registered > 0 and class_id = " . $gradeInfo[0];
					$rcResult = mysql_query($rcSql);
					$rcRow = mysql_fetch_assoc($rcResult);
					
					echo "<tr><td>" . $cRow['class_teacher'] . "</td><td>" . $gradeInfo[1] . "</td><td>";
					$cTotal = $rcRow['total'];
					echo $cTotal . "</td><td>";
					$cLines = $tanya->getTotalForClass($gradeInfo[0]);
					echo $cLines . "</td><td>" . number_format($cLines / $cTotal, 2) . "</td></tr>";
					$totalRegistered += $cTotal;
					$totalLearned += $cLines;
				}
				echo "<tr><th colspan='2' align='right'>Total:</th><th>" . number_format($totalRegistered, 0, '', ',') . "</th><th>" . 
					number_format($totalLearned, 0, '', ',') . "</th><th>" . 
					number_format($totalLearned / $totalRegistered, 2) . "</th></tr>";
				echo "</table>";
				echo "<div class='page-break'></div>";
				
				foreach ($users as $grade => $students) {
					$gradeInfo = explode(":", $grade);
					//if ($selectedGrade > 0 && $selectedGrade != $gradeInfo[0]) continue;
						
					$total = 0;
					echo "<h2>Grade " . $gradeInfo[1] . "</h2>";
					echo "<table>";
					echo "<tr><th>Chayol</th><th>Lines Learned</th></tr>";
					foreach ($students as $userID => $student) {
						//if ($user > 0 && $user != $userID) continue;
						$numLines = $tanya->getTotalForUser($userID);
						echo "<tr><td>" . $student . "</td><td>" . 
							 $numLines . "</td></tr>";
						$total += $numLines;
					}
					echo "<tr><th align='right'>Total:</th><th>" . $total . "</th></tr>";
					echo "</table>";
					echo "<div class='page-break'></div>";
				}
			}
		?>
    </body>
</html>