<?
ini_set('display_errors',1);
require '../../db.php';
require 'vars.php';

$info = array();
$sql = "
		SELECT 
				*
		FROM
				th_chidon tc
						JOIN
				users u USING (user_id)
		WHERE
				tc.year = $year AND tc.date_paid > 0
		GROUP BY user_id
		ORDER BY tc.bunk_number, last, first
";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$info[$row['gender']][$row['bunk_number']][] = $row;
}
//echo "<pre>"; print_r( $info ); echo "</pre>";
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
		</style>
	</head>
	
	<body>	
		<?php 
			foreach ($info as $gender => $more) {
				foreach ( $more as $bunk => $other ) { 
					?>
					<h3><?= $bunk ?></h3><hr /><br />
					<table>
						<caption>Sandwiches</caption>
						<tr>   
              <th>Gender</th>  
							<th>First Name</th>
							<th>Last Name</th>
							<th>Sandwich</th>
						</tr>
						<?php
						foreach ($other as $row) {
							echo "<tr><td>" . $gender . "</td><td>" . $row['first'] . "</td><td>" . $row['last'] . "</td><td>" . $row['sandwich'] . "</td></tr>";
						}
						?>
					</table>
					<div style="page-break-after: always;"></div>
					<?php
				}
			}
		?>
	</body>
</html>