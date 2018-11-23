<?php
ini_set('display_errors', 1);
$admin_auth = array('school'); 
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.schoolClasses.php';

$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
$schools = $as->getSchools();

$ids = [];
foreach ( $schools as $school_id => $school ) {
    $sc = new SchoolClasses( $school_id );
    $grades = $sc->getClasses();
    foreach ( $grades as $grade ) {
        $ids[$school_id][] = $grade['class_id'];
    }
}
//echo "<pre>"; print_r( $ids ); echo "</pre>";
$info = [];
foreach ( $ids as $school_id => $grades ) {
    $school_name = $schools[$school_id];
    $sql = "select a.first, a.last  
            from admin_auths aa 
            join admins a using (admin_id) 
            where aa.auth in ('school','staff') 
            and aa.id = " . $school_id;
    $result = mysql_query( $sql );
    while ( $row = mysql_fetch_assoc( $result ) ) {
        $info[$school_name][] = $row['first'] . ' ' . $row['last'];
    }
    foreach ( $grades as $class_id ) {
        $sql = "select a.first, a.last  
                from admin_auths aa 
                join admins a using (admin_id) 
                where aa.auth = 'school' 
                and aa.id = " . $class_id;
        $result = mysql_query( $sql );
        while ( $row = mysql_fetch_assoc( $result ) ) {
            $info[$school_name][] = $row['first'] . ' ' . $row['last'];
        }
    }
}
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
                ?>
                <div class="cert">
                    <div class="name"><?= $name ?></div>
                    <div class="school"><?= $school_name ?></div>
                </div>
                <div style="clear: both; page-break-after: always"></div>
                <?
            }
        }
        ?>
    </body>
</html>