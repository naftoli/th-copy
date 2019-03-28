<?
//$admin_auth = array('school'); 
require('db.php');

function clean(&$value) {
	if (is_array($value)) {
		foreach ($value as $k => &$v) {
			clean($v);
		}
	} else {
		$value = mysql_real_escape_string($value);
	}
}

if (isset($_POST['submit'])) {
	//sanitize values
	clean($_POST);
	$error = '';
	
	$regID 		=	$_POST['regID'];
	$gender 	=	$_POST['gender'];
	$grade 		= 	$_POST['grade'];
	$type 		= 	$_POST['type'];
	$name 		= 	$_POST['name'];
	$hname 		= 	$_POST['hname'];
	$book 		= 	$_POST['book'];
	$mark1 		= 	($_POST['mark1'] == '' ? 0 : $_POST['mark1']);
	$mark2 		= 	($_POST['mark2'] == '' ? 0 : $_POST['mark2']);
	$arrAirport =	$_POST['arrAirport'];
	$arrNumber 	= 	$_POST['arrNumber'];
	$arrTime 	= 	$_POST['arrTime'];
	$depAirport = 	$_POST['depAirport'];
	$depNumber 	=	$_POST['depNumber'];
	$depTime 	= 	$_POST['depTime'];
	$help 		= 	($_POST['help'] == 'n' ? 0 : 1);
	$family		=	$_POST['family'];
	$address	=	$_POST['address'];
	$phone		= 	$_POST['phone'];
	$notes 		=	$_POST['notes'];
	
	$sql = "update chidon_reg 
			set grade = '$grade', 
			type = '$type', 
			name = '$name', 
			hname = '$hname', 
			book = '$book', 
			help = $help, 
			family = '$family', 
			address = '$address', 
			phone = '$phone', 
			mark = 0, 
			mark1 = $mark1, 
			mark2 = $mark2, 
			arr_airport = '$arrAirport', 
			arr_number = '$arrNumber', 
			arr_time = '$arrTime', 
			dep_airport = '$depAirport', 
			dep_number = '$depNumber', 
			dep_time = '$depTime', 
			notes = '$notes' 
			where chidon_reg_id = $regID";
	if (!@mysql_query($sql)) {
		$error = "Error updating information.";
	}
	$_GET['id'] = $regID;
}

$year = 5775;
$reg = array();
$school = array();
if (isset($_GET['id'])) {	
	$sql = "select * from chidon_reg where chidon_reg_id = " . mysql_real_escape_string($_GET['id']);
	$result = mysql_query($sql);
	$reg = mysql_fetch_assoc($result);
	
	$sql = "select * from chidon_schools where year = $year and chidon_schools_id = " . 
		mysql_real_escape_string($reg['chidon_schools_id']);
	$result = mysql_query($sql);
	$school = mysql_fetch_assoc($result);
	
	//get list of ids for all participants from this school
	$idList = array();
	$sql = "select chidon_reg_id from chidon_reg where chidon_schools_id = " . mysql_real_escape_string($reg['chidon_schools_id']);
	$result = mysql_query($sql);
	while ($row = mysql_fetch_assoc($result)) {
		$idList[] = $row['chidon_reg_id'];
	}
	$numIds = count($idList);
	$key = array_search($_GET['id'], $idList);
	if ($key > 0)
		$prevID = $idList[$key-1];
	if (($key+1) < $numIds)
		$nextID = $idList[$key+1];
}
?>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>Chidon Registration Form</title>
		<style type='text/css'>
			@font-face {
				font-family: 'Sanchez';
				src: url('chidonfonts/Sanchez-Bold.otf');
			}
			h1 {
				font-family: 'Sanchez';
			}
            table {
                font-size: 12px;
            }
            th, td {
            	padding: 3px;
            	text-align: left;
            }
            th {
            	vertical-align: top;
            	width: 75px;
            }
            th:first-child {
            	width: 100px;
            }
            td:first-child {
            	text-align: left;
            }
            .school table {
            	font-size: 14px;
            }
            .school td {
            	text-align: left;
            }
            .school input[type='text'] {
            	width: 200px;
            }
            fieldset {
				margin-bottom: 20px;
				-moz-border-radius: 20px;
                -webkit-border-radius: 20px;
                border-radius: 20px;
                width: auto;
			}
			#form {
				float: left;
				padding-right: 30px;
			}
			legend {
				margin-left: 30px;
                padding: 6px;
                font-size: 16px;
                font-family: 'Sanchez';
			}
			.main {
				margin-left: 10%;
			}
			input[type='text'] {
				width: 150px;
			}
			p {
				font-size: 14px;
			}
			#edit {
				color: red;
				font-weight: bold;
			}
			#editRow {
				text-align: center;
			}
			#payment {
				text-align: right;
				color: red;
				font-weight: bold;
			}
			#leftNav {
				float: left;
			}
			#rightNav {
				float: right;
			}
			#leftNav a, #rightNav a {
				font-size: 18px;
				font-weight: bold;
				text-decoration: none;
			}
    	</style>
	</head>
	
	<body>
		<div class='main'>
			<div style='float: left; margin-right: 20px; padding-bottom: 10px;'>
				<img src="images/Chidon-Logo.jpg" />
			</div>
			
	        <h1>Chidon Registration Form</h1>
	        
	        <? 
	        if (!empty($error)) {
	        	echo "<p style='color:red'>$error</p>";
			} else if (isset($error)) {
				echo "<p style='color:red'>Update successful.</p>";
			}
			?>
	        
        	<div class='school'>
        		<table>
        			<tr>
        				<td>Username:</td>
        				<td><?=$school['username']?></td>
        			</tr>
        			<tr>
        				<td>School Name:</td>
        				<td><?=$school['school_name']?></td>
        			</tr>
        		</table>
			</div>
			
			<form action="chidon_reg_edit.php" method="post" id="regForm">
				<input type='hidden' name="schoolID" value="<?=$school['chidon_schools_id']?>" />
				<input type='hidden' name='regID' value='<?=$_GET['id']?>' />
				<input type="hidden" name="gender" value="<?=$school['gender']?>" />
				<div id="form">
					<fieldset>
						<legend>Edit Participant</legend>				
						<table>
							<tr>
								<td id="payment">
									<?
									if ($reg['paid']) echo "<i>Paid For</i>";
									else echo "<i>Not Yet Paid For</i>";
									?>
								</td>
							</tr>
							<tr>
								<td>Grade</td>
								<td>
									<select name="grade" id="grade">
										<? 
										for ($grade = 4; $grade < 9; $grade++) {
											echo "<option value='" . $grade . "'";
											if ($reg['grade'] == $grade) echo " selected='selected'";
											echo ">" . $grade . "</option>";
										}
										?> 
									</select>
								</td>
							</tr>
							<tr>
								<td>Type</td>
								<td>
									<select name="type" id="type">
										<option value='winner'
										<? if ($reg['type'] == 'winner') echo " selected='selected'"; ?>
										>Winner</option>
										<option value='runnerUp'
										<? if ($reg['type'] == 'runnerUp') echo " selected='selected'"; ?>
										>Runner up</option>
										<option value='parent'
										<? if ($reg['type'] == 'parent') echo " selected='selected'"; ?>
										>Parent</option>
									</select>
								</td>
							</tr>
							<tr>
								<td>English Name</td>
								<td><input type='text' name='name' id="name" value=<?=$reg['name']?> /></td>
							</tr>
							<tr>
								<td>Hebrew Name</td>
								<td><input type='text' name='hname' id="hname" value="<?=$reg['hname']?>" /></td>
							</tr>
							<tr>
								<td>Book Number</td>
								<td>
									<select name="book" id="book">
										<option value='1' 
										<? if ($reg['grade'] == 4) echo "selected='selected'"?>>1</option>
										<option value='2' 
										<? if ($reg['grade'] == 5) echo "selected='selected'"?>>2</option>
										<option value='3' 
										<? if ($reg['grade'] == 6) echo "selected='selected'"?>>3</option>
										<option value='4' 
										<? if ($reg['grade'] == 7 || $grade == 8) echo "selected='selected'"?>
										>4</option>
									</select>
								</td>
							</tr>
							<tr>
								<td>Mark 1</td>
								<td><input type='text' name='mark1' id="mark1" size='4' value="<?=$reg['mark1']?>" /></td>
							</tr>
							<tr>
								<td>Mark 2</td>
								<td><input type='text' name='mark2' id="mark2" size='4' value="<?=$reg['mark2']?>" /></td>
							</tr>
							<tr>
								<td>Couvert Fee</td>
								<td>$115</td>
							</tr>
							<tr>
								<td>Arrival Airport</td>
								<td><input type='text' name='arrAirport' value="<?=$reg['arr_airport']?>" /></td>
							</tr>
							<tr>
								<td>Airline / Flight Number</td>
								<td><input type='text' name='arrNumber' value="<?=$reg['arr_number']?>" /></td>
							</tr>
							<tr>
								<td>Arrival Time</td>
								<td><input type='text' name='arrTime' value="<?=$reg['arr_time']?>" /></td>
							</tr>
							<tr>
								<td>Departure Airport</td>
								<td><input type='text' name='depAirport' value="<?=$reg['dep_airport']?>" /></td>
							</tr>
							<tr>
								<td>Airline / Flight Number</td>
								<td><input type='text' name='depNumber' value="<?=$reg['dep_number']?>" /></td>
							</tr>
							<tr>
								<td>Departure Time</td>
								<td><input type='text' name='depTime' value="<?=$reg['dep_time']?>" /></td>									
							</tr>
							<tr>
								<td>Needs Accomodation</td>
								<td>
									<select name='help' id='help'>
										<option value='n'
										<? if ($reg['help'] == 0) echo " selected='selected'"; ?>
										>no</option>
										<option value='y'
										<? if ($reg['help'] == 1) echo " selected='selected'"; ?>
										>yes</option>
									</select>
								</td>
							</tr>
							<tr class="accomodation">
								<td>Family Name:</td>
								<td><input type='text' name='family' value="<?=$reg['family']?>" /></td>
							</tr>
							<tr class="accomodation">
								<td>Address</td>
								<td><input type='text' name='address' value="<?=$reg['address']?>" /></td>
							</tr>
							<tr class="accomodation">
								<td>Phone</td>
								<td><input type='text' name='phone' value="<?=$reg['phone']?>" /></td>
							</tr>
							<tr>
								<td>Notes</td>
								<td>
									<textarea rows="5" cols="20" name="notes"><?=$reg['notes']?></textarea>
								</td>
							</tr>
							<tr>
								<td colspan="2" id="editRow">
									<span id='leftNav'>
										<? 
										if (isset($prevID)) {
											echo "<a href='chidon_reg_edit.php?id=" . $prevID . "'><</a>";
										}
										?>
									</span>
									<input type="submit" name="submit" value="Update Participant" id="edit" />
									<span id='rightNav'>
										<?
										if (isset($nextID)) {
											echo "<a href='chidon_reg_edit.php?id=" . $nextID . "'>></a>";
										}									
										?>
									</span>
								</td>
							</tr>
						</table>
					</fieldset>
				</div>
			</form>
       </div>
	</body>
	
	<script type="text/javascript" src="scripts/jquery-1.8.3.js"></script>
	<script type="text/javascript">
		$(function() {
			$(".accomodation").hide();
		});
		
		$(document).on('change', '#help', function() {
			var val = $(this).val();
			var table = $(this).parent().parent().parent();
			if (val == 'y')
				$(table).find('.accomodation').show();
			else if (val == 'n')
				$(table).find('.accomodation').hide();
		});
	</script>
</html>