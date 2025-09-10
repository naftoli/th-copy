<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = array('school'); 
require('header.php');

require 'class.myShliachHachayol.php';

if (isset($_GET['id'])) {
	$id = $_GET['id'];
} else {
	$id = 61;
}

$hachayol = new MyShliachHachayol(false, $id);
$admins = $hachayol->getAdmins();
$children = $hachayol->getChildren();
$shipped_ids = [172247, 195746, 71578, 168952, 175042, 189749, 194010, 197148, 198983, 199754, 200132, 197476, 193120, 150479, 5794, 182987, 193229, 686, 1145, 3574, 71046, 71487, 119027, 140768, 150580, 168402, 172411, 194120, 196727, 197335, 198225, 199255, 199491, 199533, 200381, 200847, 200973, 200998, 118, 436, 1264, 6205, 6356, 6459, 6561, 6646, 6725, 6804, 6823, 6848, 7118, 8224, 71227, 71314, 71580, 128799, 129140, 129268, 129283, 129574, 140230, 140771, 141151, 150317, 150658, 150844, 167609, 167705, 167857, 167963, 168078, 168294, 169109, 169281, 169520, 170626, 170928, 171631, 172009, 172192, 172237, 172269, 172307, 172627, 173897, 174022, 174986, 175122, 175218, 175276, 175309, 175427, 175559, 175665, 175845, 175952, 177864, 178463, 178830, 179109, 179599, 180116, 181462, 181463, 181468, 181479, 181501, 181545, 182089, 182202, 182696, 183053, 183088, 185412, 192249, 192492, 192674, 192761, 192793, 192799, 192889, 192897, 192921, 193072, 193213, 193235, 193395, 193680, 193695, 193756, 193836, 193882, 193895, 193935, 194018, 194063, 194081, 194163, 194339, 194343, 194546, 194615, 194651, 194729, 194732, 194769, 194783, 194875, 194906, 194928, 195429, 195465, 195473, 195514, 195548, 195553, 195662, 195755, 195786, 195950, 196031, 196134, 196284, 196466, 196555, 196643, 196859, 196947, 197062, 197067, 197069, 197094, 197102, 197198, 197333, 197336, 197369, 197423, 197548, 197571, 197580, 197593, 197614, 197713, 197754, 197810, 197895, 197954, 198152, 198285, 198398, 198446, 198500, 198507, 198509, 198520, 198573, 198592, 198742, 198758, 198830, 198845, 198870, 198924, 198991, 199012, 199018, 199102, 199108, 199144, 199226, 199265, 199282, 199283, 199303, 199386, 199419, 199432, 199503, 199513, 199592, 199608, 199651, 199660, 199680, 199684, 199711, 199739, 199744, 199764, 199777, 199883, 199927, 200056, 200057, 200068, 200073, 200075, 200077, 200145, 200211, 200267, 200280, 200328, 200330, 200349, 200356, 200362, 200544, 200570, 200799, 200904, 200914];

//echo "<pre>"; print_r($children); echo "</pre>";
// CSV download
if ( isset($_GET['download']) && $_GET['download'] === 'csv' ) {
	header('Content-Type: text/csv; charset=utf-8');
	header('Content-Disposition: attachment; filename="hachayol_report.csv"');
	$out = fopen('php://output', 'w');
	// headers
	fputcsv($out, array(
		'Number of Hachayols to Send',
		'Family ID',
		'Parent',
		'Address',
		'Address 2',
		'City',
		'State',
		'Zip',
		'Country',
		'Children To Get Hachayol',
		'School'
	));
	foreach ( $admins as $adminId => $admin ) {
		$num = isset($children[$adminId]) ? count($children[$adminId]) : 0;
		$familyId = $admin['admin_id'];
		$parent = trim($admin['alast'] . ' Family');
		$address1 = $admin['admin_address1'];
		$address2 = $admin['admin_address2'];
		$city = $admin['admin_city'];
		$state = $admin['admin_state'];
		$zip = $admin['admin_postal'];
		$country = $admin['admin_country'];
		$kids = isset($children[$adminId]) ? implode(' | ', $children[$adminId]) : '';
		$school = ($id == 61 ? 'MS' : 'AK');
		fputcsv($out, array(
			$num,
			$familyId,
			$parent,
			$address1,
			$address2,
			$city,
			$state,
			$zip,
			$country,
			$kids,
			$school
		));
	}
	fclose($out);
	exit;
}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8" />
		<link href="admin_styles.css" rel="stylesheet" type="text/css">
		<title>Hachayol Report</title>
		<style>
			tr, th, td {
				font-size: 12px;
				padding: 10px;
				border-bottom: 1px solid #ccc;
			}
		</style>
	</head>
	
	<body>
	<? include('admin_header.php'); ?>
	<h1>Hachayol Report (Paid for Shipping)</h1>
		<p>
			<a href="?id=<?=$id?>&download=csv" style="display:inline-block;padding:8px 12px;background:#1b2b51;color:#fff;text-decoration:none;border-radius:4px;">Download CSV</a>
		</p>
		<table>
			<tr>
				<th>Number of Hachayols to Send</th>
				<th>Family ID</th>
				<th>Parent</th>
				<th>Address</th>
				<th>Address 2</th>
				<th>City</th>
				<th>State</th>
				<th>Zip</th>
				<th>Country</th>
				<th>Children To Get Hachayol</th>
				<th>School</th>
			</tr>
		<? foreach ($admins as $id => $admin) : ?>
            <?php if (in_array($admin['admin_id'], $shipped_ids)) continue; ?>
			<tr>
				<td><?=count($children[$id])?></td>
				<td><?=$admin['admin_id']?></td>
				<td><?=$admin['alast'] . ' Family'?></td>
				<td><?=$admin['admin_address1']?></td>
				<td><?=$admin['admin_address2']?></td>
				<td><?=$admin['admin_city']?></td>
				<td><?=$admin['admin_state']?></td>
				<td><?=$admin['admin_postal']?></td>
				<td><?=$admin['admin_country']?></td>
				<td>
					<?php
					foreach ($children[$id] as $child) {
						echo $child . "<br />";
					}
					?>
				</td>
				<td><?= $id == 61 ? 'MS' : 'AK' ?></td>
			</tr>
		<? endforeach; ?>
		</table>		
	</body>
</html>