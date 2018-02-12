<?
require 'db.php';

$schools = array();
$sql = "select * from chidon_schools 
		where year = 5776 
		and chidon_schools_id not in(112,122,80) 
		and gender = 'boys' 
		order by school_name";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$schools[$row['chidon_schools_id']] = $row;
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
			$chapInfo['name'] = $info['chaperone_name'];
			if (!empty($info['chaperone_name2'])) $chapInfo['name'] .= ', ' . $info['chaperone_name2'];
			if (!empty($info['chaperone_name3'])) $chapInfo['name'] .= ', ' . $info['chaperone_name3']; 
			
			$chapInfo['phone'] = $info['chaperone_phone'];
			if (!empty($info['chaperone_phone2'])) $chapInfo['phone'] .= ', ' . $info['chaperone_phone2'];
			if (!empty($info['chaperone_phone3'])) $chapInfo['phone'] .= ', ' . $info['chaperone_phone3'];
			//echo "<pre>"; print_r($info); echo "</pre>";
			$sql = "select * from chidon_reg where chidon_schools_id = " . $id;
			$result = mysql_query($sql);
			//if (mysql_num_rows($result) == 0) continue;
			echo "School: " . $info['school_name'] . "<br />";
			echo "Chaperone(s): " . $chapInfo['name'] . "<br />";
			echo "Chaperone Number(s): " . $chapInfo['phone'] . "<br />";
			echo "Paid for full program: " . ($info['full_program'] ? 'yes' : 'no') . "<br />";
			echo "Ordered Sweater: " . ($info['sweater'] ? 'yes' : 'no') . "<br />";
			
			if ($info['full_program']) $chap[$info['gender']]['reg']++;
			else $chap[$info['gender']]['notReg']++;
			
			if ($info['sweater']) echo "Size: " . $info['s_size'];
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
					<th class="ctr">Needs Accommodations</th>
					<th>Allowed to walk alone</th>
					<th class="ctr">Host Name</th>
					<th class="ctr">Host Address</th>
					<th class="ctr">Host Number</th>
					<th class="ctr">Picture</th>
					<th>City, State</th>
					<th>Meeting Point</th>
					<th>Walking Group #</th>
					<th>Bus #</th>
					<th>Team</th>
					<th>Hakhel #</th>
					<th>Round #</th>
					<th>Plaque</th>
					<th>Medal</th>
				</tr>
				<?
				while ($row = mysql_fetch_assoc($result)) {
					if ($info['gender'] == 'boys') {
						if ($row['paid']) $boys['reg']++;
						else $boys['notReg']++;
					} else if ($info['gender'] == 'girls') {
						if ($row['paid']) $girls['reg']++;
						else $girls['notReg']++;
					}
					
					$mark1 = $row['mark1'] + $row['bonus'];
					$mark2 = $row['mark2'];
					$mark3 = $row['mark3'];
					$avg = round(($mark1 + $mark2 + $mark3) / 3);
					
					$id = $row['chidon_reg_id'];
					$paid = $row['paid'];
					$size = $row['size'];
					$name = $row['name'];
					$lname = $row['last_name'];
					$heName = $row['hname'];
					$pos = strrpos($heName, ' ');
					$hname = substr($heName, 0, $pos);
					$hlname = substr($heName, $pos+1);
					$help = $row['help'];
					$family = $row['family'];
					$address = $row['address'];
					$phone = $row['phone'];
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
							<?=$row['grade']?>
						</td>
						<td>
							<?=$row['type']?>
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
							<?
							if (empty($row['mark1']) || empty($row['mark2']) || empty($row['mark3'])) {
								echo '<span class="red">&#x2717;</span>';
							} else {														
								echo $avg;
							}
							?>											
						</td>
						<td class="ctr">
							<?
							if (empty($row['parent_name']) || 
								empty($row['parent_email']) || 
								empty($row['parent_cell'])) {
									echo '<span class="red">&#x2717;</span>';
							} else {														
								echo '<span class="green">&#x2713;</span>';
							}
							?>
						</td>
						<td class="ctr">
							<?
							if ($row['help']) {
								echo '<span class="green">&#x2713;</span>';
							} else {
								echo '<span class="red">&#x2717;</span>';
							}
							?>
						</td>
						
						<td>
							<?=$row['walk_alone'] ? 'yes' : 'no'?>
						</td>
						
						<td class="ctr">
							<?=$row['family']?>
						</td>
						<td class="ctr">
							<?=$row['address']?>
						</td>
						<td class="ctr">
							<?=$row['phone']?>
						</td>
							
						<td class="ctr">
							<?
							if (empty($row['file']) || trim($row['file']) == '') {
								echo '<span class="red">&#x2717;</span>';
							} else {
								$file = '';
								if (strpos($row['file'], 'img/') !== false) {
									if (file_exists('chidon/' . $row['file'])) {
										$file = 'chidon/' . $row['file'];
									} else if (file_exists('mobile/chidon/' . $row['file'])) {
										$file = 'mobile/chidon/' . $row['file'];
									} else if (file_exists('chidon/photos/' . $row['file'])) {
										$file = 'chidon/photos/' . $row['file'];
									}
								} else {
									if (file_exists('chidon/photos/' . $row['file'])) {
										$file = 'chidon/photos/' . $row['file'];
									} else if (file_exists($row['file'])) {
										$file = $row['file'];
									} else {
										$file = substr($row['file'], 1);
									}
								}
								echo "<img src='" . $file . "' width='80px' />";
							}
							?>
						</td>
													
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
						
					</tr>
				<? } ?>
			</table>
			<br />
			<div style="page-break-after: always"></div>
			<hr />
			<br />
		<? } ?>
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
	</body>
</html>