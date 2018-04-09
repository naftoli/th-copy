<?
require_once( dirname(__FILE__) . '/../../../db.php' );
$subject = mysql_real_escape_string( $_POST['subject'] );

$info = array();
$sql = "SELECT medal_ord, missions_required from medals_subjects where subject_id = " . $subject;
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$info[$row['medal_ord']] = (int)$row['missions_required'];
}
echo json_encode( $info );
?>