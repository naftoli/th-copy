<?
chdir("../");
$admin_auth = array('school');
require_once 'header.php';

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
		$classNames[$row['class_id']] = $row['class_grade'] . (empty($row['class_sub']) ? '' : '-' . $row['class_sub']);
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

require_once 'class.balPehCampaign.php';
$tanya = BalPehCampaign::getInstance(7);
$mishna = BalPehCampaign::getInstance(8);

foreach ($schools as $id => $name) {
	echo "<h2>" . $name . "</h2>";
	$grandTotals['tanya']['pledged'] = 0;
	$grandTotals['tanya']['learned'] = 0;
	$grandTotals['mishna']['pledged'] = 0;
	$grandTotals['mishna']['learned'] = 0;
	?>
	<table>
		<tr>
			<th>Grade</th>
			<th>Student</th>
			<th>Tanya Pledged</th>
			<th>Tanya Learned</th>
			<th>Mishna Pledged</th>
			<th>Mishna Learned</th>
		</tr>
		<?
		foreach ($classes[$id] as $class) {
			foreach ($users[$id][$class] as $user) {
				
				$totalTanya['pledged'] = $tanya->getTotalPledged( 'user' , $user );
				$totalTanya['learned'] = $tanya->getTotalLearned( 'user', $user );
				$totalMishna['pledged'] = $mishna->getTotalPledged( 'user', $user );
				$totalMishna['learned'] = $mishna->getTotalLearned( 'user', $user );
				
				$grandTotals['tanya']['pledged'] += $totalTanya['pledged'];
				$grandTotals['tanya']['learned'] += $totalTanya['learned'];
				$grandTotals['mishna']['pledged'] += $totalMishna['pledged'];
				$grandTotals['mishna']['learned'] += $totalMishna['learned'];
				
				echo "<tr><td>" . $classNames[$class] . "</td><td>" . $userNames[$user] . 
					"<input type='hidden' class='userID' value='" . $user . "' />
					<input type='hidden' class='classID' value='" . $class . "' /> 
					<input type='hidden' class='schoolID' value='" . $id . "' /></td>
					<td class='middle'><input type='text' size='5' class='tanya pledge' value='" . $totalTanya['pledged'] . "' /></td>
					<td class='middle'><input type='text' size='5' class='tanya learn' value='" . $totalTanya['learned'] . "' /></td>
					<td class='middle'><input type='text' size='5' class='mishna pledge' value='" . $totalMishna['pledged'] . "' /></td>
					<td class='middle'><input type='text' size='5' class='mishna learn' value='" . $totalMishna['learned'] . "' /></td></tr>";
			 }
		}
		?>
	</table>
<? } ?>