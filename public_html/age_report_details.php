<?
$admin_auth = array('school'); 
require('header.php');
?>
<html>
	<head>
		<title>Age Report</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<link href="admin_styles.css" rel="stylesheet" type="text/css">
		<style>
			tr, th, td {
				padding: 5px;
				font-size: 12px;
			}
			.nextPage {
				clear: both; 
				page-break-after: always;
				width: 100%;
			}
			@media print {
				.noPrint {
					display: none;
				}
			}
		</style>
	</head>
	
	<body>
		<? include('admin_header.php'); ?>
		<h1 class="noPrint">Current Age Report</h1>
		
		<?
		require_once 'class.adminSchools.php';
        require_once 'class.schoolsUsers.php';         
       
        $as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
        $schools = $as->getSchools();
        
		$schoolsUsers = array();
        foreach ( $schools as $id => $school ) {
            $s = new SchoolsUsers( $id );
			
			//get all users and filter out correct ones
			$users = $s->getUsers();
			$temp = array();
			$dob = array();
			foreach ( $users as $user ) {
				if ( empty( $user['dob'] ) )
					continue;
				
				$datetime1 = new DateTime($user['dob']);
				$datetime2 = new DateTime();
				$interval = $datetime1->diff($datetime2);
				$age = intval( $interval->format("%y") );
				
				if ($age < 5 || $age > 13) continue;
				$grade = $user['class_grade'] . ( empty( $user['class_sub']) ? '' : "-" . $user['class_sub'] );
				$ages[$id][$grade][$user['first'] . ' ' . $user['last']] = $age;
			}
        }
		//echo "<pre>"; print_r($ages); echo "</pre>"; exit;
		foreach ($ages as $school_id => $info) {
			$totals = array();
			echo "<h2>Age Details Report</h2>";
			echo "<div style='float: left; width: 50%'>";
			echo $schools[$school_id] . "<br />";
			echo "<table><tr><th>Grade</th><th>Student</th><th>Age</th></tr>";
			foreach ($info as $grade => $students) {
				foreach ($students as $student => $age) {
					echo "<tr><td>" . $grade . "</td><td>" . $student . "</td><td>" . $age . "</td></tr>";
					if (isset($totals[$age])) {
						$totals[$age]++;
					} else {
						$totals[$age] = 1;
					}
				}
			}
			echo "</table></div>";
			echo "<div style='float: right; width: 10%'>";
			echo "<h3>Totals</h3>";
			echo "<table>";
			echo "<tr><th>Age</th><th>Total</th></tr>";
			foreach ($totals as $age => $total) {
				echo "<tr><td>" . $age . "</td><td>" . $total . "</td></tr>";
			}
			echo "</table></div>";
			echo "<div class='nextPage'>&nbsp;</div>";
		}
		?>
	</body>
</html>