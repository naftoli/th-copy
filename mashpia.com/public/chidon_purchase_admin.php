<?
$str = substr($_SERVER['SCRIPT_URI'], 4, 1);
if ($str != 's') {
	header("Location: https://mashpia.com/chidon_purchase_admin.php");
}

require 'db.php';
if (isset($_POST['submit'])) {
	foreach ($_POST as $k => $v) {
		$$k = mysql_real_escape_string(trim($v)); 
	}
		
	//check that needed values are correct
	$errors = array();
	if (!$mqty && !$gqty && !$ggqty) {
		$errors[] = "You have not chosen any tickets.";
	} 
			
	if (empty($errors)) {
		
		$year = 5775;
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
				ggqty = $ggqty,
				paid = 0, 
				approval = 'purchased by Chidon Office', 
				date_purchased = now(), 
				method = '$method', 
				chidon_reg_id = $bcontestant, 
				chidon_reg_id2 = $gcontestant, 
				year = $year";
		if ($ship == 1) {
			$sql .= ", address = '$saddress',
					city = '$scity',
					state = '$sstate',
					zip = '$szip'";
		}
		if (strtolower($code) == 'cvip') {
			$sql .= ", vip_seats = 1";
		}
		if (strtolower($code) == 'fr') {
			$sql .= ", fr = 1";
		}
		if (mysql_query($sql)) {
			//email to recipient
			$message = "Dear $name, the Chidon Office has reserved for you ";
			if ($mqty) {
				$message .= "$mqty Mens ticket(s) for the Boys Chidon";
			}
			if ($gqty) {
				if ($mqty) 
					$message .= " and ";
				$message .= "$gqty Womens ticket(s) for the Boys Chidon";
			}
			if ($ggqty) {
				if ($mqty || $gqty)
					$message .= " and ";
				$message .= "$ggqty Womens ticket(s) for the Girls Chidon";
			}

			if ($ship == 1) {
				$message .= ". Your ticket(s) will soon be mailed out.";
			} else if ($ship == 2) {
				$message .= ". Your ticket(s) are available for pickup at the JCM Museum - 792 Eastern Parkway, between 10:00am - 3:30pm.";
			} else if ($ship == 3) {
				$message .= ". Your ticket(s) will be available for pickup at the Event.";
			}
			
			//send confirmation email to buyer
			$to = $bemail;
			$subject = "Chidon Ticket purchase confirmation";
			$headers  = 'MIME-Version: 1.0' . "\r\n";
			$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
			$headers = 'From: chidon@mashpia.com' . "\r\n";
			$headers .= 'Reply-to: chidon@mashpia.com' . "\r\n";
			
			@mail($to, $subject, $message, $headers);
			
			echo "Tickets created.";
			exit;
		} else {
			echo "Error: " . mysql_error();
		}
	}			
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
			
		<div class='top'>
		<!--<b>ONLINE TICKET PURCHASING IS NOW CLOSED. TICKETS CAN ONLY BE PURCHASED AT THE DOOR.</b><br />-->

		<? 
		if (isset($message)) {
			echo "<div class='errors'>";
			echo $message;
			echo "<br /></div>";
			exit;
		}
		if (isset($errors) && !empty($errors)) {
			echo "<div class='errors'>";
			foreach ($errors as $error) {
				echo $error . "<br />";
			}
			echo "<br /></div>";
		} 
		?>
	</div>

	<div class='tickets'>
		<a name="tickets"></a>
		<form action="chidon_purchase_admin.php" method="post">
			<fieldset>
				<legend>Tickets</legend>
				<!--
				<table>
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
				</table>
				-->
				<p><i>Tickets cost $10 each. If you purchase more than five tickets, then each ticket is just $5.</i></p>
				<table id="tblTickets">
					<tr>
						<th>For Chidon</th>
						<th>Ticket Type</th>
						<th>Quantity</th>
					</tr>
					<tr>
						<td>Boys Chidon</td>
						<td>Men / Boys</td>
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
						<td>Boys Chidon</td>
						<td>Women / Girls</td>
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
						<td>Girls Chidon</td>
						<td>Women / Girls</td>
						<td>
							<select name='ggqty' class='qty' id='ggqty'>
								<? for ($i = 0; $i < 16; $i++) {
									if (isset($_POST['ggqty']) && $_POST['ggqty'] == $i) {
										echo "<option value=$i selected='selected'>$i</option>";
									} else {
										echo "<option value=$i>$i</option>";
									} 
								} ?>
							</select>
						</td>
					</tr>
					<tr>
						<td colspan="2">Enter code for VIP seats: <input type="text" name="code" /></td>
					</tr>
				</table>
			</fieldset>
			
			<br />
			<fieldset>
				<legend>For Boys Chidon</legend>
				<table>
					<tr>
						<td colspan="2">I am coming in honor of the following chidon contestant:</td>
						<td>
							<select name="bcontestant" id="bcontestant"></select>
						</td>
					</tr>
				</table>
			</fieldset>
			
			<br />
			<fieldset>
				<legend>For Girls Chidon</legend>
				<table>
					<tr>
						<td colspan="2">I am coming in honor of the following chidon contestant:</td>
						<td>
							<select name="gcontestant" id="gcontestant"></select>
						</td>
					</tr>
				</table>
			</fieldset>
			
			<br />
			<fieldset>
				<legend>Options</legend>
				I would like to have my tickets:<br />
				<div id="shippingOptions">
					<input type='radio' class='ship' name='ship' value='1'> Shipped ($4 flat fee)<br />
					<input type='radio' class='ship' name='ship' value='2'> Picked up at the JCM (Tickets available for pickup after Shavuos on 5th floor of JCM)<br />
					<input type='radio' class='ship' name='ship' value='3'> Picked up at the Event<br />
				</div>
			</fieldset>
					
			<br />
			<fieldset>
				<legend>Personal Information</legend>
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
			
			<div align='center' class="bottom">
				<input type='submit' name='submit' value='submit' id='submit' />
			</div>
		</form>
	</div>

	</body>
	<script src="jquery.js" type="text/javascript"></script>
	<script>
		function calcTotal() {
			var qty = parseInt($("#gqty").val()) + parseInt($("#mqty").val()) + parseInt($("#ggqty").val()); 
			if (qty >= 5) {
				var total = qty * 5;
			} else {
				var total = qty * 10;
			}
			var shipVal = 0;
			$(".ship").each( function() {
				if ($(this).is(":checked")) {
					shipVal = $(this).val();
					return false;
				}
			});
			if (shipVal == 1) {
				var ship = 4;
			} else {
				var ship = 0;
			}
			$(".total").text('$' + (total + ship)  + '.00');
		}
		$(function() {
			$("#shipping").hide();
			$("#copyInfo").hide();
			
			//$("#tblTickets").hide();
			
			$.post('chidon/ajax/getContestants.php', {gender : 'boys'}, function(success) {
				var data = $.parseJSON(success);
				var str = "<option value='0'>Choose Contestant</option>";
				for (var d in data) {
					for (var c in data[d]) {
						str += "<option value='" + c + "'>" + data[d][c] + "</option>";
					}
				}
				$("#bcontestant").append(str);
			});
			
			$.post('chidon/ajax/getContestants.php', {gender : 'girls'}, function(success) {
				var data = $.parseJSON(success);
				var str = "<option value='0'>Choose Contestant</option>";
				for (var d in data) {
					for (var c in data[d]) {
						str += "<option value='" + c + "'>" + data[d][c] + "</option>";
					}
				}
				$("#gcontestant").append(str);
			});
			
			$(".chidonType").change( function() {
				var val = $(this).val();
				if (val == 'b') {
					var gender = 'boys';
					$("#contestant").empty();
					$.post('ajax/getContestants.php', {gender : gender}, function(success) {
						var data = $.parseJSON(success);
						var str = '';
						for (var d in data) {
							for (var c in data[d]) {
								str += "<option value='" + c + "'>" + data[d][c] + "</option>";
							}
						}
						$("#contestant").append(str);
						$("#menTickets").show();
						$("#tblTickets").show();
					});
				} else if (val == 'g') {
					var gender = 'girls';
					$("#menTickets").hide();
					$("#contestant").empty();
					$.post('ajax/getContestants.php', {gender : gender}, function(success) {
						var data = $.parseJSON(success);
						var str = '';
						for (var d in data) {
							for (var c in data[d]) {
								str += "<option value='" + c + "'>" + data[d][c] + "</option>";
							}
						}
						$("#contestant").append(str);
						$("#tblTickets").show();
					});
				}
			});
			
			$(".qty").change(function() {
				calcTotal();
			});
			
			<? if (isset($_POST['mqty']) || isset($_POST['gqty']) || isset($_POST['ggqty'])) { ?>
				$(".qty").trigger('change');
			<? } ?>
			
			$(".ship").click(function() {
				var val = $(this).val();
				if (val == 1) {
					$("#shipping").show();
					$("#copyInfo").show();
				} else {
					$("#shipping").hide();
					$("#copyInfo").hide();
				}
				calcTotal();
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
					$(".bfname").val($(".sfname").val());
					$(".blname").val($(".slname").val());
					$(".baddress").val($(".saddress").val());
					$(".bcity").val($(".scity").val());
					$(".bstate").val($(".sstate").val());
					$(".bzip").val($(".szip").val());
				} else {
					$(".bfname").val('');
					$(".blname").val('');
					$(".baddress").val('');
					$(".bcity").val('');
					$(".bstate").val('');
					$(".bzip").val('');
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
				//var total = (parseInt($("#gqty").val()) + parseInt($("#mqty").val()) + parseInt($("#ggqty").val()));
				var fname = $(".bfname").val();
				var lname = $(".blname").val();
				//var ccnum = $("#ccnum").val();
				var phone = $(".bphone").val();
				var email = $(".bemail").val();
				var vemail = $(".bvemail").val();
				var zip = $(".bzip").val();
				//var sec = $(".sec").val();
				
				var errors = '';
				/*
				if (total == 0) {
					errors += "You have not chosen any tickets!";
				}
				
				if (checkType() == false) {
					if (errors != '') errors += "\n";
					errors += "You must indicate which chidon you are buying tickets for!";
				}
				*/
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
				/*
				if (ccnum == '' || sec == '') {
					if (errors != '') errors += "\n";
					errors += "You need to enter your credit card number and security code.";
				}
				*/
				if (errors != '') {
					alert(errors);
					return false;
				}
			});
		});
	</script>
</html>