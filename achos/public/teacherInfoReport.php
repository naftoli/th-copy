<?
$admin_auth = array('school'); 
require('header.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<HTML>

    <HEAD>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Teacher's Info Report</title>
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
        <h1>Teacher's Info Report</h1>
        <? 
        require_once 'class.adminSchools.php';
        require_once 'class.schoolClasses.php';         
       
        $as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
        $schools = $as->getSchools();
        $schoolsClasses = array();
        
        foreach ( $schools as $id => $school ) {
            $s = new SchoolClasses($id);
            $schoolsClasses[$id] = $s->getClasses();
        } 
        
        foreach ( $schoolsClasses as $school => $classes ) {
            echo "<h2>" . $schools[$school] . "</h2>";
            echo "<table>";
            echo "<tr><th>Grade</th><th>Teacher</th><th>Email</th><th>Cell Phone</th></tr>";
            foreach ( $classes as $class ) {
                echo "<tr><td>" . $class['class_grade'] . ( empty( $class['class_sub']) ? '' : "-" . $class['class_sub'] ) . 
                    "</td><td>" . $class['class_teacher'] . "</td><td>" . $class['email'] . "</td><td>" . $class['cell'] . "</td></tr>"; 
            }
            echo "</table><br />";
        }
        ?>
    </body>
</html>