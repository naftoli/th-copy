<?
require 'db.php';

if (isset($_POST['submit'])) {
	//echo "<pre>"; print_r($_POST); echo "</pre>"; exit;
	
	//save to database	
	foreach ($_POST['pay'] as $rid => $val) {
		$sql = "update chidon_reg set paid = 1, 
				approval = 'registered by chidon admin' 
				where chidon_reg_id = " . $rid;
		if (!@mysql_query($sql)) {
			echo "Error registering " . $rid;
		}
	}
}

$sql = "select cr.*, cs.school_name from chidon_reg cr 
		join chidon_schools cs using (chidon_schools_id)
		where cs.year = 5775";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$reg[] = $row;
}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8" />
		<style>
			body,td,th {
				font-family: "sans-serif";
				font-size: 14px;
			}
			th, td {
				padding: 3px;
			}
			fieldset {
				margin-bottom: 20px;
				-moz-border-radius: 20px;
                -webkit-border-radius: 20px;
                border-radius: 20px;
                width: 1250px;
			}
			legend {
				margin-left: 30px;
                padding: 6px;
                font-size: 16px;
			}
		</style>
	</head>
	
	<body>
		<form action="chidon_reg_admin.php" method="post" id="payForm">
			<div id="regList">
				<fieldset>
					<legend>Chidon Participants</legend>
					<table>
						<tr>
							<th class="ctr">Pay For</th>
							<th class="ctr">First Name</th>
							<th class="ctr">Last Name</th>
							<th class="ctr">Grade</th>
							<th class="ctr">Type</th>
							<th class="ctr">Sweater Size</th>
							<th class="ctr">Hebrew Names</th>
							<th class="ctr">Test Marks</th>
							<th class="ctr">Parent Info</th>
							<th class="ctr">CH Accommodation Info</th>
							<th class="ctr">Airport Transportation</th>
							<th class="ctr">Photo</th>
						</tr>
						<?
						$total = count($reg);
						for ($i = 0; $i < $total; $i++) {
							$row = $reg[$i];
							$id = $row['chidon_reg_id'];
							$paid = $row['paid'];
							$size = $row['size'];
							$name = $row['name'];
							$lname = $row['last_name'];
							$hname = $row['hname'];
							$hlname = $row['hname_last'];
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
									?>
									<input type="checkbox" name='pay[<?=$row['chidon_reg_id']?>]' class="pay" />
									<? } ?>
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
									if (empty($hname) || empty($hlname)) {
										echo '<span class="red">&#x2717;</span>';
									} else {														
										echo '<span class="green">&#x2713;</span>';
									}
									?>											
								</td>
								<td class="ctr">
									<?
									if (empty($row['mark1']) || empty($row['mark2'])) {
										echo '<span class="red">&#x2717;</span>';
									} else {														
										echo '<span class="green">&#x2713;</span>';
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
									if (!$row['help']) {
										if (empty($row['family']) ||
											empty($row['address']) || 
											empty($row['phone'])) {
												echo '<span class="red">&#x2717;</span>';
										} else {														
											echo '<span class="green">&#x2713;</span>';
										}
									} else {
										echo "n/a";
									} 
									?>
								</td>
								<td class="ctr">
									<?
									if ($row['transportation']) {
										if (empty($row['arr_airport']) ||
											empty($row['arr_number']) || 
											empty($row['arr_time']) || 
											empty($row['dep_airport']) ||
											empty($row['dep_number']) ||
											empty($row['dep_time'])) {
												echo '<span class="red">&#x2717;</span>';
										} else {														
											echo '<span class="green">&#x2713;</span>';
										}
									} else {
										echo "n/a";
									} 
									?>
								</td>
								<td class="ctr">
									<? 
									if (!empty($row['file'])) {
										echo '<span class="green">&#x2713;</span>';
										echo "<input type='hidden' class='file' value='1' />";
									} else {
										echo '<span class="red">&#x2717;</span>';
										echo "<input type='hidden' class='file' value='0' />";
									} 
									?>
								</td>
							</tr>
						<? } ?>
					</table>
					<div>
						<input type='submit' name='submit' value='I agree to terms and charges' id="submit" />
					</div>
				</fieldset>
			</div>
        </form>
	</body>
</html>
