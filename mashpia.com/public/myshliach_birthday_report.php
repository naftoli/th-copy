<?
$admin_auth = array('school'); 
require('header.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<HTML>

    <HEAD>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Birthday Report</title>
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <style type='text/css'>
            table {
                font-size: 12px;
            }
            th, td {
                padding: 3px 10px;
            }
        </style>
    </HEAD>

    <BODY>
        <? include('admin_header.php'); ?>
        <h1>Birthday Report</h1>
        <? 
        require_once 'class.adminSchools.php';
        require_once 'class.schoolsUsers.php';         
       
        $as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
        $schools = $as->getSchools();
		$s = new SchoolsUsers( 61 );
        $schoolsUsers[61] = $s->getUsers();
        
        foreach ( $schoolsUsers as $school => $users ) {
            echo "<h2>" . $schools[$school] . "</h2>";
            ?>
            <table>
            	<tr>
            		<th>First Name</th>
            		<th>Last Name</th>
            		<th>Email</th>
            		<th>Address</th>
            		<th>City</th>
            		<th>State</th>
            		<th>Zip</th>
            		<th>Country</th>
            		<th>Hebrew Day</th>
            		<th>Hebrew Month</th>
            		<th>Hebrew Year</th>
            	</tr>
            <?
            foreach ( $users as $user ) {
            	$s = "select a.admin_email from admins a 
            		join admin_auths aa using (admin_id) 
            		where aa.id = " . $user['user_id'] . " 
            		and aa.auth = 'user'";
				$r = mysql_query($s);
				$row = mysql_fetch_assoc($r); 
            	$arrDOB = explode('-', $user['dob']);
                //check if dob_he should be one day further
                if ($user['dob_he_offset']) {
                    //add one to dob
                    $date = new DateTime( $dob );
                    $date->add( new DateInterval( 'P1D' ) );
                    $newDate = $date->format( 'Y-m-d' );
                    $arrDOB = explode('-', $newDate);
                }                   
                $jd = gregoriantojd($arrDOB[1], $arrDOB[2], $arrDOB[0]);
                $jewish = jdtojewish($jd);
				$arrJ = explode('/', $jewish);
                echo "<tr><td>" . $user['first'] . "</td><td>" . $user['last'] . "</td><td>" . 
                	$row['admin_email'] . "</td><td>" . $user['user_address1'] . "</td><td>" . 
                	$user['user_city'] . "</td><td>" . $user['user_state'] . "</td><td>" . 
                	$user['user_postal'] . "</td><td>" . $user['user_country'] . "</td><td>" . 
                	$arrJ[1] . "</td><td>" . $arrJ[0] . "</td><td>" . $arrJ[2] . "</td></tr>";       	
            }
            echo "</table><br />";
        }
        ?>
    </body>
</html>