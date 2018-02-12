<?
$admin_auth = array('school'); 
require('header.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<HTML>

    <HEAD>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Not Yet Registered Report</title>
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <style type='text/css'>
            table {
                font-size: 12px;
            }
            th, td {
                padding: 3px 10px;
                vertical-align: top;
            }
            .page-break {
                page-break-after: always;
            }
        </style>
    </HEAD>

    <BODY>
        <? include('admin_header.php'); ?>
        <h1>Not Yet Registered Report</h1>
        <? 
        require_once 'class.adminSchools.php';
        require_once 'class.schoolsUsers.php';         
       
        $as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
        $schools = $as->getSchools();
        $schoolsUsers = array();
        $totals = array();
        
        foreach ( $schools as $id => $school ) {
            $s = new SchoolsUsers( $id );
            $schoolsUsers[$id] = $s->getUsers(false, true);
        }
        
        /*
        echo "<pre>";
        print_r( $schoolsUsers );
        echo "</pre>";
         * 
         */
        
        foreach ( $schoolsUsers as $school => $users ) {
            echo "<h2>" . $schools[$school] . "</h2>";
            echo "<table>";
            echo "<tr><th>Grade</th><th>Student</th><th>User ID</th><th>Start Date</th>
            	<th>DOB</th><th>Email</th><th>Phone Numbers</th></tr>";
            foreach ( $users as $user ) {
            	//get phone numbers, email from parent account
            	$qry = "select admin_email, admin_phone_home, admin_phone_work, admin_phone_mobile 
            			from admins join admin_auths aa using (admin_id) 
            			where aa.id = " . $user['user_id'] . " 
            			and aa.auth = 'user'";
				$res = mysql_query( $qry );
				if ( mysql_num_rows( $res ) > 0 ) {
					$admin = mysql_fetch_assoc( $res );
				} else {
					$admin = null;
				}
								
                $grade = $user['class_grade'] . ( empty( $user['class_sub']) ? '' : "-" . $user['class_sub'] );
                echo "<tr><td>" . $grade . "</td><td>" . $user['first'] . " " . $user['last'] . 
                    "</td><td>" . $user['user_id'] . "</td><td>" . jdtogregorian( $user['user_start_date'] ) . 
					"</td><td>" . $user['dob'] . "</td><td>";
				if ( is_null( $admin ) ) {
                    echo "</td><td>" . $user['email'];
                } else {
                	if ( !empty( $admin['email'] ) ) {
                		echo $admin['admin_email'];
                	} else {
                		echo $user['email'];
                	}
                	echo "</td><td>";
					if ( !empty( $admin['admin_phone_home'] ) ) {
						echo $admin['admin_phone_home'] . "<br />";
					}
					if ( !empty( $admin['admin_phone_work'] ) ) {
						echo $admin['admin_phone_work'] . "<br />";
					}
					if ( !empty( $admin['admin_phone_mobile'] ) ) {
						echo $admin['admin_phone_mobile'];
					}
                }     
                echo "</td></tr>"; 
                if ( isset( $totals[$schools[$school]][$grade] ) ) 
                    $totals[$schools[$school]][$grade]++;
                else 
                    $totals[$schools[$school]][$grade] = 1; 
            }
            echo "</table><br /><div class='page-break'></div>";
        }
        
        echo "<div class='page-break'></div>";
        echo "<h2>Totals</h2>";
        foreach ( $totals as $school => $info ) {
        	$grandTotal = 0; 
            echo "Total number of children not yet registered in " . $school . "<br />";
            echo "<table>";
            echo "<tr><th>Grade</th><th>Total</th></tr>";
            foreach ( $info as $grade => $total ) {
                echo "<tr><td>" . $grade . "</td><td>" . $total . "</td></tr>";
                $grandTotal += $total;
            }
			echo "<tr><td><b>Grand Total</b></td><td><b>" . $grandTotal . "</b></td></tr>";
            echo "</table>";
            echo "<br /><br />";
            echo "<div class='page-break'></div>";
        }
        ?>
    </body>
</html>