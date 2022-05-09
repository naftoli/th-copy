<?
$admin_auth = array('school'); 
require('header.php');
?>
<html>
	<head>
		<title>Age Report</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<link href="admin_styles.css" rel="stylesheet" type="text/css">
	</head>
	
	<body>
		<? include('admin_header.php'); ?>
		<h1>Current Age Report</h1>

        <div class="infobox">
            This report shows how many students of each age there currently are in each school.
            For example: If it says for age 5, a total of 7, that means you need to pack 7 kapitel vov cards for that school.
        </div>
		
		<?
		require_once 'class.adminSchools.php';
        require_once 'class.schoolsUsers.php';         
       
        $as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
        $schools = $as->getSchools();
        
		$schoolsUsers = array();
        foreach ( $schools as $id => $school ) {
            $s = new SchoolsUsers( $id );
			for ($i = 5; $i < 14; $i++) {
				$ages[$id][$i] = 0;
			}
			
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
				$ages[$id][$age]++;
			}
        }
		//echo "<pre>"; print_r($ages); echo "</pre>"; exit;
		$grandtotal = 0;
		foreach ($ages as $school_id => $info) {
			echo "<h2>Current Age Report</h2>";
			echo $schools[$school_id] . "<br />";
			echo "<table><tr><th>Age</th><th>Total</th></tr>";
			foreach ($info as $age => $total) {
				echo "<tr><td>" . $age . "</td><td>" . $total . "</td></tr>";
				$grandtotal += $total;
			}
			echo "</table>";
			echo "<br />";
			echo "<div style='page-break-after: always'></div>";
		}
		//echo $grandtotal;
		?>
	</body>
</html>