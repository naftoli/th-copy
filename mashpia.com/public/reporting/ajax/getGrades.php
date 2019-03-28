<?php
require_once( __DIR__ . '/../../db.php' );
require_once( __DIR__ . '/../../class.schoolClasses.php' );

$school_id = mysql_real_escape_string( $_POST['school'] );
$sc = new SchoolClasses( $school_id );
$grades = $sc->getClasses();

$info = [];
foreach ( $grades as $grade ) {
    $info[$grade['class_id']] = $grade['class_grade'] . (empty($grade['class_sub']) ? '' : '-' . $grade['class_sub']);
}

echo json_encode( $info );
