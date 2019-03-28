<?php
if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off') {
	header("Location: https://mashpia.com/cds.php");
}

function genRandomString() {
    $length = 10;
    $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
    $string = '';
 
    for ($p = 0; $p < $length; $p++) {
        $string .= $characters[mt_rand(0, strlen($characters))];
    }
 
    return $string;
}

$cds = array(
	1 => 'Chof Daled Teves', 
	2 => 'Reb Amnon', 
	3 => 'The Tehilim Zoger', 
	4 => 'Yud Tes Kislev'
);

$msg = "";
$showForm = true;
if (isset($_POST['submit'])) {
	
	if (!strpos($_POST['email'], '@')) {
		$msg .= "Your email address is invalid.<br />";
	}
	
	$cc = $_POST['ccnum'];
	$exp = $_POST['ccexp'];
	if (!is_numeric($cc) || !is_numeric($exp)) {
		$msg .= "Your credit card number as well as your expiry date can only contain digits with no spaces.<br />";
	}
	
	if (!empty($msg)) $msg .= "Please try again.";
	
	if (empty($msg)) {
		$_POST['ccamount'] = count($_POST['cds']) * 8;	
		$response = "";	
		//require_once 'authorize_teves.php';
		//if ($response_array[0] != 1) {
		//	$msg .= $response;
		//} else {
			$msg = "Thank you for your order.<br />You should be receiving an email with your link shortly.";
			$showForm = false;
			
			require_once 'db.php';
			
			//insert transaction into db
			$name = mysql_real_escape_string($_POST['fname'] . ' ' . $_POST['lname']);
			$email = mysql_real_escape_string($_POST['email']);
			$phone = mysql_real_escape_string($_POST['phone']);
			
			$desc = count($_POST['cds']) . " CD(s): ";
			$purchased = '';
			foreach ($_POST['cds'] as $order) {
				$desc .= $cds[$order] . "; ";
				$purchased .= $order . "|";
			}
			$purchased = substr($purchased, 0, strlen($purchased) - 1);
			
			$code = genRandomString();
			
			$sql = "insert into perl_cds set 
					name = '$name', 
					email = '$email', 
					phone = '$phone', 
					cds_purchased = '" . mysql_real_escape_string($purchased) . "', 
					description = '" . mysql_real_escape_string($desc) . "', 
					download_code = '" . mysql_real_escape_string($code) . ", 
					auth = '" . mysql_real_escape_string($response) . "'";
			if (! @mysql_query($sql)) {
				$error = $sql . "<br />" . mysql_error();
				@mail('naftolir@gmail.com', 'Error in CD Purchases', $error);
			} else {
				$orderID = mysql_insert_id();
			}

			//email links to purchaser
			$to = $_POST['email'];
			$subject = "Your CD purchase from Tzivos Hashem";
			$message = "Thank you " . $_POST['fname'] . ' ' . $_POST['lname'] . " for your purchase of " . count($_POST['cds']) . " CD's.<br />";
			$message .= "Your order number is: " . $orderID . ".<br />";
			$message .= "Here is your link: https://mashpia.com/cds/cd.php?orderID=" . $orderID . "<br />";
			$message .= "We are sure that you will enjoy your purchase.<br />Thank you.";
			$headers  = 'MIME-Version: 1.0' . "\r\n";
			$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
			//$headers .= "Cc: shimmy@jcm.museum, cth@tzivoshashem.org" . "\r\n";
			$headers .= "From: cth@tzivoshashem.org" . "\r\n";
			$headers .= "Reply-To: cth@tzivoshashem.org";
			@mail($to, $subject, $message, $headers);
		//}
	}
}
?>
<html>
	<head>
		<script type="text/javascript" src="//code.jquery.com/jquery-1.10.2.min.js"></script>
		<script type="text/javascript" src="bootstrap/js/bootstrap.js"></script> 
		<link rel="stylesheet" type="text/css" href="bootstrap/css/bootstrap.min.css" />
		<title>Purchase CD's</title>
		<script>
			$(function() {				
				$(".cds").click( function() {
					var num = 0;
					 $(".cds").each(function() {
					 	if ($(this).is(":checked")) {
					 		num++;
					 	}
					 });
					var total = num * 8;
					if (!isNaN(total)) {
						$("#ccamount").text(total + '.00');
					}
				});
				
				$("#submit").click( function() {
					var num = 0;
					 $(".cds").each(function() {
					 	if ($(this).is(":checked")) {
					 		num++;
					 	}
					 });
					 if (num == 0) {
					 	alert("You must choose at least one CD for purchase.");
					 	return false;
					 } else {
					 	return true;
					 }
				});
			});
		</script>
		
		<style>
			body {
				margin-left: 30%;
				margin-right: 30%;
			}
		</style>
	</head>
	
	<body>
		<!--
		<div align="center">
			<img src="images/24Teves.jpg" width="300px" /><br />
			<audio src="downloads/Trailer Chof Daled Teves.m4a" controls>
			    Your browser does not support the <video> element.   
			</audio>
		</div>
		<br />
		-->
		<p>&nbsp;</p>
		
		<? if (!empty($msg)) { ?>
			<div class="alert alert-dismissable alert-danger">
			  <button type="button" class="close" data-dismiss="alert">×</button>
			  <strong><?=$msg;?></strong>
			</div>
			<?
			if (!$showForm) {
				exit;
			} 
		}
		?>
		
		<div class="panel panel-info">
		  <div class="panel-heading">
		    <h3 class="panel-title">Instructions</h3>
		  </div>
		  <div class="panel-body">
		    Please choose which audio cd(s) you would like to purchase for download.<br />
			After purchasing you will be sent the download link.<br />
			Each CD costs $8.
		  </div>
		</div>	
		
		<form class="form-horizontal" action="cds.php" method="post">
		  
		    <ul class="nav nav-pills" style="float: right">
			  <li class="active"><a href="#">Total <span class="badge">$<span id="ccamount">0.00</span></span></a></li>
			</ul>
			
		<div class="well bs-component">
			
		  <fieldset>
		    <legend>Purchase(s)</legend>
		    <div class="form-group">
		      <label for="inputPassword" class="col-lg-2 control-label"></label>
		      <div class="col-lg-10">
		        <div class="checkbox">
		            <?
					foreach ($cds as $k => $v) {
						echo "<input type='checkbox' name='cds[]' class='cds' value='$k'> $v<br />";
					}
					?>
		        </div>
		      </div>
		    </div>
		  </fieldset>
		  
		  <fieldset>
		    <legend>Personal Info</legend>
		    <div class="form-group">
	            <label for="fname" class="col-lg-2 control-label">First Name</label>
	            <div class="col-lg-10">
		            <input name="fname" type="text" class="form-control" placeholder="First Name" required="required" 
		            value="<?=isset($_POST['fname'])?$_POST['fname']:''?>" />
		        </div>
	        </div>
	        
	        <div class="form-group">
	            <label for="lname" class="col-lg-2 control-label">Last Name</label>
	            <div class="col-lg-10">
		            <input name="lname" type="text" class="form-control" placeholder="Last Name" required="required"
		            value="<?=isset($_POST['lname'])?$_POST['lname']:''?>" />
		        </div>
	        </div>
	        
	        <div class="form-group">
	            <label for="email" class="col-lg-2 control-label">Email</label>
	            <div class="col-lg-10">
		            <input name="email" type="text" class="form-control" placeholder="Email" required="required" size="50"
		            value="<?=isset($_POST['email'])?$_POST['email']:''?>" />
		        </div>
	        </div>
	        
	        <div class="form-group">
	            <label for="phone" class="col-lg-2 control-label">Phone</label>
	            <div class="col-lg-10">
		            <input name="phone" type="text" class="form-control" placeholder="Phone Number" required="required"
		            value="<?=isset($_POST['phone'])?$_POST['phone']:''?>" />
		        </div>
	        </div>
	      </fieldset>
	      
	      <fieldset>
	        <legend>Payment Info</legend>
		    <div class="form-group">
	            <label for="cctype" class="col-lg-2 control-label">Credit Card</label>
	            <div class="col-lg-10">
		            <select name="cctype" class="form-control">
            			<option value="mc" selected="selected">MasterCard</option>
            			<option value="visa">Visa</option>
            			<option value="amex">American Express</option>
            			<option value="discover">Discover</option>
            			<option value="dinersclub">Diners Club</option>
            		</select>
		        </div>
	        </div>
	        
	        <div class="form-group">
	            <label for="ccnum" class="col-lg-2 control-label">Number</label>
	            <div class="col-lg-10">
		            <input name="ccnum" type="text" class="form-control" placeholder="Credit Card Number" required="required">
		        </div>
	        </div>
	        
	        <div class="form-group">
	            <label for="ccexp" class="col-lg-2 control-label">Expiration</label>
	            <div class="col-lg-10">
		            <input name="ccexp" type="text" class="form-control" placeholder="MMYY" required="required" size="4">
		        </div>
	        </div>
	      </fieldset>
	      	
		</div>
		    
	    <div class="form-group">
	      <div align="center">
			<button type="submit" class="btn btn-primary" id="submit" name="submit">Submit</button>
	        <button type="reset" class="btn btn-info">Reset</button>
	      </div>
	    </div>
	  </form>
		
	</body>
</html>