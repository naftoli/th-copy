<?php
// ini_set('display_errors', 1);
// ini_set('error_reporting', E_ALL);

$admin_auth = array('school'); 
require '../header.php';
require '../class.myShliachHachayol.php';
?>
<!doctype html>
<html>
	<head>
		<meta charset="UTF-8" />
		<link href="../admin_styles.css" rel="stylesheet" type="text/css">
		<title>Hachayol Report</title>
		<style>
            table {
                font-size: 12px;
            }
            th {
                font-size: 14px;
            }
			tr, th, td {
				padding: 10px;
				border-bottom: 1px solid #ccc;
			}
		</style>
	</head>
	
	<body>
	<?php include '../admin_header.php'; ?>
	<h1>Hachayol Report</h1>
    <?php
    $schools = [
        61 => 'MyShliach',
        269 => 'Anash Kinder'
    ];
    foreach ($schools as $school_id => $school_name) {
        $hachayol = new MyShliachHachayol(true, $school_id);
        $admins = $hachayol->getAdmins();
        $children = $hachayol->getChildren();
        ?>
        <h2><?=$school_name?> Hachayol Report</h2>
		<table>
			<tr>
				<th class='num'>Number of Hachayols to Send</th>
				<th>Parent</th>
				<th>Address</th>
				<th>Children That Get Hachayol</th>
			</tr>
		<?php 
        if (empty($admins)) {
            echo "<tr><td colspan='4'>No Hachayols to Send</td></tr>";
        } else {
            foreach ($admins as $id => $admin) {
            ?>
			<tr>
				<td><?=count($children[$id])?></td>
				<td><?=$admin['afirst'] . ' ' . $admin['alast']?></td>
				<td><?=$admin['admin_address1'] . "<br />" . (empty($admin['admin_address2']) ? '' : 
						$admin['admin_address2'] . "<br />") . $admin['admin_city'] . ', ' 
						. $admin['admin_state'] . '  ' . $admin['admin_postal'] . "<br />" . 
						$admin['admin_country']?></td>
				<td>
					<table>
						<?php
						foreach ($children[$id] as $child) {
							echo $child . "<br />";
						}
						?>
					</table>
				</td>
			</tr>
		    <?php } 
        } ?>
		</table>	
    <?php } ?>
	</body>
</html>