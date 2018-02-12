<?
$admin_auth = array('school');
require 'header.php';
require_once 'class.bpSummary.php';

$campaigns = array(5,6);

require_once 'class.adminSchools.php';
$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth']);
$schools = $as->getSchools();
$schoolIDs = array();
foreach ($schools as $id => $school) {
	$schoolIDs[] = $id;
}

$classes = array();
$classNames = array();
foreach ($schoolIDs as $id) {
	$sql = "select * from classes where school_id = " . $id . " and class_era = 0 order by class_grade, class_sub";
	$result = mysql_query($sql);
	while ($row = mysql_fetch_assoc($result)) {
		$classes[$id][] = $row['class_id'];
	}
}

$users = array();
$userNames = array();
foreach ($classes as $school => $grades) {
	foreach ($grades as $grade) {
		$sql = "select * from users where class_id = " . $grade . " and user_registered > 0 order by last, first";
		$result = mysql_query($sql);
		if (mysql_num_rows($result) > 0) {
			while ($row = mysql_fetch_assoc($result))	{
				$users[$school][$grade][] = $row['user_id'];
			}
		}
	}
}

foreach ($campaigns as $campaign) {
	foreach ($users as $school => $info) {
		$bp = new BpSummary($campaign, 'school');
		$bp->updateSummary($school);
		foreach ($info as $grade => $other) {
			$bp = new BpSummary($campaign, 'class');
			$bp->updateSummary($grade);
			foreach ($other as $user) {
				$bp = new BpSummary($campaign, 'user');
				$bp->updateSummary($user);
			}
		}
	}
}
?>