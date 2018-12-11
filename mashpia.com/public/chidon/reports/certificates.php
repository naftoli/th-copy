<?
echo 'needs updating';
exit;

require '../../db.php';
require 'vars.php';
		
$info = array();
$sql = "select * from th_chidon tc  
		join schools s using (school_id)
		join users u using (user_id) 
		where tc.year = " . $year . "  
		and tc.paid > 0  
		order by school_name, last, first";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	if ($row['contestant']) {
		$avg = number_format(($row['test1a'] + $row['test1b'] + $row['test2a'] + $row['test2b'] + $row['test3a'] + $row['test3b']) / 6, 2);
	} else {
		$avg = number_format(($row['test1a'] + $row['test2a']+ $row['test3a']) / 3, 2);
	}
	$info[$row['gender']][$avg][] = $row;
}
krsort($info['boys']);
krsort($info['girls']);
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
		</style>
	</head>
	
	<body>
		<table>
			<caption>Certificates</caption>
			<tr>
				<th>ID</th>
				<th>Gender</th>
				<th>School</th>
				<th>Hebrew Name</th>
				<th>Avg Mark</th>
				<th>Name Entered</th>
				<th>Printed</th>
				<th>Verified Printed</th>
				<th>Plaque Created</th>
			</tr>
			<?
			foreach ($info as $gender => $other) {
				foreach ($other as $avg => $rest) {
					foreach ($rest as $row) {
						echo "<tr><td class='chidonRegId'>" . $row['th_chidon_id'] . "</td><td>" . 
							$gender . "</td><td>" . $row['school_name'] . "</td><td>" . $row['hname'] . 
							"</td><td>" . $avg . "</td></td><td>
							<input type='checkbox' name='entered' class='entered' ";
						if ($row['entered']) echo "checked ";
						echo " /></td><td><input type='checkbox' name='printed' class='printed' ";
						if ($row['cert_printed']) echo "checked ";	
						echo "/></td><td><input type='checkbox' name='vprinted' class='vprinted' ";
						if ($row['cert_conf_printed']) echo "checked ";
						echo "/></td><td><input type='checkbox' name='plaque' class='plaque' ";
						if ($row['plaque_created']) echo "checked ";
						echo "/></td></tr>";
					}
				}
			}
			?>
		</table>
	</body>
	<script src="../js/jquery.min.js"></script>
	<script>
		$(".entered").click( function() {
			var id = $(this).parent().parent().find('.chidonRegId').text();
			var checked = $(this).is(":checked");
			$.post('ajax/updateCert.php', { id : id, field : 'entered', val : checked }, function( error ) {
				if (error != 0) {
					alert('There was an error updating.');
				}
			});
		});
		
		$(".printed").click( function() {
			var id = $(this).parent().parent().find('.chidonRegId').text();
			var checked = $(this).is(":checked");
			$.post('ajax/updateCert.php', { id : id, field : 'cert_printed', val : checked }, function( error ) {
				if (error != 0) {
					alert('There was an error updating.');
				}
			});
		});
		
		$(".vprinted").click( function() {
			var id = $(this).parent().parent().find('.chidonRegId').text();
			var checked = $(this).is(":checked");
			$.post('ajax/updateCert.php', { id : id, field : 'cert_conf_printed', val : checked }, function( success ) {
				if (error != 0) {
					alert('There was an error updating.');
				}
			});
		});
		
		$(".plaque").click( function() {
			var id = $(this).parent().parent().find('.chidonRegId').text();
			var checked = $(this).is(":checked");
			$.post('ajax/updateCert.php', { id : id, field : 'plaque_created', val : checked }, function( success ) {
				if (error != 0) {
					alert('There was an error updating.');
				}
			});
		});
	</script>
</html>