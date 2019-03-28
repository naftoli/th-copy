<?php
require 'db.php';

$info = array();
$sql = "select parent_name, parent_email 
		from chidon_reg  
		join chidon_schools cs using (chidon_schools_id) 
		where cs.year = 5775 
		and cs.gender = 'boys'";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$info[$row['parent_name']] = $row['parent_email'];
}
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
		</style>
	</head>
	
	<body>
		<table>
			<tr>
				<th>Parent Name</th>
				<th>Email</th>
			</tr>
			<?
			foreach ($info as $name => $email) {
				echo "<tr><td>" . $name . "</td><td>" . $email . "</td></tr>";
			}
			?>
		</table>
	</body>
</html>