<?
$admin_auth = array('school'); 
require('header.php');

require 'class.myShliachHachayol.php';

$hachayol = new MyShliachHachayol();
$admins = $hachayol->getAdmins();
$children = $hachayol->getChildren();

//echo "<pre>"; print_r($children); echo "</pre>";
?>
<!doctype html>
<html>
	<head>
		<meta charset="UTF-8" />
		<link href="admin_styles.css" rel="stylesheet" type="text/css">
		<title>Hachayol Report</title>
		<style>
			tr, th, td {
				padding: 3px;
				font-size: 12px;
				vertical-align: top;
			}
			.num {
				width: 60px;
			}
			.hachayols {
				width: 20px;
				text-align: center;
			}
		</style>
	</head>
	
	<body>
	<? include('admin_header.php'); ?>
	<h1>Hachayol Report</h1>
		<table>
			<tr>
				<th class='num'>Number of Hachayols to Send</th>
				<th>Parent</th>
				<th>Address</th>
				<th>Children</th>
				<th>Do NOT Ship</th>
			</tr>
		<? foreach ($admins as $id => $admin) : ?>
			<tr>
				<td class='numInput'>
					<input type='text' class='hachayols' value='<?=$admin['num_hachayols']?>' />
					<span class='<?=$id?>'></span>
				</td>
				<td><?=$admin['afirst'] . ' ' . $admin['alast']?></td>
				<td><?=$admin['admin_address1'] . "<br />" . (empty($admin['admin_address2']) ? '' : 
						$admin['admin_address2'] . "<br />") . $admin['admin_city'] . ', ' 
						. $admin['admin_state'] . '  ' . $admin['admin_postal'] . "<br />" . 
						$admin['admin_country']?></td>
				<td>
					<table>
						<?
						foreach ($children[$id] as $child) {
							echo $child . "<br />";
						}
						?>
					</table>
				</td>
				<td>
					<?
					 $noShip = $admin['no_shipping'] ? true : false; 
					 echo "<input type='checkbox' class='ship' ";
					 if ($noShip) echo "checked='checked' ";
					 echo "/>";
					 ?>
					 <span class='<?=$id?>'></span>
				</td>
			</tr>
		<? endforeach; ?>
		</table>		
	</body>
	
	<script src="jquery-1.8.1.min.js"></script>
	<script>
		$( function() {
			$(".hachayols").blur( function() {
				var id = $(this).parent().find('span').attr('class');
				var val = $(this).val().trim();
				if (val == '') return;
				$.post('ajax/updateHachayols.php', {id : id, val: val}, function( success ) {
					if (success == 1) {
						alert("updated");
					} else if (success == 0) {
						alert("error updating");
					}
				});
			});
			
			$(".ship").click( function() {
				var id = $(this).parent().find('span').attr('class');
				var checked = $(this).is(":checked");
				$.post('ajax/updateShipping.php', {id : id, ship : checked}, function( success ) {
					if (success == 1) {
						alert('updated');
					} else if (success == 0) {
						alert('error updating');
					}
				});
			});
		});
	</script>
</html>