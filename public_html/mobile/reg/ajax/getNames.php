<?
require '../../../db.php';
$school = mysql_real_escape_string( $_POST['school'] );
$grade = mysql_real_escape_string( $_POST['grade'] );

$users = array();
$sql = "select distinct last from users
		where school_id = " . $school . "
		and class_id = " . $grade . "
		and user_id not in (51946,51963,53028,53029) 
		order by last";
$result = mysql_query( $sql );
while ($row = mysql_fetch_assoc($result)) {
	$users[] = $row['last'];
}
echo json_encode( $users );
?>