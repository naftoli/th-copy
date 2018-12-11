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
	</head>
	
	<body>
		<div class="container-fluid" style="max-width: 600px;">
			<div class="row">
				<div class="col-xs-2"></div>
				<div class="col-xs-8"><img src="hakhel.jpg" class="img-responsive" /></div>
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
			
			<form action="donate.php" method="post">
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
                        <input type="text" name="ccaddress" id="ccaddress" class="form-control" placeholder="Billing Address Line 1" />
                    </div>
                    <div class="form-group">
                        <input type="text" name="ccaddress2" id="ccaddress2" class="form-control" placeholder="Billing Address Line 2" />
                    </div>
	              </div>
	              <div class="col-xs-4">
                    <div class="form-group">
                        <input type="text" name="cccity" id="cccity" class="form-control" placeholder="City" />
                    </div>
	              </div>
	              <div class="col-xs-4">
                    <div class="form-group">
                        <input type="text" name="ccstate" id="ccstate" class="form-control" placeholder="State" />
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
                        <input type="number" name="cccvv" id="cccvv" class="form-control" placeholder="CVV" />
                    </div>
	              </div>
	              
	              <div class="col-xs-12">
	              	<div align="center">
		              	<select class="selectpicker" name="amount" id="amount">
		              		<option value='0'>Choose Amount</option>
		              		<option value='140'>$140 1x הקהל</option>
		              		<option value='280'>$280 2x הקהל</option>
		              		<option value='320'>$320 3x הקהל</option>
		              		<option value='460'>$460 4x הקהל</option>
		              		<option value='500'>$500 VIP Seat</option>
		              		<option value='1400'>$1400 10x הקהל</option>
		              		<option value='5000'>$5000 Sponsor one of the 12 Pesukim</option>
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
	              	<div align="center">
	              		<br />
            			<input type="submit" name="submit" value="Submit" id="submit" class="btn btn-primary" />
	              		<br />
	              	</div>
	              </div>
	  		</form>
		</div>
		<script src="/mobile/reg/plugins/bootstrap-select/dist/js/bootstrap-select.js"></script>
		<script>				
			$( function() {
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
				
				$("#submit").click( function() {
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
		</script>
	</body>
</html>