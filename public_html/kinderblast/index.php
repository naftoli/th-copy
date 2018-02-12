<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8" />
		<script type="text/javascript" src="//code.jquery.com/jquery-1.10.2.min.js"></script>
		<script type="text/javascript" src="../bootstrap/js/bootstrap.js"></script> 
		<link rel="stylesheet" type="text/css" href="../bootstrap/css/bootstrap.min.css" />
		<title>Kinderblast</title>
		<style>
			body {
				margin-left: 30%;
				margin-right: 30%;
			}
			h2 {
				text-align: center;
				border-bottom: 1px solid #B0B0B0;
			}
			img {
				width: 600px;
			}
			#amount1, #amount2 {
				color: red;
			}
		</style>
	</head>
	
	<body>	
		<?
		if (isset($_GET['m'])) {
			echo "<br />";
			echo "<div style='color: red; font-weight: bold'>";
			if ($_GET['m'] == 1) {
				echo "Your purchase has been successful. You will be receiving a confirmation email 
					shortly. Thank you.";
			} else {
				echo $_GET['m'];
			} 
			echo "</div>";
		}
		?>
	  <br />	
	  <form class="form-horizontal" action="order.php" method="post">
		<div class="well bs-component">
			
		  <fieldset>
		    <legend>Order Your Pesach Coloring Books</legend>	        
	        <div class="form-group">
	            <div class="col-md-12">
	            	<p>
						Tzivos Hashem has collaborated with Kinderblast Judaica to produce an exciting new Pesach product 
						for your children. So whether you want to reward Hebrew school students, or treat your own kids, 
						make sure your order while supplies last. The twelve page water color paint book, Includes 
						paintbrush and watercolor strips and will keep your kids busy for hours while learning about Pesach.
					</p>
					 
					<p style="font-style: italic">
						Cost <span style="color: red; text-decoration: line-through;">$7.00</span> - Your cost is $5.00 each<br />
						Order in bulk and only pay $3.60 each 
					</p>  
		        </div>
	        </div>	        
	      </fieldset>
	      
	      <fieldset>
	        <div class="form-group">
	            <div class="col-md-12">
	            	<img src="kinderblast2.jpg" />
	            </div>
	        </div>	        
	      </fieldset>
		
			
		  <div style="float: right">
        	  <span id="amount1">$5.00</span>
          </div>
	      
		  <fieldset>
		    <legend>Number of booklets</legend>
		    <div class="form-group">
	            <label for="qty" class="col-md-3 control-label">Qty</label>
	            <div class="col-md-2">
		            <select name="qty" id="qty" class="form-control">
		            	<?
		            	for ($i = 1; $i < 12; $i++) {
		            		echo "<option value='$i'>$i</option>";
		            	}
						for (; $i < 100; $i += 12) {
		            		echo "<option value='$i'>$i</option>";
						}
		            	?>
		            </select>
		        </div>
	        </div>
	      </fieldset>	
		  
		  <fieldset>
		    <legend>Personal Info</legend>
		    <div class="form-group">
	            <label for="fname" class="col-md-3 control-label">First Name</label>
	            <div class="col-md-9">
		            <input name="fname" type="text" class="form-control" placeholder="First Name" required="required" 
		            value="<?=isset($_POST['fname'])?$_POST['fname']:''?>" />
		        </div>
	        </div>
	        
	        <div class="form-group">
	            <label for="lname" class="col-md-3 control-label">Last Name</label>
	            <div class="col-md-9">
		            <input name="lname" type="text" class="form-control" placeholder="Last Name" required="required"
		            value="<?=isset($_POST['lname'])?$_POST['lname']:''?>" />
		        </div>
	        </div>
	        
	        <div class="form-group">
	            <label for="email" class="col-md-3 control-label">Email</label>
	            <div class="col-md-9">
		            <input name="email" type="text" class="form-control" placeholder="Email" required="required" size="50"
		            value="<?=isset($_POST['email'])?$_POST['email']:''?>" />
		        </div>
	        </div>
	        
	        <div class="form-group">
	            <label for="phone" class="col-md-3 control-label">Phone</label>
	            <div class="col-md-9">
		            <input name="phone" type="text" class="form-control" placeholder="Phone Number" required="required"
		            value="<?=isset($_POST['phone'])?$_POST['phone']:''?>" />
		        </div>
	        </div>
	      </fieldset>
	      
	      <fieldset>
		    <legend>Shipping</legend>
		    <div class="form-group">
		    	<div class="col-md-2"></div>
		    	<div class="col-md-10">
		    		<b>IMPORTANT!</b><br />
		    		If you choose to have it shipped you <b>WILL BE CHARGED</b> as follows:<br />
		    		1 - 6 Booklets: $6<br />
		    		6 - 12 Booklets (1 box): $12<br />
		    		Each additional box (12 booklets in a box) will be an additional $3.
		    	</div>
		    </div>
		  </fieldset>
		  
		  <div style="float: right">
		  	<span id="amount2">+ $6.00</span>
		  </div>
		  
		  <fieldset>
		  	<legend>Shipping Option</legend>
	        <div class="form-group">
	            <div class="col-md-12">
	            	<div class="col-md-3"></div>
		            <div class="col-md-3">
		            	<input type="radio" name="choice" value="1" checked="checked" class="choice" /> Ship to me
		            </div>
		            <div class="col-md-6">
		            	<input type="radio" name="choice" value="2" class="choice" /> 
		            	I will pick it up from Tzivos Hashem<br />
		            	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(792 Eastern Pkwy)
		            </div>
		        </div>
	        </div>	        
	      </fieldset>
		  		  
	      <fieldset id="shipInfo">
		    <legend>Shipping Info</legend>	        
	        <div class="form-group">
	            <label for="address" class="col-md-3 control-label">Address</label>
	            <div class="col-md-9">
		            <input name="address" type="text" class="form-control" placeholder="Address" size="50"
		            value="<?=isset($_POST['address'])?$_POST['address']:''?>" />
		        </div>
	        </div>
	        
	        <div class="form-group">
	            <label for="city" class="col-md-3 control-label">City</label>
	            <div class="col-md-3">
		            <input name="city" type="text" class="form-control" placeholder="City" 
		            value="<?=isset($_POST['city'])?$_POST['city']:''?>" />
		        </div>
		        <label for="state" class="col-md-3 control-label">State</label>
	            <div class="col-md-3">
		            <input name="state" type="text" class="form-control" placeholder="State" 
		            value="<?=isset($_POST['state'])?$_POST['state']:''?>" />
		        </div>
		     </div>
		     <div class="form-group">
		        <label for="zip" class="col-md-3 control-label">Zip</label>
	            <div class="col-md-3">
		            <input name="zip" type="text" class="form-control" placeholder="Zip" 
		            value="<?=isset($_POST['zip'])?$_POST['zip']:''?>" />
		        </div>
		        <label for="country" class="col-md-3 control-label">Country</label>
	            <div class="col-md-3">
		            <input name="country" type="text" class="form-control" placeholder="Country" 
		            value="<?=isset($_POST['country'])?$_POST['country']:''?>" />
		        </div>
	        </div>
	      </fieldset>
	      
	      <br />
	      <ul class="nav nav-pills" style="float: right">
		    <li class="active"><a href="#">Total <span class="badge">$<span id="ccamount">11.00</span></span></a></li>
		  	<input type="hidden" name="ccamount" class="ccamount" value="11.00" />
		  </ul>
		  
	      <fieldset>
	        <legend>Payment Info</legend>
		    <div class="form-group">
	            <label for="cctype" class="col-md-3 control-label">Credit Card</label>
	            <div class="col-md-9">
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
	            <label for="ccnum" class="col-md-3 control-label">Number</label>
	            <div class="col-md-9">
		            <input name="ccnum" type="text" class="form-control" placeholder="Credit Card Number" required="required">
		        </div>
	        </div>
	        
	        <div class="form-group">
	            <label for="ccexp" class="col-md-3 control-label">Expiration</label>
	            <div class="col-md-3">
		            <input name="ccexp" type="text" class="form-control" placeholder="MMYY" required="required" size="4">
		        </div>
		        <label for="cvv" class="col-md-3 control-label">CVV</label>
	            <div class="col-md-3">
		            <input name="cvv" type="text" class="form-control" placeholder="CVV" required="required" size="4">
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
	  <br />
	</body>
	
	<script>
		function calcTotal() {
			var shipping;
			$(".choice").each( function() {
				if ($(this).is(":checked")) {
					shipping = $(this).val();
				}
			});
			
			var val = $("#qty").val(); 
			if (val < 12) {
				var amount = val * 5;
				$("#amount1").text('$' + Number(amount).toFixed(2));
				
				if (shipping == 1) {
					if (val < 7) {
						amount += 6;
						$("#amount2").text('+ $6.00');
					} else if (val > 6) {
						amount += 12;
						$("#amount2").text('+ $12.00');
					}
				} else if (shipping = 2) {
					$("#amount2").text('+ $0.00');
				}
			} else if (val >= 12) {
				var amount = val * 3.6;
				$("#amount1").text('$' + Number(amount).toFixed(2));
				if (shipping == 1) {
					amount += 12;
					var exp = val / 12;
					if (exp > 1) {
						amount += (exp -1) * 3;
					}
					$("#amount2").text('+ ' + (12 + ((exp -1) * 3)) + '.00');
				} else if (shipping == 2) {
					$("#amount2").text('+ $0.00');
				}
			}
			var balance = Number(amount).toFixed(2);
			$("#ccamount").text(balance);
			$(".ccamount").val(balance);
		}
		
		$(".choice").click( function() {
			var val = $(this).val();
			if (val == 1) {
				$("#shipInfo").show();
				calcTotal();
			} else if (val == 2) {
				$("#shipInfo").hide();
				calcTotal();
			}
		});
		
		$("#qty").change( function() {
			calcTotal();
		});
	</script>
</html>