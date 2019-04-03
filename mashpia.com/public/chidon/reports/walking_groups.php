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
				schools s USING (school_id)
						JOIN
				users u USING (user_id)
						JOIN
				th_chidon_chaps tcc ON tcc.school_id = tc.school_id 
		WHERE
				tc.year = $year AND tc.date_paid > 0
						AND chap_type = 1 
						AND u.gender = 'M' 
		GROUP BY user_id
		ORDER BY school_name, tc.grade, last, first
";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$info[$row['gender']][$row['school_name']][] = $row;
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
				foreach ( $more as $school => $other ) { 
					?>
					<h3><?= $school ?></h3><hr /><br />
					<table>
						<caption>Walking Groups</caption>
						<tr>     
							<th>Gender</th>
							<th>First Name</th>
							<th>Last Name</th>
							<th>Chaperone Name</th>
							<th>Chaperone Cell</th>
							<th>Walking Group #</th>
							<th>Walking Counselor Name</th>
							<th>Walking Counselor Cell</th>
							<th>Host Family</th>
							<th>Host Cell</th>
							<th>Host Address</th>
							<th>Between Streets</th>
							<th>Permission to walk alone</th>
						</tr>
						<?php
						foreach ($other as $row) {
							// find out walking counselor 
							if ( empty( $row['walking_group'] ) ) continue;
							$sql = "select s.* from th_chidon_staff s 
											join th_chidon_staff_assignments sa using (staff_id) 
											where sa.group_number = '" . $row['walking_group'] . "' 
											and sa.staff_type_id = 9";
							$result = mysql_query( $sql );
							$counselor = mysql_fetch_assoc( $result );
							echo "<tr><td>" . $gender . "</td><td>" . $row['first'] . "</td><td>" . $row['last'] . "</td><td>" . ($row['first_name'] . ' ' . $row['last_name']) . 
								"</td><td>" . $row['phone'] . "</td><td>" . $row['walking_group'] . "</td><td>" . $counselor['first_name'] . ' ' . $counselor['last_name'] . "</td><td>" . 
								$counselor['cell'] . "</td><td>" . $row['host'] . "</td><td>" . $row['host_number'] . "</td><td>" . 
								$row['host_street_num'] . $row['host_street_num_suffix'] . ' ' . $row['host_street'] . ' ' . $row['host_street_apt'] . 
								 "</td><td>" . $row['between_streets1'] . ' and ' . $row['between_streets2'] . "</td><td>" . ($row['walking'] ? 'yes' : 'no') . "</td></tr>";
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