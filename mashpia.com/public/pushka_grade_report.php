<?
$admin_auth = array('school'); 
require('header.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<HTML>

    <HEAD>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Pushka Report</title>
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <script type="text/javascript" src="scripts/jquery-1.8.3.js"></script>
        <script type="text/javascript" src="mobile/reg/js/keyboard.js" charset="UTF-8"></script>
        <link rel="stylesheet" type="text/css" href="mobile/reg/css/keyboard.css">
        <style type='text/css'>
            table {
                font-size: 14px;
            }
            th, td {
                padding: 5px 10px;
            }
            @media print {
            	.noPrint {
            		display: none;
            	}
            }
    	</style>
    </HEAD>

    <BODY>
        <? include('admin_header.php'); ?>
        <h1 class="noPrint">Pushka Report</h1>
        <? 
        require_once 'class.adminSchools.php';
        require_once 'class.schoolsUsers.php';         
       
        $as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
        $schools = $as->getSchools();
        $schoolsUsers = array();
		
        foreach ( $schools as $id => $school ) {
            $s = new SchoolsUsers( $id );
			$users = $s->getUsers();
            $schoolsUsers[$id] = $users;
        }
		
		$grades = array();
		foreach ( $schoolsUsers as $school => $users ) {
			foreach ( $users as $user ) {
				$name = $user['first'] . ' ' . $user['last'];
				$hname = $user['he_name'];
				$grade = $user['class_grade'] . ( empty( $user['class_sub'] ) ? '' : '-' . $user['class_sub'] );
				$grades[$school][$user['class_id']][$grade][] = array(
					'name'	=>	$name, 
					'hname'	=>	$hname
				);
			}
		}
		
        foreach ( $grades as $school => $info ) {			
			foreach ( $info as $other ) {
				foreach ( $other as $grade => $rest ) {
					echo "<h2>" . $schools[$school] . "-" . $grade . "</h2>";
					echo "<p>Please confirm that all the names in your class are spelled correctly.</p>";
					echo "<table><tr><th>Name</th><th>Hebrew Name for Pushka</th></tr>";
					foreach ( $rest as $names ) {
						echo "<tr><td>" . $names['name'] . "</td><td>" . $names['hname'] . "</td></td></tr>";
					}
					echo "</table><br /><div style='page-break-after: always'></div>";
				}
			}		
        }
        ?>
 	</body>
 </html>