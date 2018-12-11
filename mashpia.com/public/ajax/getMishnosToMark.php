<?
require_once '../db.php';
$school = mysql_real_escape_string( $_POST['school'] );
$grade = mysql_real_escape_string( $_POST['grade'] );
$user = isset( $_POST['user'] ) ? mysql_real_escape_string( $_POST['user'] ) : 0;

require_once '../class.mishnaInfo.php';
$assigned = MishnaInfo::getAssignedAll( $school, $grade, $user, true );
echo json_encode( $assigned );
?>