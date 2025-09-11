<?php
// ini_set('display_errors', 1);
// ini_set('error_reporting', E_ALL);

$admin_auth = array('school'); 
require('../header.php');

require '../class.myShliachHachayol.php';

if (isset($_GET['id'])) {
	$id = $_GET['id'];
} else {
	$id = 61;
}

$family_ids = [
    172247, 195746, 71578, 168952, 175042, 189749, 194010, 197148, 198983, 199754,
    200132, 197476, 193120, 150479, 5794, 182987, 193229, 686, 1145, 3574,
    71046, 71487, 119027, 140768, 150580, 168402, 172411, 194120, 196727, 197335,
    198225, 199255, 199491, 199533, 200381, 200847, 200973, 200998, 118, 436,
    1264, 6205, 6356, 6459, 6561, 6646, 6725, 6804, 6823, 6848,
    7118, 8224, 71227, 71314, 71580, 128799, 129140, 129268, 129283, 129574,
    140230, 140771, 141151, 150317, 150658, 150844, 167609, 167705, 167857, 167963,
    168078, 168294, 169109, 169281, 169520, 170626, 170928, 171631, 172009, 172192,
    172237, 172269, 172307, 172627, 173897, 174022, 174986, 175122, 175218, 175276,
    175309, 175427, 175559, 175665, 175845, 175952, 177864, 178463, 178830, 178946,
    179238, 179460, 179692, 179729, 179730, 179731, 179732, 179749, 179754, 180026,
    180166, 180359, 180419, 180669, 180825, 180949, 181042, 181144, 181265, 181385,
    181398, 181498, 181565, 181579, 181626, 181676, 181716, 181719, 181742, 181808,
    181892, 181974, 182023, 182103, 182189, 182283, 182332, 182360, 182451, 182497,
    182542, 182579, 182633, 182646, 182768, 182769, 182822, 182880, 182998, 183085,
    183096, 183162, 183184, 183250, 183331, 183397, 183420, 183422, 183515, 183613,
    183721, 183829, 183948, 184027, 184167, 184233, 184365, 184403, 184459, 184517,
    184583, 184594, 184677, 184797, 184823, 184877, 184960, 185023, 185121, 185199,
    185320, 185379, 185466, 185584, 185631, 185779, 185831, 185906, 185999, 186099,
    186170, 186288, 186318, 186429, 186467, 186573, 186674, 186761, 186818, 186913,
    187003, 187071, 187154, 187253, 187357, 187401, 187523, 187612, 187752, 187828,
    187936, 188067, 188159, 188294, 188395, 188506, 188601, 188742, 188823, 188935,
    189027, 189138, 189261, 189371, 189489, 189600, 189701, 189810, 189925, 190074,
    190161, 190299, 190400, 190532, 190659, 190726, 190865, 190988, 191115, 191211,
    191344, 191472, 191590, 191725, 191871, 191999, 192115, 192270, 192392, 192523,
    192684, 192819, 192957, 193099, 193286, 193385, 193534, 193689, 193802, 193940,
    194088, 194245, 194389, 194533, 194695, 194834, 194985, 195153, 195302, 195457,
    195627, 195800, 195966, 196139, 196317, 196502, 196679, 196868, 197048, 197238,
    197421, 197618, 197820, 198031, 198234, 198444, 198654, 198863, 199084, 199314,
    199536, 199774, 200003, 200244, 200328, 200330, 200349, 200356, 200362, 200544,
    200570, 200799, 200904, 200914
];

$hachayol = new MyShliachHachayol(false, $id, true);
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
		<link href="../admin_styles.css" rel="stylesheet" type="text/css">
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
	<? include('../admin_header.php'); ?>
	<h1>Hachayol Report (Paid for Shipping)</h1>
		<p>
			<a href="?id=<?=$id?>&download=csv" class="btn">Download CSV</a>
		</p>
		Shipment Number: <select id="shipment_number" class="form-control">
            <option value="1">1</option>
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
		<?php foreach ($admins as $admin_id => $admin) : ?>
            <?php $numChildren = count($children[$admin_id]); ?>
            <?php if (in_array(intval($admin_id), $family_ids) && $numChildren <= 1) continue; ?>
            <?php if (in_array(intval($admin_id), $family_ids)) $numChildren--; ?>
			<tr>
				<td><?=$numChildren?></td>
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
                    $skipFirst = false;
                    if (in_array(intval($admin_id), $family_ids)) $skipFirst = true;
					foreach ($children[$admin_id] as $user_id => $child) {
                        if ($skipFirst) {
                            $skipFirst = false;
                            continue;
                        }
                        // find out if child already got hachayol
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
			fetch('api/setAsShipped.php', {
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