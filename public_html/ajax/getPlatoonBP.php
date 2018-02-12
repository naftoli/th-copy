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
			<th>Tanya Pledged</th>
			<th>Tanya Learned</th>
			<th>Mishna Pledged</th>
			<th>Mishna Learned</th>
		</tr>
		<?
		foreach ($classes[$id] as $class) {
			
			$totalTanya['pledged'] = $tanya->getTotalPledged( 'class', $class );
			$totalTanya['learned'] = $tanya->getTotalLearned( 'class', $class );
			$totalMishna['pledged'] = $mishna->getTotalPledged( 'class', $class );
			$totalMishna['learned'] = $mishna->getTotalLearned( 'class', $class );
			
			$grandTotals['tanya']['pledged'] += $totalTanya['pledged'];
			$grandTotals['tanya']['learned'] += $totalTanya['learned'];
			$grandTotals['mishna']['pledged'] += $totalMishna['pledged'];
			$grandTotals['mishna']['learned'] += $totalMishna['learned'];

			echo "<tr><td>" . $classNames[$class] . "<input type='hidden' class='classID' value='" . $class . "' />
				<input type='hidden' class='schoolID' value='" . $id . "' /></td> 
				<td class='middle'><input type='text' class='tanya pledge' size='5' value='" . $totalTanya['pledged'] . "' /></td>
				<td class='middle'><input type='text' class='tanya learn' size='5' value='" . $totalTanya['learned'] . "' /></td>
				<td class='middle'><input type='text' class='mishna pledge' size='5' value='" . $totalMishna['pledged'] . "' /></td>
				<td class='middle'><input type='text' class='mishna learn' size='5' value='" . $totalMishna['learned'] . "' /></td></tr>";
		}
		echo "<tr><th align='right'>Total:</th><th class='middle'>" . 
			$grandTotals['tanya']['pledged'] . "</th><th class='middle'>" . 
			$grandTotals['tanya']['learned'] . "</th><th class='middle'>" . 
			$grandTotals['mishna']['pledged'] . "</th><th class='middle'>" . 
			$grandTotals['mishna']['learned'] . "</th></tr>";
		?>
	</table>
<? } ?>