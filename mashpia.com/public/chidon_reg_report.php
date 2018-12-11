<?
$admin_auth = array('school'); 
require('header.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<HTML>

    <HEAD>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Chidon Registration Report 5776</title>
        <style type='text/css'>
            table {
                font-size: 12px;
            }
            th, td {
                padding: 3px 10px;
            }
            td {
            	vertical-align: top;
            	text-align: center;
            }
            .newPage {
                page-break-after: always;
            }
        </style>
    </HEAD>

    <BODY>
        <h1>Chidon Registration Report 5776</h1>
        
        <h2>Boys</h2>
        <?
        $schools = array();
        $sql = "select * from chidon_schools 
        		join chidon_reg using (chidon_schools_id) 
        		where year = 5776 
        		and gender = 'boys' 
        		order by school_name";
		$result = mysql_query($sql);
		while ($row = mysql_fetch_assoc($result)) {
			$schools[$row['chidon_schools_id']] = $row;
		}
		
		$gtotals['winner']['boys'] = 0;
		$gtotals['runnerUp']['boys'] = 0;
		$details = array();
		foreach ($schools as $id => $arr) {
			$sql = "select * from chidon_reg where chidon_schools_id = $id order by last_name, name";
			$result = mysql_query($sql);
			while ($row = mysql_fetch_assoc($result)) {
				$details[$id][] = $row;
				$gtotals[$row['type']]['boys']++;
			}
		}
		
		//echo "<pre>"; print_r($details); echo "</pre>";
		$num = 1;
		$reset = false;
		foreach ($schools as $id => $school) {			
			if (isset($details[$id])) {
				?>
				<table>
					<tr>
						<th>Order</th>
						<th>ID</th>
						<th>School</th>
						<th>Chaperone Name</th>
						<th>Chaperone Number</th>
						<th>Date Reserved</th>
						<th>Paid</th>
						<th>Photo</th>
						<th>Grade</th>
						<th>Type</th>
						<th>First Name</th>
						<th>Last Name</th>
						<th>Hebrew First Name</th>
						<th>Hebrew Last Name</th>
						<th>Book</th>
						<th>Test 1</th>
						<th>Replacement Questions</th>
						<th>Test 2</th>
						<th>Test 3</th>
						<th>Average Mark</th>
						<th>Sweater Size</th>
						<th>Parent Name</th>
						<th>Parent Email</th>
						<th>Father Cell</th>
						<th>Mother Cell</th>
						<th>Needs Accomodation Help</th>
						<th>Accomodation Family</th>
						<th>Accomodation Address</th>
						<th>Accomodation Phone</th>
						<th>Allergies</th>
						<th>Add to Whatsapp</th>
						<th>Can walk alone</th>
						<th>Notes</th>
					</tr>
					<? 
					foreach ($details[$id] as $row) {
						
						if (!$reset && $school['gender'] != 'girls') {
							$num = 1;
							$reset = true;
						}
						if ($num > 53) {
							$num = 1;
						}
				
						$paid = ($row['paid'] == 0 ? 'no' : 'yes');
						$help = ($row['help'] == 0 ? 'no' : 'yes');
						
						$total = $row['mark1'] + $row['mark2'] + $row['bonus'];
						$div = 2;
						if ($row['mark3'] > 0) {
							$total += $row['mark3'];
							$div++;
						}
						$avg = round($total / $div);
						$hname = explode(' ', $row['hname']);
						$num = count($hname);
						$hlname = $hname[$num-1];
						$hfname = '';
						for ($i = 0; $i < ($num-1); $i++) {
							$hfname .= $hname[$i] . ' ';
						}
						
						$whatsapp = $row['whatsapp'] ? 'yes' : 'no';
						$walkAlone = $row['walk_alone'] ? 'yes' : 'no';
						
						$photo = '';
						if (!empty($row['file'])) {
							if (strpos($row['file'], 'img/') !== false)	{
								$photo = "<img src='/mobile/chidon/" . $row['file'] . "' width='50' />";
							} else {
								$photo = "<img src='chidon/photos/" . $row['file'] . "' width='50' />";
							}
						}
						
						echo "<tr><td>" . $num++ . "</td><td>" . $row['chidon_reg_id'] . "</td><td>" . 
							$school['school_name'] . "</td><td>" . $school['chaperone_name'] . "</td><td>" . 
							$school['chaperone_phone'] . "</td><td>" . 
							$row['date'] . "</td><td>" . $paid . "</td><td>" . 
							(!empty($photo) ? $photo . "</td><td>" : "</td><td>") . 
							$row['grade'] . "</td><td>" . $row['type'] . "</td><td>" . $row['name'] . "</td><td>" . 
							$row['last_name'] . "</td><td>" . $hfname . "</td><td>" . 
							$hlname . "</td><td>" . $row['book'] . "</td><td>" . $row['mark1'] . 
							"</td><td>" . $row['bonus'] . "</td><td>" . $row['mark2'] . "</td><td>" . 
							$row['mark3'] . "</td><td>" . $avg . "</td><td>" . $row['size'] . "</td><td>" . 
							$row['parent_name'] . "</td><td>" . $row['parent_email'] . 
							"</td><td>" . $row['parent_cell'] . "</td><td>" . $row['parent_cell2'] . "</td><td>" . 
							$help . "</td><td>" . $row['family'] . "</td><td>" . 
							$row['address'] . "</td><td>" . $row['phone'] . "</td><td>" . 
							$row['allergies'] . "</td><td>" . $whatsapp . "</td><td>" . $walkAlone . 
							"</td><td>" . $row['notes'] . "</td></tr>";
					} 
					?>
				</table>
				<?=$school['school_name']?>: <?=count($details[$id])?> participants.
				<br /><br />
			<? } 
			} ?>
		<h2>Girls</h2>	
		<?
        $schools = array();
        $sql = "select * from chidon_schools 
        		join chidon_reg using (chidon_schools_id) 
        		where year = 5776 
        		and gender = 'girls' 
        		order by school_name";
		$result = mysql_query($sql);
		while ($row = mysql_fetch_assoc($result)) {
			$schools[$row['chidon_schools_id']] = $row;
		}
		
		$gtotals['winner']['girls'] = 0;
		$gtotals['runnerUp']['girls'] = 0;
		$details = array();
		foreach ($schools as $id => $arr) {
			$sql = "select * from chidon_reg 
					where chidon_schools_id = $id 
					order by last_name, name";
			$result = mysql_query($sql);
			while ($row = mysql_fetch_assoc($result)) {
				$details[$id][] = $row;
				$gtotals[$row['type']]['girls']++;
			}
		}
		
		//echo "<pre>"; print_r($details); echo "</pre>";
		$num = 1;
		$reset = false;
		foreach ($schools as $id => $school) {			
			if (isset($details[$id])) {
				?>
				<table>
					<tr>
						<th>Order</th>
						<th>ID</th>
						<th>School</th>
						<th>Chaperone Name</th>
						<th>Chaperone Number</th>
						<th>Date Reserved</th>
						<th>Paid</th>
						<th>Photo</th>
						<th>Grade</th>
						<th>Type</th>
						<th>First Name</th>
						<th>Last Name</th>
						<th>Hebrew First Name</th>
						<th>Hebrew Last Name</th>
						<th>Book</th>
						<th>Test 1</th>
						<th>Replacement Questions</th>
						<th>Test 2</th>
						<th>Test 3</th>
						<th>Average Mark</th>
						<th>Sweater Size</th>
						<th>Parent Name</th>
						<th>Parent Email</th>
						<th>Parent Cell</th>
						<th>Needs Accomodation Help</th>
						<th>Accomodation Family</th>
						<th>Accomodation Address</th>
						<th>Accomodation Phone</th>
						<th>Arrival Airport</th>
						<th>Airline / Flight Number</th>
						<th>Arrival Time</th>
						<th>Departure Airport</th>
						<th>Airline / Flight Number</th>
						<th>Departure Time</th>
						<th>Notes</th>
					</tr>
					<? 
					foreach ($details[$id] as $row) {
						
						if (!$reset && $school['gender'] != 'girls') {
							$num = 1;
							$reset = true;
						}
						if ($num > 53) {
							$num = 1;
						}
				
						$paid = ($row['paid'] == 0 ? 'no' : 'yes');
						$help = ($row['help'] == 0 ? 'no' : 'yes');
						
						$total = $row['mark1'] + $row['mark2'] + $row['bonus'];
						$div = 2;
						if ($row['mark3'] > 0) {
							$total += $row['mark3'];
							$div++;
						}
						$avg = round($total / $div);
						echo "<tr><td>" . $num++ . "</td><td>" . $row['chidon_reg_id'] . "</td><td>" . 
							$school['school_name'] . "</td><td>" . $school['chaperone_name'] . "</td><td>" . 
							$school['chaperone_phone'] . "</td><td>" . 
							$row['date'] . "</td><td>" . $paid . "</td><td>" . 
							(!empty($row['file']) ? "<img src='chidon/photos/" . $row['file'] . 
							"' width='50' /></td><td>" : "</td><td>") . $row['grade'] . "</td><td>" . 
							$row['type'] . "</td><td>" . $row['name'] . "</td><td>" . 
							$row['last_name'] . "</td><td>" . $row['hname'] . "</td><td>" . 
							$row['hname_last'] . "</td><td>" . $row['book'] . "</td><td>" . $row['mark1'] . 
							"</td><td>" . $row['bonus'] . "</td><td>" . $row['mark2'] . "</td><td>" . 
							$row['mark3'] . "</td><td>" . $avg . "</td><td>" . $row['size'] . "</td><td>" . 
							$row['parent_name'] . "</td><td>" . $row['parent_email'] . 
							"</td><td>" . $row['parent_cell'] . "</td><td>" . 
							$help . "</td><td>" . $row['family'] . "</td><td>" . 
							$row['address'] . "</td><td>" . $row['phone'] . 
							"</td><td>" . $row['arr_airport'] . "</td><td>" . 
							$row['arr_number'] . "</td><td>" . $row['arr_time'] . "</td><td>" . 
							$row['dep_airport'] . "</td><td>" . $row['dep_number'] . "</td><td>" . 
							$row['dep_time'] . "</td><td>" . $row['notes'] . "</td></tr>";
					} 
					?>
				</table>
				<?=$school['school_name']?>: <?=count($details[$id])?> participants.
				<br /><br />
			<? } 
			} ?>
			
			<h2>Totals</h2>
			<table>
				<tr>
					<th>Winners Boys</th>
					<th>Winners Girls</th>
					<th>Runner Ups Boys</th>
					<th>Runner Ups Girls</th>
				</tr>
				<tr>
					<?
					echo "<pre>";
					//print_r($gtotals);
					echo "</pre>";
					$boyTotals = 0;
					$girlTotals = 0;
					foreach ($gtotals as $type => $other) {
						if ($type == 'contestant') break;
						foreach ($other as $gender => $total) {
							if ($gender == 'boys') $boyTotals += $total;
							else if ($gender == 'girls') $girlTotals += $total;
							echo "<td>" . $total . "</td>";
						}
					}
					?>
				</tr>			
			</table>
			<br />
			<table>
				<tr>
					<th>Total Boys</th>
					<th>Total Girls</th>
				</tr>
				<?
				echo "<tr><td>" . $boyTotals . "</td><td>" . $girlTotals . "</td></tr>";
				?>
			</table>
    </body>
</html>