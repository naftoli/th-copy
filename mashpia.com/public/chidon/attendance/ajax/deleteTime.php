<?php
require_once '../../../db.php';
$id = mysql_real_escape_string( $_POST['id'] );

$sql = "delete from th_chidon_attendance_times where att_time_id = " . $id;
if ( mysql_query( $sql ) ) {
  echo json_encode([
    'success' =>  true
  ]);
} else {
  json_encode([
    'success' =>  false, 
    'error'   =>  mysql_error()
  ]);
}