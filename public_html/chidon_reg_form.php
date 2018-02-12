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
		$reg[$row['grade']][] = $row;
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
            }
            th {
            	vertical-align: top;
            	width: 75px;
            }
            th:first-child {
            	width: 100px;
            }
            td {
            	text-align: center;
            }
            td:first-child {
            	text-align: left;
            }
            .school {
            	margin-bottom: 10px;
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
                width: 1250px;
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
			}
			.main {
				margin-left: 7.5%;
			}
			input[type='text'] {
				width: 90px;
			}
			p {
				font-size: 14px;
			}
    	</style>
	</head>
	
	<body>
		<div class='main'>
			<div style='float: left; margin-right: 20px; padding-bottom: 30px;'>
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
	        
	        <form action="chidon_reg_post.php" method="post">
	        	<input type='hidden' name='gender' value='<?=$gender?>' />
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
	        							echo "<option value='' ";
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
	        				<td><input type='text' name='ch_name' size='40' 
	        					<? if (!empty($school)) echo "value='" . $school['chaperone_name'] . "'" ?>
	        					/></td>
	        			</tr>
	        			<tr>
	        				<td>Chaperone Cell Number</td>
	        				<td><input type='text' name='ch_number' size='40' 
	        					<? if (!empty($school)) echo "value='" . $school['chaperone_phone'] . "'" ?>
	        					/></td>
	        			</tr>
	        			<? if (!isset($_GET['id'])) { ?>
	        			<tr>
	        				<td colspan="2"><button id="loadSaved">Load Saved Info</button></td>
	        			</tr>
	        			<? } ?>
	        		</table>
				</div>	
				
				<? if (isset($_GET['id'])) { ?>
					<p style='color: purple; font-weight: bold'>
						Please fill in the page as you gather your information. 
						Your details will be stored when you press save. (besides for Credit Card information)
					</p>
					
					<? 
					$ctr = 0;
					for ($grade = 4; $grade < 9; $grade++) { ?>
						<fieldset>
							<legend>Grade <?=$grade?></legend>				
							<table>
								<tr>
									<th>Pay for</th>
									<th></th>
									<th>First & Last English Name<br />(for Name Tags)</th>
									<th>First & Last Hebrew Name<br />(for Certificate)</th>
									<th>Book Learned</th>
									<th>Test 1 Mark</th>
									<th>Test 2 Mark</th>
									<th>Couvert Fee</th>
									<th>Needs Accomodation</th>
									<th>Accomodation Family Name</th>
									<th>Accomodation<br />Address</th>
									<th>Accomondation<br />Phone Number</th>
									<th>Airline</th>
									<th>Departure Time</th>
									<th>Arrival Time</th>
								</tr>
								
								<?
								if (!empty($reg[$grade])) {
									$total = count($reg[$grade]);
									for ($i = 0; $i < $total; $i++) {
										$row = $reg[$grade][$i];
										$paid = $row['paid'];
										$type = $row['type'];
										$name = $row['name'];
										$hname = $row['hname'];
										$book = $row['book'];
										$mark1 = $row['mark1'];
										$mark2 = $row['mark2'];
										$help = $row['help'];
										$family = $row['family'];
										$address = $row['address'];
										$phone = $row['phone'];
										$airline = $row['airline'];
										$departure = $row['departure'];
										$arrival = $row['arrival'];
										?>
										<tr>
											<td>
												<? 
												if ($paid) {
													echo "paid";
												} else { 
												?>
												<input type="checkbox" name='pay[<?=$ctr++?>]' class="pay" />
												<? } ?>
											</td>
											<td>
												<select name="type[]">
													<option value='winner' 
													<? if ($type == 'winner') echo " selected='selected'"; ?>
													>Winner</option>
													<option value='runnerUp'
													<? if ($type == 'runnerUp') echo " selected='selected'"; ?>
													>Runner up</option>
													<option value='parent'
													<? if ($type == 'parent') echo " selected='selected'"; ?>
													>Parent</option>
												</select>
											</td>
											<td><input type='text' name='name[]' class="name" 
												value="<?=$name?>" /></td>
											<td><input type='text' name='hname[]' class="hname" 
												value="<?=$hname?>" /></td>
											<td>
												<select name="book[]">
													<option value='1' 
													<? if ($book == 1) echo "selected='selected'"?>>1</option>
													<option value='2' 
													<? if ($book == 2) echo "selected='selected'"?>>2</option>
													<option value='3' 
													<? if ($book == 3) echo "selected='selected'"?>>3</option>
													<option value='4' 
													<? if ($book == 4) echo "selected='selected'"?>
													>4</option>
												</select>
											</td>
											<td><input type='text' name='mark1[]' class="mark1" size='4' 
												value="<?=$mark1?>"	/></td>
											<td><input type='text' name='mark2[]' class="mark2" size='4' 
												value="<?=$mark2?>" /></td>
											<td>$115</td>
											<td>
												<select name='help[]' class='help'>
													<option value='n'
													<? if (!$help) echo " selected='selected'"; ?> 
													>no</option>
													<option value='y'
													<? if ($help) echo " selected='selected'"; ?> 
													>yes</option>
												</select>
											</td>
											<td><input type='text' name='family[]' 
												value="<?=$family?>" /></td>
											<td><input type='text' name='address[]' 
												value="<?=$address?>" /></td>
											<td><input type='text' name='phone[]' 
												value="<?=$phone?>" /></td>
											<td><input type='text' name='airline[]' 
												value="<?=$airline?>" /></td>
											<td><input type='text' name='departure[]' 
												value="<?=$departure?>" /></td>
											<td><input type='text' name='arrival[]' 
												value="<?=$arrival?>" /></td>
											<input type='hidden' class="grade" name='grade[]' value='<?=$grade?>' />
										</tr>
									<? 
									}
								} 
								for ($k = 0; $k < 2; $k++) {
									if (!empty($reg[$grade])) $k = 1;
									?>					
									<tr>
										<td>
											<input type="checkbox" name='pay[<?=$ctr++?>]' class="pay" />
										</td>
										<td>
											<select name="type[]">
												<option value='winner'>Winner</option>
												<option value='runnerUp'>Runner up</option>
												<option value='winnerP'>Parent</option>
											</select>
										</td>
										<td><input type='text' name='name[]' class="name" /></td>
										<td><input type='text' name='hname[]' class="hname" /></td>
										<td>
											<select name="book[]">
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
										<td><input type='text' name='mark1[]' class="mark1" size='4' /></td>
										<td><input type='text' name='mark2[]' class="mark2" size='4' /></td>
										<td>$115</td>
										<td>
											<select name='help[]' class='help'>
												<option value='n'>no</option>
												<option value='y'>yes</option>
											</select>
										</td>
										<td><input type='text' name='family[]' /></td>
										<td><input type='text' name='address[]' /></td>
										<td><input type='text' name='phone[]' /></td>
										<td><input type='text' name='airline[]' /></td>
										<td><input type='text' name='departure[]' /></td>
										<td><input type='text' name='arrival[]' /></td>
										<input type='hidden' class="grade" name='grade[]' value='<?=$grade?>' />
									</tr>
								<? } ?>
								<tr>
									<td colspan='2'><button class="addRow">Add Row</button></td>
								</tr>
								<tr>
									<td colspan="2"><button class="save">Save</button></td>
								</tr>
							</table>
						</fieldset>
					<? } ?>
					
					<fieldset>
						<legend>Terms</legend>
						<p>
							<?=$terms?>
							<b>Chidon Hosts</b><br />
							Chidon hosts are just for sleeping and the Friday night meal. 
							The Chidon will provide all other as meals, as well as transportation to and from their homes.
						</p>
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
					
					<div>
						<input type='submit' name='submit' value='I agree to terms and charges' id="submit" />
					</div>
				<? } ?>
	        </form>
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
		
		$(document).on('click', '.save', function() {
			$('#submit').trigger('click');
		});
		
		$(document).on('click', '.addRow', function(e) {
			e.preventDefault();
			var grade = $(this).parent().parent().parent().find('.grade').val();
			var str = "<tr><td><input type='checkbox' name='pay[]' class='pay' /></td>" 
				+	"<td><select name='type[]'><option value='runnerUp'>Runner up</option>" 
				+	"<option value='winner'>Winner</option>" 
				+	"<option value='winnerP'>Parent</option>"
				+	"</select></td><td><input type='text' name='name[]' class='name' /></td>" 
				+	"<td><input type='text' name='hname[]' class='hname' /></td>";
			str += "<td><select name='book[]'><option value='1'";
			if (grade == 4) {
				str += " selected='selected'";
			}
			str += ">1</option><option value='2'";
			if (grade == 5) {
				str += " selected='selected'";
			}
			str += ">2</option><option value='3'";
			if (grade == 6) {
				str += " selected='selected'";
			}
			str += ">3</option><option value='4'";
			if (grade == 7 || grade == 8) {
				str += " selected='selected'";
			}
			str += ">4</option></select>";
			str += "<input type='hidden' name='grade[]' value='" + grade + "' /></td>";
			str += "<td><input type='text' name='mark1[]' class='mark1' size='4' /></td>"
				+	"<td><input type='text' name='mark2[]' class='mark2' size='4' /></td>"
				+	"<td>$115<input type='hidden' name='fee[]' class='fee' value='115' /></td>"
				+	"<td><select name='help[]' class='help'>"
				+	"<option value='n'>no</option><option value='y'>yes</option>"
				+	"</select></td><td><input type='text' name='family[]' /></td>"
				+	"<td><input type='text' name='address[]' /></td>"
				+	"<td><input type='text' name='phone[]' /></td>"
				+	"<td><input type='text' name='airline[]' /></td>"
				+	"<td><input type='text' name='departure[]' /></td>"
				+	"<td><input type='text' name='arrival[]' /></td>" 
				+ 	"<input type='hidden' name='grade[]' class='grade' value='" + grade + "'></tr>";
					
			$(this).parent().parent().before(str);
		});
		
		$(function() {
			$("#loadSaved").on('click', function(e) {
				e.preventDefault();
				var username = $("#username").val();
				var school = $("#school").val();
				var year = 5775;
				if (username == '' || school == '') {
					alert('You must enter the username and school name in order to retrieve your information.');
					return false;
				}
				$.post('ajax/getChidonSchool.php', {year : year, username : username, school : school}, function( success ) {
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
			
			if ($.trim($("#username").val()) == '') {
				errors.push('You must enter a username.');
			}
			
			if ($.trim($("#school").val()) == '' ) {
				errors.push('You must enter a school name.');
			}
			
			$(".name").each(function() {
				var name = $.trim($(this).val());
				if (name != '') {
					var tr = $(this).parent().parent();
					var hname = $.trim(tr.find(".hname").val());
					if (hname == '') {
						errors.push('You must enter a hebrew name for ' + name + '.');
					}
				}
			});
			
			$(".pay").each( function() {
				if ($(this).is(":checked")) {
					var row = $(this).parent().parent();
					if ($(row).find(".name").val() == '' || $(row).find(".hname").val() == '') {
						errors.push('You must enter a name and hebrew name for the people you are paying for.');
					}
				}
			});
			
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