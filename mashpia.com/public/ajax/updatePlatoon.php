<?php
require '../db.php';

$class_id = mysql_real_escape_string( $_POST['class_id'] );
$class_teacher = mysql_real_escape_string( $_POST['class_teacher'] );
$email = mysql_real_escape_string( $_POST['email'] );
$cell = mysql_real_escape_string( $_POST['cell'] );

$sql = "UPDATE classes set
        class_teacher = '$class_teacher',
        email = '$email',
        cell = '$cell'
        WHERE class_id = $class_id;";

if ( mysql_query( $sql ) ) {
    echo json_encode( [ 'success' => true ] );
} else {
    echo json_encode( [ 'success' => false, 'message' => mysql_error() ] );
}
