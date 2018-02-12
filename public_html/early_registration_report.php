<?
$admin_auth = array('school'); 
require('header.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<HTML>

    <HEAD>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Registered Report</title>
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <style type='text/css'>
            table {
                font-size: 12px;
            }
            th, td {
                padding: 3px 10px;
            }
            .page-break {
                page-break-after: always;
            }
        </style>
    </HEAD>

    <BODY>
        <? include('admin_header.php'); ?>
        <h1>Registered Report</h1>
        <?
        $year = 5777;
        require_once 'class.adminSchools.php';
        require_once 'class.schoolsUsers.php';         
       
        $as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
        $schools = $as->getSchools();
        $schoolsUsers = array();
        $totals = array();
        //ksort($schools);
        foreach ( $schools as $id => $school ) {
        	//echo $id . '-' . $school . "<br />";
            $s = new SchoolsUsers( $id );
            $s->setYear($year);
			$users = $s->getUsers();
			if (!empty($users)) $schoolsUsers[$id] = $users;
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
            echo "<tr><th>Grade</th><th>Student</th><th>User ID</th><th>Start Date</th><th>Registered this year</th></tr>";
            foreach ( $users as $user ) {
                $grade = $user['class_grade'] . ( empty( $user['class_sub']) ? '' : "-" . $user['class_sub'] );
                echo "<tr><td>" . $grade . "</td><td>" . $user['first'] . " " . $user['last'] . 
                    "</td><td>" . $user['user_id'] . "</td><td>" . jdtogregorian( $user['user_start_date'] ) . 
                    "</td><td>" . $user['user_registered'] . "</td></tr>"; 
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
            echo "Total number of children registered in " . $school . "<br />";
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