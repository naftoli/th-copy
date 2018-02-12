<?
chdir("../");
$admin_auth = array('school');
require_once 'header.php';

require_once 'class.adminSchools.php';
$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth']);
$schools = $as->getSchools();

require_once 'class.balPehCampaign.php';
$tanya = BalPehCampaign::getInstance(7);
$mishna = BalPehCampaign::getInstance(8);
?>
<table>
	<tr>
		<th>School</th>
		<th>Tanya Pledged</th>
		<th>Tanya Learned</th>
		<th>Mishna Pledged</th>
		<th>Mishna Learned</th>
	</tr>
	<?
	$grandTotal['tanya']['pledged'] = 0;
	$grandTotal['tanya']['learned'] = 0;
	$grandTotal['mishna']['pledged'] = 0;
	$grandTotal['mishna']['learned'] = 0;
	
	foreach ($schools as $id => $name) {
		//$time[$id]['start'] = time();
		$totalTanya['pledged'] = $tanya->getTotalPledged( 'school', $id );
		$totalTanya['learned'] = $tanya->getTotalLearned( 'school', $id );				
		$totalMishna['pledged'] = $mishna->getTotalPledged( 'school', $id );
		$totalMishna['learned'] = $mishna->getTotalLearned( 'school', $id );
		
		$grandTotal['tanya']['pledged'] += $totalTanya['pledged'];
		$grandTotal['tanya']['learned'] += $totalTanya['learned'];
		$grandTotal['mishna']['pledged'] += $totalMishna['pledged'];
		$grandTotal['mishna']['learned'] += $totalMishna['learned'];
		
		echo "<tr><td>" . $name . "<input type='hidden' class='schoolID' value='" . $id . "' /></td>
			<td><input class='tanya pledge' type='text' size='6' value='" . number_format( $totalTanya['pledged'] ) . "' /></td>
			<td><input class='tanya learn' type='text' size='6' value='" . number_format( $totalTanya['learned'] ) . "' /></td> 
			<td><input class='mishna pledge' type='text' size='6' value='" . number_format( $totalMishna['pledged'] ) . "' /></td>
			<td><input class='mishna learn' type='text' size='6' value='" . number_format( $totalMishna['learned'] ) . "' /></td></tr>";
		//$time[$id]['end'] = time();
	} 
	echo "<tr><th align='right'>Total:</th><th>" . 
		number_format( $grandTotal['tanya']['pledged'] ) . "</th><th>" . 
		number_format( $grandTotal['tanya']['learned'] ) . "</th><th>" . 
		number_format( $grandTotal['mishna']['pledged'] ) . "</th><th>" . 
		number_format( $grandTotal['mishna']['learned'] ) . "</th></tr>";
	?>
</table>
<? //echo "<pre>"; print_r($time); echo "</pre>"; ?>
