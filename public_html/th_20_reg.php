<?
$str = substr($_SERVER['SCRIPT_URI'], 4, 1);
if ($str != 's') {
	header("Location: https://mashpia.com/th_20_reg.php");
}
if (isset($_POST['submit'])) {
	//echo "<pre>"; print_r($_POST); echo "</pre>"; exit;
	require_once 'db.php';
	
	foreach ($_POST as $k => $v) {
		$_POST[$k] = mysql_real_escape_string(trim($v));
	}
	
	$camp = $_POST['camp'];
	$staff = (int)$_POST['staff'];
	$campers = (int)$_POST['campers'];
	
	//check for spammers    
    include 'check_for_spammers.php';
	
	//send through authorize.net
	$card_num = $_POST['ccnum'];
	$exp_date = $_POST['mm'] . $_POST['yy'];
	$amount = $_POST['total'];
	$description = "Registration for Shnas Ho'esrim Rally";
	$first_name = '';
	$last_name = '';
	$address = '';
	$state = '';
	$zip = '';
	
	require_once 'authorize.php';
	
	$success = false;
	if (isset($response_array) && !empty($response_array)) {
		if ($response_array[0] == 1) { //success
			$success = true; 
		
			$sql = "insert into th_20_reg 
					set camp = '$camp', 
					number_staff = $staff, 
					number_campers = $campers, 
					ccauth = '" . implode(',', $response_array) . "'";
					
			if (@mysql_query($sql)) {
				$msg = "Your card has been charged and your registration has been accepted. Thank you.";
			} else {
				$msg = "Your card has been charged but there was an error saving your information please contact Tzivos Hashem.<br /><br />";
			}
		}
		else {
			$msg = $response_array[3];
		}
	} else {
		$msg = "There was an error processing your request, please try again.";
	}
}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>Gimmul Tammuz Shnas Ho'Esrim Tzivos Hashem rally registration</title>
		<script type="text/javascript" src="scripts/jquery-1.8.3.js"></script>
		<script>
			function calculateTotal() {
				var staff = parseInt($("#staff").val().trim());
				var campers = parseInt($("#campers").val().trim());
				var total = 0;
				if (staff) {
					total += staff;
				}
				if (campers) {
					total += campers;
				}
				$("#total").text(total*5);
				$(".total").val(total*5);
			}
			$(function() {
				$("#staff").blur(function() {
					calculateTotal();
				});
				
				$("#campers").blur(function() {
					calculateTotal();
				});
				
				$("#submit").click(function() {
					var errors = [];
					
					var camp = $("#camp").val().trim();
					var campers = parseInt($("#campers").val().trim());
					var staff = parseInt($("#staff").val().trim());
										
					if (camp == '') {
						errors.push("You need to enter your camp name.");
					}
					if (!campers) {
						errors.push("You need to enter the number of campers that you would like to register.");
					}
					if (!staff) {
						errors.push("You need to enter the number of staff that you would like to register.");
					}
					
					var ccnum = parseInt($("#ccnum").val().trim());
					var scode = parseInt($("#scode").val().trim());
					
					if (!ccnum) {
						errors.push("You must enter a valid credit card number.");
					}
					if (!scode) {
						errors.push("You must enter a valid security code.")
					}
					
					if (errors.length > 0) {
						var str = '';
						for (error in errors) {
							str += errors[error] + "\n";
						}
						alert(str);
						return false;
					} else {
						return true;
					}
				});
			});
		</script>
		<style>
			.main {
				margin-left: 20%;
				margin-right: 20%;
			}
            .camp table {
            	font-size: 14px;
            }
            td {
            	vertical-align: top;
            }
    	</style>
	</head>
	
	<body>
		<div class='main'>
			<div style='float: left; margin-right: 20px;'>
				<img src="images/Chidon-Logo.jpg" />
			</div>
			
	        <h1>Gimmul Tammuz Shnas Ho'Esrim<br />Tzivos Hashem Rally Registration</h1>
	        
	        <? 
	        if (isset($msg)) {
	        	if ($success) {
	        		echo $msg;
	        		exit;
	        	} else {
	        		echo "<div style='color:red'><b>$msg</b><br /><br /></div>";
	        	}
	        }	
	        ?>
	        
	        <h4>Every staff member as well as camper that is registered costs $5.</h4>
	        
	        <form action="th_20_reg.php" method="post">
	        	<div class='camp'>
	        		<table>
	        			<tr>
	        				<td>Camp Name</td>
	        				<td><input type='text' name='camp' id='camp' size='40' /></td>
	        			</tr>
	        			
	        			<tr>
	        				<td>Number of Staff Members</td>
	        				<td><input type='text' name='staff' id='staff' size='4' /></td>
	        			</tr>
	        			
	        			<tr>
	        				<td>Number of Campers</td>
	        				<td><input type='text' name='campers' id='campers' size='4' /></td>
	        			</tr>
	        			
	        			<tr>
	        				<td>&nbsp;</td>
	        				<td>&nbsp;</td>
	        			</tr>
	        			
	        			<tr>
							<td>Credit Card Type</td>
							<td>
								<select name='cctype' id='cctype'>
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
							<td><input type='text' name='scode' class="sec" size='3' id='scode' /></td>
						</tr>
						
						<tr>
							<td colspan='2'><b>You will be charged a total of $<span id='total'></span>.</b></td>
						</tr>
	        			
	        			<tr>
	        				<td colspan="2">
	        					<br />
	        					<input type='hidden' name='total' class="total" value='0' />
	        					<input type="submit" name="submit" id="submit" value="submit" />
	        				</td>
	        			</tr>
	        		</table>
	        	</div>
	        </form>
	    </div>
	</body>
</html>