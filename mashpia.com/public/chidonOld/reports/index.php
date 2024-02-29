<?php
ini_set('display_errors',1);
ini_set('error_reporting', E_ALL);

$admin_auth = array('school'); 
require('../../header.php');
if (!$admin_user || $admin_user['auth'] != 'super') {
	echo "Permission Denied.";
	exit;
}

$info = array(
	'Chidon User Info' => array(
		'chidon_id'		=>	'Chidon ID',
    'user_id'    =>  'User ID',
		'user_serial'   =>  'Serial Number',
		'first_name'	=>	'First Name',
		'last_name'		=>	'Last Name',
		'he_first_name'	=>	'Hebrew First Name',
		'he_last_name'	=>	'Hebrew Last Name',
		'gender'		=>	'Gender',
		'dob'			=>	'Date of Birth',
		'book'			=>	'Book',
		'grade'			=>	'Grade',
		'school_id'     =>  'School ID',
		'school'		=>	'School',
    'school_address'  => 'School Address',
    'school_city'   =>  'School City',
    'school_state'  =>  'School State',
    'school_postal' =>  'School Zip',
    'school_country'  =>  'School Country',
    'school_phone'  =>  'School Phone',
		'host_name'		=>	'Host Name',
		'host_number'	=>	'Host Number',
		'host_address_num'	=>	'Accomodation Address Number',
		'host_address'	=>	'Accomodation Address',
		'between_streets'	=>	'Cross Streets',
		'walking_zone'	=>	'Walking Zone',
		'admin_city'	=>	'City', 
		'admin_state'	=>	'State', 
		'allergies'		=>	'Allergies',
		'sandwich'		=>	'Sandwich',
		'sweater_size'	=>	'Sweater Size',
		'shoe_size'		=>	'Shoe Size',
		'test_type' 	=>	'Track',
		'eligibility'   =>  'Eligibility Status',
    'chidon_final_mark' =>  'Chidon Final Mark',
    'shabbaton_trophy'   =>  'Trohpy Contestant',
		'rep_type'      =>  'Type of Rep',
		'walking'		=>	'Walk Alone',
		'walking_group'	=>	'Walking Group',
		'bunk_number'	=>	'Bunk',
		'test1'         =>  'Test 1 Mark',
		'test2'         =>  'Test 2 Mark',
		'test3'         =>  'Test 3 Mark',
		'test4'         =>  'Test 4 Mark',
		//'bus'			=>	'Bus Number',
		//'seat'			=>	'Seat Number',
		//'medal'			=>	'Medal Info',
		//'plaque'		=>	'Plaque Info',
//		'test1a'		=>	'Test 1 Part 1',
//		'test1b'		=>	'Test 1 Part 2',
//		'test2a'		=>	'Test 2 Part 1',
//		'test2b'		=>	'Test 2 Part 2',
//		'test3a'		=>	'Test 3 Part 1',
//		'test3b'		=>	'Test 3 Part 2',
//		'mm_test1'      =>  'Mitzva Maven Test 1',
//		'mm_test2'      =>  'Mitzva Maven Test 2',
//		'mm_test3'      =>  'Mitzva Maven Test 3',
//		'avg1'			=>	'Average Part 1',
//		'avg2'			=>	'Average Part 2',
		'history'		=>	'Number of years attended Chidon',
		'poll'			=>	'Poll',
		'team'			=>	'Team',
		'test_table'	=>	'Test Table',
		'test_lang'		=>	'Test Language',
		'bowling_lane'	=>	'Bowling Lane',
		// 'workshop_number'	=>	'Workshop Number',
		// 'dropoff_bus'	=>	'Dropoff Bus',
		// 'dropoff_seat'	=>	'Dropoff Seat',
		'coach_bus'		=>	'Coach Bus',
		'school_bus'	=>	'School Bus',
		'open_air_bus'	=>	'Open Air Bus',
		'sunday_pm_bus' =>	'Sunday Bus',
		//'seat_type'		=>	'Seat Type',
		//'seat_number'	=>	'Seat Number',
		'date_paid'		=>	'Enrolled',
		'paid'			=>	'Amount Paid',
		'cert_number'	=>  'Certificate Code', 
		'transportation'=>	'Transportation after Event', 
		'airport'		=>	'Airport after Event', 
		'flight'		=>	'Time of flight',
		'snack_way_back'=>	'Snack / Sandwich for after Event', 
		'non_th_school'	=>	'Non TH School Name',
		'affiliation'	=>	'Affiliation',
		'home_address'	=>	'Home Address (AK)', 
		'home_city'		=>	'Home City (AK)', 
		'home_state'	=>	'Home State (AK)', 
		'home_zip'		=>	'Home Zip (AK)', 
		'medications'	=>	'Medications', 
		'friend_name'	=>	'Friend Name', 
		'bunk_request'	=>	'Bunk Request', 
		'anash_airport'	=>	'Airport (AK)', 
		'anash_arrival'	=>	'Arrival (AK)',
		'known_family'	=>	'Known Family', 
		'morning_pickup'=>	'Morning Pickup',
        'trip_option'   =>  'Trip Option',
        'raised'        =>  'Chidon Drive Raised',
        'cd_rohr'       =>  'Rohr',
        'cd_total'      =>  'Total Chidon Drive',
        'cd_balance'    =>  'Registration Balance'
	),
	'Parent Info'	=>	array(
		'parent_id'		=>	'Admin ID',
		'parent_name'	=>	'Parent Name',
		'parent_email'	=>	'Parent Email',
		'parent_address'=>  'Parent Address',
		'parent_number'	=>	'Parent Contact Number',
		'parent_login'	=>	'Parent Login Info',
		'donations'		=>	'Number of Trips Sponsored'
	),
	'Chaperone Info'	=>	array(
		'chap_id'			=>	'Chaperone ID',
		'chap_type'		=>	'Chaperone / Walking Counselor',
		// 'chap_name'		=>	'Name',
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
		'chap_walking_zone'	=>	'Chap Walking Zone',
	),
	'Bunk Info'		=>	array(
		'bunk_name'				=>	'Bunk Name',
		'bunk_counselor'		=>	'Counselor',
		'bunk_c_number' 		=> 	'Counselor Number',
		'bunk_c_coach_bus'		=> 	'Bunk Thursday Bus',
		'bunk_c_school_bus'		=> 	'Bunk Friday Bus',
		'bunk_c_double_decker'	=> 	'Bunk Sunday Bus',
		//'bunk_grade'			=>	'Bunk Grade',
		'bunk_walking_zone'		=>	'Bunk Walking Zone',
		'bunk_host_name'		=>	'Counselor Host Name',
		'bunk_host_address1'	=>	'Counselor Host Address Number',
		'bunk_host_address2'	=>	'Counselor Host Address Street',
		'bunk_host_between_strets'	=>	'Counselor Host Cross Streets',
		'bunk_chidon_type'		=>	'Bunk Chidon Type',
	),
	
	'Walking Counselor (BETA)'		=>	array(
		'walking_counselor_name'	=> 'Walking Counselor Name',
		'walking_counselor_number'	=> 'Walking Counselor Number',
		'walking_counselor_zone'	=> 'Walking Counselor Zone',
	),
	
	'Walking Chaperone (BETA)'		=>	array(
		'walking_chaperone_name'	=> 'Walking Chaperone Name',
		'walking_chaperone_number'	=> 'Walking Chaperone Number',
		'walking_chaperone_zone'	=> 'Walking Chaperone Zone',
	)
);

require_once '../../class.globalSettings.php';
$year = GlobalSettings::getChidonRegYear();

if (isset($_POST['submit'])) {	
    $data = array();
	$gender = false;
	$limit = false;
	$chidonType = '';
	$byAvg = array();
	$rohr = false;
	$cd_total = false;
	$cd_balance = false;
	foreach ($_POST as $k => $v) {
		if ($k == 'submit') break;
		if ($k == 'year') $year = mysql_real_escape_string(intval($v));
		else if ($k == 'genderLimit') $gender = mysql_real_escape_string($v);
		else if ($k == 'limitTo') $limit = mysql_real_escape_string($v);
		else if ($k == 'chidon_type') $chidonType = mysql_real_escape_string($v);
		else if (strpos($k, 'cd_') !== false) {
            if ($k == 'cd_rohr') $rohr = true;
            else if ($k == 'cd_total') $cd_total = true;
            else if ($k == 'cd_balance') $cd_balance = true;
		    continue; // don't add chidon drive extras to data
        }
		else if ( !in_array( $k, $data ) ) $data[] = mysql_real_escape_string($k);
		if ( $k == 'accomodations' ) $data[] = 'between_streets'; // add between streets to accomodation info
	}
    
	$report = array();
    require_once 'class.reports.php';
    $r = new Reports( $year );
	
	// find out if we need to limit to certain avg
	if ((isset($_POST['avgLow']) && $_POST['avgLow'] != '') || (isset($_POST['avgHigh']) && $_POST['avgHigh'] != 0)) {
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
		'host_address_num'	=>	array('host_street_num', 'host_street_num_suffix'),
		'host_address'	=>	array('host_street', 'host_street_apt'),
		'between_streets'	=>	array('between_streets1', 'between_streets2'),
		'medal'			=>	array('medal', 'medal_number'),
		'plaque'		=>	array('plaque', 'plaque_number'),
		'parent_name'	=>	array('first', 'last'),
		'parent_number'	=>	array('admin_phone_mobile', 'admin_phone_mobile2'),
		'parent_login'	=>	array('username', 'password'),
    'parent_address'=>  array('admin_address1', 'admin_city', 'admin_state', 'admin_postal', 'admin_country'),
    'grade'         =>  array('class_grade', 'class_sub'),
    'eligibility'   =>  array('shabbaton_maven', 'shabbaton_pro', 'shabbaton_expert', 'shabbaton_trophy'),
    'school_address' =>  array('school_address1', 'school_address2'),
	);
}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
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
                  if ($column == 'raised') {
                      // check if we need to add any chidon drive stuff
                      if ($rohr) echo "<th>Rohr</th>";
                      if ($cd_total) echo "<th>Total Chidon Drive</th>";
                      if ($cd_balance) echo "<th>Registration Balance</th>";
                  }
								}
							}
						}
						?>
					</tr>
				</thead>
				<tbody>
					<?php
					$totals = array();
					//echo "<pre>"; print_r( $data ); echo "</pre>";
					foreach ($report as $index => $row) {
						echo "<tr>";
						foreach ($data as $column) {
							if (!array_key_exists($column, $lookup)) {
								if ($column == 'history') {
									if (!empty($row[$column])) $history = explode(',', $row[$column]);
									else $history = array();
									echo "<td>" . count($history) . "</td>";
								} else if ($column == 'chap_type') {
									if ($row[$column] == 1) echo "<td>Chaperone</td>";
									else echo "<td>Walking Counselor</td>";
								} else if ( in_array($column, ['walking','snack_way_back','known_family','morning_pickup']) ) {
									if ( intval( $row[$column] == 1 ) ) echo "<td>Yes</td>"; 
									else echo "<td>No</td>";
								} else if (in_array($column, array('avgTests','avgLow','avgHigh'))) {
									// don't output anything they are just avgs for sql qry
								} else if ($column == 'transportation') {
									switch ( intval($row[$column]) ) {
										case 0:
											echo "<td>School Bus</td>";
											break;
										case 1:
											echo "<td>Chidon Bus to Airport</td>";
											break;
										case 2:
											echo "<td>Chidon Bus to Crown Heights</td>";
											break;
										case 3:
											echo "<td>Private Ride</td>";
											break;
									}
              } else if ($column == 'trip_option') {
                  switch ( intval($row[$column]) ) {
                      case 1:
                          echo "<td>New York Trip</td>";
                          break;
                      case 2:
                          echo "<td>California Trip</td>";
                          break;
                      case 3:
                          echo "<td>Family Trip</td>";
                          break;
                      default:
                          echo "<td></td>";
                          break;
                  }
              } else if (in_array($column, ['shabbaton_trophy', 'khk_rep', 'school_rep'])) {
                  if ( intval( $row[$column] == 1 ) ) echo "<td>Yes</td>";
                  else echo "<td>No</td>";
              } else if ($column == 'chidon_final_mark') {
                  echo "<td>" . intval($row[$column]) * 2 . "%</td>";
              } else {
                  echo "<td>" . $row[$column] . "</td>";
                  if ($column == 'raised') {
                      // check if we need to add any chidon drive stuff
                      if ($rohr) {
                          if (intval($row[$column]) >= 270) echo "<td>100</td>";
                          else echo "<td>0</td>";
                      }
                      if ($cd_total) {
                          $total = intval($row[$column]);
                          if ($total >= 270) $total += 100;
                          echo "<td>" . $total . "</td>";
                      }
                      if ($cd_balance) {
                          $half = intval($row[$column]) / 2;
                          $balance = 185 - $half;
                          if ($balance < 0) $balance = 0;
                          echo "<td>" . $balance . "</td>";
                      }
                  }
								}
							} else {
								// build html output
								$html = '';
								if ($column == 'avg1' || $column == 'avg2') {
									$test = 0;
									$numTests = 0;
									foreach ($lookup[$column] as $val) {
										if (floatval($row[$val]) > 0) {
											$numTests++;
											$test += floatval($row[$val]);
										}
									}
									// now that 3 tests have been done, divide by 3
									$numTests = 3;
									$avg = $test > 0 ? number_format(($test / $numTests), 2) : 0;
									$html .= $avg;
								} else if ( in_array( $column, ['host_address', 'host_address_num'] ) ) {
									foreach ( $lookup[$column] as $val ) {
										$html .= $row[$val] . ' ';
									}
								} else if ( $column == 'between_streets' ) {
									$html .= $row[$lookup[$column][0]] . ' and ' . $row[$lookup[$column][1]];
                                } else if ( $column == 'eligibility' ) {
								    if ( intval($row[$lookup[$column][0]]) ) {
								        $html .= 'Sweater';
                                    } else if ( intval($row[$lookup[$column][1]]) ) {
								        $html .= "Sweater and Gifts";
                                    } else if ( intval($row[$lookup[$column][2]]) || intval($row[$lookup[$column][3]]) ) {
								        $html .= "Prizes and Trips";
                                    }
								} else {
								    $sep = ', ';
								    if ($column == 'grade') $sep = '-';
									foreach ($lookup[$column] as $val) {
										$html .= $row[$val] . $sep;
									}
									$html = mb_substr($html, 0, mb_strlen($html) - 1);
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
<!--					<input type="radio" name="limitTo" value='contestant' /> Contestant / Representative<br />
					<input type="radio" name="limitTo" value='activated' /> Shabbaton Enrollment Activated<br />-->
					<input type="radio" name="limitTo" value='paid' /> Shabbaton Paid<br />
					<input type="radio" name="limitTo" value='notDeleted' /> Not Deleted by BC<br />
<!--					<input type="radio" name="limitTo" value='confirmed' /> Shabbaton Confirmed-->
				</fieldset>
<!--				<fieldset>-->
<!--					<legend>Mark Avg</legend>-->
<!--					<input type="radio" name="avgTests" value="2" /> First 2 tests<br />-->
<!--					<input type="radio" name="avgTests" value="3" /> First 3 tests<br />-->
<!--                    <input type="radio" name="avgTests" value="4" /> All 4 tests<br />-->
<!--					<table>-->
<!--						<tr>-->
<!--							<td>Low Mark:</td>-->
<!--							<td><input type="text" name="avgLow" size="3" /></td>-->
<!--						</tr>-->
<!--						<tr>-->
<!--							<td>High Mark:</td>-->
<!--							<td><input type="test" name="avgHigh" size = 3 /></td>-->
<!--						</tr>-->
<!--					</table>					 -->
<!--				</fieldset>-->
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