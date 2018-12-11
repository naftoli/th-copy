<?
//$admin_auth = array('school'); 
require('db.php');

$uri = $_SERVER['PHP_SELF'];
$uri = substr($uri, 1);

$school = array();
$reg = array();
$year = 5775;
if (isset($_GET['id'])) {
	$sql = "select * from chidon_schools where year = $year and chidon_schools_id = " . mysql_real_escape_string($_GET['id']);
	$result = mysql_query($sql);
	$school = mysql_fetch_assoc($result);
	
	$sql = "select * from chidon_reg where chidon_schools_id = " . mysql_real_escape_string($_GET['id']);
	$result = mysql_query($sql);
	while ($row = mysql_fetch_assoc($result)) {
		$reg[] = $row;
	}
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
			fieldset#cc, fieldset#terms {
				width: 600px;
			}
			#form {
				float: left;
				padding-right: 30px;
			}
			#regList {
				width: auto;
				float: left;
				margin-top: -15px;
			} 
			#regList fieldset {
				clear: right;
			}
			#regList th {
				width: 100px;
			}
			legend {
				margin-left: 30px;
                padding: 6px;
                font-size: 16px;
                font-family: 'Sanchez';
			}
			#cc table {
				font-size: 14px;
			}
			#cc td {
				text-align: left;
				min-width: 150px;
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
			#add {
				color: red;
				font-weight: bold;
			}
			#addRow {
				text-align: center;
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
	        if (isset($_GET['error'])) {
	        	echo "<p style='color:red'>" . urldecode($_GET['error']) . "</p>";
	        } else if (isset($_GET['success'])) {
	        	if (isset($_GET['paid'])) {
	        		echo "<p style='color:red'>Thank you for your payment. You will receive an email confirmation shortly.<br />
	        		You have successfully created / updated your information.</p>";
	        	} else {
	        		echo "<p style='color:red'>You have successfully created / updated your information.</p>";
				}
	        }
	        ?>
	        
	        <?=$text?>
	        
	        <p>
	        	You must enter your username and choose your school name.
	        	Then click on "Load Saved Info".
	        </p>
	        
        	<div class='school'>
        		<table>
        			<tr>
        				<td>Username</td>
        				<td><input type="text" name="username" size="40" id="username" 
        					<? if (!empty($school)) echo "value='" . $school['username'] . "'" ?>
        					/></td>
        			</tr>
        			<tr>
        				<td>School Name</td>
        				<td>
        					<select name="school" id="school">
        						<?
        						foreach ($schools as $name) {
        							echo "<option value='$name' ";
									if (!empty($school) && $school['school_name'] == $name) {
										echo "selected='selected'";
									}
									echo ">" . $name . "</option>";
        						}
        						?>
        					</select>
        				</td>
        			</tr>
        			<tr>
        				<td>Chaperone Name</td>
        				<td><input type='text' name='ch_name' size='40' id="chaperone" 
        					<? if (!empty($school)) echo "value='" . $school['chaperone_name'] . "'" ?>
        					/></td>
        			</tr>
        			<tr>
        				<td>Chaperone Cell Number</td>
        				<td><input type='text' name='ch_number' size='40' id="chaperonePhone" 
        					<? if (!empty($school)) echo "value='" . $school['chaperone_phone'] . "'" ?>
        					/></td>
        			</tr>
        			<tr>
        				<td colspan="2">
        					<? if (!isset($_GET['id'])) { ?>
        					<button id="loadSaved">Load Saved Info</button>
        					<? } else { echo "&nbsp;"; } ?>
        				</td>
        			</tr>
        		</table>
			</div>
			
			<? if (isset($_GET['id'])) { ?>		
				<form action="chidon_reg_post.php" method="post" id="regForm">
					<input type='hidden' name='schoolID' value='<?=$_GET['id']?>' />
					<div id="form">
		        		<input type='hidden' name='gender' value='<?=$gender?>' />			
						<fieldset>
							<legend>Add Participant</legend>				
							<table>
								<tr>
									<td>Grade</td>
									<td>
										<select name="grade" id="grade">
											<? for ($grade = 4; $grade < 9; $grade++) { ?>
												<option value="<?=$grade?>"><?=$grade?></option>
											<? } ?>
										</select>
									</td>
								</tr>
								<tr>
									<td>Type</td>
									<td>
										<select name="type" id="type">
											<option value='winner'>Winner</option>
											<option value='runnerUp'>Runner up</option>
											<option value='parent'>Parent</option>
										</select>
									</td>
								</tr>
								<tr>
									<td>English Name</td>
									<td><input type='text' name='name' id="name" /></td>
								</tr>
								<tr>
									<td>Hebrew Name</td>
									<td><input type='text' name='hname' id="hname" /></td>
								</tr>
								<tr>
									<td>Book Number</td>
									<td>
										<select name="book" id="book">
											<option value='1' 
											<? if ($grade == 4) echo "selected='selected'"?>>1</option>
											<option value='2' 
											<? if ($grade == 5) echo "selected='selected'"?>>2</option>
											<option value='3' 
											<? if ($grade == 6) echo "selected='selected'"?>>3</option>
											<option value='4' 
											<? if ($grade == 7 || $grade == 8) echo "selected='selected'"?>
											>4</option>
										</select>
									</td>
								</tr>
								<tr>
									<td>Mark 1</td>
									<td><input type='text' name='mark1' id="mark1" size='4' /></td>
								</tr>
								<tr>
									<td>Mark 2</td>
									<td><input type='text' name='mark2' id="mark2" size='4' /></td>
								</tr>
								<tr>
									<td>Couvert Fee</td>
									<td>$115</td>
								</tr>
								<tr>
									<td>Arrival Airport</td>
									<td><input type='text' name='arrAirport' /></td>
								</tr>
								<tr>
									<td>Airline / Flight Number</td>
									<td><input type='text' name='arrNumber' /></td>
								</tr>
								<tr>
									<td>Arrival Time</td>
									<td><input type='text' name='arrTime' /></td>
								</tr>
								<tr>
									<td>Departure Airport</td>
									<td><input type='text' name='depAirport' /></td>
								</tr>
								<tr>
									<td>Airline / Flight Number</td>
									<td><input type='text' name='depNumber' /></td>
								</tr>
								<tr>
									<td>Departure Time</td>
									<td><input type='text' name='depTime' /></td>									
								</tr>
								<tr>
									<td>Needs Accomodation</td>
									<td>
										<select name='help' id='help'>
											<option value='n'>no</option>
											<option value='y'>yes</option>
										</select>
									</td>
								</tr>
								<tr class="accomodation">
									<td>Family Name:</td>
									<td><input type='text' name='family' /></td>
								</tr>
								<tr class="accomodation">
									<td>Address</td>
									<td><input type='text' name='address' /></td>
								</tr>
								<tr class="accomodation">
									<td>Phone</td>
									<td><input type='text' name='phone' /></td>
								</tr>
								<tr>
									<td>Notes</td>
									<td>
										<textarea rows="5" cols="20" name="notes"></textarea>
									</td>
								</tr>
								<tr>
									<td colspan="2" id="addRow">
										<input type="submit" name="submit" value="Add Participant" id="add" />
									</td>
								</tr>
							</table>
						</fieldset>
					</div>
				</form>
					
				<form action="chidon_reg.php" method="post" id="payForm">
					<input type='hidden' name='schoolID' value='<?=$_GET['id']?>' />
					<div id="regList">
						<fieldset>
							<legend>Chidon Participants</legend>
								<table>
									<tr>
										<th>Pay For</th>
										<th>Type</th>
										<th>English Name</th>
										<th>Hebrew Name</th>
										<th>Couvert Fee</th>
									</tr>
									<?
									if (!empty($reg)) { 
										$total = count($reg);
										for ($i = 0; $i < $total; $i++) {
											$row = $reg[$i];
											$id = $row['chidon_reg_id'];
											$paid = $row['paid'];
											$type = $row['type'];
											if ($type == 'runnerUp') $type = 'runner up';
											$name = $row['name'];
											$hname = $row['hname'];
											?>
											<tr>
												<td>
													<? 
													if ($paid) {
														echo "<i>paid</i>";
													} else { 
													?>
													<input type="checkbox" name='pay[<?=$row['chidon_reg_id']?>]' class="pay" />
													<? } ?>
												</td>
												<td>
													<?=$type?>
												</td>
												<td>
													<?=$name?>
												</td>
												<td>
													<?=$hname?>
												</td>
												<td>$115</td>
												<td>
													<a href="chidon_reg_edit.php?id=<?=$id?>">edit</a>
												</td>
											</tr>
										<? }
									} ?>
								</table>
						</fieldset>
																
						<fieldset id='cc'>
							<legend>Credit Card Info</legend>
							<table>
								<tr>
									<td colspan="2">Total being charged: <span class='total'></span></td>
								</tr>
								<tr>
									<td>Credit Card Type</td>
									<td>
										<select name='cctype'>
											<option value='mc'>MasterCard</option>
											<option value='visa'>Visa</option>
											<option value='amex'>Amex</option>
											<option value='disc'>Discover</option>
										</select>
									</td>
								</tr>
								<tr>
									<td>Name on Credit Card</td>
									<td><input type='text' name='ccname' id='ccname' /></td>
								</tr>
								<tr>
									<td>Credit Card Number</td>
									<td><input type='text' name='ccnum' id='ccnum' size="40"
										value="<?=isset($_POST['ccnum']) ? $_POST['ccnum'] : ''?>" /></td>
								</tr>
								<tr>
									<td>Expiry</td>
									<td>
										<select name='mm' id='mm'>
											<? for ($i = 1; $i < 13; $i++) {
												$val = (string)$i;
												if (strlen($val) == 1)
													$val = '0' . $val;
												if (isset($_POST['mm']) && $_POST['mm'] == $val) {
													echo "<option value=$val selected='selected'>$val</option>";
												} else {
													echo "<option value=$val>$val</option>";
												}
											} ?>
										</select>
										<select name='yy' id='yy'>
											<? for ($i = 2015; $i < 2021; $i++) {
												if (isset($_POST['yy']) && $_POST['yy'] == $i) {
													echo "<option value=$i selected='selected'>$i</option>";
												} else {
													echo "<option value=$i>$i</option>";
												} 
											} ?>
										</select>
									</td>
								</tr>
								<tr>
									<td>Security Code</td>
									<td><input type='text' name='scode' id="scode" size='3' /></td>
								</tr>
								<tr>
									<td>Billing Zip Code</td>
									<td><input type='text' name='zcode' id='zcode' size='5' /></td>
								</tr>
								<tr>
									<td>Email (for confirmation email)</td>
									<td><input type='email' name='email' id='email' size='30' /></td>
								</tr>
								<tr>
									<td colspan="2">
										<input type='checkbox' name='agree' id='agree' /> 
										I allow Tzivos Hashem to charge my credit card <span class='total'></span>
										<input type='hidden' name='total' id='total' value='0' />
									</td>
								</tr>
							</table>
						</fieldset>
						
						<fieldset id='terms'>
							<legend>Terms</legend>
							<p>
								<?=$terms?>
								<b>Chidon Hosts</b><br />
								Chidon hosts are just for sleeping and the Friday night meal. 
								The Chidon will provide all other meals, as well as transportation to and from their homes.
							</p>
							<div>
								<input type='submit' name='submit' value='I agree to terms and charges' id="submit" />
							</div>
						</fieldset>
					</div>
		        </form>
	       <? } ?>
       </div>
	</body>
	
	<script type="text/javascript" src="scripts/jquery-1.8.3.js"></script>
	<script type="text/javascript">
		$(document).on('click', '.pay', function() {
			calculateTotal();
		});
		
		$(document).on('click', '#submit', function() {
			return validate();
		});
		
		$(document).on('click', '#add', function() {
			if ($("#name").val() == '') {
				alert('You must enter a name for the participant.');
				return false;
			} else if ($("#hname").val() == '') {
				alert('You must enter a hebrew name for the participant.');
				return false;
			}
			$('#regForm').submit();
		});
		
		$(document).on('change', '#help', function() {
			var val = $(this).val();
			var table = $(this).parent().parent().parent();
			if (val == 'y')
				$(table).find('.accomodation').show();
			else if (val == 'n')
				$(table).find('.accomodation').hide();
		});
		
		$(function() {
			$(".accomodation").hide();
			
			$("#loadSaved").on('click', function(e) {
				e.preventDefault();
				var username = $("#username").val();
				var school = $("#school").val();
				var chaperone = $("#chaperone").val();
				var chNumber = $("#chaperonePhone").val();
				var year = 5775;
				var gender = '<?=$gender?>';
				if (username == '') {
					alert('You must enter the username in order to retrieve your information.');
					return false;
				}
				$.post('ajax/getChidonSchool.php', {
					year : year, 
					username : username, 
					school : school, 
					gender : gender, 
					chaperone : chaperone, 
					chNumber : chNumber
				}, function( success ) {
					if (success == 0) {
						alert('No such username / school exists. Please contact Tzivos Hashem.');
					} else {
						var gender = '<?=$gender?>';
						window.location = "https://mashpia.com/chidon_reg_" + gender + ".php?id=" + success;
					}
				});
			});
		});
		
		function calculateTotal() {
			var total = 0;
			$(".pay").each( function() {
				if ($(this).is(":checked")) {
					total++;
				}
			});
			if (total > 0) {
				$(".total").text('$' + (total * 115));
			} else {
				$(".total").text('');
			}
			$("#total").val(total * 115);
		}
		
		function validate() {
			var errors = [];			
			$(".pay").each( function() {
				if ($(this).is(":checked")) {		
					if ($.trim($("#ccname").val()) == '') {
						errors.push('You must enter the name on the credit card.');
					}
					if ($.trim($("#ccnum").val()) == '') {
						errors.push('You must enter your credit card number.');
					} 
					if ($.trim($("#scode").val()) == '') {
						errors.push('You must enter your security code.');
					}
					if ($.trim($("#zcode").val()) == '') {
						errors.push('You must enter your billing zip code.');
					}
					var email = $.trim($("#email").val());
					if (email == '') {
						errors.push('You must enter an email address.');
					}
					var filter = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
					if (!filter.test(email)) {
						errors.push('You must enter a valid email.');	
					}
					if (!$("#agree").is(":checked")) {
						errors.push('You must indicate that you agree to the terms.');
					}
					return false;
				}
			});
			
			if (errors.length) {
				var str = '';
				for (e in errors) {
					str += errors[e] + "\n";
				}
				alert(str);
				return false;
			} else {
				return true;
			}
		}
	</script>
</html>