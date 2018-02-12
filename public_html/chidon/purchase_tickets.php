<?php
if ((empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off') || $_SERVER['SERVER_PORT'] != 443) {
    header("Location: https://mashpia.com/chidon/purchase_tickets.php");
}
require '../db.php';

$year = 5777;
$totalTickets = 0;

$max = array(
	'm10'	=>	37,
	'm18'	=>	101,
	'm36'	=>	102,
	'm50'	=>	84,
	'm100'	=>	38,
	'g10'	=>	161,
	'g18'	=>	138,
	'gg10'	=>	198,
	'gg18'	=>	179,
	'gg36'	=>	102,
	'gg50'	=>	84,
	'gg100'	=>	38
);

$soldOut = array('g10','g18','m10','m18','m36','m50','m100','gg10','gg18','gg36','gg50','gg100');

$maxTotal = 0;
foreach ($max as $field => $amount) {
	$maxTotal += $amount;
	$sql = "select sum(" . $field . ") as total from chidon
			where year = " . $year;
	$result = mysql_query($sql);
	$row = mysql_fetch_assoc($result);
	$total = $row['total'];
	if ($total >= $amount) {
		if ( !in_array($field, $soldOut) )
			$soldOut[] = $field;
	}
}

$sql = "select sum(mqty) as total from chidon 
		where year = " . $year . " 
		and mqty > 0";
$result = mysql_query($sql);
$row = mysql_fetch_assoc($result);
$totalTickets += $row['total'];

$sql = "select sum(gqty) as total from chidon 
		where year = " . $year . " 
		and gqty > 0";
$result = mysql_query($sql);
$row = mysql_fetch_assoc($result);
$totalTickets += $row['total'];

$sql = "select sum(ggqty) as total from chidon 
		where year = " . $year . " 
		and gqty > 0";
$result = mysql_query($sql);
$row = mysql_fetch_assoc($result);
$totalTickets += $row['total'];

if ($totalTickets >= $maxTotal) {
	$msg = "All tickets have been sold out. Please contact Chidon HQ for more info.";
}

$tickets = array(10,18,36,50,100);
$purchased = false;
if (isset($_POST['submit'])) {
	
	//echo "<pre>"; print_r($_POST); echo "</pre>"; exit;
	$bcontestant = 0;
	$gcontestant = 0;
	
	foreach ($_POST as $k => $v) {
		$$k = mysql_real_escape_string(trim($v)); 
	} 
	
	$m10 = 0;
    $m18 = 0;
    $m36 = 0;
    $m50 = 0;
    $m100 = 0;
    $g10 = 0;
    $g18 = 0;
    $gg10 = 0;
    $gg18 = 0;
    $gg36 = 0;
    $gg50 = 0;
    $gg100 = 0;
    
    $mqty = 0;
    $gqty = 0;
    $ggqty = 0;
	$total = 0;
    
	foreach ($_POST['btickets'] as $amount => $qty) {
		if ($qty) {
            $var = "m" . $amount;
			$$var += $qty;
            $mqty += $qty;
			$total += intval($amount * $qty);
		}
	}
	
	foreach ($_POST['gtickets'] as $amount => $qty) {
		if ($qty) {
            $var = "g" . $amount;
            $$var += $qty;
            $gqty += $qty;
			$total += intval($amount * $qty);
		}
	}
	
	foreach ($_POST['ggtickets'] as $amount => $qty) {
		if ($qty) {
            $var = "gg" . $amount;
            $$var += $qty;
            $ggqty += $qty;
			$total += intval($amount * $qty);
		}
	}
	
	//check that needed values are correct
	$errors = array();
	if ($total == 0) {
		$errors[] = "You have not chosen any tickets.";
	} 
	if (empty($bfname) || empty($blname) || empty($bphone) || empty($bemail) || empty($bvemail) || empty($bzip) || empty($baddress) || empty($bcity) || empty($bstate)) {
		$errors[] = "All personal information is mandatory.";
	}
	if (!filter_var($bemail, FILTER_VALIDATE_EMAIL) || 
		!filter_var($bvemail, FILTER_VALIDATE_EMAIL) || 
		$bemail != $bvemail) {
			$errors[] = "Your emails are empty, incorrect format or do not match.";
	}
	if (empty($ccnum)) {
		$errors[] = "You need to enter a valid credit card number.";
	}
	/*
	if ($ship == 1) {
		//shipping address is mandatory
		if (empty($sfname) || empty($slname) || empty($saddress) || empty($scity) || empty($sstate) || empty($szip)) {
			$errors[] = "You have indicated that you would like to have your tickets shipped.<br /> 
						Please fill out all personal information.";
		}
	}
	*/		
	if (empty($errors)) {
		chdir('../');

		//check for spammers    
		include 'check_for_spammers.php';
				
		//send through authorize.net
		$card_num = $ccnum;
		$yy = substr($yy, 2, 2);
		$exp_date = $mm . $yy;
		if ($ship == 1) $total += 4;
		$amount = $total;
		$description = "Tickets for Chidon " . $year;
		$first_name = $bfname;
		$last_name = $blname;
		$address = $baddress;
		$state = $bstate;
		$city = $bcity;
		$zip = $bzip;
		
		//echo $card_num . "<br />" . $exp_date . "<br />" . $zip; exit;
		$skipAuth = false;
		if ($card_num == '11111111' && $exp_date == '1219' && $zip == '12345') {
			$skipAuth = true;
		}
		
		if (!$skipAuth) {
			require_once 'authorize.php';
		} else {
			$response_array[0] = 1;
			$response_array[3] = 'Test';
			$response_array[4] = 'from';
			$response_array[6] = 'Tzivos Hashem';
			$response_array[9] = 'HQ';
		}
		
		chdir('chidon');
		
		if (isset($response_array) && !empty($response_array)) {
			if ($response_array[0] == 1) { //success
                $purchased = true;
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
						set name = '" . $bfname . ' ' . $blname . "',
						phone = '" . $bphone . "',
						email = '" . $bemail . "',
                        mqty = " . $mqty . ",
                        gqty = " . $gqty . ",
                        ggqty = " . $ggqty . ", 
						paid = " . $amount . ",
						approval = '" . $approval . "',
						date_purchased = now(),
						method = '" . $method . "',
						address = \"" . $baddress . "\",
						city = '" . $bcity . "',
						state = '" . $bstate . "',
						zip = '" . $bzip . "',
						year = " . $year . ",
						chidon_reg_id = " . $bcontestant . ",
						chidon_reg_id2 = " . $gcontestant . ",
						m10 = " . $m10 . ",
						m18 = " . $m18 . ",
						m36 = " . $m36 . ",
						m50 = " . $m50 . ",
						m100 = " . $m100 . ", 
						g10 = " . $g10 . ",
						g18 = " . $g18 . ",
						gg10 = " . $gg10 . ",
						gg18 = " . $gg18 . ",
						gg36 = " . $gg36 . ",
						gg50 = " . $gg50 . ",
						gg100 = " . $gg100;
				if (! @mysql_query($sql)) {
					$to = 'naftolir@gmail.com';
					$subject = 'Error in Chidon Ticket SQL';
					$message = $sql . " - " . mysql_error();
					@mail($to, $subject, $message);
				}
				
				$message = "Dear $name, thank you for your purchase of ";
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
					$message .= ". Your ticket(s) are available for pickup at the JCM Museum - 792 Eastern Parkway, Starting March 5th between 10:00am - 3:30pm.";
				} else if ($ship == 3) {
					$message .= ". Your ticket(s) will be available for pickup at the Event.";
				}
				
				//send confirmation email to buyer
				$to = $bemail;
				$subject = "Chidon Ticket purchase confirmation";
				$headers  = 'MIME-Version: 1.0' . "\r\n";
				$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
				$headers = 'From: chidon@tzivoshashem.org' . "\r\n";
				$headers .= 'Reply-to: chidon@tzivoshashem.org' . "\r\n";
				$headers .= 'CC: chidon@tzivoshashem.org' . "\r\n";
				
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
<!DOCTYPE html>
<html>
<head>
<meta charset="utf8" />
<title>Chidon</title>
<link href="css/bootstrap.css" rel="stylesheet" type="text/css" media="all" />
<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
<script src="js/jquery.min.js"></script>
<script src="js/bootstrap.js"></script>
<!-- Custom Theme files -->
<!--theme-style-->
<link href="css/style.css" rel="stylesheet" type="text/css" media="all" />	
<!--//theme-style-->
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta http-equiv="Content-Type" content="text/html; charset=windows-1255" />
<meta name="keywords" content="" />
<script type="application/x-javascript"> addEventListener("load", function() { setTimeout(hideURLbar, 0); }, false); function hideURLbar(){ window.scrollTo(0,1); } </script>
<!--fonts-->

<link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Sanchez" />
<!--//fonts-->
<script type="text/javascript" src="js/move-top.js"></script>
<script type="text/javascript" src="js/easing.js"></script>
				<script type="text/javascript">
					jQuery(document).ready(function($) {
						$(".scroll").click(function(event){		
							event.preventDefault();
							$('html,body').animate({scrollTop:$(this.hash).offset().top},1000);
						});
					});
				</script>
<link href="css/nav.css" rel="stylesheet" type="text/css" media="all"/>
<style type="text/css">
body,td,th {
	font-family: Sanchez, "Sanchez Slab";
}
</style>
<script src="js/easyResponsiveTabs.js" type="text/javascript"></script>
		    <script type="text/javascript">
			    $(document).ready(function () {
			        $('#horizontalTab,#horizontalTab1,#horizontalTab2').easyResponsiveTabs({
			            type: 'default', //Types: default, vertical, accordion           
			            width: 'auto', //auto or any width like 600px
			            fit: true   // 100% fit in a container
			        });
			    });
			   </script>


<script src="js/main.js"></script> <!-- Resource jQuery -->

<style>
			@font-face {
				font-family: 'Sanchez';
				src: url('chidonfonts/Sanchez-Bold.otf');
			}
			#my .top {
				text-align: center;
			}
			#my .top img {
				width: 500px;
			}
			#my .tickets {
				width: 500px;
				margin: auto;
				margin-top: 20px;
			}
			#my table, fieldset {
				font-size: 14px;
			}
			#my th {
				text-align: left;
			}
			#my tr, th, td {
				padding: 3px;
			}
			#my fieldset {
				margin-bottom: 20px;
				-moz-border-radius: 10px;
                -webkit-border-radius: 10px;
                border-radius: 10px;
			}
			#my legend {
				margin-left: 20px;
                padding: 5px;
                font-size: 16px;
                font-family: 'Sanchez';
                font-weight: bold;
			}
			.errors {
				color: red;
				font-size: 14px;
				font-weight: bold;
			}
			#my .bottom p {
				font-size: 12px;
			}
			.number p span.total {
				display: inline !important;
			}
			#my #shippingOptions {
				font-size: 12px;
				margin-left: 20px;
			}
			.ticketImg {
				padding: 30px;
			}
			.nav > li > a {
				color: grey;
				font-size: 20px;
				font-weight: normal;
				font-family: Sanchez, "Sanchez Slab";
			}
			ul.nav li.active a {
				color: #000;
			}
		</style>
    	
</head>
<body>
<!--header-->

<div class="container">

<div class="main-top">
	<div class="main">
		<div class="header">
			<div class="header-top">
				<div class="header-in">
					<div class="logo"> <img src="images/topheader.png" alt="" >
					</div>
					
				  <div class="clearfix"> </div>
				</div>
				
				<div class="clearfix"> </div>
			</div>
			<!---->
			
	</div>
		
	<?php require 'menu.php' ?>
	
<div class="content">

	<div class="col-md-9 content-top">
		<div class="number">
				
    		
    		<div class="row_8">
   		       
			  
               	
               
		<div class='my'>
			
			<?php if (isset($msg) || (isset($errors) && !empty($errors))) : ?>
			
			<div class='top'>
			
			<? 
			if (isset($msg)) {
				echo "<div class='errors'>";
				echo $msg;
				echo "<br /></div>";
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
			
			<?php else : ?>
            
            <?php if ($purchased) : ?>
				<div class='errors'>
					Your ticket(s) has been purchased. You will receive a confirmation email shortly. Thank you!
					<br /><br />
				</div>
			<?php endif; ?>
		<!--
		<div align="center">
			<img class="ticketImg" src="images/Chidon-Tickets-1.jpg" width="200" />
			<img class="ticketImg" src="images/Chidon-Tickets-2.jpg" width="200" />
			<img class="ticketImg" src="images/Chidon-Tickets-3.jpg" width="200" />
			
		</div>
		-->
		<!--
		<div>
			<fieldset>
				<legend>Ticket Info</legend>
				Not Yet Available for Purchase
			</fieldset>
		</div>
		-->
		<div style="color: red; font-size: 16px; font-weight: bold;">
			<!--Disclaimer: This site is optimized for Desktop use. It may not work properly on mobile devices.-->
			We are currently sold out! You can watch the rally live at http://www.chabad.org/chidon.
			<br /><br />
		</div>
		<div>
			<fieldset>
				<legend>Chidon Info</legend>
				Family and friends are invited to the Grand Chidon Event taking place at:<br />
				Francis de Sales School for the Deaf - 260 Eastern Parkway, Brooklyn, NY 11225
				<br /><br />
				Sunday, March 19 for the Girls. (Women and girls only)<br />
				Sunday, March 26 for the Boys. (Women are invited to attend the boys chidon as well. There will be separate seating.)<br />
				<br />
				Tickets are: $10, $18, $36, $50, $100.<br />
				Higher priced tickets will get seats closer to the front as well as helping us cover the costs of the Chidon.
			</fieldset>
		</div>
		<br />
		<!--
		<? //if ($total['m'] > 252 && $total['f'] > 220) : ?>
		<div>
			 <h4>Please call the Chidon office to purchase tickets. 718-907-8884.</h4>
		</div>
		<? //else : ?>
		-->
		<div class='tickets'>
			<a name="tickets"></a>
			<form action="purchase_tickets.php" method="post" id="ticketsForm">
				<fieldset>
					<legend>Tickets</legend>
					
					<div>
						<!-- Nav tabs -->
						<ul class="nav nav-tabs" role="tablist">
						  <li role="presentation" class="active"><a href="#bchidon" aria-controls="bchidon" role="tab" data-toggle="tab">Boy's Chidon</a></li>
						  <li role="presentation"><a href="#gchidon" aria-controls="gchidon" role="tab" data-toggle="tab">Girl's Chidon</a></li>
						</ul>
					  
						<!-- Tab panes -->
						<div class="tab-content">
						  <div role="tabpanel" class="tab-pane active" id="bchidon">
							<div class="table-responsive">
							  <table class="table table-striped table-bordered">
								<?php foreach ($tickets as $amount) : ?>
									<tr>
										<td width="150">
											$<?=$amount?> Tickets
										</td>
										<td>
											Men Ticket Qty:
											<select name="btickets[<?=$amount?>]" class="qty"
											<?php
											$sold = false;
											$field = "m" . $amount;
											if (in_array($field, $soldOut)) {
												echo "disabled ";
												echo "style='opacity:.4'";
												$sold = true;
											}
											?>
											>
												<?php
												for ($i = 0; $i <= 20; $i++) {
													echo "<option value='" . $i . "'";
													if (isset($_POST['btickets'][$amount]) && $_POST['btickets'][$amount] == $i) echo " selected"; 
													echo ">" . $i . "</option>";
												}
												?>
											</select>
											<?php if ($sold) : ?>
												<span style="color: red; font-weight: bold; margin-left: 2px;"><i>sold out</i></span>
											<?php endif; ?>
										</td>
										<td>
											<?php if ($amount <= 18) : ?>
												Women Ticket Qty:
												<select name="gtickets[<?=$amount?>]" class="qty"
												<?php
												$sold = false;
												$field = "g" . $amount;
												if (in_array($field, $soldOut)) {
													echo "disabled ";
													echo "style='opacity:.4'";
													$sold = true;
												}
												?>
												>
													<?php
													for ($i = 0; $i <= 20; $i++) {
														echo "<option value='" . $i . "'";
														if (isset($_POST['gtickets'][$amount]) && $_POST['gtickets'][$amount] == $i) echo " selected";
														echo ">" . $i . "</option>";
													}
													?>
												</select>
												<?php if ($sold) : ?>
													<span style="color: red; font-weight: bold; margin-left: 2px;"><i>sold out</i></span>
												<?php endif; ?>
											<?php endif; ?>
										</td>										
									</tr>
								<? endforeach; ?>
								<tr>
									<td colspan="3">I am coming in honor of the following chidon contestant:
										<select name="bcontestant" id="bcontestant"></select>
									</td>
								</tr>
							  </table>
							</div>
						  </div>
						  <div role="tabpanel" class="tab-pane" id="gchidon">
							<div class="table-responsive">
							  <table class="table table-striped table-bordered">
								<?php foreach ($tickets as $amount) : ?>
									<tr>
										<td width="150">
											$<?=$amount?> Tickets
										</td>
										<td>
											Women Ticket Qty:
											<select name="ggtickets[<?=$amount?>]" class="qty"
											<?php
											$sold = false;
											$field = "gg" . $amount;
											if (in_array($field, $soldOut)) {
												echo "disabled ";
												echo "style='opacity:.4'";
												$sold = true;
											}
											?>
											>
												<?php
												for ($i = 0; $i <= 20; $i++) {
													echo "<option value='" . $i . "'";
													if (isset($_POST['ggtickets'][$amount]) && $_POST['ggtickets'][$amount] == $i) echo " selected";
													echo ">" . $i . "</option>";
												}
												?>
											</select>
											<?php if ($sold) : ?>
												<span style="color: red; font-weight: bold; margin-left: 2px;"><i>sold out</i></span>
											<?php endif; ?>
										</td>
									</tr>
								<? endforeach; ?>
								<tr>
									<td colspan="3">I am coming in honor of the following chidon contestant:
										<select name="gcontestant" id="gcontestant"></select>
									</td>
								</tr>
							  </table>
							</div>
						  </div>
						</div>
					  
					</div>
				</fieldset>
				
				<fieldset>
					<legend>Options</legend>
					I would like to have my tickets:<br />
					<div id="shippingOptions">
						<!--<input type='radio' class='ship' name='ship' value='1'> Shipped ($4 flat fee)<br />-->
						<input type='radio' class='ship' name='ship' value='2'> Picked up at the JCM (Tickets available for pickup from Wednesday before the Chidon on 5th floor of JCM)<br />
						<input type='radio' class='ship' name='ship' value='3'> Picked up at the Event<br />
					</div>
				</fieldset>
				<br />
				
				<p><b>Total: <span class='total'></span></b></p>
				
				<br />
				<fieldset>
					<legend>Personal Information</legend>
					<table>
						<tr>
							<td>First Name</td>
							<td><input type='text' name='bfname' class='bfname' 
								value="<?=isset($_POST['bfname']) ? $_POST['bfname'] : ''?>" tabindex="27" required /></td>
							<td>Phone</td>
							<td><input type='text' name='bphone' class='bphone'
								value="<?=isset($_POST['bphone']) ? $_POST['bphone'] : ''?>" tabindex="33" /></td>
						</tr>
						<tr>
							<td>Last Name</td>
							<td><input type='text' name='blname' class='blname'
								value="<?=isset($_POST['blname']) ? $_POST['blname'] : ''?>" tabindex="28" required /></td>
							<td>Email</td>
							<td><input type='email' name='bemail' class='bemail'
								value="<?=isset($_POST['bemail']) ? $_POST['bemail'] : ''?>" tabindex="34" required /></td>
						</tr>
						<tr>
							<td>Address</td>
							<td><input type='text' name='baddress' class='baddress'
								value="<?=isset($_POST['baddress']) ? $_POST['baddress'] : ''?>" tabindex="29" required /></td>
							<td>Verify Email</td>
							<td><input type='email' name='bvemail' class='bvemail'
								value="<?=isset($_POST['bvemail']) ? $_POST['bvemail'] : ''?>" tabindex="35" required /></td>
						</tr>
						<tr>
							<td>City</td>
							<td><input type='text' name='bcity' class='bcity'
								value="<?=isset($_POST['bcity']) ? $_POST['bcity'] : ''?>" tabindex="30" required /></td>
							<td></td>
							<td></td>
						</tr>
						<tr>
							<td>State</td>
							<td><input type='text' name='bstate' class='bstate'
								value="<?=isset($_POST['bstate']) ? $_POST['bstate'] : ''?>" tabindex="31" required /></td>
							<td></td>
							<td></td>
						</tr>
						<tr>
							<td>Zip</td>
							<td><input type='text' name='bzip' class='bzip'
								value="<?=isset($_POST['bzip']) ? $_POST['bzip'] : ''?>" tabindex="32" required /></td>
							<td></td>
							<td></td>
						</tr>
					</table>
				</fieldset>
				
				<br />
				<fieldset>
					<legend>Credit Card Info</legend>
					<table>
						<tr>
							<td>Credit Card Number</td>
							<td><input type='text' name='ccnum' id='ccnum' size="40"
								value="<?=isset($_POST['ccnum']) ? $_POST['ccnum'] : ''?>" tabindex="36" required /></td>
						</tr>
						<tr>
							<td>Expiry</td>
							<td>
								<select name='mm' id='mm' tabindex="37">
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
								<select name='yy' id='yy' tabindex="38">
									<? for ($i = 2016; $i < 2025; $i++) {
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
							<td><input type='text' name='scode' class="sec" size='3' tabindex="39" required /></td>
						</tr>
					</table>
				</fieldset>
				
				<div align='center' class="bottom">
					<p>
						Ticket Prices are in U.S. dollars. There will be separate seating for men and women.<br />
						No refunds. For questions email <a href="mailto:chidon@tzivoshashem.org">chidon@tzivoshashem.org</a>.
					</p>
					<input type='submit' name='submit' value='submit' id='submit' tabindex="40" />
				</div>
			</form>
		</div>
		
		<?php endif; ?>
		
       </div> 
	  
	    </div>
			
		</div>
			<!---->
			
			<!---->
			
			<!---->
	</div>
	<!---->
		<!---->
	
		<div class="clearfix"></div>
		</div>
	<div class="archives-top">
				
				<div class="col-md-4 top-archives">
				  <h3>Yahadus curriculum created in memory of Sara Rohr
</h3>
				</div>
				<div class="col-md-4 top-archives">
				  <h3>A project of: <img src="images/sponsors.png" width="102" height="47" alt=""/></h3>
				</div>
				<div class="col-md-4 top-archives">
               
				  
				  
				  <h3>Chidon Sponsor:
					הרוצה בעילום שמו להצלחה מופלגה בגשמיות וברוחניות
					<? 
					//$str = " הרוצה בעילום שמו להצלחה מופלגה בגשמיות וברוחניות ";
					//$he = iconv('utf8', 'windows-1255', $str);
					//echo $he;
					?>
				  </h3>
					
				</div>
				
		<div class="col-md-12 top-archives" align="center" style="background: #fff">
			לע"נ הרב אליעזר בן הרב מרדכי ע"ה וונגר 
			לע"נ הרב יצחק בן הרב אליעזר צבי זאב ע"ה צירקינד
		</div>
                
      <div class="col-md-12 top-archives">
					<h3>Chidon Partners:
					<div id="sponsors">
							<div>
							<?
							$partners = array();
							if ($dh = opendir(getcwd() . '/sponsors')) {
							    while (($file = readdir($dh)) !== false) {
							    	if ($file != '.' && $file != '..') {
							      		$partners[] = $file;
									}
							    }
							    closedir($dh);
							}
							sort($partners);
							foreach ($partners as $key => $file) {
								if ($key && ($key % 5 == 0)) {
									echo "</div><div style='clear: both'></div><div>";
								}
								echo "<img src='sponsors/" . $file . "' />";
							}
							?>
							</div>
						</div>
					</h3>
					
				</div>
				<div class="clearfix"></div>
                
  </div>
	
	
	</div>
	
<script type="text/javascript">
						$(document).ready(function() {
							/*
							var defaults = {
					  			containerID: 'toTop', // fading element id
								containerHoverID: 'toTopHover', // fading element hover id
								scrollSpeed: 1200,
								easingType: 'linear' 
					 		};
							*/
							
							$().UItoTop({ easingType: 'easeOutQuart' });
							
						});
					</script>
				<a href="#" id="toTop" style="display: block;"> <span id="toTopHover" style="opacity: 1;"> </span></a>


</div>
</body>
	<script>
		var year = <?=$year?>;
		var total = 0;
		function calcTotal() {
			total = 0;
				
			$(".qty").each( function() {
				var name = $(this).attr('name');
				var pos = name.indexOf('[');
				var pos2 = name.indexOf(']');
				var amount = name.substring(pos+1, pos2);
				var val = $(this).val();
				total += (amount * val);
			});
			
			var ship = 0;
			var shipVal = 0;
			$(".ship").each( function() {
				if ($(this).is(":checked")) {
					shipVal = $(this).val();
					return false;
				}
			});
			if (shipVal == 1) {
				ship = 4;
			} else {
				ship = 0;
			}
			
			total += ship;
			$(".total").text('$' + total  + '.00');
		}
		$(function() {
			//$("#shipping").hide();
			//$("#copyInfo").hide();
			
			//$("#tblTickets").hide();
			
			$.post('ajax/getContestants.php', {gender : 'm', year : year}, function(success) {
				var data = $.parseJSON(success);
				var str = "<option value='0'>Choose Contestant</option>";
				for (var d in data) {
					for (var c in data[d]) {
						str += "<option value='" + c + "'>" + data[d][c] + "</option>";
					}
				}
				$("#bcontestant").append(str);
			});
			
			$.post('ajax/getContestants.php', {gender : 'f', year : year}, function(success) {
				var data = $.parseJSON(success);
				var str = "<option value='0'>Choose Contestant</option>";
				for (var d in data) {
					for (var c in data[d]) {
						str += "<option value='" + c + "'>" + data[d][c] + "</option>";
					}
				}
				$("#gcontestant").append(str);
			});
			
			$(".qty").change(function() {
				calcTotal();
			});
			
			<? if (isset($_POST['btickets']) || isset($_POST['gtickets']) || isset($_POST['ggtickets'])) { ?>
				$(".qty").trigger('change');
			<? } ?>
			
			$(".ship").click(function() {
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
			
			$("#submit").click(function(e) {
				//e.preventDefault();
				var fname = $(".bfname").val();
				var lname = $(".blname").val();
				var ccnum = $("#ccnum").val();
				var phone = $(".bphone").val();
				var email = $(".bemail").val();
				var vemail = $(".bvemail").val();
				var zip = $(".bzip").val();
				var sec = $(".sec").val();
				
				var errors = '';
				if (total < 10) {
					errors += "You have not chosen any tickets!";
				}

				if (fname == '' || lname == '') {
					if (errors != '') errors += "\n";
					errors += "You need to enter BOTH your first AND last name.";
				}
				if (zip == '') {
					if (errors != '') errors += "\n";
					errors += "You need to enter your zip code.";
				}
				if (phone == '') {
					if (errors != '') errors += "\n";
					errors += "You need to enter your phone number.";
				}
				if (phone.length < 10) {
					if (errors != '') errors += "\n";
					errors += "You need to enter the area code and seven digit phone number.";
				}
				if (email == '' || vemail == '') {
					if (errors != '') errors += "\n";
					errors += "You need to enter your email.";
				}
				if (email != vemail) {
					if (errors != '') errors += "\n";
					errors += "Your emails do not match, please try again.";
				}
				if (ccnum == '') {
					if (errors != '') errors += "\n";
					errors += "You need to enter your credit card number.";
				}
				
				if (sec == '') {
					if (errors != '') errors += "\n";
					errors += "You need to enter your security code.";
				}
				
				if (!$(".ship:checked").length) {
					if (errors != '') errors += "\n";
					errors += "You must choose how you want to recieve your ticket(s)";
				}

				var baddress = $(".baddress").val().trim();
				var bcity = $(".bcity").val().trim();
				var bstate = $(".bstate").val().trim();
				
				if (baddress == '') {
					if (errors != '') errors += "\n";
					errors += "You need to enter your address.";
				}
				
				if (bcity == '') {
					if (errors != '') errors += "\n";
					errors += "You need to enter your city.";
				}
				
				if (bstate == '') {
					if (errors != '') errors += "\n";
					errors += "You need to enter your state.";
				}
				
				if (errors != '') {
					e.preventDefault();
					alert(errors);
					return false;
				} else {
					return true;
				}
			});
		});
	</script>
</html>