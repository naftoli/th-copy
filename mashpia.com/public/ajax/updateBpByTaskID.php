<?
exit;
require_once '../db.php';
require_once '../class.bpSummary.php';

$task = mysql_real_escape_string($_POST['task']);
$user = mysql_real_escape_string($_POST['user']);

$sql = "select short_name from date_tasks where date_task_id = " . $task;
$result = mysql_query($sql);
$row = mysql_fetch_assoc($result);
$short = $row['short_name'];
$campaign = 0;
if ($short == 'Tanya for the Rebbe') $campaign = 9;
else if ($short == 'Mishnayos for the Rebbe') $campaign = 10;

if ($campaign) {
	$sql = "select school_id, class_id from users where user_id = " . $user;
	$result = mysql_query($sql);
	$row = mysql_fetch_assoc($result);
	$school = mysql_real_escape_string( $row['school_id'] );
	$grade = mysql_real_escape_string( $row['class_id'] );
	
	$bp = new BpSummary( $campaign, 'user' );
	$bp->updateSummary( $user );
	if ($school) {	
		$bp = new BpSummary( $campaign, 'school' );
		$bp->updateSummary( $school );
	}
	if ($grade) {
		$bp = new BpSummary( $campaign, 'class' );
		$bp->updateSummary( $grade );
	}
}
?>