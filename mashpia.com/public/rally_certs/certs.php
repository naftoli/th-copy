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
//echo "<pre>"; print_r( $info ); echo "</pre>"; exit;
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset='utf8' />
        <link rel="stylesheet" href="certs.css" />
    </head>

    <body>
        <?php 
        foreach ( $info as $school_name => $names ) {
            foreach ( $names as $name ) {
                if ( !empty( $name ) ) {
                    ?>
                    <div class="cert">
                        <div class="name"><?= $name ?></div>
                        <div class="school"><?= $school_name ?></div>
                    </div>
                    <div style="clear: both; page-break-after: always"></div>
                    <?
                }
            }
            echo "<div style='height: 50px'></div><div style='clear: both; page-break-after: always'></div>";
        }
        ?>
    </body>
</html>