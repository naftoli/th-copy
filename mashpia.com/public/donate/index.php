<?php
// make sure using secure page
if (!((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443)) {
	header("Location: https://mashpia.com/donate");
	exit;
}
$ip = $_SERVER['SERVER_ADDR'];
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<script src="//code.jquery.com/jquery-1.11.3.min.js"></script>
		
		<!-- Latest compiled and minified CSS -->
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
		<!-- Optional theme -->
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap-theme.min.css">
		<!-- Latest compiled and minified JavaScript -->
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
		
		<link rel="stylesheet" href="/mobile/reg/plugins/bootstrap-select/dist/css/bootstrap-select.css">
		<script src='https://www.google.com/recaptcha/api.js'></script>
	</head>
	
	<body>
		<div class="container-fluid" style="max-width: 600px;">
			<div class="row">
				<div class="col-xs-2"></div>
				<div class="col-xs-8">
					<img src="/mobile/img_new/TH Logo-colorful-svg.svg" class="img-responsive center-block">
				</div>
				<div class="col-xs-2"></div>
			</div>
			<br />
			
			<? if (isset($_GET['msg'])) : ?>
				<div class="alert alert-success" role="alert">
					 <?=urldecode($_GET['msg'])?>
				</div>
			<? endif; ?>
			
			<? if (isset($_GET['error'])) : ?>
				<div class="alert alert-danger" role="alert">
					<?=urldecode($_GET['error'])?>
				</div>
			<? endif; ?>
			
			<form action="https://mashpia.com/donate/donate.php" method="post">
	  			<div class="col-xs-12">
	  				<div class="form-group">
                        <input type="email" name="email" id="email" class="form-control" placeholder="Email Address" required />
                    </div>
                    <div class="form-group">
                        <input type="text" name="phone" id="phone" class="form-control" placeholder="Contact Phone Number" required />
                    </div>
                    <div class="form-group">
                        <input type="text" name="ccfname" id="ccfname" class="form-control" placeholder="First Name on Card" required />
                    </div>
                    <div class="form-group">
                        <input type="text" name="cclname" id="cclname" class="form-control" placeholder="Last Name on Card" required />
                    </div>
                    <div class="form-group">
                        <input type="text" name="ccaddress" id="ccaddress" class="form-control" placeholder="Billing Address Line 1" required />
                    </div>
                    <div class="form-group">
                        <input type="text" name="ccaddress2" id="ccaddress2" class="form-control" placeholder="Billing Address Line 2" />
                    </div>
	              </div>
	              <div class="col-xs-4">
                    <div class="form-group">
                        <input type="text" name="cccity" id="cccity" class="form-control" placeholder="City" required />
                    </div>
	              </div>
	              <div class="col-xs-4">
                    <div class="form-group">
                        <input type="text" name="ccstate" id="ccstate" class="form-control" placeholder="State" required />
                    </div>
	              </div>
	              <div class="col-xs-4">
                    <div class="form-group">
                        <input type="text" name="cczip" id="cczip" class="form-control" placeholder="Zip" required />
                    </div>
	              </div>
	              <div class="col-xs-12">
                    <div class="form-group">
                        <input type="text" name="cccountry" id="cccountry" class="form-control" placeholder="Country" />
                    </div>
	              </div>
	              <div class="col-xs-12">
                    <div class="form-group">
                        <input type="number" name="ccnum" id="ccnum" class="form-control" placeholder="Credit Card Number" required />
                    </div>
	              </div>
	              <div class="col-xs-6">
                    <div class="form-group">
                        <input type="number" name="ccexp" id="ccexp" class="form-control" placeholder="Expiry - MMYY" required />
                    </div>
	              </div>
	              <div class="col-xs-6">
                    <div class="form-group">
                        <input type="number" name="cccvv" id="cccvv" class="form-control" placeholder="CVV" required />
                    </div>
	              </div>
	              
	              <div class="col-xs-12">
	              	<div align="center">
		              	<select class="selectpicker" name="amount" id="amount">
		              		<option value='0'>Choose Amount</option>
		              		<?php
							$amounts = array(18,36,50,54,72,90,100,250,500,1000,5000);
							foreach ($amounts as $amount) {
								echo "<option value='" . $amount . "'>$" . $amount . "</option>";
							}
							?>
		              		<option value='-1'>Other Amount</option>              		
		              	</select>
		              </div>
	              </div>
					
				  <div class="otherGroup" style="display: none;">
				  	  <div class="col-xs-2"></div>	
		              <div class="col-xs-8">
		              	<br />
	                    <div class="input-group" align="center">
						  <span class="input-group-addon">$</span>
						  <input type="number" class="form-control" name="other" id="other" placeholder="Amount (to the nearest dollar)" />
						  <span class="input-group-addon">.00</span>
						</div>
		              </div>
		              <div class="col-xs-2"></div>
	              </div>

				  <div class="col-xs-12">
					<div align="center" style="padding-top: 20px">
						<textarea name="desc" rows="4" cols="60" placeholder="In Honor of... / In memory of... / Description"></textarea>
					</div>
				  </div>
				  
				  <div class="col-xs-12">
					<div align="center" style="padding-top: 20px">
						<div class="g-recaptcha" data-sitekey="6LcPSR0UAAAAADTTGGdFV71lEqIKFxf52FFN0An8"></div>
					</div>
				  </div>
	              
	              <div class="col-xs-12">
	              	<div align="center">
	              		<br />
            			<input type="submit" name="submit" value="Submit" id="submit" class="btn btn-primary" />
	              		<br />
	              	</div>
	              </div>
	  		</form>
		</div>
		<br />
		<script src="/mobile/reg/plugins/bootstrap-select/dist/js/bootstrap-select.js"></script>
		<script>
			var ip = "<?=$ip?>";
			$( function() {
				checkFraud();
				//$(".alert").hide();
				$('.selectpicker').selectpicker();
				
				$("#amount").change( function() {
					var amount = parseInt($(this).val());
					if (amount == -1) {
						$(".otherGroup").show();
					} else {
						$(".otherGroup").hide();
					}
				});
				
				$("#submit").click( function(e) {
					// check if same ip has been requesting this in the past few minutes more than 3 times
					if ( checkFraud() ) {
						alert("You cannot submit multiple requests in such a short time span.");
						return false;
					}

					var val = parseInt($("#amount").val());
					if (val == 0) {
						alert("You have not chosen an amount!");
						return false;
					} else if (val == -1) {
						var other = $("#other").val();
						if (other < 1) {
							alert("You must enter an amount!");
							return false;
						}
					}
				});					
			});

			function checkFraud() {
				let ips = [];
				if (typeof( Storage ) !== 'undefined') {
					if (! localStorage.getItem('ips') ) {
						var d = new Date();
						ips.push({
							address: ip, 
							requests: [ d ]
						});
						localStorage.setItem('ips', JSON.stringify( ips ));
					} else {
						let found = false;
						console.log(  localStorage.getItem('ips') );
						ips = JSON.parse( localStorage.getItem('ips') );
						for (i in ips) {
							let info = ips[i];
							if ( info.address == ip ) {
								found = true;
								// loop through requests to see how many there are and how spaced out they are
								let prevTime = 0;
								let numRequests = 0;
								for (r in info.requests) {
									let request = info.requests[r];
									let curTime = new Date(request).getTime();
									if ( prevTime ) {
										// check if previous request was within one minute of current request
										let diff = (curTime - prevTime) / 1000;
										if ( diff <= 60 ) {
											numRequests++;
										}
										// if last time there was a request from this ip is more than 24 hours, empty the requests array
										let day = 60 * 60 * 24;
										if ( diff > day ) {
											ips[i].requests = [];
										}
									}
									prevTime = curTime;
								}
								if ( numRequests >= 5 ) {
									alert("You cannot submit many donations in such a short amount of time.");
									return false;
								} else {
									ips[i].requests.push( new Date() );
								}
							} 
						}
						if ( !found ) {
							var d = new Date();
							ips.push({
								address: ip, 
								requests: [ d ]
							});
						}
						localStorage.setItem('ips', JSON.stringify( ips ));
					}
				}
			}
		</script>
	</body>
</html>