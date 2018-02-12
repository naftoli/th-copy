<?
require '../../../db.php';
$school = mysql_real_escape_string( $_POST['school'] );

$grades = array();
$sql = "select class_id, class_grade, class_sub from classes where class_era = 0 and school_id = " . $school;
$result = mysql_query( $sql );
while ($row = mysql_fetch_assoc($result)) {
	$grades[$row['class_id']] = $row['class_grade'] . (empty($row['class_sub']) ? '' : '-' . $row['class_sub']);
}
echo json_encode( $grades );
?>