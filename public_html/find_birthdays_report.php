<?
$admin_auth = array('school'); 
require('header.php');
require_once('calendar.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<HTML>

<HEAD>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>Birthday Report</title>
	<link href="admin_styles.css" rel="stylesheet" type="text/css">
	<script type="text/javascript" src="scripts/jquery-1.8.3.js"></script>
	<SCRIPT type="text/javascript" src="icalendar.js"></SCRIPT>
	<script type="text/javascript">
		$( function() { 
			$(".add").click( function() { 
				var user_id = $(this).find('input[type=checkbox]').attr('name');
				//alert( user_id );	      			
				if ( $(this).find('input').is(':checked') ) {
					//run script to add one to hebrew dob
					$.post( 'ajax/hebrewDateAdd.php', {user_id : user_id}, function(data){
						//alert( data );
						location.reload();
					});
				}        			
			});
			
			$(".subtract").click( function() { 
				var user_id = $(this).find('input[type=checkbox]').attr('name');
				//alert( user_id );
				if ( $(this).find('input').is(':checked') ) {
					//run script to subtract one from hebrew dob
					$.post( 'ajax/hebrewDateSubtract.php', {user_id : user_id}, function(data){
						//alert( data );
						location.reload();
					});
				}        			
			});
			
			$("input[name=submit]").click( function() {
				if ( !$(".dob").is(":checked") ) {
					alert('You must at least choose one birthday type.\nPlease try again.');
					return false;
				}
			});
		});
	</script>
	<style type='text/css'>
		table {
			font-size: 12px;
		}
		th, td {
			padding: 3px 10px;
		}
		fieldset {
			border: 1px solid white;
			padding: 10px;
			padding-top: 0px;
			-moz-border-radius: 10px;
			-webkit-border-radius: 10px;
			border-radius: 10px;
			line-height: 1.5;
		}
		legend {
			margin-left: 20px;
			padding: 5px;
			color: purple;
		}
		.page-break {
			page-break-after: always;
		}
		@media print {
			.no-print {
				display: none;
			}
		}
	</style>
</HEAD>

<BODY>
	<? include('admin_header.php'); ?>
	<h1>Birthday Report</h1>
	<? 
	if ( !isset( $_POST['submit'] ) ) {
	?>
	<form action="find_birthdays_report.php" method="post">
		<fieldset>
			<legend>
				Select Birthday Type
			</legend>
			<input type="radio" name="dob" value="he" class="dob" checked="checked" /> Hebrew Birthday<br />
			<input type="radio" name="dob" value="en" class="dob" /> English Birthday
		</fieldset>
		<fieldset>
			<legend>
				Select Dates
			</legend>
			<span class='dates'>
				<INPUT type="hidden" name="start_date" value="<?=unixtojd()?>">
				<LABEL>
					From: 
					<INPUT type="text" name="start_date_disp" READONLY value="<?=es(dateToHebrew(unixtojd()))?>" onClick="getDate(this.form, 'start_date', true);"/>
				</LABEL>
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<INPUT type="hidden" name="end_date" value="<?=unixtojd()+30?>">
				<LABEL>
					To: 
					<INPUT type="text" name="end_date_disp" READONLY value="<?=es(dateToHebrew(unixtojd()+30))?>" onClick="getDate(this.form, 'end_date', true);"/>
				</LABEL>
			</span>
		</fieldset>
		<div align='center'>
			<br /><input type="submit" name="submit" value="Submit" />
		</div>
	</form>
	<?	
	} else {
		/*
		echo "<pre>";
		print_r( $_POST );
		echo "</pre>";
		*/
	
		require_once 'class.adminSchools.php';
		require_once 'class.schoolsUsers.php';         
		
		$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
		$schools = $as->getSchools();
		$schoolsUsers = array();
		
		foreach ( $schools as $id => $school ) {
			$s = new SchoolsUsers( $id );
			//get all users and filter out correct ones
			$users = $s->getUsers();
			$temp = array();
			$dob = array();
			foreach ( $users as $user ) {
				if ( empty( $user['dob'] ) )
					continue;
				
				//find out what day birthday is in current year
				//english dob is in format yy-mm-dd
				$arrDOB = explode( '-', $user['dob'] );					
				$yy = date('Y');
				$en_birthday = gregoriantojd( $arrDOB[1], $arrDOB[2], $yy );
				
				//check if hebrew dob should be one day further
				if ( $user['dob_he_offset'] ) {
					//add one to dob
					$date = new DateTime( $user['dob'] );
					$date->add( new DateInterval( 'P1D' ) );
					$newDate = $date->format( 'Y-m-d' );
					$arrDOB = explode('-', $newDate);
				}
				
				//find out what day hebrew birthday is in current year
				$jd = gregoriantojd($arrDOB[1], $arrDOB[2], $yy);
				$jDate = jdtojewish($jd);
				$arrJDate = explode("/", $jDate);
				$hMonth = $arrJDate[0];
				$hDay = $arrJDate[1];
				
				//find out if user born in leap year
				if (((7 * $arrJDate[2] + 1) % 19) < 7) {
					$bornInLeap = true;
				} else {
					$bornInLeap = false;
				}
				
				//find out if current year is leap year
				$jNow = jdtojewish(unixtojd());
				$arrJNow = explode('/', $jNow);
				$hYear = $arrJNow[2];
				if (((7 * $hYear + 1) % 19) < 7) {
					$leap = true;
				} else {
					$leap = false;
				}
				
				//if born in regular year and current year is leap year, 
				//and month is adar, then month needs to be changed to adar II
				if (!$bornInLeap && $leap && $hMonth == 6) {
					$hMonth++;
				}
				
				$he_birthday = jewishtojd($hMonth, $hDay, $hYear);
				if ( $_POST['dob'] == 'en' ) {
					if ( $en_birthday >= $_POST['start_date'] && $en_birthday <= $_POST['end_date'] ) {
						$temp[] = $user;
						$dob[] = $en_birthday;
					}
				} else if ( $_POST['dob'] == 'he' ) {
					if ( $he_birthday >= $_POST['start_date'] && $he_birthday <= $_POST['end_date'] ) {
						$temp[] = $user;
						$dob[] = $he_birthday;
					}
				}
			}
			array_multisort( $dob, SORT_ASC, $temp );
			$schoolsUsers[$id] = $temp;
		}
		
		foreach ( $schoolsUsers as $school => $users ) { ?>
			<h2><?= $schools[$school] ?></h2>
			<table>
				<tr>
					<th>Grade</th> <th>First Name</th> <th>Last Name</th> 
					<th>First Hebrew Name</th> <th>Last Hebrew Name</th>
					<th><?= $_POST['dob'] == 'en' ? 'English' : 'Hebrew' ?> DOB</th>
				</tr>
			<?php
			foreach ( $users as $user ) { ?>
				<tr>
					<td> <?= $user['class_grade'] . ( empty( $user['class_sub']) ? '' : "-" . $user['class_sub'] ) ?></td>
					<td> <?= $user['first'] ?> </td>
					<td> <?= $user['last'] ?> </td>
					<td> <?= $user['first_he'] ?> </td>
					<td> <?= $user['last_he'] ?> </td>
					<td><?= $_POST['dob'] == 'en' ? $user['dob'] : $user['dob_he'] ?></td>
				</tr>
		<?php } ?>
			</table><br />
		<?php
		}
	}
	?>
</body>
</html>