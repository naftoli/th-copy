<?
if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == "") {
    $redirect = "https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    header("Location: $redirect");
	exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Yud Aleph Nissan Rally</title>

    <!-- Bootstrap Core CSS - Uses Bootswatch Flatly Theme: http://bootswatch.com/flatly/ -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="css/freelancer.css" rel="stylesheet">

    <!-- Custom Fonts -->
    <link href="font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Lato:400,700,400italic,700italic" rel="stylesheet" type="text/css">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

</head>

<body id="page-top" class="index">

    <!-- Navigation -->
    <nav class="navbar navbar-default navbar-fixed-top">
        <div class="container">
            <!-- Brand and toggle get grouped for better mobile display -->
            <div class="navbar-header page-scroll">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="#page-top">Yud Aleph Nissan Rally 5776</a>
            </div>

            <!-- Collect the nav links, forms, and other content for toggling -->
            <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                <ul class="nav navbar-nav navbar-right">
                    <li class="hidden">
                        <a href="#page-top"></a>
                    </li>
                    <li class="page-scroll">
                    	<a href="#about">Info</a>
                    </li>
                    <li class="page-scroll">
                        <a href="#contact">Reserve Seat(s)</a>
                    </li>
                </ul>
            </div>
            <!-- /.navbar-collapse -->
        </div>
        <!-- /.container-fluid -->
    </nav>

    <!-- Header -->
    <header>
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <img class="img-responsive" src="img/CTH---11-Nissan-Rally-Poster-5776-HR.jpg" alt="">
                </div>
            </div>
        </div>
    </header>
    
    <!-- About Section -->
    <section class="success" id="about">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 text-center">
                    <h2>Reserve your seats for the Yud Aleph Nissan Rally 5776</h2>
                    <hr class="star-light">
                </div>
            </div>
            <div class="row">
                <div class="col-lg-8 col-lg-offset-2">
                    <p>There is a $5 Charge per seat</p>
                </div>
                <div class="col-lg-8 col-lg-offset-2">
                    <p>There are limited seats, and are available on first come first serve basis</p>
                </div>
          	</div>
          	<div class="row">
          		<!--
                <div class="col-lg-8 col-lg-offset-2">
                    <p>If your child is attending with his/her school money should be paid directly to your school</p>
                </div>
                -->
                <div class="col-lg-8 col-lg-offset-2">
                    <p>Wrist bands must be picked up by Tuesday Yud Aleph Nissan 10:00am from the JCM Ticket Booth</p>
                </div>
          	</div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 text-center">
                    <h2>Reserve Seat(s)</h2>
                    <hr class="star-primary" />
                </div>
            </div>
            <div class="row">
                <div class="col-lg-8 col-lg-offset-2">
                	<!--<div class="row" style="font-size: 48px; text-align: center; padding-top: 3%">SORRY WE ARE SOLD OUT</div>-->
                    <!-- To configure the contact form email address, go to mail/contact_me.php and update the email address in the PHP file on line 19. -->
                    <!-- The form should work on most web servers, but if the form is not working you may need to configure your web server differently. -->
                    
                    <form name="sentMessage" id="contactForm" >
                        <div class="row control-group">
                            <div class="form-group col-xs-12 floating-label-form-group controls">
                                <label>Family Name</label>
                                <input type="text" class="form-control" placeholder="Family Name" id="name" required data-validation-required-message="Please enter your name.">
                                <p class="help-block text-danger"></p>
                            </div>
                        </div>
                        <div class="row control-group">
                            <div class="form-group col-xs-12 floating-label-form-group controls">
                                <label>Email Address</label>
                                <input type="email" class="form-control" placeholder="Email Address" id="email" required data-validation-required-message="Please enter your email address.">
                                <p class="help-block text-danger"></p>
                            </div>
                        </div>
                        <div class="row control-group">
                            <div class="form-group col-xs-12 floating-label-form-group controls">
                                <label>Cell Number</label>
                                <input type="tel" class="form-control" placeholder="Cell Number" id="phone" required data-validation-required-message="Please enter your phone number.">
                                <p class="help-block text-danger"></p>
                            </div>
                        </div>
                        <div class="row control-group" style="padding-top: 3%;">
                            <div class="form-group col-xs-6">
                            	<select class="form-control" id="boys">
                            		<option value='0'>Boy Seats</option>
									<?
									for ($i = 1; $i < 11; $i++) {
										echo "<option value='" . $i . "'>" . $i . " boy seat" . ($i > 1 ? 's' : '') . "</option>";
									}
									?>
								</select>
							</div>
                            <div class="form-group col-xs-6">
                            	<select class="form-control" id="girls">
                            		<option value='0'>Girl Seats</option>
									<?
									for ($i = 1; $i < 11; $i++) {
										echo "<option value='" . $i . "'>" . $i . " girl seat" . ($i > 1 ? 's' : '') . "</option>";
									}
									?>
								</select>
                            </div>
                        </div>
                        <div class="row control-group">
                            <div class="form-group col-xs-12 floating-label-form-group controls">
                                <label>Name on Credit Card</label>
                                <input type="text" class="form-control" placeholder="Name on Credit Card" id="ccname" required data-validation-required-message="Please enter the name on the credit card.">
                                <p class="help-block text-danger"></p>
                            </div>
                        </div>
                        <div class="row control-group">
                            <div class="form-group col-xs-12 floating-label-form-group controls">
                                <label>Credit Card Number</label>
                                <input type="number" class="form-control" placeholder="Credit Card Number" id="ccnum" required data-validation-required-message="Please enter your credit card number.">
                                <p class="help-block text-danger"></p>
                            </div>
                        </div>
                        <div class="row control-group">
                            <div class="form-group col-xs-6 floating-label-form-group controls">
                                <label>Expiry</label>
                                <input type="number" class="form-control" placeholder="MMYY" id="expiry" required data-validation-required-message="Please enter credit card expiry.">
                                <p class="help-block text-danger"></p>
                            </div>
                            <div class="form-group col-xs-6 floating-label-form-group controls">
                                <label>CVV</label>
                                <input type="number" class="form-control" placeholder="CVV" id="cvv" required data-validation-required-message="Please enter the cvv number.">
                                <p class="help-block text-danger"></p>
                            </div>
                        </div>
                        <div class="row control-group">
                            <div class="form-group col-xs-6 floating-label-form-group controls">
                                <label>Billing Address Zip</label>
                                <input type="number" class="form-control" placeholder="Billing Address Zip Code" id="zip" required data-validation-required-message="Please enter billing address zip code.">
                                <p class="help-block text-danger"></p>
                            </div>
                        </div>
                        <br>
                        <div id="success"></div>
                        <div class="row pull-left">
                            <div class="form-group col-xs-12">
                                <button class="btn btn-success btn-lg total">$0.00</button>
                            </div>
                        </div>
                        <div class="row pull-right">
                            <div class="form-group col-xs-12">
                                <button type="submit" class="btn btn-success btn-lg reserve">Reserve Seat(s)</button>
                            </div>
                        </div>
                	</form>
                </div>
            </div>
            <div id="result"></div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="text-center">
        <div class="footer-below">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        Copyright &copy; Tzivos Hashem 5776
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scroll to Top Button (Only visible on small and extra-small screen sizes) -->
    <div class="scroll-top page-scroll visible-xs visible-sm">
        <a class="btn btn-primary" href="#page-top">
            <i class="fa fa-chevron-up"></i>
        </a>
    </div>

    <!-- jQuery -->
    <script src="js/jquery.js"></script>

    <!-- Bootstrap Core JavaScript -->
    <script src="js/bootstrap.min.js"></script>

    <!-- Plugin JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.3/jquery.easing.min.js"></script>
    <script src="js/classie.js"></script>
    <script src="js/cbpAnimatedHeader.js"></script>

    <!-- Contact Form JavaScript -->
    <script src="js/jqBootstrapValidation.js"></script>
    <script src="js/contact_me.js"></script>

    <!-- Custom Theme JavaScript -->
    <script src="js/freelancer.js"></script>
    
    <script>
    	var year = 5776;
    	$(function() {
    		$("#contactForm").submit( function() {
    			var boys = $("#boys").val();
    			var girls = $("#girls").val();
    			if (boys == 0 && girls == 0) {
    				alert("You must choose to reserve at least one boy's or girl's seat.");
    				if (boys == 0) $("#boys").focus();
    				else if (girls == 0) $("#girls").focus();
    			} else {
    				//process credit card
    				var name = $("#name").val().trim();
    				var email = $("#email").val().trim();
    				var phone = $("#phone").val().trim();
    				var ccname = $("#ccname").val().trim();
    				var ccnum = $("#ccnum").val().trim();
    				var exp = $("#expiry").val().trim();
    				var zip = $("#zip").val().trim();
    				var total = calcTotal();
    				var cvv = $("#cvv").val();
    				
    				var process = true;
    				if (name == '' || email == '' || phone == '' || ccname == '' || ccnum == '' || ccnum == 0 || 
    					exp == '' || exp == 0 || zip == '' || zip == 0 || total == 0 || cvv == '' || cvv == 0) {
    						return false;
    					}
    				
    				$.post('reserve.php', {
    					name : name, 
    					email : email, 
    					phone : phone, 
    					ccname : ccname, 
    					ccnum : ccnum, 
    					exp : exp, 
    					zip : zip,
    					boys: boys, 
    					girls : girls,  
    					total : total, 
    					year : year
    				}, function( data ) {
    					var success = $.parseJSON( data );
    					if (success == 'unsuccessful') {
    						alert('There was an error processing your transaction.');
    						return false;
    					} else {
    						var pos = success.indexOf(':');
    						if (pos == -1) {
    							var html = '<div class="alert alert-danger" role="alert">' + success + '</div>';
    						} else {
    							var html = '<div class="alert alert-success" role="alert">Thank you for your reservation. You should receive an email confirmation shortly.</div>';
    						}
    						$("#result").empty();
    						$("#result").append( html );
    						$("#result").focus();
    					}
    				});
    			}
    			return false;
    		});
    		$(".reserve").click( function() {
    			
    		});
    		
    		$("#boys, #girls").change( function() {
    			var total = calcTotal();
    			$(".total").text("$" + total + ".00");
    		});
    	});
    	
    	function calcTotal() {
    		var boys = $("#boys").val();
    		var girls = $("#girls").val();
    		var sum = parseInt( boys ) + parseInt( girls );
    		var total = sum * 5;
    		return total;
    	}
    </script>

</body>

</html>
