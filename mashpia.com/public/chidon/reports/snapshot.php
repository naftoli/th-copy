<?
require '../../db.php';
require 'vars.php';

$schools = array();
$sql = "select * from th_chidon tc 
		join users u using (user_id)
		join classes c using (class_id) 
		where tc.paid > 0 
		and tc.year = " . $year . "
		and tc.shabbaton = 1";
if (isset($_GET['gender'])) $sql .= " and u.gender = '" . $gender . "'";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$schools[$row['school_id']][] = $row;
}

$schoolNames = array();
$sql = "select school_id, school_name from schools where school_id in (" . implode(',', array_keys($schools)) . ")";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$schoolNames[$row['school_id']] = $row['school_name'];
}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8" />
		<style>
			th, td {
				font-size: 12px;
				padding: 5px;
			}
		</style>
	</head>
	
	<body>
		<? 
		$boys['reg'] = 0;
		$boys['notReg'] = 0;
		$girls['reg'] = 0;
		$girls['notReg'] = 0;
		$chap['boys']['reg'] = 0;
		$chap['boys']['notReg'] = 0;
		$chap['girls']['reg'] = 0;
		$chap['girls']['notReg'] = 0;
		
		foreach ($schools as $id => $info) {
			echo "<h2>" . $schoolNames[$id] . "</h2>";
			// get school chap info
			/*
			echo "School: " . $info['school_name'] . "<br />";
			echo "Chaperone(s): " . $chapInfo['name'] . "<br />";
			echo "Chaperone Number(s): " . $chapInfo['phone'] . "<br />";
			echo "Paid for full program: " . ($info['full_program'] ? 'yes' : 'no') . "<br />";
			echo "Ordered Sweater: " . ($info['sweater'] ? 'yes' : 'no') . "<br />";
			
			if ($info['full_program']) $chap[$info['gender']]['reg']++;
			else $chap[$info['gender']]['notReg']++;
			
			if ($info['sweater']) echo "Size: " . $info['s_size'];
			*/
			?>
			<table>
				<tr>
					<th class="ctr">Paid / Registered</th>
					<th class="ctr">First Name</th>
					<th class="ctr">Last Name</th>
					<th class="ctr">Grade</th>
					<th class="ctr">Type</th>
					<th class="ctr">Sweater Size</th>
					<th class="ctr">Hebrew Name</th>
					<th class="ctr">Avg Mark</th>
					<th class="ctr">Parent Info</th>
					<th>Allowed to walk alone by Day</th>
					<th>Allowed to walk alone by Night</th>
					<th class="ctr">Host Name</th>
					<th class="ctr">Host Address</th>
					<th class="ctr">Host Number</th>
					<!--
					<th>City, State</th>
					<th>Meeting Point</th>
					<th>Walking Group #</th>
					<th>Bus #</th>
					<th>Team</th>
					<th>Hakhel #</th>
					<th>Round #</th>
					<th>Plaque</th>
					<th>Medal</th>
					-->
				</tr>
				<?
				foreach ($info as $row) {
					/*
					if ($info['gender'] == 'boys') {
						if ($row['paid']) $boys['reg']++;
						else $boys['notReg']++;
					} else if ($info['gender'] == 'girls') {
						if ($row['paid']) $girls['reg']++;
						else $girls['notReg']++;
					}
					*/
					$avg1 = ($row['test1a'] + $row['test2a'] + $row['test3a']) / 3;
					$avg2 = ($row['test1b'] + $row['test2b'] + $row['test3b']) / 3;
					$avg = round(($avg1 + $avg2) / 2, 2);
					
					$id = $row['th_chidon_id'];
					$paid = $row['paid'];
					$size = $row['size'];
					$name = $row['first'];
					$lname = $row['last'];
					$hname = $row['first_he'];
					$hlname = $row['last_he'];
					$family = $row['host'];
					$address = $row['host_address'];
					$phone = $row['host_number'];
					?>
					<tr>
						<td class="ctr">
							<? 
							if ($paid) {
								echo "<span class='green'>&#x2713;</span>";
							} else {
								 echo '<span class="red">&#x2717;</span>';
							} 
							?>
						</td>
						<td>
							<?=$name?>
						</td>
						<td>
							<?=$lname?>
						</td>
						<td class="ctr">
							<?php
							echo $row['class_grade'] . (empty($row['class_sub']) ? '' : '-' . $row['class_sub']);
							?>
						</td>
						<td>
							<?=$row['contestant'] ? 'representative' : 'contestant'?>
						</td>
						<td>
							<?=$size?>
						</td>
						<td class="ctr">
							<?
							if (empty($hname) && empty($hlname)) {
								echo '<span class="red">&#x2717;</span>';
							} else {														
								echo $hname . ' ' . $hlname;
							}
							?>											
						</td>
						<td class="ctr">
							<?=$avg?>										
						</td>
						<td class="ctr">
							
						</td>

						<td>
							<?=$row['walk_day'] ? 'yes' : 'no'?>
						</td>
						
						<td>
							<?=$row['walk_night'] ? 'yes' : 'no'?>
						</td>
						
						<td class="ctr">
							<?=$row['host']?>
						</td>
						<td class="ctr">
							<?=$row['host_address']?>
						</td>
						<td class="ctr">
							<?=$row['host_number']?>
						</td>
						<!--							
						<td>
							<?=$row['child_city_state']?>
						</td>
							
						</td>
						
						<td>
							<?=$row['walking_group'];?>
						</td>
						
						<td>
							<?=$row['bus'];?>
						</td>
						-->
					</tr>
				<? } ?>
			</table>
			<br />
			<div style="page-break-after: always"></div>
			<hr />
			<br />
		<? } ?>
		<!--
		<h3>Totals</h3>
		<table>
			<tr>
				<th>Registered Boys</th>
				<th>Not Registered Boys</th>
				<th>Registered Girls</th>
				<th>Not Registered Girls</th>
				<th>Registered Boys Chaperones</th>
				<th>Not Registered Boys Chaperones</th>
				<th>Registered Girls Chaperones</th>
				<th>Not Registered Girls Chaperones</th>
			</tr>
			<tr>
				<td><?=$boys['reg']?></td>
				<td><?=$boys['notReg']?></td>
				<td><?=$girls['reg']?></td>
				<td><?=$girls['notReg']?></td>
				<td><?=$chap['boys']['reg']?></td>
				<td><?=$chap['boys']['notReg']?></td>
				<td><?=$chap['girls']['reg']?></td>
				<td><?=$chap['girls']['notReg']?></td>
			</tr>
		</table>
		-->
	</body>
</html>