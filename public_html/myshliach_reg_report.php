<?php
require_once 'db.php';
require_once 'class.globalSettings.php';
$year = GlobalSettings::getRegistrationYear();

$reg = array();
$sql = "select * from registration r 
		join admins a using (admin_id) 
		where year = $year";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$reg[$row['school_id']][] = $row;
}
echo "<pre>";
//print_r($reg);
echo "</pre>";

$view = array('no', 'yes');
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8" />
		<style>
			table {
                font-size: 12px;
                font-family: "sans-serif";
            }
            th, td {
                padding: 3px 10px;
            }
            th {
            	border-bottom: 1px solid grey;
            }
            td {
            	vertical-align: top;
            }
			caption {
				font-size: 16px;
			}
		</style>
	</head>
	
	<body>
		<? foreach ($reg as $school => $info) {	?>
			<table>
				<caption>
					<?
					if ($school == 61) echo "MyShliach School";
					else if ($school == 269) echo "Anash Kinder School";
					?>
				</caption>
				<tr>
					<th>Child</th>
					<th>Parent</th>
					<th>Address</th>
					<th>City</th>
					<th>State</th>
					<th>Zip</th>
					<th>Country</th>
					<th>Email</th>
					<th>Phone Number(s)</th>
					<!--
					<th>WhatsApp</th>
					<th>Tutorial</th>
					<? if ($school == 61) { ?> 
					<th>Chavrusa EN</th>
					<th>Chavrusa HE</th>
					<th>Library</th>
					<th>Birthday</th>
					<th>Chidon (Mishmor)</th>
					<? } ?>
					-->
					<? if ($school == 269) { ?>
						<th>Shipping Option</th>
					<? } else { ?>
						<th>Ship Hachayol</th>
					<? } ?>
						<th>Shipping Dest</th>
					<? //} ?>
					<!--
					<? if ($school == 61) { ?>
						<th>Ship Supplies</th>
					<? } ?>
					-->
				</tr>
				<?
				foreach ($info as $row) {
					//get children info
					$children = array();
					$userIDs = array();
					$sql = "select user_id, first, last from users where user_id in (" . $row['users'] . ")";
					$result = mysql_query($sql);
					while ($chRow = mysql_fetch_assoc($result)) {
						$children[] = $chRow['first'] . ' ' . $chRow['last'];
						$userIDs[] = $chRow['user_id'];
					}
					$numChildren = count($children);
					
					$phone = "H: " . $row['admin_phone_home'] . "<br />W: " . $row['admin_phone_work'] . 
						"<br />C: " . $row['admin_phone_mobile'];
					
					$i = 1;
					do {
						echo "<tr><td>" . ($children[$i-1] ? $children[$i-1] : '') . "</td><td>" . 
							$row['first'] . ' ' . $row['last'] . "</td><td>" . $row['admin_address1'] . 
							(empty($row['admin_address2']) ? '' : "<br />" . $row['admin_address2']) . 
							"</td><td>" . $row['admin_city'] . "</td><td>" . $row['admin_state'] . 
							"</td><td>" . $row['admin_postal'] . "</td><td>" . $row['admin_country'] . 
							"</td><td>" . $row['admin_email'] . "</td><td>" . $phone . "</td><td>";
						if ($school == 61) {
							$send = $row['ship_dest'] ? $row['ship_option'] == 1 ? 'yes' : 'no' : 'yes';
							echo $send . "</td><td>" . ($row['ship_dest'] ? $row['ship_dest'] : 'USA') . "</td></tr>";
						} else if ($school == 269) {
							echo $row['ship_option'] . "</td><td>" . $row['ship_dest'] . "</td></tr>";
						}
						//$info = explode(':', $row['approval']);
						//$paid = $info[3];
						/*
						echo $view[$row['whatsapp']] . "</td><td>" . $view[$row['tutorial']] . "</td><td>";
						if ($school == 61) {
							echo $view[$row['chavrusaEn']] . "</td><td>" . $view[$row['chavrusaHe']] . 
								"</td><td>" . $view[$row['library']] . "</td><td>" . $view[$row['birthday']] . 
								"</td><td>" . $view[$row['mishmor']] . "</td><td>";
						}
						else if ($school == 61) {
							if (strpos($row['extra_shipping'], $userIDs[$i-1]) !== false) {
								echo "yes";
							} else {
								echo "no";
							}
							echo "</td></tr>";
						}
						*/
					} while ($i++ < $numChildren);
				}
				?>
			</table>
			<br />
		<? } ?>
	</body>
</html>