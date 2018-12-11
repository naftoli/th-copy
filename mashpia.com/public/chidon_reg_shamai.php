<?
$admin_auth = array('school'); 
require('header.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<HTML>

    <HEAD>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Chidon Registration Report 5775</title>
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
        <h1>Chidon Registration Report 5775</h1>
        
        <?
        $schools = array();
        $sql = "select * from chidon_schools where year = 5775 order by gender desc, school_name asc";
		$result = mysql_query($sql);
		while ($row = mysql_fetch_assoc($result)) {
			$schools[$row['chidon_schools_id']] = $row;
		}
		
		$details1 = array();
		foreach ($schools as $id => $arr) {
			$sql = "select * from chidon_reg 
				where chidon_schools_id = $id 
				and grade in ('4','5') 
				order by last_name, name";
			$result = mysql_query($sql);
			while ($row = mysql_fetch_assoc($result)) {
				$details1[$id][] = $row;
			}
		}
		
		$details2 = array();
		foreach ($schools as $id => $arr) {
			$sql = "select * from chidon_reg 
				where chidon_schools_id = $id 
				and grade in ('6','7','8') 
				order by last_name, name";
			$result = mysql_query($sql);
			while ($row = mysql_fetch_assoc($result)) {
				$details2[$id][] = $row;
			}
		}
		
		
		//echo "<pre>"; print_r($details); echo "</pre>";
		$num = 1;
		$reset = false;
		foreach ($schools as $id => $school) {			
			if (isset($details1[$id])) {
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
						<th>Mark 1</th>
						<th>Mark 2</th>
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
					foreach ($details1[$id] as $row) {
						
						if (!$reset && $school['gender'] != 'girls') {
							$num = 1;
							$reset = true;
						}
						if ($num > 53) {
							$num = 1;
						}
				
						$paid = ($row['paid'] == 0 ? 'no' : 'yes');
						$help = ($row['help'] == 0 ? 'no' : 'yes');
						echo "<tr><td>" . $num++ . "</td><td>" . $row['chidon_reg_id'] . "</td><td>" . 
							$school['school_name'] . "</td><td>" . $school['chaperone_name'] . "</td><td>" . 
							$school['chaperone_phone'] . "</td><td>" . 
							$row['date'] . "</td><td>" . $paid . "</td><td>" . 
							(!empty($row['file']) ? "<img src='chidon/photos/" . $row['file'] . 
							"' width='50' /></td><td>" : "</td><td>") . $row['grade'] . "</td><td>" . 
							$row['type'] . "</td><td>" . $row['name'] . "</td><td>" . 
							$row['last_name'] . "</td><td>" . $row['hname'] . "</td><td>" . 
							$row['hname_last'] . "</td><td>" . $row['book'] . "</td><td>" . $row['mark1'] . 
							"</td><td>" . $row['mark2'] . "</td><td>" . $row['size'] . "</td><td>" . 
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
				<?=$school['school_name']?>: <?=count($details1[$id])?> participants.
				<br /><br />
			<? } 
			} 
			
		$num = 1;
		$reset = false;
		foreach ($schools as $id => $school) {			
			if (isset($details2[$id])) {
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
						<th>Mark 1</th>
						<th>Mark 2</th>
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
					foreach ($details2[$id] as $row) {
						
						if (!$reset && $school['gender'] != 'girls') {
							$num = 1;
							$reset = true;
						}
						if ($num > 53) {
							$num = 1;
						}
				
						$paid = ($row['paid'] == 0 ? 'no' : 'yes');
						$help = ($row['help'] == 0 ? 'no' : 'yes');
						echo "<tr><td>" . $num++ . "</td><td>" . $row['chidon_reg_id'] . "</td><td>" . 
							$school['school_name'] . "</td><td>" . $school['chaperone_name'] . "</td><td>" . 
							$school['chaperone_phone'] . "</td><td>" . 
							$row['date'] . "</td><td>" . $paid . "</td><td>" . 
							(!empty($row['file']) ? "<img src='chidon/photos/" . $row['file'] . 
							"' width='50' /></td><td>" : "</td><td>") . $row['grade'] . "</td><td>" . 
							$row['type'] . "</td><td>" . $row['name'] . "</td><td>" . 
							$row['last_name'] . "</td><td>" . $row['hname'] . "</td><td>" . 
							$row['hname_last'] . "</td><td>" . $row['book'] . "</td><td>" . $row['mark1'] . 
							"</td><td>" . $row['mark2'] . "</td><td>" . $row['size'] . "</td><td>" . 
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
				<?=$school['school_name']?>: <?=count($details2[$id])?> participants.
				<br /><br />
			<? } 
			} ?>
    </body>
</html>