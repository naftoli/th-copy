<?
echo 'needs updating';
exit;

require '../../db.php';
require 'vars.php';

$info = array();
$sql = "select * from th_chidon_schools ts
		join th_chidon_chaps tc using (school_id)
		join schools s on s.school_id = ts.school_id 
		where ts.year = " . $year . "
		and ts.school_id not in (82) 
		order by school_name";
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
			</tr>
			<?
			foreach ($info as $row) {
				echo "<tr><td class='schoolID'>" . $row['th_chidon_chap_id'] . "</td><td>" . $row['school_name'] . "</td><td>" . $row['name'] .
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
				echo "/></td></tr>";
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
		});
	</script>
</html>