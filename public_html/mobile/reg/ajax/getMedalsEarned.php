<?
require '../../../db.php';
$user_id = mysql_real_escape_string( $_POST['user_id'] );
$subject = mysql_real_escape_string( $_POST['subject'] );

$medals = array();
$sql = "select medal_ord, date_awarded from medal_marks where user_id = " . $user_id . " and subject_id = " . $subject;
$result = mysql_query( $sql );
while ($row = mysql_fetch_assoc( $result )) {
	$awarded = jdtogregorian($row['date_awarded']);
	$medals[$row['medal_ord']] = $awarded;
}
$info['medals'] = $medals;

$sql = "SELECT SUM( mission_count ) as total from date_tasks_mission_marks where user_id = " . $user_id . " and subject_id = " . $subject;
$result = mysql_query($sql);
$row = mysql_fetch_assoc($result);
$info['total'] = $row['total'];

echo json_encode($info);
?>