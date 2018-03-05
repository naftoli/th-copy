<?php
ini_set('display_errors',1);

$admin_auth = array('school'); 
require('../../header.php');
if (!$admin_user || $admin_user['auth'] != 'super') {
	echo "Permission Denied.";
	exit;
}

$info = array(
	'Chidon User Info' => array(
		'chidon_id'		=>	'Chidon ID',
		'first_name'	=>	'First Name',
		'last_name'		=>	'Last Name',
		'he_first_name'	=>	'Hebrew First Name',
		'he_last_name'	=>	'Hebrew Last Name',
		'gender'		=>	'Gender',
		'dob'			=>	'Date of Birth',
		'book'			=>	'Book',
		'grade'			=>	'Grade',
		'school'		=>	'School',
		'accomodations'	=>	'Accomodation Info',
		'between_streets'	=>	'Cross Streets',
		'admin_city'	=>	'City', 
		'admin_state'	=>	'State', 
		'allergies'		=>	'Allergies',
		'sandwich'		=>	'Sandwich',
		'sweater_size'	=>	'Sweater Size',
		'shoe_size'		=>	'Shoe Size',
		'winner_type'	=>	'Contestant / School Rep.',
		'walking'		=>	'Walk Alone',
		'bus'			=>	'Bus Number',
		'seat'			=>	'Seat Number',
		//'medal'			=>	'Medal Info',
		//'plaque'		=>	'Plaque Info',
		'test1a'		=>	'Test 1 Part 1',
		'test1b'		=>	'Test 1 Part 2',
		'test2a'		=>	'Test 2 Part 1',
		'test2b'		=>	'Test 2 Part 2',
		'test3a'		=>	'Test 3 Part 1',
		'test3b'		=>	'Test 3 Part 2', 
		'avg1'			=>	'Average Part 1',
		'avg2'			=>	'Average Part 2',
		'history'		=>	'Number of years attended Chidon',
		'team'			=>	'Team',
		'test_table'	=>	'Test Table',
		'bowling_lane'	=>	'Bowling Lane',
		'dropoff_bus'	=>	'Dropoff Bus',
		'dropoff_seat'	=>	'Dropoff Seat',
		'coach_bus'		=>	'Coach Bus',
		'school_bus'	=>	'School Bus',
		'double_decker'	=>	'Double Decker Bus',
		'seat_type'		=>	'Seat Type',
		'seat_number'	=>	'Seat Number',
		'date_paid'		=>	'Enrolled',
		'paid'			=>	'Amount Paid'
	),
	'Parent Info'	=>	array(
		'parent_id'		=>	'Admin ID',
		'parent_name'	=>	'Parent Name',
		'parent_email'	=>	'Parent Email',
		'parent_number'	=>	'Parent Contact Number',
		'parent_login'	=>	'Parent Login Info',
		'donations'		=>	'Number of Trips Sponsored'
	),
	'Chaperone Info'	=>	array(
		'chap_name'		=>	'Name',
		'chap_first_name'	=>	'Chap First Name',
		'chap_last_name'	=>	'Chap Last Name',
		'chap_phone'	=>	'Chap Contact Number',
		'chap_email'	=>	'Chap Email',
		'chap_sweater'	=>	'Chap Sweater Size',
		'chap_acc_name'	=>	'Chap Accomodation Name',
		'chap_acc_addr'	=>	'Chap Accomodation Address',
		'chap_acc_cross_st'	=>	'Chap Accomodation Cross Streets',
		'chap_acc_phone'=>	'Chap Accomodation Number',
		'chap_vehicle'	=>	'Chap Vehicle',
		'chap_school'	=>	'Chap School',
	),
	'Bunk Info'		=>	array(
		'bunk_name'				=>	'Bunk Name',
		'bunk_counselor'		=>	'Bunk Counselor',
		'bunk_c_number' 		=> 	'Bunk Counselor Number'
	)
);

require_once '../../class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

if (isset($_POST['submit'])) {	
    $data = array();
	$gender = false;
	$limit = false;
	$chidonType = '';
	$byAvg = array();
	foreach ($_POST as $k => $v) {
        if ($k == 'submit') break;
		if ($k == 'year') $year = mysql_real_escape_string(intval($v));
		else if ($k == 'genderLimit') $gender = mysql_real_escape_string($v);
		else if ($k == 'limitTo') $limit = mysql_real_escape_string($v);
		else if ($k == 'chidon_type') $chidonType = mysql_real_escape_string($v);
        else $data[] = mysql_real_escape_string($k);
    }
    
	$report = array();
    require_once 'class.reports.php';
    $r = new Reports( $year );
	
	// find out if we need to limit to certain avg
	if ($_POST['avgLow'] != '' || $_POST['avgHigh'] != 0) {
		$avgs = array(
			'tests' => $_POST['avgTests'],
			'low'	=> empty($_POST['avgLow']) ? 0 : $_POST['avgLow'],
			'high'	=> empty($_POST['avgHigh']) ? 100 : $_POST['avgHigh']
		);
		$r->setAvgs( $avgs );
	}
	
	//echo "<pre>"; print_r($data); echo "</pre>"; exit;
	
	// figure out if it's only a chaperone report
	$chap = true;
	foreach ($data as $val) {
		if (!in_array($val, array('avgLow','avgHigh')) && strpos($val, 'chap') === false) {
			$chap = false;
			break;
		}
	}
	
	// figure out if it's only a bunk report
	$bunkOnly = true;
	foreach ($data as $val) {
		if (!in_array($val, array('avgLow','avgHigh')) && strpos($val, 'bunk') === false) {
			$bunkOnly = false;
			break;
		}
	}
	
	if ($chap) $root = 'th_chidon_chaps';
	else if ($bunkOnly) $root = 'th_chidon_bunks';
	else $root = 'th_chidon';
	
	//echo $root; exit;
	
    if ($sql = $r->createSQL($data, $root, $gender, $limit, $chidonType)) {
		echo "<input type='hidden' name='sql' value=\"" . $sql . "\" />";
		echo "<input type='hidden' name='root' value='" . $root . "' />";
		$result = mysql_query($sql) or die($sql . "<br />" . mysql_error());
		while ($row = mysql_fetch_assoc($result)) {
			$report[] = $row;
		}
	} else {
		echo "There was an error.";
		exit;
	}
	
	$lookup = array(
		'accomodations'	=>	array('host', 'host_address1', 'host_address2'),
		'winner_type'	=>	array('contestant', 'school_rep'),
		'walking'		=> 	array('walk_day', 'walk_night'),
		'medal'			=>	array('medal', 'medal_number'),
		'plaque'		=>	array('plaque', 'plaque_number'),
		'parent_name'	=>	array('first', 'last'),
		'parent_number'	=>	array('admin_phone_mobile', 'admin_phone_mobile2'),
		'parent_login'	=>	array('username', 'password'),
		'avg1'			=>	array('test1a', 'test2a', 'test3a'),
		'avg2'			=>	array('test1b', 'test2b', 'test3b')
	);
}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Chidon Reports</title>
		<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs-3.3.7/jqc-1.12.4/dt-1.10.13/cr-1.3.2/fc-3.2.2/fh-3.1.2/r-2.1.1/sc-1.4.2/se-1.2.0/datatables.min.css"/>
		<style>
			body {
				font-family: sans-serif;
				font-size: 12px;
				padding-left: 3%;
				padding-right: 3%;
			}
			fieldset {
				float: left;
				width: 40%;
				padding-right: 20px;
				padding-left: 20px;
				padding-bottom: 20px;
			}
		</style>
	</head>
	
	<body>
			<?
			/*
			$files = array();
			if ($handle = opendir(getcwd())) {		
			    while (false !== ($entry = readdir($handle))) {
			        if ($entry != '.' && $entry != '..' && $entry != 'index.php' && strpos($entry, '.') !== false) {
			        	$files[] = $entry;
			        }
			    }
			    closedir($handle);
			}
			
			foreach ($files as $file) {
				$report = ucwords(str_replace('_', ' ', substr($file, 0, strpos($file, '.'))));
				echo "<li><a href='" . $file . "'>" . $report . "</a></li>";
			}
			*/
			?>
		<?php if (isset($_POST['submit'])) : ?>
			<h2>Chidon Reports</h2>
			<table id="table" class="table table-striped table-condensed">
				<thead>
					<tr>
						<?php
						foreach ($data as $column) {	
							foreach ($info as $legend => $other) {
								if (array_key_exists($column, $info[$legend])) {
									echo "<th>" . $info[$legend][$column] . "</th>";
								}
							}
						}
						?>
					</tr>
				</thead>
				<tbody>
					<?php
					$totals = array();
					foreach ($report as $row) {
						echo "<tr>";
						foreach ($data as $column) {
							if (!array_key_exists($column, $lookup)) {
								if ($column == 'history') {
									if (!empty($row[$column])) $history = explode(',', $row[$column]);
									else $history = array();
									echo "<td>" . count($history) . "</td>";
								} else if (in_array($column, array('avgTests','avgLow','avgHigh'))) {
									// don't output anything they are just avgs for sql qry
								} else {
									echo "<td>" . $row[$column] . "</td>";
								}
							} else {
								// build html output
								$html = '';
								if ($column == 'avg1' || $column == 'avg2') {
									$test = 0;
									$numTests = 0;
									foreach ($lookup[$column] as $val) {
										if (intval($row[$val]) > 0) {
											$numTests++;
											$test += intval($row[$val]);
										}
									}
									// now that 3 tests have been done, divide by 3
									$numTests = 3;
									$avg = $test > 0 ? number_format(($test / $numTests), 2) : 0;
									$html .= $avg;
								} else if ($column == 'winner_type') {
									if (intval($row[$lookup[$column][1]])) {
										$html .= 'school rep.';
									} else if (intval($row[$lookup[$column][0]])) {
										$html .= 'contestant';
									}
								} else if ($column == 'walking') {
									if (intval($row[$lookup[$column][1]]) == 1) {
										$html .= "yes";
									} else if (intval($row[$lookup[$column][0]]) == 1 && intval($row[$lookup[$column][1]]) == 0) {
										$html .= "day only";
									} else if (intval($row[$lookup[$column][0]]) == 0 && intval($row[$lookup[$column][1]]) == 0) {
										$html .= "no";
									} 
								} else {
									foreach ($lookup[$column] as $val) {
										$html .= $row[$val] . ", ";
									}
									$html = substr($html, 0, strlen($html) - 2);
								}
								echo "<td>" . $html . "</td>";
							}
						}
						echo "</tr>";
					}
					?>
				</tbody>
			</table>
		<?php else : ?>		
		<div class="container">
			<h2>Chidon Reports</h2>
			<div>
				Use the following form to choose what you want to have on your report.
			</div>
			<br />
			<form method="post" action="index.php">
				<?php foreach ($info as $legend => $other) : ?>
					<fieldset>
						<legend><?=$legend?></legend>
						<table>
							<?php foreach ($other as $column => $desc) : ?>
								<tr>
									<td>
										<input type="checkbox" name="<?=$column?>" style="margin-right: 5px !important;" />
									</td>
									<td class="form-group">
										<?=$desc?>
									</td>
								</tr>
							<?php endforeach; ?>
						</table>
					</fieldset>
				<?php endforeach; ?>
				<fieldset>
					<legend>Chidon Year</legend>
					<select name="year">
						<?php
						for ($i = 5777; $i <= $year; $i++) {
							echo "<option value='" . $i . "'";
							if ($i == $year) echo " selected ";
							echo ">" . $i . "</option>";
						}
						?>
					</select>
				</fieldset>
				<fieldset>
					<legend>Gender</legend>
					<input type="radio" name="genderLimit" value='F' /> Girls<br />
					<input type="radio" name="genderLimit" value='M' /> Boys<br />
				</fieldset>
				<fieldset>
					<legend>Limit to</legend>
					<input type="radio" name="limitTo" value='contestant' /> Contestant / Representative<br />
					<input type="radio" name="limitTo" value='activated' /> Shabbaton Enrollment Activated<br />
					<input type="radio" name="limitTo" value='paid' /> Shabbaton Paid<br />
					<input type="radio" name="limitTo" value='confirmed' /> Shabbaton Confirmed
				</fieldset>
				<fieldset style="margin-top: -20px;">
					<legend>Mark Avg</legend>
					<input type="radio" name="avgTests" value="2" /> First 2 tests<br />
					<input type="radio" name="avgTests" value="3" /> All 3 tests<br />
					<table>
						<tr>
							<td>Low Mark:</td>
							<td><input type="text" name="avgLow" size="3" /></td>
						</tr>
						<tr>
							<td>High Mark:</td>
							<td><input type="test" name="avgHigh" size = 3 /></td>
						</tr>
					</table>					 
				</fieldset>
				<fieldset>
					<legend>Limit Chaperone To (Only works when choosing Chaperone Info ONLY)</legend>
					<input type="radio" name="chidon_type" value='boys'> Boys Chidon<br />
					<input type="radio" name="chidon_type" value='girls'> Girls Chidon<br />
				</fieldset>
				<div style="clear: both"></div>
				<input type="submit" name="submit" value="Create Report" />
			</form>
		</div>
		<br />
		<?php endif; ?>
	</body>
	<script type="text/javascript" src="https://cdn.datatables.net/v/bs-3.3.7/jqc-1.12.4/dt-1.10.13/cr-1.3.2/fc-3.2.2/fh-3.1.2/r-2.1.1/sc-1.4.2/se-1.2.0/datatables.min.js"></script>
	<script>
		<?php if (isset($_POST['submit'])) : ?>
		$('#table').DataTable({
			paging : false
		});
		<?php endif; ?>
	</script>
</html>