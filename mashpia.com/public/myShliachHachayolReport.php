<?php
// ini_set('display_errors', 1);
// ini_set('error_reporting', E_ALL);

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

$totalHachayols = 0;

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
			<a href="?id=<?=$id?>&download=csv" class="btn">Download CSV</a>
		</p>
		Shipment Number: <select id="shipment_number" class="form-control">
			<option value="2">2</option>
			<option value="3">3</option>
			<option value="4">4</option>
			<option value="5">5</option>
		</select>
		<button onclick="setAsShipped()" class="btn btn-primary">Set as Shipped</button>
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
		<? foreach ($admins as $admin_id => $admin) : ?>
			<tr>
				<td><?=count($children[$admin_id])?></td>
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
					foreach ($children[$admin_id] as $user_id => $child) {
						echo "<span class='shipped' id=" . $user_id . ">" . $child . "</span><br />";
						$totalHachayols++;
					}
					?>
				</td>
				<td><?= $id == 61 ? 'MS' : 'AK' ?></td>
			</tr>
		<? endforeach; ?>
		</table>
		<br />		
		<p>Total Hachayols: <?=$totalHachayols?></p>
	</body>
	<script>
		const setAsShipped = () => {
			let toShip = [];
			const shipped = document.querySelectorAll('.shipped');
			shipped.forEach(elem => {
				toShip.push(elem.id);
			});
			// console.log(toShip);
			fetch('hachayols/api/setAsShipped.php', {
				method: 'POST',
				body: JSON.stringify({ info: toShip, total: toShip.length, shipment_number: document.getElementById('shipment_number').value })
			})
			.then(response => response.json())
			.then(data => {
				if (data.success) {
					alert('Hachayols set as shipped.');
				} else {
					alert('Failed to set hachayols as shipped.');
				}
			});
		}
	</script>
</html>