<?php
//echo 'needs updating';
//exit;
require '../../db.php';
require 'vars.php';

$info = array();
$sql = "select * from th_chidon_chaps tc
		join schools s on s.school_id = tc.school_id 
		where tc.year = " . $year . "
		and tc.school_id not in (82) 
		order by school_name";
//echo $sql;
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$info[] = $row;
}
//echo "<pre>"; print_r($info); echo "</pre>"; exit;
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8" />
		<style>
			body {
				font-family: Arial, Helvetica, sans-serif;
				font-size: 14px;
			}
			caption {
				font-size: 16px;
				font-weight: bold;
				border-bottom: 1px solid grey;
			}
			td:last-child {
				text-align: center;
			}
		</style>
	</head>
	
	<body>	
		<table>
			<caption>Chaperone Office Report</caption>
			<tr>
				<th>School ID</th>
				<th>School</th>
				<th>Chaperone Name</th>
				<th>Chaperone Number</th>
				<th>Chaperone Email</th>
				<th>Sweater</th>
				<th>Sweater Size</th>
				<th>Full Trip</th>
				<th>Chidon Ticket</th>
				<th>Chidon Attending</th>
			</tr>
			<?
			foreach ($info as $row) {
				echo "<tr id=" . $row['th_chidon_chap_id'] . "><td class='schoolID'>" . $row['th_chidon_chap_id'] . "</td><td>" . $row['school_name'] . "</td><td>" . $row['name'] .
				"</td><td>" . $row['phone'] . "</td><td>" . $row['email'] . "</td><td>";
				if (!$row['sweater']) echo 'NO';
				else echo 'YES';
				echo "</td><td>";
				if ($row['sweater_size']) echo strtoupper($row['sweater_size']);
				echo "</td><td>";
				if ($row['full_program']) echo 'YES';
				else echo 'NO';
				echo "</td><td><input type='checkbox' class='ticket' ";
				if ($row['ticket']) echo "checked ";
				echo "/></td><td>";
				echo "<select name='type' class='chidon_type'><option value='0'>Choose one</option>";
				echo "<option value='boys'";
				if ($row['chidon_type'] == 'boys') echo " selected";
				echo ">Boys Chidon</option>";
				echo "<option value='girls'";
				if ($row['chidon_type'] == 'girls') echo " selected";
				echo ">Girls Chidon</option>";
				echo "</td></tr>";
			}
			?>
		</table>
	</body>
	<script src="../js/jquery.min.js"></script>
	<script>
		$(function () {
			$(".ticket").click( function() {
				var id = $(this).parent().parent().find('.schoolID').text();
				var checked = 0;
				if ($(this).is(":checked")) checked = 1;
				$.post('ajax/updateTicket.php', { id : id, checked : checked }, function( success ) {
					if (!parseInt(success)) {
						alert('Error updating.');
					}
				});
			});
			
			$(".chidon_type").change( function() {
				var id = $(this).parent().parent().attr('id');
				var val = $(this).val();
				if (val) {
					$.post('ajax/updateChidonType.php', { id : id, val : val }, function( error ) {
						if (parseInt(error) == 1) {
							alert('Error updating.');
						}
					});
				}
			});
		});
	</script>
</html>