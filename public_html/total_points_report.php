<?php
$admin_auth = array('school', 'user'); 
require('header.php');

require_once 'class.adminSchools.php';
require_once 'class.schoolsUsers.php';
require_once 'class.points.php';

$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
$schools = $as->getSchools();

$users = array();
foreach ( $schools as $id => $name ) {
    $su = new SchoolsUsers( $id );
    $users[$id] = $su->getUsers();
}
//echo "<pre>"; print_r($users); echo "</pre>"; exit;
?>
<!DOCTYPE html>
<HTML>
    <HEAD>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Total Miles Report</title>
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <style>
            tr, th, td {
                padding: 5px;
                font-size: 12px;
            }
        </style>
    </HEAD>

    <BODY>
        <? include('admin_header.php'); ?>
        <h1>Total Miles Report</h1>
        
        <table>
            <tr>
                <th>Grade</th>
                <th>Student</th>
                <th>Total Miles</th>
            </tr>
            <?php
            foreach ($users as $schoolID => $other) {
                foreach ($other as $user) {
                    $p = new Points( $user['user_id'] );
                    $points = $p->getTotalPoints();
                    $grade = $user['class_grade'] . (empty($user['class_sub']) ? '' : '-' . $user['class_sub']);
                    echo "<tr><td>" . $grade . "</td><td>" . $user['first'] . ' ' . $user['last'] . "</td><td>" . $points . "</td></tr>";
                }
            }
            ?>
        </table>
    </body>
</html>