<!doctype html>
<html>
<head>
    <title>Tzivos Hashem | Authorize.net Profile Demo</title>
<!--    Bootstrap 4 css-->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css" integrity="sha384-/Y6pD6FV/Vv2HJnA6t+vslU6fwYXjCFtcEpHbNJ0lyAFsXTsjBbfaDjzALeQsN6M" crossorigin="anonymous">
<!-- Our CSS-->
    <link href="style.css" rel="stylesheet" />
<!--    Bootstrap 4 JS -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js" integrity="sha384-b/U6ypiBEHpOf/4+1nzFpr53nxSS+GLCkfwBdFNTxtclqqenISfwAzpKaMNFNmj4" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/js/bootstrap.min.js" integrity="sha384-h0AbiXch4ZDo7tp9hKZ4TsHbi047NrKGLO3SEJAg45jXxnGIfYzk4Si90RDIqNm1" crossorigin="anonymous"></script>
<!--    Our JS -->
    <script src="script.js"></script>
</head>
<body>
    <div class="container">
        <h1>Authorize.net Customer Profile Demo</h1>
        <hr />
        <h2>1. Create Customer Profile</h2>
        <div class="row">
            <div class="col-md-6">
                <form method="post" id="createProfileId" action="ajax/createCustomerProfile.php">
                    <input type="hidden" name="command" value="createCustomerProfile" />
                    <div class="form-group">
                        <label for="merchantCustomerId">Merchant ID</label>
                        <input type="text" class="form-control" name="merchantCustomerId" placeholder="TH_0067"/>
                    </div>
                    <div class="form-group">
                        <label for="description">Description</label>
                        <input type="text" class="form-control" name="description" placeholder="Tzivos Hashem Headquarters"/>
                    </div>
                    <div class="form-group">
                        <label for="email">Email address</label>
                        <input type="text" class="form-control" name="email" placeholder="test@test.com"/>
                    </div>
                    <h4>Initial Payment Profile (Required)</h4>
                    <!--<div class="form-group">-->
                    <!--    <label for="customerType">Customer Type</label>-->
                    <!--    <select class="form-control" name="customerType" placeholder="Customer Type">-->
                    <!--        <option value="individual">Individual</option>-->
                    <!--        <option value="business">Business</option>-->
                    <!--    </select>-->
                    <!--</div>-->
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label for="cardNumber">Card Number</label>
                            <input type="text" class="form-control" name="cardNumber" placeholder="XXXXXXXXXXXXXXXX"/>
                        </div>
                        <div class="form-group col-md-4 col-sm-6">
                            <label for="expirationDate">Exparation Date</label>
                            <input type="text" class="form-control" name="expirationDate" placeholder="YYYY-MM"/>
                        </div>
                        <div class="form-group col-md-2 col-sm-6">
                            <label for="cardCode">Code</label>
                            <input type="text" class="form-control" name="cardCode" placeholder="XXX"/>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </form>
            </div>
            <div class="col-md-6">
                <h3>Result: </h3>
                <pre id="createProfileIdResult">No Result</pre>
            </div>
        </div>
        <div class="mt-4"></div>
        <h2>2. Get Customer Profile</h2>
        <div class="row">
            <div class="col-md-6">
                <form method="post" id="getProfileId" action="ajax/getCustomerProfile.php">
                    <input type="hidden" name="command" value="getCustomerProfile" />
                    <div class="form-group">
                        <label for="profileId">Profile ID</label>
                        <input type="text" class="form-control" name="profileId" placeholder="Sample Profile ID"/>
                    </div>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </form>
            </div>
            <div class="col-md-6">
                <h3>Result: </h3>
                <pre id="getProfileIdResult">No Result</pre>
            </div>
        </div>
        <div class="mt-4"></div>
        <h2>3. Update Customer Profile</h2>
        <div class="row">
            <div class="col-md-6">
                <form method="post" id="editProfileId" action="ajax/editCustomerProfile.php">
                    <input type="hidden" name="command" value="editCustomerProfile" />
                    <div class="form-group">
                        <label for="profileId">Profile ID</label>
                        <input type="text" class="form-control" name="profileId" placeholder="Sample Profile ID"/>
                    </div>
                    <div class="form-group">
                        <label for="merchantCustomerId">Merchant ID</label>
                        <input type="text" class="form-control" name="merchantCustomerId" placeholder="TH_0067"/>
                    </div>
                    <div class="form-group">
                        <label for="description">Description</label>
                        <input type="text" class="form-control" name="description" placeholder="Tzivos Hashem Headquarters"/>
                    </div>
                    <div class="form-group">
                        <label for="email">Email address</label>
                        <input type="text" class="form-control" name="email" placeholder="bugs@tzivoshashem.org"/>
                    </div>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </form>
            </div>
            <div class="col-md-6">
                <h3>Result: </h3>
                <pre id="editProfileIdResult">No Result</pre>
            </div>
        </div>
        <div class="mt-4"></div>
        
        <h2>4. Charge Customer Profile</h2>
        <div class="row">
            <div class="col-md-6">
                <form method="post" id="chargeProfileId" action="ajax/chargeCustomerProfile.php">
                    <input type="hidden" name="command" value="chargeCustomerProfile" />
                    <div class="form-group">
                        <label for="profileId">Profile ID</label>
                        <input type="text" class="form-control" name="profileId" placeholder="Sample Profile ID*"/>
                    </div>
                    <div class="form-group">
                        <label for="merchantCustomerId">Payment Profile ID</label>
                        <input type="text" class="form-control" name="paymentProfileId" placeholder="Sample Payment Profile ID"/>
                    </div>
                    <div class="form-group">
                        <label for="merchantCustomerId">Amount*</label>
                        <input type="text" class="form-control" name="amount" placeholder="87.63"/>
                    </div>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </form>
            </div>
            <div class="col-md-6">
                <h3>Result: </h3>
                <pre id="chargeProfileIdResult">No Result</pre>
            </div>
        </div>
        <div class="mt-4"></div>
        
        <h2>5. Create Payment Profile</h2>
        <div class="row">
            <div class="col-md-6">
                <form method="post" id="createPaymentId" action="ajax/createPaymentProfile.php">
                    <input type="hidden" name="command" value="createPaymentProfile" />
                    <div class="form-group">
                        <label for="profileId">Profile ID</label>
                        <input type="text" class="form-control" name="profileId" placeholder="Sample Profile ID*"/>
                    </div>
<!--                    <h3>Bill To:</h3>
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label for="firstName">First</label>
                            <input type="text" class="form-control" name="firstName" placeholder="Chaim"/>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="lastName">Last</label>
                            <input type="text" class="form-control" name="lastName" placeholder="Berliner"/>
                        </div>
                        <div class="form-group col-md-12">
                            <label for="address">address</label>
                            <input type="text" class="form-control" name="address" placeholder="770 Eastern Parkway"/>
                        </div>
                        <div class="form-group col-md-5">
                            <label for="city">City</label>
                            <input type="text" class="form-control" name="city" placeholder="Brooklyn"/>
                        </div>
                        <div class="form-group col-md-4">
                            <label for="state">State</label>
                            <input type="text" class="form-control" name="state" placeholder="NY"/>
                        </div>
                        <div class="form-group col-md-3">
                            <label for="zip">Zip</label>
                            <input type="text" class="form-control" name="zip" placeholder="11213"/>
                        </div>
                    </div>-->
<!--                    <h3>CC info:</h3>-->
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label for="cardNumber">Card Number</label>
                            <input type="text" class="form-control" name="cardNumber" placeholder="XXXXXXXXXXXXXXXX"/>
                        </div>
                        <div class="form-group col-md-4 col-sm-6">
                            <label for="expirationDate">Exparation Date</label>
                            <input type="text" class="form-control" name="expirationDate" placeholder="YYYY-MM"/>
                        </div>
                        <div class="form-group col-md-2 col-sm-5">
                            <label for="cardCode">Code</label>
                            <input type="text" class="form-control" name="cardCode" placeholder="XXX"/>
                        </div>
                        <div class="form-check">
                            <label class="form-check-label" >
                                <input type="checkbox" class="form-check-input" name="default"/> Default?
                            </label>  
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </form>
            </div>
            <div class="col-md-6">
                <h3>Result: </h3>
                <pre id="createPaymentIdResult">No Result</pre>
            </div>
        </div>
        <div class="mt-4"></div>
        
        <h2>6. Edit Payment Profile</h2>
        <div class="row">
            <div class="col-md-6">
                <form method="post" id="editPaymentId" action="ajax/editPaymentProfile.php">
                    <input type="hidden" name="command" value="editPaymentProfile" />
                    <div class="form-group">
                        <label for="profileId">Profile ID</label>
                        <input type="text" class="form-control" name="profileId" placeholder="Sample Profile ID*"/>
                    </div>
                    <div class="form-group">
                        <label for="merchantCustomerId">Payment Profile ID</label>
                        <input type="text" class="form-control" name="paymentProfileId" placeholder="Sample Payment Profile ID"/>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label for="cardNumber">Card Number</label>
                            <input type="text" class="form-control" name="cardNumber" placeholder="XXXXXXXXXXXXXXXX"/>
                        </div>
                        <div class="form-group col-md-4 col-sm-6">
                            <label for="expirationDate">Exparation Date</label>
                            <input type="text" class="form-control" name="expirationDate" placeholder="YYYY-MM"/>
                        </div>
                        <div class="form-group col-md-2 col-sm-5">
                            <label for="cardCode">Code</label>
                            <input type="text" class="form-control" name="cardCode" placeholder="XXX"/>
                        </div>
                        <div class="form-check">
                            <label class="form-check-label" >
                                <input type="checkbox" class="form-check-input" name="default"/> Default?
                            </label>  
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </form>
            </div>
            <div class="col-md-6">
                <h3>Result: </h3>
                <pre id="editPaymentIdResult">No Result</pre>
            </div>
        </div>
        <div class="mt-4"></div>
    </div> 
</body>
</html>