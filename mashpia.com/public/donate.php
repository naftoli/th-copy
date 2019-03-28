<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">

    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Donate to Tzivos Hashem Management System</title>
        <script type="text/javascript" src="scripts/jquery-1.8.3.js"></script>
        <style type="text/css">
            body {
                font-family: 'Georgia';
                background-color: #B1BAC9;
                font-size: 14px;
            }
            form {
                background-color: #D5D8DE;
                width: 500px;
                padding: 1em;
                text-align: left;
                border: 1px solid black;
            }
            fieldset {
                border: 1px solid white;
                -webkit-border-radius: 10px;
                -moz-border-radius: 10px;
                border-radius: 10px;
                margin: 0 0 1em 0;
            }
            legend {
                margin-left: 20px;
                color: purple;
            } 
            form div {
                float: left;
                width: 100%;
                padding: 0 0 0.75em 0;
                position: relative;
            }
            form p {
                font-style: italic;
                padding: 0;
                margin-left: 10px;
            }
            form fieldset div:first-of-type {
                padding-top: 1em;
            }
            form label {
                float: left;
                width: 30%;
                margin-left: 10px;
            }
            form input {
                width: 250px;
                border: 0;
                padding: 0.25em;
            }
            form textarea {
                width: 250px;
                border: 0;
                padding: 0.25em;
            }
            form input#amount {
                width: 50px;
            }
            form input[type="submit"], input[type="reset"] {
                width: 100px;
                color: purple;
                border: 1px solid white;
                -webkit-border-radius: 10px;
                -moz-border-radius: 10px;
                border-radius: 10px;
                cursor: pointer;
            }
            #copy {
                color: purple;
                cursor: pointer;
            }
            .em {
                color: red;
                font-weight: bold;
            }
            label.error {
                color: red;
            }
            #authorize {
                color: red;
                font-size: 18px;
            }
            #result {
                display: none;
                line-height: 2.0;
            }
        </style>
        <script type="text/javascript">
            $(function() { 
                $("#fname").focus();
                
                $("#amount").attr('disabled', true);
                
                $("#donation").change( function() { 
                    var donation = $(this).val();
                    if ( donation == 0 ) {
                        $("#amount").attr('disabled', false);
                        $("#amount").focus();
                    } else {
                        $("#amount").attr('disabled', true);
                    }
                });
                
                $("#copy").click( function() {
                    $("#ccfname").val( $("#fname").val() );
                    $("#cclname").val( $("#lname").val() );
                    $("#ccaddress").val( $("#address").val() );
                    $("#ccaddress2").val( $("#address2").val() );
                    $("#cccity").val( $("#city").val() );
                    $("#ccstate").val( $("#state").val() );
                    $("#cczip").val( $("#zip").val() );
                    $("#cccountry").val( $("#country").val() );
                });
                
                $("#reason").change( function() {
                    if ( $(this).val() != 'dedication' ) {
                        $("#dedication").attr('disabled', true);
                    } else {
                        $("#dedication").attr('disabled', false);
                        $("#dedication").focus();
                    }
                });
                
                $("#submit").click( function() {
                    return false;
                	var error = '';
                	
                	$(".required").each(function() {
                		if ($(this).val() == "") {
                			var label = $(this).parent().find('label');
                			error += label.text() + ' is required.\n';
                		}
                	});
                	
                	$(".email").each(function() {
                		var reg = /\S+@\S+\.\S+/;
    					if (!reg.test($(this).val())) {
    						error += 'Email provided is invalid.\n';
    					}
                	});
                	
                	$(".number").each(function() {
                		var reg = /\d/;
    					if (!reg.test($(this).val())) {
                			var label = $(this).parent().find('label');
                			error += label.text() + ' must contain digits only.\n';
                		}
                	});
                	
                	if (error != '') {
                		alert(error);
                		return false;
                	}
                	                	
                    var amount;
                    if ( $("#donation").val() > 0 ) 
                        amount = $("#donation").val();
                    else 
                        amount = $("#amount").val();
                    if ( amount > 0 ) { 
                        var address = $("#ccaddress").val();
                        if ( $("#ccaddress2").val() != "" ) 
                            address += ("\n" + $("#ccaddress2").val());
                        
                        $.post('authorize_net.php', {
                            ccnum: $("#ccnum").val(), 
                            ccexp: $("#ccexp").val(), 
                            ccamount: amount, 
                            desc: 'Donation Form', 
                            fname: $("#ccfname").val(), 
                            lname: $("#cclname").val(), 
                            address: address, 
                            city: $("#cccity").val(), 
                            state: $("#ccstate").val(), 
                            zip: $("#cczip").val(), 
                            country: $("#cccountry").val(), 
                            email: $("#email").val(), 
                            phone: $("#phone").val(), 
                            reason: $("#reason").val(), 
                            dedication: $("#dedication").val(), 
                            family: $("#family").val()
                        }, function( data ) {
                        	data = data.split(":");
                            if ( data[0] == 1 ) { 
                                $("#donationForm").find("fieldset:not('#result')").hide();
                                $("#donAmount").html( amount );
                                $("#result").show();
                            } else { 
                                $("#authorize").html( data );
                            }
                            $("html, body").animate({ scrollTop: 0 }, 0);
                        });
                            
                    } else {
                        alert( "You have not indicated how much you would like to donate!" );
                    }
                    
                    return false;
                });
                 
                
                $("#reset").click( function() {
                    window.location = "https://www.mashpia.com/donate.php";
                });               
            });
            
        </script>
    </head>
    
    <body>
        <h3 align="center">Chayolei Tzivos Hashem Donation</h3>
        
        <div align="center">
            <form action="https://www.mashpia.com/donate.php" method="post" id="donationForm">
                <fieldset id="result">
                    <legend>
                        Thank You
                    </legend>
                    <p>
                        Your donation of $<span id="donAmount"></span> has been recieved.<br />
                        You should receive a confirmation email shortly.<br />
                        Thank You!
                    </p>
                </fieldset>
                <div id="authorize"></div>
                <fieldset>
                    <legend>
                        Personal Info
                    </legend>
                    <div>
                        <label for="fname">First Name</label>
                        <input type="text" name="fname" id="fname" class="required" /> <span class="em">*</span>
                    </div>
                    <div>
                        <label for="lname">Last Name</label>
                        <input type="text" name="lname" id="lname" class="required" /> <span class="em">*</span>
                    </div>
                    <div>
                        <label for="address">Address</label>
                        <input type="text" name="address" id="address" />
                    </div>
                    <div>
                        <label for="address2">&nbsp;</label>
                        <input type="text" name="address2" id="address2" />
                    </div>
                    <div>
                        <label for="city">City</label>
                        <input type="text" name="city" id="city" />
                    </div>
                    <div>
                        <label for="state">State</label>
                        <input type="text" name="state" id="state" />
                    </div>
                    <div>
                        <label for="zip">Zip</label>
                        <input type="text" name="zip" id="zip" />
                    </div>
                    <div>
                        <label for="fname">Country</label>
                        <input type="text" name="country" id="country" />
                    </div>
                    <div>
                        <label for="email">Email</label>
                        <input type="text" name="email" id="email" class="required email" /> <span class="em">*</span>
                    </div>
                    <div>
                        <label for="phone">Phone Number</label>
                        <input type="text" name="phone" id="phone" />
                    </div>                      
                </fieldset>
                <fieldset>
                    <legend>
                        Donation
                    </legend>
                    <div>
                        <label for="donation">Amount</label>
                        <select name="donation" id="donation" class="required">
                            <option value="">choose</option>
                            <option value="18">$18</option>
                            <option value="36">$36</option>
                            <option value="50">$50</option>
                            <option value="100">$100</option>
                            <option value="180">$180</option>
                            <option value="200">$200</option>
                            <option value="250">$250</option>
                            <option value="360">$360</option>
                            <option value="500">$500</option>
                            <option value="540">$540</option>
                            <option value="1000">$1000</option>
                            <option value="1800">$1800</option>
                            <option value="2000">$2000</option>
                            <option value="3600">$3600</option>
                            <option value="5400">$5400</option>
                            <option value="10000">$10,000</option>
                            <option value="0">other</option>
                        </select> <span class="em">*</span>
                    </div>
                    <div>
                        <label for="amount">Other Amount</label>
                        $<input type="text" name="amount" id="amount" />
                    </div>
                    <div>
                        <label for="reason">Towards</label>
                        <select name="reason" id="reason" class="required">
                            <option value="dedication">Dedication</option>
                            <option value="nosson">Nosson's Tanya</option>
                            <option value="tanya">Tanya Baal Peh</option>
                            <option value="tehillim">Shabbos Mevorchim Tehillim</option>
                            <option value="prize_sponsor">Prize Sponsor</option>
                            <option value="shnas_hamosayim">Shnas Hamosayim</option>
                            <option value="weekly_hachayol">Weekly Hachayol</option>
                            <option value="monthly_hachayol">Monthly Hachayol</option>
                            <option value="rally">Rally Sponsor</option>
                            <option value="chidon">Chidon Sefer Hamitzvos</option> 
                    </select>
                    </div>
                    <div>
                        <label for="dedication">Dedication</label>
                        <textarea name="dedication" id="dedication"></textarea>
                    </div> 
                    <div>
                        <label for="family">From Family of</label>
                        <select name="family" id="family">
                            <option value="none">choose</option>
                            <option value="deitsch">The Deitsch Family</option>
                            <option value="rader">The Rader Family</option>
                            <option value="zirkind">The Zirkind Family</option>
                            <option value="kratz">The Kratz Family</option>
                            <option value="shimmy">Shimmy's Friends</option>
                        </select>
                    </div>                   
                </fieldset>
                <fieldset>
                    <legend>
                        Credit Card
                    </legend>
                    <p>Click <span id="copy">here</span> to copy the information from your Personal Info.</p>
                    <div>
                        <label for="ccfname">First Name on Card</label>
                        <input type="text" name="ccfname" id="ccfname" class="required" /> <span class="em">*</span>
                    </div>
                    <div>
                        <label for="cclname">Last Name on Card</label>
                        <input type="text" name="cclname" id="cclname" class="required" /> <span class="em">*</span>
                    </div>
                    <div>
                        <label for="ccaddress">Billing Address</label>
                        <input type="text" name="ccaddress" id="ccaddress" />
                    </div>
                    <div>
                        <label for="ccaddress2">&nbsp;</label>
                        <input type="text" name="ccaddress2" id="ccaddress2" />
                    </div>
                    <div>
                        <label for="cccity">City</label>
                        <input type="text" name="cccity" id="cccity" />
                    </div>
                    <div>
                        <label for="ccstate">State</label>
                        <input type="text" name="ccstate" id="ccstate" />
                    </div>
                    <div>
                        <label for="cczip">Zip</label>
                        <input type="text" name="cczip" id="cczip" />
                    </div>
                    <div>
                        <label for="cccountry">Country</label>
                        <input type="text" name="cccountry" id="cccountry" />
                    </div>
                    <div>
                        <label for="ccnum">Credit Card Number</label>
                        <input type="text" name="ccnum" id="ccnum" class="required number" /> <span class="em">*</span>
                    </div>
                    <div>
                        <label for="ccexp">Credit Card Expiry</label> 
                        <input type="text" name="ccexp" id="ccexp" class="required number" /> <span class="em">*</span> (MMYY)
                    </div>
                    <div>
                        <label for="cccvv">CVV Code</label>
                        <input type="text" name="cccvv" id="cccvv" class="required number" /> <span class="em">*</span>
                    </div>                        
                </fieldset>
                <fieldset>
                    <div align="center">
                        <input type="reset" name="reset" value="Reset" id="reset" />
                        <input type="submit" name="submit" value="Submit" id="submit" />
                    </div>
                </fieldset>
            </form>
        </div>
    </body>
</html>