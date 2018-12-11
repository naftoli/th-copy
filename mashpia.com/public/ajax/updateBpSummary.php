<?
require_once '../db.php';
require_once '../class.bpSummary.php';

$campaign = mysql_real_escape_string($_POST['campaign']);
$user = isset($_POST['user']) ? mysql_real_escape_string($_POST['user']) : 0;
$grade = isset($_POST['grade']) ? mysql_real_escape_string($_POST['grade']) : 0;
$school = mysql_real_escape_string($_POST['school']);

$bp = new BpSummary( $campaign, 'school' );
$bp->updateSummary( $school );
if ($grade) {
	$bp = new BpSummary( $campaign, 'class' );
	$bp->updateSummary( $grade );
}
if ($user) {
	$bp = new BpSummary( $campaign, 'user' );
	$bp->updateSummary( $user );
}
?>