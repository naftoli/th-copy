<?
require_once '../db.php';
$school = mysql_real_escape_string( $_POST['school'] );
$grade = mysql_real_escape_string( $_POST['grade'] );

$users = array();
if ($user > 0) {
	$users[] = $user;
} else {
	require_once '../class.schoolsUsers.php';
	$su = new SchoolsUsers( $school );

	if ($grade > 0) {
		$su->setClasses( array($grade) );
	} else {
		$su->setClasses( 'all' );
	}
	$ids = $su->getUserIDs();
	foreach ($ids as $grade => $info) {
		foreach ($info as $user) {
			$users[] = $user;
		}
	}
}

require_once '../class.mishnaSummary.php';
foreach ($users as $user) {
	MishnaSummary::updateSummary( $user );
}
?>