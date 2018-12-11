<?
$admin_auth = array('school');
require_once 'header.php';

if ($admin_user['auth'] != 'super') {
	echo "you do not have the necessary permission to be here.";
	exit;
}

$pledges = array(
	'Pre1a'	=>	11, 
	'1'		=>	22,
	'2'		=>	44,
	'3'		=>	66,
	'4'		=>	77,
	'5'		=>	88,
	'6'		=>	100, 
	'7'		=>	113,
	'8'		=>	113 
);

$campaigns = array('5','6');

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
		$classNames[$row['class_id']] = $row['class_grade'];
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
				$userNames[$row['user_id']] = $row['first'] . ' ' . $row['last'];
			}
		}
	}
}

$sqls = array();
foreach ($campaigns as $campaign) {
	foreach ($users as $school => $info) {
		foreach ($info as $grade => $arr) {
			foreach ($arr as $user) {
				$class = $classNames[$grade];
				if (array_key_exists($class, $pledges)) {
					$pledge = $pledges[$class];
				} else {
					continue;
				}
				$sqls[] = "insert into lines_pledged 
						set campaign_id = " . $campaign . ", 
						school_id = " . $school . ", 
						class_id = " . $grade . ", 
						user_id = " . $user . ", 
						lines_pledged = " . $pledge;
			}
		}
	}
}

foreach( $sqls as $sql ) {
	mysql_query( $sql );
	//echo $sql . "<br />";
}
