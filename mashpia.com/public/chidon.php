<?
$str = substr($_SERVER['SCRIPT_URI'], 4, 1);
if ($str != 's') {
	header("Location: https://mashpia.com/chidon.php");
}

require 'db.php';
$contestants = array();
$sql = "select chidon_reg_id, name from chidon_reg cr 
		join chidon_schools cs using (chidon_schools_id) 
		where cs.year = 5776 
		and cs.chidon_schools_id not in (33,44) 
		order by cr.name"; 
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$contestants[$row['chidon_reg_id']] = $row['name'];
}
?>
<html>
	<head>
		<meta charset="UTF-8" />
		<title>Chidon Tickets</title>
		<script type="text/javascript" src="scripts/jquery-1.8.3.js"></script>
		<script>
			$(function() {
				$("#shipping").hide();
				
				$(".qty").change(function() {
					var total = (parseInt($("#gqty").val()) + parseInt($("#mqty").val())) * 10;
					$(".total").text('$' + total + '.00');
				});
				
				<? if (isset($_POST['mqty']) || isset($_POST['gqty'])) { ?>
					$(".qty").trigger('change');
				<? } ?>
				
				$(".ship").click(function() {
					var val = $(this).val();
					if (val == 1) {
						$("#shipping").show();
					} else {
						$("#shipping").hide();
					}
				});
				
				<? if (isset($_POST['ship'])) { ?>
					var val = <?=$_POST['ship']?>;
					$(".ship").each(function() {
						if ($(this).val() == val) {
							$(this).attr('checked', true);
							$(this).trigger('click');
						}
					});
				<? } ?>
				
				$("#copy").click(function() {
					if ($(this).is(":checked")) {
						$(".sfname").val($(".bfname").val());
						$(".slname").val($(".blname").val());
						$(".saddress").val($(".baddress").val());
						$(".scity").val($(".bcity").val());
						$(".sstate").val($(".bstate").val());
						$(".szip").val($(".bzip").val());
						
					} else {
						$(".sfname").val('');
						$(".slname").val('');
						$(".saddress").val('');
						$(".scity").val('');
						$(".sstate").val('');
						$(".szip").val('');
					}
				});
				
				function checkType() {
					var checked = false;
					$(".chidonType").each( function() {
						if ($(this).is(":checked")) {
							checked = true;
							return checked;
						}
					});
					return checked;
				}
				
				$("#submit").click(function() {
					var total = (parseInt($("#gqty").val()) + parseInt($("#mqty").val())) * 10;
					var fname = $(".bfname").val();
					var lname = $(".blname").val();
					var ccnum = $("#ccnum").val();
					var phone = $(".bphone").val();
					var email = $(".bemail").val();
					var vemail = $(".bvemail").val();
					var zip = $(".bzip").val();
					var sec = $(".sec").val();
					
					var errors = '';
					if (total == 0) {
						errors += "You have not chosen any tickets!";
					}
					
					if (checkType() == false) {
						if (errors != '') errors += "\n";
						errors += "You must indicate which chidon you are buying tickets for!";
					}
					
					if (fname == '' || lname == '') {
						if (errors != '') errors += "\n";
						errors += "You need to enter your first and last name.";
					}
					if (zip == '') {
						if (errors != '') errors += "\n";
						errors += "You need to enter your zip code.";
					}
					if (phone == '' || email == '' || vemail == '') {
						if (errors != '') errors += "\n";
						errors += "You need to enter your phone and email.";
					}
					if (email != vemail) {
						if (errors != '') errors += "\n";
						errors += "Your emails do not match, please try again.";
					}
					if (ccnum == '' || sec == '') {
						if (errors != '') errors += "\n";
						errors += "You need to enter your credit card number and security code.";
					}
					
					if (errors != '') {
						alert(errors);
						return false;
					}
				});
			});
		</script>
		<style>
			@font-face {
				font-family: 'Sanchez';
				src: url('chidonfonts/Sanchez-Bold.otf');
			}
			.top {
				text-align: center;
			}
			.top img {
				width: 500px;
			}
			.main {
				width: 500px;
				margin: auto;
				margin-top: 20px;
			}
			table, fieldset {
				font-size: 12px;
			}
			th {
				text-align: left;
			}
			tr, th, td {
				padding: 3px;
			}
			fieldset {
				margin-bottom: 20px;
				-moz-border-radius: 10px;
                -webkit-border-radius: 10px;
                border-radius: 10px;
			}
			legend {
				margin-left: 20px;
                padding: 5px;
                font-size: 16px;
                font-family: 'Sanchez';
			}
			.errors {
				color: red;
				font-size: 14px;
				font-weight: bold;
				padding: 20px;
			}
			.bottom p {
				font-size: 12px;
			}
		</style>
	</head>
	
	<body>
		
		<?
		require_once 'db.php';
		if (isset($_POST['submit'])) {
			foreach ($_POST as $k => $v) {
				$$k = mysql_real_escape_string(trim($v)); 
			}
			
			//check that needed values are correct
			$errors = array();
			if (!$mqty && !$gqty) {
				$errors[] = "You have not chosen any tickets.";
			} 
			if (empty($bfname) || empty($blname) || empty($bphone) || empty($bemail) || empty($bvemail) || empty($bzip)) {
				$errors[] = "First and Last name, Zip, Phone and Email are mandatory.";
			}
			if (!filter_var($bemail, FILTER_VALIDATE_EMAIL) || 
				!filter_var($bvemail, FILTER_VALIDATE_EMAIL) || 
				$bemail != $bvemail) {
					$errors[] = "Your emails are empty, incorrect or do not match.";
			}
			if (empty($ccnum)) {
				$errors[] = "You need to enter a valid credit card number.";
			}
			if ($ship == 1) {
				//shipping address is mandatory
				if (empty($sfname) || empty($slname) || empty($saddress) || empty($scity) || empty($sstate) || empty($szip)) {
					$errors[] = "You have indicated that you would like to have your tickets shipped.<br /> 
								Please fill out all shipping information.";
				}
			}
			
			if (empty($errors)) {
				//check for spammers    
    			include 'check_for_spammers.php';
				
				//send through authorize.net
				$card_num = $ccnum;
				$exp_date = $mm . $yy;
				$amount = ($mqty + $gqty) * 10.00;
				$description = "Tickets for Chidon";
				$first_name = $bfname;
				$last_name = $blname;
				$address = $baddress;
				$state = $bstate;
				$zip = $bzip;
				
				$year = 5775;
				
				require_once 'authorize.php';
				
				if (isset($response_array) && !empty($response_array)) {
					if ($response_array[0] == 1) { //success
						//save to database
						$approval = $response_array[3] . ':' . 
									$response_array[4] . ':' . 
									$response_array[6] . ':' . 
									$response_array[9];
						$name = $bfname . ' ' . $blname;
						switch ($ship) {
							case 1:
								$method = 'ship';
								break;
							case 2:
								$method = 'jcm pickup';
								break;
							case 3:
								$method = 'event pickup';
								break;
						}
						
						$sql = "insert into chidon 
								set name = '$name',
								phone = '$bphone',
								email = '$bemail',
								mqty = $mqty,
								gqty = $gqty,
								paid = $amount,
								approval = '$approval', 
								date_purchased = now(), 
								method = '$method', 
								chidon_type = '$chidonType', 
								chidon_reg_id = $contestant, 
								year = 5775";
						if ($ship == 1) {
							$sql .= ", address = '$saddress',
									city = '$scity',
									state = '$sstate',
									zip = '$szip'";
						}
						@mysql_query($sql);
						
						$message = "Dear $name, thank you for your purchase of ";
						if ($mqty) {
							$message .= "$mqty Mens ticket(s)";
						}
						if ($gqty) {
							if ($mqty) 
								$message .= " and ";
							$message .= "$gqty Womens ticket(s)";
						}
						if ($ship == 1) {
							$message .= ".<br />Your ticket(s) will soon be mailed out.";
						} else if ($ship == 2) {
							$message .= "<br />Your ticket(s) are available for pickup at the JCM Museum - 792 Eastern Parkway, between 10:00am - 3:30pm.";
						} else if ($ship == 3) {
							$message .= "<br />Your ticket(s) will be available for pickup at the Event.";
						}
						
						//send confirmation email to buyer
						$to = $bemail;
						$subject = "Chidon Ticket purchase confirmation";
						$headers  = 'MIME-Version: 1.0' . "\r\n";
						$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
						$headers = 'From: cth@mashpia.com' . "\r\n";
						$headers .= 'Reply-to: cth@mashpia.com' . "\r\n";
						
						@mail($to, $subject, $message, $headers);
					} else {
						$errors[] = $response_array[3];
					}
				} else {
					$errors[] = "There was an error processing your request.";
				}
			}			
		}
		?>
			
		<div class='top'>
			<!--<b>ONLINE TICKET PURCHASING IS NOW CLOSED. TICKETS CAN ONLY BE PURCHASED AT THE DOOR.</b><br />-->

			<? 
			if (isset($message)) {
				echo "<div class='errors'>";
				echo $message;
				echo "</div>";
				echo "<img src='downloads/Chidon.jpg' />";
				echo "</div>";
				exit;
			}
			if (isset($errors) && !empty($errors)) {
				echo "<div class='errors'>";
				foreach ($errors as $error) {
					echo $error . "<br />";
				}
				echo "</div>";
			} 
			?>
		</div>

		<div class='main'>
			<a name="tickets"></a>
			<form action="chidon.php" method="post">
				<fieldset>
					<legend>Tickets</legend>
					<table>
						<tr>
							<th>Ticket</th>
							<th>Price</th>
							<th>Quantity</th>
						</tr>
						<tr>
							<td>Men / Boys</td>
							<td>$10.00</td>
							<td>
								<select name='mqty' class='qty' id='mqty'>
									<? for ($i = 0; $i < 16; $i++) {
										if (isset($_POST['mqty']) && $_POST['mqty'] == $i) {
											echo "<option value=$i selected='selected'>$i</option>";
										} else {
											echo "<option value=$i>$i</option>";
										}
									} ?>
								</select>
							</td>
						</tr>
						<tr>
							<td>Women / Girls</td>
							<td>$10.00</td>
							<td>
								<select name='gqty' class='qty' id='gqty'>
									<? for ($i = 0; $i < 16; $i++) {
										if (isset($_POST['gqty']) && $_POST['gqty'] == $i) {
											echo "<option value=$i selected='selected'>$i</option>";
										} else {
											echo "<option value=$i>$i</option>";
										} 
									} ?>
								</select>
							</td>
						</tr>
						<tr>
							<td colspan="3"><hr /></td>
						</tr>
						<tr>
							<td style="vertical-align: text-top">Tickets are for the: </td>
							<td>
								<input type="radio" name="chidonType" value="b" class="chidonType" 
								<? if (isset($_POST['chidonType']) && $_POST['chidonType'] == 'b') 
									echo "selected='selected'"; ?>
								/> Boys Chidon<br />
								<input type="radio" name="chidonType" value="g" class="chidonType" 
								<? if (isset($_POST['chidonType']) && $_POST['chidonType'] == 'g') 
									echo "selected='selected'"; ?>
								/> Girls Chidon
							</td>
							<td></td>
						</tr>
						<tr>
							<td colspan="3"><hr /></td>
						</tr>
						<tr>
							<td colspan="2">I am coming in honor of the following chidon contestant:</td>
							<td>
								<select name="contestant">
									<? foreach ($contestants as $id => $contestant) {
										echo "<option value=" . $id . ">" . $contestant . "</option>";
									} ?>
								</select>
							</td>
						</tr>
					</table>
				</fieldset>
				
				<p><b>Total: <span class='total'></span></b></p>
				
				<fieldset>
					<legend>Personal / Billing Information</legend>
					<table>
						<tr>
							<td>First Name</td>
							<td><input type='text' name='bfname' class='bfname' 
								value="<?=isset($_POST['bfname']) ? $_POST['bfname'] : ''?>" /></td>
							<td>Phone</td>
							<td><input type='text' name='bphone' class='bphone'
								value="<?=isset($_POST['bphone']) ? $_POST['bphone'] : ''?>" /></td>
						</tr>
						<tr>
							<td>Last Name</td>
							<td><input type='text' name='blname' class='blname'
								value="<?=isset($_POST['blname']) ? $_POST['blname'] : ''?>" /></td>
							<td>Email</td>
							<td><input type='email' name='bemail' class='bemail'
								value="<?=isset($_POST['bemail']) ? $_POST['bemail'] : ''?>" /></td>
						</tr>
						<tr>
							<td>Address</td>
							<td><input type='text' name='baddress' class='baddress'
								value="<?=isset($_POST['baddress']) ? $_POST['baddress'] : ''?>" /></td>
							<td>Verify Email</td>
							<td><input type='email' name='bvemail' class='bvemail'
								value="<?=isset($_POST['bvemail']) ? $_POST['bvemail'] : ''?>" /></td>
						</tr>
						<tr>
							<td>City</td>
							<td><input type='text' name='bcity' class='bcity'
								value="<?=isset($_POST['bcity']) ? $_POST['bcity'] : ''?>" /></td>
							<td></td>
							<td></td>
						</tr>
						<tr>
							<td>State</td>
							<td><input type='text' name='bstate' class='bstate'
								value="<?=isset($_POST['bstate']) ? $_POST['bstate'] : ''?>" /></td>
							<td></td>
							<td></td>
						</tr>
						<tr>
							<td>Zip</td>
							<td><input type='text' name='bzip' class='bzip'
								value="<?=isset($_POST['bzip']) ? $_POST['bzip'] : ''?>" /></td>
							<td></td>
							<td></td>
						</tr>
					</table>
				</fieldset>
				
				<fieldset>
					<legend>Credit Card Info</legend>
					<table>
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
									<? for ($i = 2014; $i < 2021; $i++) {
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
							<td><input type='text' name='scode' class="sec" size='3' /></td>
						</tr>
					</table>
				</fieldset>
				
				<fieldset>
					<legend>Options</legend>
					I would like to have my tickets:<br />
					<blockquote>
						<input type='radio' class='ship' name='ship' value='1'> Shipped<br />
						<input type='radio' class='ship' name='ship' value='2'> Picked up at the JCM<br />
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(792 Eastern Parkway between 10:00am - 3:30pm)<br />
						<input type='radio' class='ship' name='ship' value='3'> Picked up at the Event<br />
					</blockquote>
				</fieldset>
				
				<fieldset id='shipping'>
					<legend>Shipping Information</legend>
					<input type='checkbox' name='copy' id='copy'> My shipping information is the same as my billing information
					<table>
						<tr>
							<td>First Name</td>
							<td><input type='text' name='sfname' class='sfname'
								value="<?=isset($_POST['sfname']) ? $_POST['sfname'] : ''?>" /></td>
						</tr>
						<tr>
							<td>Last Name</td>
							<td><input type='text' name='slname' class='slname'
								value="<?=isset($_POST['slname']) ? $_POST['slname'] : ''?>" /></td>
						</tr>
						<tr>
							<td>Address</td>
							<td><input type='text' name='saddress' class='saddress'
								value="<?=isset($_POST['saddress']) ? $_POST['saddress'] : ''?>" /></td>
						</tr>
						<tr>
							<td>City</td>
							<td><input type='text' name='scity' class='scity'
								value="<?=isset($_POST['scity']) ? $_POST['scity'] : ''?>" /></td>
						</tr>
						<tr>
							<td>State</td>
							<td><input type='text' name='sstate' class='sstate'
								value="<?=isset($_POST['sstate']) ? $_POST['sstate'] : ''?>" /></td>
						</tr>
						<tr>
							<td>Zip</td>
							<td><input type='text' name='szip' class='szip'
								value="<?=isset($_POST['szip']) ? $_POST['szip'] : ''?>" /></td>
						</tr>
					</table>
				</fieldset>
				<div align='center' class="bottom">
					<p>
						Ticket Prices are in U.S. dollars. There will be separate seating for men and women.<br />
						No refunds. For questions email <a href="mailto:cth@tzivoshashem.org">cth@tzivoshashem.org</a>.
					</p>
					<input type='submit' name='submit' value='submit' id='submit' />
				</div>
			</form>
		</div>

	</body>
</html>