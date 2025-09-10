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

//echo "<pre>"; print_r($children); echo "</pre>";
// CSV download
if ( isset($_GET['download']) && $_GET['download'] === 'csv' ) {
	header('Content-Type: text/csv; charset=utf-8');
	header('Content-Disposition: attachment; filename="hachayol_report.csv"');
	$out = fopen('php://output', 'w');
	// headers
	fputcsv($out, array('Number of Hachayols to Send', 'Parent', 'Address', 'Children'));
	foreach ( $admins as $adminId => $admin ) {
		$num = isset($children[$adminId]) ? count($children[$adminId]) : 0;
		$parent = trim($admin['afirst'] . ' ' . $admin['alast']);
		$addressParts = array_filter([
			$admin['admin_address1'],
			$admin['admin_address2'],
			trim($admin['admin_city'] . ', ' . $admin['admin_state'] . ' ' . $admin['admin_postal']),
			$admin['admin_country']
		]);
		$address = implode(', ', $addressParts);
		$kids = isset($children[$adminId]) ? implode(' | ', $children[$adminId]) : '';
		fputcsv($out, array($num, $parent, $address, $kids));
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