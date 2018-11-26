<?php
ini_set('display_errors', 1);
$admin_auth = array('school'); 
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.schoolClasses.php';

$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
$schools = $as->getSchools();

$info = [];
foreach ( $schools as $school_id => $school ) {
    $sc = new SchoolClasses( $school_id );
    $grades = $sc->getClasses();
    $prevTeacher = '';
    foreach ( $grades as $grade ) {
        if ( $grade['class_teacher'] != $prevTeacher ) {
            $info[$school][] = $grade['class_teacher'];
            $prevTeacher = $grade['class_teacher'];
        }
    }
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset='utf8' />
        <style>
            tr, th, td {
                padding: 10px;
                font-size: 14px;
                font-family: Verdana;
            }
        </style>
    </head>

    <body>
        <table>
            <tr>
                <th>School</th>
                <th>Name of Staff Member</th>
            </tr>
            <?php 
            foreach ( $info as $school_name => $names ) {
                foreach ( $names as $name ) {
                    if ( !empty( $name ) ) {
                        echo "<tr><td>" . $school_name . "</td><td>" . $name . "</td></tr>";
                    }
                }
            }
            ?>
        </table>
    </body>
</html>