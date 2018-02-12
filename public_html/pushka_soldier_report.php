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
            h2, p {
            	font-size: 24px;
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
					foreach ( $rest as $names ) {
						echo "<h2>" . $schools[$school] . "-" . $grade . "</h2>";
						echo "<br /><br /><p>Please confirm that your child’s HEBREW NAME is spelled correctly.<br /><br />";
						echo $names['name'] . "<br />";
						if ( empty( $names['hname'] ) ) echo "<i>No Hebrew Name Available</i>";
						else echo $names['hname'];
						echo "<br /><br />Parent's Signature:_________________________</p><br /><br />";
						echo "<div style='page-break-after: always'></div>";
					}
				}
			}		
        }
        ?>
 	</body>
 </html>