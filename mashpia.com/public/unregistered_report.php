<?
$admin_auth = array('school'); 
require('header.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<HTML>

    <HEAD>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Unregistered Report</title>
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
        <h1>Unregistered Report</h1>
        <? 
        require_once 'class.adminSchools.php';
        require_once 'class.schoolsUsers.php';         
       
        $as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
        $schools = $as->getSchools();
        $schoolsUsers = array();
        $totals = array();
        
        foreach ( $schools as $id => $school ) {
            $s = new SchoolsUsers( $id );
            $schoolsUsers[$id] = $s->getUsers(false);
        }
        
        /*
        echo "<pre>";
        print_r( $schoolsUsers );
        echo "</pre>";
         * 
         */
         
        foreach ( $schoolsUsers as $school => $users ) {
            $grades = array();
            /*
            echo "<h2>" . $schools[$school] . "</h2>";
            echo "<table>";
            echo "<tr><th>Grade</th><th>Student</th></tr>";
             * 
             */
            foreach ( $users as $user ) {
                $grade = $user['class_grade'] . ( empty( $user['class_sub']) ? '' : "-" . $user['class_sub'] );
                $grades[$grade][] = $user['first'] . " " . $user['last'];
                /*
                echo "<tr><td>" . $grade . "</td><td>" . $user['first'] . " " . $user['last'] . "</td></tr>"; 
                if ( isset( $totals[$schools[$school]][$grade] ) ) 
                    $totals[$schools[$school]][$grade]++;
                else 
                    $totals[$schools[$school]][$grade] = 1; 
                 * 
                 */
            }
            echo "</table><br /><div class='page-break'></div>";
            foreach ($grades as $grade => $names) {
                echo "<h2>" . $schools[$school] . '-' . $grade . "</h2>";
                echo "<table>";
                foreach ($names as $name) {
                    echo "<tr><td>" . $name . "</td></tr>";
                }
                echo "</table>";
                echo "<div class='page-break'></div>";
            }
        }
        /*
        echo "<div class='page-break'></div>";
        echo "<h2>Totals</h2>";
        foreach ( $totals as $school => $info ) {
            $grandTotal = 0; 
            echo "Total number of children not registered in " . $school . "<br />";
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
         * 
         */
        ?>
    </body>
</html>