<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta name="viewport" content="width=device-width">
    <meta charset="UTF-8">
    <meta name="google" content="notranslate">
    <title>Chidon Enrollment | Chidon Drive</title>
    <meta name=”description” content="Every Child, Every Mitzvah: Making it Happen">
    <link rel="icon" type="image/png" href="img/favicon.png" />
    <link rel="stylesheet" href="css/font.css" type="text/css" />
    <link rel="stylesheet" href="css/style.css" type="text/css" />
    <link rel="stylesheet" href="css/nice-select.css" type="text/css" />
    <link rel="stylesheet" href="css/enroll.css" type="text/css" />
    <style>
        #loginForm input:not(.button) {
            margin: 10px 0;
        }
        .login form {
            padding-bottom: 0;
        }
        .login input.button {
            top: 10px;
        }

        .floating-div {
            background: rgba(6, 9, 47, .8);
            border-radius: .5rem;
            width: 150px;
            padding: 2rem;
            margin: auto;
            z-index: 100;
            position: fixed;
            bottom: 0;
            right: 0;
        }
        .floating-div p {
            color: #85abda;
            font-size: 1.4rem;
            font-weight: bold;
            text-transform: uppercase;
            font-family: 'Gotham Narrow', sans-serif;
        }

        .listSection {
            padding-bottom: 5px;
        }

        .prizes {
            padding: 10px;
            /*display: none;*/
        }

        .flex-kids {
            display: flex;
            align-items: flex-start;
        }

        /*.form input[type='checkbox'], .form input[type='radio'] {*/
        /*	margin-top: -15px;*/
        /*}*/
        .bar {
            position: relative;
            padding-top: 1.1rem;
            margin-top: 2rem;
            height: 3rem;
        }

        .wrapper {
            font-family: 'Gotham Narrow', sans-serif;
        }

        .wrapper .form {
            box-sizing: initial !important;
        }

        .form h5 {
            font-family: 'bould', sans-serif;
            font-weight: normal;
        }

        .form .nice-select .option, .form .nice-select .option .selected, .form .nice-select .option .selected .focus {
            padding-top: 10px !important;
            text-align: center !important;
        }

        .form .nice-select .option, .form .nice-select .option:hover, .form .nice-select .option.focus, .form .nice-select .option.selected.focus {
            padding: 0;
        }

        .childImg {
            border-radius: 15px;
            width: 90px;
            margin-top: 15px;
        }

        h5 .title {
            font-size: 1.6rem;
            text-transform: uppercase;
            margin-top: 1rem;
            font-family: 'Gotham Narrow', sans-serif;
            font-weight: bold;
        }

        .form h5 span.track {
            font-family: 'Tahu', sans-serif;
            font-weight: normal;
            color: #e3b949;
        }

        form h5.formDetails {
            font-size: 1.3rem;
            line-height: 1.5;
        }

        .form form .checkboxLabel .xtra {
            font-family: 'bould', sans-serif !important;
            font-weight: normal !important;
        }

        .form form .flex > label {
            font-size: 1.3rem;
            font-weight: normal;
        }

        .field {
            margin-top: 0 !important;
        }

        #paymentDiv {
            padding-top: 20px;
            text-align: center;
        }

        #payment {
            color: #000;
            text-decoration: none;
            text-align: center;
        }

        .addressInfo {
            margin-bottom: 1rem;
        }
    </style>
</head>

<body>

<header>
    <div class="wrapper">
        <div class="left">
            <a href="#" id="menu-opener"><span></span><span></span><span></span></a>
            <a href="index.html" class="logo">
                <h1>Chidon</h1><img src="img/chidon.png?v=1" alt="Chidon" />
            </a>
            <nav>
                <a href="index.html">Home</a>
                <a href="donate.html">Donate</a>
                <a href="leaderboard.html">Leaderboard</a>
                <a href="story.html">Our Story</a>
            </nav>
        </div>
        <div class="icons">
            <a href="#"><img src="img/merkos.png" alt="Merkas Linyonei Chinuch" /></a>
            <a href="#"><img src="img/army.png" alt="Tzivos Hashem" /></a>
        </div>
    </div>
</header>
<div class="main">
    <div class="wrapper">
        <h2 class="title">Chidon Enrollment</h2>
        <!--		<div class="form login" style="display: none;">-->
        <div class="form login">
            <form id="loginForm">
                <p>Please log in to your Mashpia.com account to proceed with Chidon enrollment*</p>
                <div class="field">
                    <label>Username: <input type="text" id="username" name="username" required /></label>
                </div>
                <div class="field">
                    <label>Password: <input type="password" id="password" name="password" required /></label>
                </div>
                <h5 class="field formDetails">
                    *<i>If you don't remember your username or password, please go to mashpia.com/mobile
                        and click on the "forgot user/password" link</i>
                </h5>
                <input type="submit" class="button" value="LOGIN" />
            </form>
        </div>
<!--        <div class="form kids-wrapper blue-circle" style="display: none;"></div>-->

<!--        <div class="floating-div">-->
<!--            <p>Total: $<span id="total">0</span></p>-->
<!--        </div>-->
        <div class="form" id="regForm" style="display: none">
            <form class="personalInfo">
                <div class="children">

                </div>
            </form>
        </div>

        <!-- FORM PART 2 -->
        <div class="form form2" id="purchasesForm" style="display: none">
            <form id="formPart2">
                <h5 class="title" style="font-weight: bold">Extra Purchases</h5>
                <h5 class="formDetails">
                    You can use this form to purchase Celebration Boxes as well as Sweaters (for Tatty / Mommy / Bubby / Zaidy).
                    Please Note: This year, there are no celebration boxes being AUTOMATICALLY SENT. You need to EXPLICITLY
                    purchase them.
                </h5>
                <div class="flex small-select">
                    <label class="checkboxLabel xtra">
                        Please select how many Celebration Box(es) you would like to purchase.
                    </label>
                    <select class="numCelebBoxes">
                        <?php
                        for ($i = 0; $i <= 10; $i++) {
                            echo "<option value='$i'>$i</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="listSection" style="background-color: #394190">
                    <img src="../assets/Chidon 5781 Box.png" style="float: right; width: 200px;" />
                    <ul>
                        <li>10 Chidon Plates</li>
                        <li>10 Chidon Cups</li>
                        <li>10 Chidon Napkins</li>
                        <li>7 Chidon Latex Balloons (with stand)</li>
                        <li>1 Chidon Foil Balloon</li>
                        <li>1 Chidon Tablecloth</li>
                        <li>2 Laminated Benching Cards</li>
                    </ul>
                </div>

                <h5 class="title" style="font-weight: bold; margin-bottom: 1rem;">Parents and Grandparents Sweaters</h5>
                <div class="flex">
                    <h5 class="formDetails">Mother Sweater - $25/ea</h5>
                    <div>
                        <select name="motherSweater" class="sweater">
                            <?php
                            for ($i = 0; $i <= 10; $i++) {
                                echo "<option value='$i'>$i</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="addressInfo"></div>

                <div class="flex">
                    <h5 class="formDetails">Father Sweater - $25/ea</h5>
                    <div>
                        <select name="fatherSweater" class="sweater">
                            <?php
                            for ($i = 0; $i <= 10; $i++) {
                                echo "<option value='$i'>$i</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="addressInfo"></div>
                <div class="flex">
                    <h5 class="formDetails">Bubby Sweater - $25/ea</h5>
                    <div>
                        <select name="bubbySweater" class="sweater">
                            <?php
                            for ($i = 0; $i <= 10; $i++) {
                                echo "<option value='$i'>$i</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="addressInfo"></div>

                <div class="flex">
                    <h5 class="formDetails">Zaidy Sweater - $25/ea</h5>
                    <div>
                        <select name="zaidySweater" class="sweater">
                            <?php
                            for ($i = 0; $i <= 10; $i++) {
                                echo "<option value='$i'>$i</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="addressInfo"></div>

<!--                <div class="flex">-->
<!--                    <input class="inputCheckbox" type="checkbox" id="sweaterForMother" name="sweaterForFamily">-->
<!--                    <label class="checkboxLabel" for="sweaterForMother">-->
<!--                        $25 Mother-->
<!--                    </label>-->
<!--                </div>-->
<!--                <div id="sweaterForMotherDetails" style="display: none;">-->
<!--                    <div class="field flex medium-select">-->
<!--                        <label>Select Size</label>-->
<!--                        <select id="sweaterForMotherSize" name="pay">-->
<!--                            							<option value='xs'>Adult XS (50 left)</option>-->
<!--                            							<option value='small'>Adult Small (50 left)</option>-->
<!--                            							<option value='medium'>Adult Medium (100 left)</option>-->
<!--                            							<option value='large'>Adult Large (50 Left)</option>-->
<!--                            							<option value='xl'>Adult XL large (50 Left)</option>-->
<!--                        </select>-->
<!--                    </div>-->
<!--                    <div class="listSection">-->
<!--                        <div class="flex">-->
<!--                            <input class="inputCheckbox" type="radio" id="motherSweaterShippedToSchool"-->
<!--                                   name="motherSweaterShippingLocation">-->
<!--                            <label class="checkboxLabel" for="motherSweaterShippedToSchool">-->
<!--                                to be shipped to my school.-->
<!--                            </label>-->
<!--                        </div>-->
<!--                        <div class="flex">-->
<!--                            <input class="inputCheckbox" type="radio" id="motherSweaterShippedToAddress"-->
<!--                                   name="motherSweaterShippingLocation">-->
<!--                            <label class="checkboxLabel" for="motherSweaterShippedToAddress">-->
<!--                                +$10 to be shipped to an address in the USA-->
<!--                            </label>-->
<!--                        </div>-->
<!--                        <input id="motherSweaterShippedToAddressInput" type="text" placeholder="Address">-->
<!--                    </div>-->
<!--                </div>-->
<!---->
<!--                <div class="flex">-->
<!--                    <input class="inputCheckbox" type="checkbox" id="sweaterForFather" name="sweaterForFamily">-->
<!--                    <label class="checkboxLabel" for="sweaterForFather">-->
<!--                        $25 Father-->
<!--                    </label>-->
<!--                </div>-->
<!--                <div id="sweaterForFatherDetails" style="display: none;">-->
<!--                    <div class="field flex medium-select">-->
<!--                        <label>Select Size</label>-->
<!--                        <select id="sweaterForFatherSize" name="pay">-->
<!--                            							<option value='xs'>Adult XS (50 left)</option>-->
<!--                            							<option value='small'>Adult Small (50 left)</option>-->
<!--                            							<option value='medium'>Adult Medium (100 left)</option>-->
<!--                            							<option value='large'>Adult Large (50 Left)</option>-->
<!--                            							<option value='xl'>Adult XL large (50 Left)</option>-->
<!--                        </select>-->
<!--                    </div>-->
<!--                    <div class="listSection">-->
<!--                        <div class="flex">-->
<!--                            <input class="inputCheckbox" type="radio" id="fatherSweaterShippedToSchool"-->
<!--                                   name="fatherSweaterShippingLocation">-->
<!--                            <label class="checkboxLabel" for="fatherSweaterShippedToSchool">-->
<!--                                to be shipped to my school.-->
<!--                            </label>-->
<!--                        </div>-->
<!--                        <div class="flex">-->
<!--                            <input class="inputCheckbox" type="radio" id="fatherSweaterShippedToAddress"-->
<!--                                   name="fatherSweaterShippingLocation">-->
<!--                            <label class="checkboxLabel" for="fatherSweaterShippedToAddress">-->
<!--                                +$10 to be shipped to an address in the USA-->
<!--                            </label>-->
<!--                        </div>-->
<!--                        <input id="fatherSweaterShippedToAddressInput" type="text" placeholder="Address">-->
<!--                    </div>-->
<!--                </div>-->
<!---->
<!--                <div class="flex">-->
<!--                    <input class="inputCheckbox" type="checkbox" id="sweaterForBubby" name="sweaterForFamily">-->
<!--                    <label class="checkboxLabel" for="sweaterForBubby">-->
<!--                        $25 Bubby-->
<!--                    </label>-->
<!--                </div>-->
<!--                <div id="sweaterForBubbyDetails" style="display: none;">-->
<!--                    <div class="field flex medium-select">-->
<!--                        <label>Select Size</label>-->
<!--                        <select id="sweaterForBubbySize" name="pay">-->
<!--                            							<option value='xs'>Adult XS (50 left)</option>-->
<!--                            							<option value='small'>Adult Small (50 left)</option>-->
<!--                            							<option value='medium'>Adult Medium (100 left)</option>-->
<!--                            							<option value='large'>Adult Large (50 Left)</option>-->
<!--                            							<option value='xl'>Adult XL large (50 Left)</option>-->
<!--                        </select>-->
<!--                    </div>-->
<!--                    <div class="listSection">-->
<!--                        <div class="flex">-->
<!--                            <input class="inputCheckbox" type="radio" id="bubbySweaterShippedToSchool"-->
<!--                                   name="bubbySweaterShippingLocation">-->
<!--                            <label class="checkboxLabel" for="bubbySweaterShippedToSchool">-->
<!--                                to be shipped to my school.-->
<!--                            </label>-->
<!--                        </div>-->
<!--                        <div class="flex">-->
<!--                            <input class="inputCheckbox" type="radio" id="bubbySweaterShippedToAddress"-->
<!--                                   name="bubbySweaterShippingLocation">-->
<!--                            <label class="checkboxLabel" for="bubbySweaterShippedToAddress">-->
<!--                                +$10 to be shipped to an address in the USA-->
<!--                            </label>-->
<!--                        </div>-->
<!--                        <input id="bubbySweaterShippedToAddressInput" type="text" placeholder="Address">-->
<!--                    </div>-->
<!--                </div>-->
<!---->
<!--                <div class="flex">-->
<!--                    <input class="inputCheckbox" type="checkbox" id="sweaterForZaidy" name="sweaterForFamily">-->
<!--                    <label class="checkboxLabel" for="sweaterForZaidy">-->
<!--                        $25 Zaidy-->
<!--                    </label>-->
<!--                </div>-->
<!--                <div id="sweaterForZaidyDetails" style="display: none;">-->
<!--                    <div class="field flex medium-select">-->
<!--                        <label>Select Size</label>-->
<!--                        <select id="sweaterForZaidySize" name="pay">-->
<!--                            							<option value='xs'>Adult XS (50 left)</option>-->
<!--                            							<option value='small'>Adult Small (50 left)</option>-->
<!--                            							<option value='medium'>Adult Medium (100 left)</option>-->
<!--                            							<option value='large'>Adult Large (50 Left)</option>-->
<!--                            							<option value='xl'>Adult XL large (50 Left)</option>-->
<!--                        </select>-->
<!--                    </div>-->
<!--                    <div class="listSection">-->
<!--                        <div class="flex">-->
<!--                            <input class="inputCheckbox" type="radio" id="zaidySweaterShippedToSchool"-->
<!--                                   name="zaidySweaterShippingLocation">-->
<!--                            <label class="checkboxLabel" for="zaidySweaterShippedToSchool">-->
<!--                                to be shipped to my school.-->
<!--                            </label>-->
<!--                        </div>-->
<!--                        <div class="flex">-->
<!--                            <input class="inputCheckbox" type="radio" id="zaidySweaterShippedToAddress"-->
<!--                                   name="zaidySweaterShippingLocation">-->
<!--                            <label class="checkboxLabel" for="zaidySweaterShippedToAddress">-->
<!--                                +$10 to be shipped to an address in the USA-->
<!--                            </label>-->
<!--                        </div>-->
<!--                        <input id="zaidySweaterShippedToAddressInput" type="text" placeholder="Address">-->
<!--                    </div>-->
<!--                </div>-->

                <h5 class="formDetails">
                    <b>Please note:</b> We are doing our best to ensure all items will be received at your school or at
                    the US addresses specified before the Chidon Event & Awards Ceremony. We do not take responsibility
                    in case of unforeseen circumstances.
                </h5>
<!--                <h5 class="formDetails"><b>Chidon Registration Payment:</b></h5>-->
<!--                				<h5 class="formDetails">-->-->
<!--                					<b>Please note:</b>-->-->
<!--                					In order to complete registration you need to choose one of the following 2 option.<br />-->-->
<!--                					IF YOU HAVE PREVIOUSLY PAID OR PUT A CARD ON HOLD, AFTER PRESSING PAY YOU WILL SEE A POPUP NOTIFYING YOU THAT YOUR PREVIOUS PAYMENT HAS BEEN APPLIED TO YOUR BALANCE.-->-->
<!--                				</h5>-->-->
<!--                <div class="listSection">-->
<!--                    <div class="flex">-->
<!--                        <input class="inputCheckbox" type="radio" id="chargeCard" name="chidonRegistrationPayment" checked>-->
<!--                        <label class="checkboxLabel" for="chargeCard">-->
<!--                            Please charge my card now.-->
<!--                        </label>-->
<!--                    </div>-->
<!--                    					<div class="flex">-->
<!--                    						<input class="inputCheckbox" type="radio" id="holdMoney" name="chidonRegistrationPayment">-->
<!--                    						<label class="checkboxLabel" for="holdMoney">-->-->
<!--                    							Please hold the money on my card until the Chidon Drive ends on Tuesday, 3 Nissan, March-->
<!--                    							16, 2021. If there is still any money that needs to be charged, please charge it then.-->
<!--                    						</label>-->
<!--                    					</div>-->
<!--                </div>-->
<!--                <div class="flexCenter">-->
<!--                    <a href="" class="button pay" style="text-align: center; margin-top: 20px;">Continue to Payment</a>-->
<!--                </div>-->
                <div id="paymentDiv">
                    <a href="#" class="button" id="payment">Continue to Payment</a>
                </div>
            </form>
        </div>

        <div class="payment-box" id="paymentInfo" style="display: none">
            <div class="form">
                <form id="paymentForm">
                    <div id="paymentFormDetails">
                        <div class="flex">
                            <input type="radio" class="inputCheckbox cc_on_file" name="cc_on_file" id="cc_on_file_yes" value="1" />
                            <label class="checkboxLabel" for="cc_on_file">Use Credit Card on file</label>
                        </div>
                        <div class="flex">
                            <input type="radio" class="inputCheckbox cc_on_file" name="cc_on_file" id="cc_on_file_no" value="0" />
                            <label class="checkboxLabel" for="cc_on_file">Don't use Credit Card on file</label>
                        </div>
                        <div class="field">
                            <label>Name on Credit Card:</label>
                            <input type="text" id="name" placeholder="Full Name" required />
                        </div>
                        <div class="field">
                            <label>Email <span>(for receipt)</span>:</label>
                            <input type="text" id="email" placeholder="Email" required />
                        </div>
                        <div class="field">
                            <label>Card Number:</label>
                            <input type="text" id="cc_num" placeholder="0000 0000 0000 0000" required />
                        </div>
                        <div class="group">
                            <div class="field">
                                <label>Exp. Month:</label>
                                <select id="exp_mm"><option>01</option><option>02</option><option>03</option><option>04</option><option>05</option><option>06</option><option>07</option><option>08</option><option>09</option><option>10</option><option>11</option><option>12</option></select>
                            </div>
                            <div class="field">
                                <label>Exp. Year:</label>
                                <select id="exp_yy">
                                    <option>2021</option><option>2022</option><option>2023</option><option>2024</option>
                                    <option>2025</option><option>2026</option><option>2027</option><option>2028</option>
                                </select>
                            </div>
                            <div class="field">
                                <label>CVV:</label>
                                <input type="text" id="cc_cvv" placeholder="CVV" required />
                            </div>
                        </div>
                        <h5>Billing Address</h5>
                        <div class="field">
                            <label>Country:</label>
                            <select id="bill_country">
                                <option>United States</option>
                                <option>Canada</option>
                                <option>England</option>
                                <option>Australia</option>
                                <option>S Africa</option>
                                <option>France</option>
                                <option>Europe</option>
                                <option>S America</option>
                            </select>
                        </div>
                        <div class="field">
                            <label>Address:</label>
                            <input type="text" id="bill_address" placeholder="Address" required />
                        </div>
                        <div class="field">
                            <label>Apt./Suite:</label>
                            <input type="text" id="bill_apt" placeholder="Apt./Suite (optional)" />
                        </div>
                        <div class="group">
                            <div class="field">
                                <label>Zip/Postcode:</label>
                                <input type="text" id="bill_zip" placeholder="Zip/Postcode" required />
                            </div>
                            <div class="field">
                                <label>City:</label>
                                <input type="text" id="bill_city" placeholder="City" required />
                            </div>
                            <div class="field">
                                <label>State/Prov/Region:</label>
                                <input type="text" id="bill_state" placeholder="State/Prov/Region" required />
                            </div>
                        </div>
                    </div>
                    <!--					<input type="submit" class="button processPayment" value="Pay" style="cursor: pointer" />-->
                </form>
            </div>
        </div>

        <!-- END OF FORMS -->

        <div align="center" style="padding-top: 20px;" id="logoffDiv" style="display: none;">
            <a href="#" class="button" id="logoff">Log Out</a>
        </div>
    </div>
</div>

<footer>
    <div class="wrapper">
        <a href="#" id="top">Back to top</a>

        <div class="credits">
            <p>Dedicated by George Rohr in loving memory of Mrs. Sarah (Charlotte) Rohr</p>
            <p>לע"נ הרב אליעזר בן הרב מרדכי ע"ה וונגר | לע"נ הרב יצחק בן הרב אליעזר צבי זאב ע"ה צירקינד
                לזכות הרב יוסף יצחק שליט"א ראזענבלום</p>
        </div>

        <div class="footer-main">
            <div class="footer-about">
                <div class="icon-wrapper">
                    <a href="https://thechidon.com" target="blank">
                        <img src="img/chidon-logo.png" alt="Chidon" />
                    </a>
                </div>
                <div class="icon-wrapper">
                    <img src="img/merkos-color.png" alt="Merkos Linyonei Chinuch" />
                </div>
                <div class="icon-wrapper">
                    <img src="img/army-color.png" alt="Tzivos Hashem" />
                </div>
                <p>A project of Merkos L'inyonei Chinuch and Tzivos Hashem</p>
            </div>
            <nav>
                <ul>
                    <li><a href="index.html">Home</a></li>
                    <li><a href="donate.html">Donate</a></li>
                    <li><a href="leaderboard.html">Leaderboard</a></li>
                    <li><a href="story.html">Our Story</a></li>
                </ul>
                <ul>
                    <li>
                        <h5>Quick Links</h5>
                    </li>
                    <li><a href="family.html">Find a Family</a></li>
                    <li><a href="sponsor.html">Sponsors</a></li>
                    <!-- <li><a href="enroll.html">Enrollment Form</a></li> -->
                </ul>
                <ul>
                    <li>
                        <h5>Contact Us</h5>
                    </li>
                    <li><a href="mailto:chidon@tzivoshashem.org">chidon@tzivoshashem.org</a></li>
                    <li><a href="tel:7189078884">718.907.8884</a></li>
                </ul>
            </nav>
        </div>

        <div class="footer-bottom">
            <div class="social">
                <a href="http://facebook.com"><span class="socicon socicon-facebook"></span></a>
                <a href="http://instragram.com"><span class="socicon socicon-instagram"></span></a>
            </div>
            <p>&copy; 2021 Tzivos Hashem - All Rights Reserved</p>
        </div>
    </div>
</footer>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="js/circletype.min.js"></script>
<script src="js/jquery.nice-select.min.js"></script>
<script src='js/circles.min.js'></script>
<script src="js/js.cookie.js"></script>
<script type="text/javascript">
    // alert("Enrollment is now closed.");
    // location.href = "/";
    if (location.protocol != 'https:') {
        let url = window.location.href;
        let pos = url.indexOf(':');
        let newUrl = 'https' + url.substring(pos);
        location.href = newUrl;
    }

    let selectedChildId
    let cart = {}
    let cart_total = 0

    let url = window.location.href;
    let pos = url.indexOf('?a=');
    if (pos > 0) {
        let admin = url.substring(pos + 3);
        Cookies.set('chidon_admin', admin);
    }

    if (Cookies.get('chidon_admin')) {
        getChildren(Cookies.get('chidon_admin'));
        $("#logoffDiv").show();
        getSweaterInfo()
    }
    else {
        $(".login").show();
        $("#logoffDiv").hide();
    }

    $('#menu-opener').click(function (e) {
        $('header nav').toggleClass('open');
    });

    var children = []; // global variable for children info
    var children_ids = []; // global variable to keep track of order of children and ids
    var completed = [] // global variable for making sure all children's forms have been completed
    var numChildren; // global variable to track number of children
    let numCelebrationBoxes = 0;
    let prizes = {};
    let past_reg_info = {} // keep global var of past reg info bc of screwup

    function getPreviousPurchaseInfo() {
        // get whatever purchases were already done
        $.post('../ajax/getPastReg.php', { admin: Cookies.get('chidon_admin') }, function(result) {
            const res = JSON.parse(result)
            console.log(res)
            past_reg_info = res
            if (res.children) {
                for (let user_id in res.children) {
                    const child = res.children[user_id]
                    if (child.paid) {
                        const item = {
                            desc: 'reg',
                            amount: parseInt(child.paid)
                        }
                        addToCart(user_id, item)
                    }
                    if (parseInt(child.yarmulka)) {
                        const item = {
                            desc: 'yarmulka',
                            amount: 0,
                            size: child.yarmulka
                        }
                        addToCart(user_id, item)
                    }
                }

                const celeb_box = res.parent.celeb_box
                if (parseInt(celeb_box)) {
                    const item = {
                        desc: 'celeb_box',
                        amount: 0,
                        qty: celeb_box
                    }
                    addToCart(0, item)
                }

                const celeb_box_add = res.parent.celeb_box_add
                if (parseInt(celeb_box_add) == 1) {
                    const item = {
                        desc: 'celeb_box_add',
                        amount: 20
                    }
                    addToCart(0, item)
                }

                const celeb_box_add_ship = res.parent.celeb_box_add_ship
                if (parseInt(celeb_box_add_ship) == 1) {
                    const item = {
                        desc: 'celeb_box_add_ship',
                        amount: 10
                    }
                    addToCart(0, item)
                }

                const celeb_box_add_addr = res.parent.celeb_box_add_addr
                if (celeb_box_add_addr && celeb_box_add_addr.length) {
                    const item = {
                        desc: 'celeb_box_add_addr',
                        amount: 0,
                        address: celeb_box_add_addr
                    }
                    addToCart(0, item)
                }

                const sweater_mother = res.parent.sweater_mother
                if (sweater_mother) {
                    const item = {
                        desc: 'sweater_mother',
                        amount: 25,
                        size: sweater_mother
                    }
                    addToCart(0, item)
                }

                const sweater_mother_ship = res.parent.sweater_mother_ship
                if (parseInt(sweater_mother_ship) == 1) {
                    const item = {
                        desc: 'sweater_mother_ship',
                        amount: 10
                    }
                    addToCart(0, item)
                }

                const sweater_mother_ship_addr = res.parent.sweater_mother_ship_addr
                if (sweater_mother_ship_addr && sweater_mother_ship_addr.length) {
                    const item = {
                        desc: 'sweater_mother_ship_addr',
                        amount: 0,
                        address: sweater_mother_ship_addr
                    }
                    addToCart(0, item)
                }

                const sweater_father = res.parent.sweater_father
                if (sweater_father) {
                    const item = {
                        desc: 'sweater_father',
                        amount: 25,
                        size: sweater_father
                    }
                    addToCart(0, item)
                }

                const sweater_father_ship = res.parent.sweater_father_ship
                if (parseInt(sweater_father_ship) == 1) {
                    const item = {
                        desc: 'sweater_father_ship',
                        amount: 10
                    }
                    addToCart(0, item)
                }

                const sweater_father_ship_addr = res.parent.sweater_father_ship_addr
                if (sweater_father_ship_addr && sweater_father_ship_addr.length) {
                    const item = {
                        desc: 'sweater_father_ship_addr',
                        amount: 0,
                        address: sweater_father_ship_addr
                    }
                    addToCart(0, item)
                }

                const sweater_bubby = res.parent.sweater_bubby
                if (sweater_bubby) {
                    const item = {
                        desc: 'sweater_bubby',
                        amount: 25,
                        size: sweater_bubby
                    }
                    addToCart(0, item)
                }

                const sweater_bubby_ship = res.parent.sweater_bubby_ship
                if (parseInt(sweater_bubby_ship) == 1) {
                    const item = {
                        desc: 'sweater_bubby_ship',
                        amount: 10
                    }
                    addToCart(0, item)
                }

                const sweater_bubby_ship_addr = res.parent.sweater_bubby_ship_addr
                if (sweater_bubby_ship_addr && sweater_bubby_ship_addr.length) {
                    const item = {
                        desc: 'sweater_bubby_ship_addr',
                        amount: 0,
                        address: sweater_bubby_ship_addr
                    }
                    addToCart(0, item)
                }

                const sweater_zaidy = res.parent.sweater_zaidy
                if (sweater_zaidy) {
                    const item = {
                        desc: 'sweater_zaidy',
                        amount: 25,
                        size: sweater_zaidy
                    }
                    addToCart(0, item)
                }

                const sweater_zaidy_ship = res.parent.sweater_zaidy_ship
                if (parseInt(sweater_zaidy_ship) == 1) {
                    const item = {
                        desc: 'sweater_zaidy_ship',
                        amount: 10
                    }
                    addToCart(0, item)
                }

                const sweater_zaidy_ship_addr = res.parent.sweater_zaidy_ship_addr
                if (sweater_zaidy_ship_addr && sweater_zaidy_ship_addr.length) {
                    const item = {
                        desc: 'sweater_zaidy_ship_addr',
                        amount: 0,
                        address: sweater_zaidy_ship_addr
                    }
                    addToCart(0, item)
                }
            }
        })
    }

    function changeYarmulka(elem) {
        const item = {
            desc: 'yarmulka',
            amount: 0,
            size: $(elem).val()
        }
        editCart(selectedChildId, item)
    }

    function addToCart(id, item) {
        if (!cart[id]) {
            cart[id] = []
        }
        // if item doesn't already exist add to cart
        let add = true;
        for (let i of cart[id]) {
            if (i.desc == item.desc) {
                // if it's trip option update option value
                if (item.option) i.option = item.option
                add = false
                break
            }
        }
        if (add) {
            cart[id].push(item)
        }
        calculateTotal()
    }

    function subtractFromCart(id, item) {
        for (let c in cart[id]) {
            if (cart[id][c].desc == item.desc) {
                cart[id].splice(c, 1)
            }
        }
        calculateTotal()
    }

    function editCart(id, item) {
        for (i of cart[id]) {
            if (i.desc == item.desc) {
                if (item.address) {
                    i.value = item.address
                } else if (item.size) {
                    i.size = item.size
                } else if (item.qty) {
                    i.qty = item.qty
                }
            }
        }
    }

    function calculateTotal() {
        total = 0
        let hide = false
        for (let id in cart) {
            hide = true
            for (let item of cart[id]) {
                total += item.amount
            }
        }
        if (total < 0) total = 0
        // update total on page
        $("#total").text(total)
        cart_total = total
        updateReceiptDetails()

        $("#paymentFormDetails").show()
        if (total == 0 && hide) {
            $("#paymentFormDetails").hide()
            $("input.processPayment").val('Confirm Registration')
            $(window).scrollTop(600)
        }
    }

    function updateReceiptDetails() {
        const options = {
            celeb_box: 'Celebration Box(es)',
            celeb_box_add: 'Additional Celebration Box',
            celeb_box_add_ship: 'Ship Additional Celebration Box',
            celeb_box_add_addr: 'Additional Celebration Box Shipping Address',
            sweater_mother: 'Mother Sweater',
            sweater_mother_ship: 'Shipping for Mother Sweater',
            sweater_mother_ship_addr: 'Mother Sweater Shipping Address',
            sweater_father: 'Father Sweater',
            sweater_father_ship: 'Shipping for Father Sweater',
            sweater_father_ship_addr: 'Father Sweater Shipping Address',
            sweater_bubby: 'Bubby Sweater',
            sweater_bubby_ship: 'Shipping for Bubby Sweater',
            sweater_bubby_ship_addr: 'Shipping Address for Bubby Sweater',
            sweater_zaidy: 'Zaidy Sweater',
            sweater_zaidy_ship: 'Shipping for Zaidy Sweater',
            sweater_zaidy_ship_addr: 'Zaidy Sweater Shipping Address',
            past_payment: 'Previous Payment'
        }
        let html = '<ul>'
        for (id in cart) {
            let name = ''
            if (children[id]) {
                const child = children[id]
                name = child.first + ' ' + child.last
            }
            for (item of cart[id]) {
                if (item.desc.indexOf('addr') != -1) {
                    html += `<li>${options[item.desc]} - ${item.address}</li>`
                } else if (item.desc == 'reg') {
                    html += `<li>Registration for ${name} - $${item.amount}</li>`
                } else if (item.desc == 'yarmulka') {
                    html += `<li>Yarmulka for ${name}, Size: ${item.size} - $${item.amount}</li>`
                } else if (item.desc.includes('sweater') && !item.desc.includes('ship')) {
                    html += `<li>${options[item.desc]}, Size: ${item.size} - $${item.amount}</li>`
                } else {
                    html += `<li>${options[item.desc]} - $${item.amount}</li>`
                }
            }
        }
        html += '</ul>'
        html += `<h5 class="formDetails">Total Cost: $${cart_total}</h5>`
        $("#receiptDetails").empty()
        $("#receiptDetails").append(html)
    }

    $("#yarmulkaSizeB").change( function() {
        changeYarmulka(this)
    })

    $("#yarmulkaSizeC").change( function() {
        changeYarmulka(this)
    })

    $("#yarmulkaSizeD").change( function() {
        changeYarmulka(this)
    })

    $('.form2 input[type="checkbox"]#additionalCelebrationBox').click(function () {
        var additionalCelebrationBoxChecked = $(this)[0].checked;
        if (additionalCelebrationBoxChecked) {
            $('.form2 #additionalCelebrationBoxDetails').show();
            document.getElementById('shippedToSchool').checked = true
        } else {
            $('.form2 #additionalCelebrationBoxDetails').hide();
            // remove shipping from cart if exists
            if (document.getElementById('shippedToAddress').checked) {
                document.getElementById('shippedToAddress').checked = false
                const item = {
                    desc: 'celeb_box_add_ship',
                    amount: 10
                }
                subtractFromCart(0, item)
            }
        }
    });

    $('.form2 input[type="checkbox"]#sweaterForMother').click(function () {
        var sweaterForMotherChecked = $(this)[0].checked;
        if (sweaterForMotherChecked) {
            $('.form2 #sweaterForMotherDetails').show();
            document.getElementById('motherSweaterShippedToSchool').checked = true
        } else {
            $('.form2 #sweaterForMotherDetails').hide();
        }
    });

    $('.form2 input[type="checkbox"]#sweaterForFather').click(function () {
        var sweaterForFatherChecked = $(this)[0].checked;
        if (sweaterForFatherChecked) {
            $('.form2 #sweaterForFatherDetails').show();
            document.getElementById('fatherSweaterShippedToSchool').checked = true
        } else {
            $('.form2 #sweaterForFatherDetails').hide();
        }
    });

    $('.form2 input[type="checkbox"]#sweaterForBubby').click(function () {
        var sweaterForBubbyChecked = $(this)[0].checked;
        if (sweaterForBubbyChecked) {
            $('.form2 #sweaterForBubbyDetails').show();
            document.getElementById('bubbySweaterShippedToSchool').checked = true
        } else {
            $('.form2 #sweaterForBubbyDetails').hide();
        }
    });

    $('.form2 input[type="checkbox"]#sweaterForZaidy').click(function () {
        var sweaterForZaidyChecked = $(this)[0].checked;
        if (sweaterForZaidyChecked) {
            $('.form2 #sweaterForZaidyDetails').show();
            document.getElementById('zaidySweaterShippedToSchool').checked = true
        } else {
            $('.form2 #sweaterForZaidyDetails').hide();
        }
    });

    $("#paymentAmountA").change( function() {
        // find amount in cart and change it
        const amount = parseInt($(this).val())
        for (let c in cart[selectedChildId]) {
            if (cart[selectedChildId][c].desc == 'reg') {
                cart[selectedChildId][c].amount = amount
            }
        }
        calculateTotal()
    })

    $("#paymentAmountB").change( function() {
        // find amount in cart and change it
        const amount = parseInt($(this).val())
        for (let c in cart[selectedChildId]) {
            if (cart[selectedChildId][c].desc == 'reg') {
                cart[selectedChildId][c].amount = amount
            }
        }
        calculateTotal()
    })

    $("#celebrationBox").click( function() {
        const checked = document.getElementById('celebrationBox').checked
        const item = {
            desc: 'celeb_box' ,
            amount: 0,
            qty: $("#numberOfCelebrationBoxes").val()
        }
        if (checked) addToCart(0, item)
        else subtractFromCart(0, item)
    })

    $("#numberOfCelebrationBoxes").change( function() {
        const item = {
            desc: 'celeb_box' ,
            amount: 0,
            qty: $(this).val()
        }
        editCart(0, item)
    })

    $("#additionalCelebrationBox").click( function() {
        const checked = document.getElementById('additionalCelebrationBox').checked
        const item = {
            desc: 'celeb_box_add',
            amount: 20
        }
        if (checked) addToCart(0, item)
        else subtractFromCart(0, item)
        calculateTotal()
    })

    $("#shippedToSchool").click( function() {
        const checked = $(this).is(":checked")
        const item = {
            desc: 'celeb_box_add_ship',
            amount: 10
        }
        if (checked) subtractFromCart(0, item)
        calculateTotal()
    })

    $("#shippedToAddress").click( function() {
        const checked = $(this).is(":checked")
        const item = {
            desc: 'celeb_box_add_ship',
            amount: 10
        }
        if (checked) addToCart(0, item)
        calculateTotal()
    })

    $("#shippedToAddressInput").blur( function() {
        const item = {
            desc: 'celeb_box_add_addr',
            amount: 0,
            address: $(this).val()
        }
        addToCart(0, item)
        editCart(0, item)
        calculateTotal()
    })

    $("#sweaterForMother").click( function() {
        const checked = document.getElementById('sweaterForMother').checked
        const item = {
            desc: 'sweater_mother',
            amount: 25,
            size: $("#sweaterForMotherSize").val()
        }
        if (checked) {
            // make sure there's some stock left
            if (item.size != 0)
                addToCart(0, item)
            else {
                alert('There are no sweaters left in stock for this size, please choose a different size.')
                return false
            }
        }
        else {
            subtractFromCart(0, item)
            // check if shipping was added for this sweater and remove as well
            if ($("#motherSweaterShippedToAddress").is(":checked")) {
                const item2 = {
                    desc: 'sweater_mother_ship',
                    amount: 10
                }
                subtractFromCart(0, item2)
                document.getElementById('motherSweaterShippedToAddress').checked = false
            }
        }
        calculateTotal()
    })

    $("#sweaterForMotherSize").change( function() {
        // make sure there's some stock left
        if ($(this).val() == 0) {
            alert('There are no sweaters left in stock for this size, please choose a different size.')
            return false
        }
        // find item in cart and update
        for (item of cart[0]) {
            if (item.desc == 'sweater_mother') {
                item.size = $(this).val()
            }
        }
    })

    $("#motherSweaterShippedToSchool").click( function() {
        const checked = $(this).is(":checked")
        const item = {
            desc: 'sweater_mother_ship',
            amount: 10
        }
        if (checked) subtractFromCart(0, item)
        calculateTotal()
    })

    $("#motherSweaterShippedToAddress").click( function() {
        const checked = $(this).is(":checked")
        const item = {
            desc: 'sweater_mother_ship',
            amount: 10
        }
        if (checked) addToCart(0, item)
        calculateTotal()
    })

    $("#motherSweaterShippedToAddressInput").blur( function() {
        const item = {
            desc: 'sweater_mother_ship_addr',
            amount: 0,
            address: $(this).val()
        }
        addToCart(0, item)
        editCart(0, item)
        calculateTotal()
    })

    $("#sweaterForFather").click( function() {
        const checked = document.getElementById('sweaterForFather').checked
        const item = {
            desc: 'sweater_father',
            amount: 25,
            size: $("#sweaterForFatherSize").val()
        }
        if (checked) {
            if (item.size != 0)
                addToCart(0, item)
            else {
                alert('There are no sweaters left in stock for this size, please choose a different size.')
                return false
            }
        }
        else {
            subtractFromCart(0, item)
            // check if shipping was added for this sweater and remove as well
            if ($("#fatherSweaterShippedToAddress").is(":checked")) {
                const item2 = {
                    desc: 'sweater_father_ship',
                    amount: 10
                }
                subtractFromCart(0, item2)
                document.getElementById('fatherSweaterShippedToAddress').checked = false
            }
        }
        calculateTotal()
    })

    $("#sweaterForFatherSize").change( function() {
        // make sure there's stock left
        if ($(this).val() == 0) {
            alert('There are no sweaters left in stock for this size, please choose a different size.')
            return false
        }
        // find item in cart and update
        for (item of cart[0]) {
            if (item.desc == 'sweater_father') {
                item.size = $(this).val()
            }
        }
    })

    $("#fatherSweaterShippedToSchool").click( function() {
        const checked = $(this).is(":checked")
        const item = {
            desc: 'sweater_father_ship',
            amount: 10
        }
        if (checked) subtractFromCart(0, item)
        calculateTotal()
    })

    $("#fatherSweaterShippedToAddress").click( function() {
        const checked = $(this).is(":checked")
        const item = {
            desc: 'sweater_father_ship',
            amount: 10
        }
        if (checked) addToCart(0, item)
        calculateTotal()
    })

    $("#fatherSweaterShippedToAddressInput").blur( function() {
        const item = {
            desc: 'sweater_father_ship_addr',
            amount: 0,
            address: $(this).val()
        }
        addToCart(0, item)
        editCart(0, item)
        calculateTotal()
    })

    $("#sweaterForBubby").click( function() {
        const checked = document.getElementById('sweaterForBubby').checked
        const item = {
            desc: 'sweater_bubby',
            amount: 25,
            size: $("#sweaterForBubbySize").val()
        }
        if (checked) {
            if (item.size != 0)
                addToCart(0, item)
            else {
                alert('There are no sweaters left in stock for this size, please choose a different size.')
                return false
            }
        }
        else {
            subtractFromCart(0, item)
            // check if shipping was added for this sweater and remove as well
            if ($("#bubbySweaterShippedToAddress").is(":checked")) {
                const item2 = {
                    desc: 'sweater_bubby_ship',
                    amount: 10
                }
                subtractFromCart(0, item2)
                document.getElementById('bubbySweaterShippedToAddress').checked = false
            }
        }
        calculateTotal()
    })

    $("#sweaterForBubbySize").change( function() {
        // check stock
        if ($(this).val() == 0) {
            alert('There are no sweaters left in stock for this size, please choose a different size.')
            return false
        }
        // find item in cart and update
        for (item of cart[0]) {
            if (item.desc == 'sweater_bubby') {
                item.size = $(this).val()
            }
        }
    })

    $("#bubbySweaterShippedToSchool").click( function() {
        const checked = $(this).is(":checked")
        const item = {
            desc: 'sweater_bubby_ship',
            amount: 10
        }
        if (checked) subtractFromCart(0, item)
        calculateTotal()
    })

    $("#bubbySweaterShippedToAddress").click( function() {
        const checked = $(this).is(":checked")
        const item = {
            desc: 'sweater_bubby_ship',
            amount: 10
        }
        if (checked) addToCart(0, item)
        calculateTotal()
    })

    $("#bubbySweaterShippedToAddressInput").blur( function() {
        const item = {
            desc: 'sweater_bubby_ship_addr',
            amount: 0,
            address: $(this).val()
        }
        addToCart(0, item)
        editCart(0, item)
        calculateTotal()
    })

    $("#sweaterForZaidy").click( function() {
        const checked = document.getElementById('sweaterForZaidy').checked
        const item = {
            desc: 'sweater_zaidy',
            amount: 25,
            size: $("#sweaterForZaidySize").val()
        }
        if (checked) {
            if (item.size != 0)
                addToCart(0, item)
            else {
                alert('There are no sweaters left in stock for this size, please choose a different size.')
                return false
            }
        }
        else {
            subtractFromCart(0, item)
            // check if shipping was added for this sweater and remove as well
            if ($("#zaidySweaterShippedToAddress").is(":checked")) {
                const item2 = {
                    desc: 'sweater_zaidy_ship',
                    amount: 10
                }
                subtractFromCart(0, item2)
                document.getElementById('zaidySweaterShippedToAddress').checked = false
            }
        }
        calculateTotal()
    })

    $("#sweaterForZaidySize").change( function() {
        // check for stock
        if ($(this).val() == 0) {
            alert('There are no sweaters left in stock for this size, please choose a different size.')
            return false
        }
        // find item in cart and update
        for (item of cart[0]) {
            if (item.desc == 'sweater_zaidy') {
                item.size = $(this).val()
            }
        }
    })

    $("#zaidySweaterShippedToSchool").click( function() {
        const checked = $(this).is(":checked")
        const item = {
            desc: 'sweater_zaidy_ship',
            amount: 10
        }
        if (checked) subtractFromCart(0, item)
        calculateTotal()
    })

    $("#zaidySweaterShippedToAddress").click( function() {
        const checked = $(this).is(":checked")
        const item = {
            desc: 'sweater_zaidy_ship',
            amount: 10
        }
        if (checked) addToCart(0, item)
        calculateTotal()
    })

    $("#zaidySweaterShippedToAddressInput").blur( function() {
        const item = {
            desc: 'sweater_zaidy_ship_addr',
            amount: 0,
            address: $(this).val()
        }
        addToCart(0, item)
        editCart(0, item)
        calculateTotal()
    })

    function checkKhkEligibility(child) {
        if (child.grade === 8 && (child.track === 'havonah' || child.track === 'iyun')) return true
        else return false
    }

    function checkForRohrGrant(amount) {
        if (amount >= 270 && amount <= 370) return true
        else return false
    }

    function getLowest(amounts, coupon, raised, khk) {
        let lowest = amounts[amounts.length-1]
        if (coupon > 0) lowest -= coupon
        if (raised > 0) lowest -= raised / 2
        if (raised > 170 && raised < 270 && !khk) {
            const diff = 270 - raised
            if (diff < lowest) lowest = diff
        }
        if (lowest < 0) lowest = 0
        return lowest
    }

    function getChildren(admin) {
        // getPreviousPurchaseInfo()
        const trackText = {
            yesod: "a sweater and gifts",
            yediah: "a sweater, gifts and prizes",
            havonah: "a sweater, gifts, prizes and the regional trip",
            iyun: "a sweater, gifts, prizes and the regional trip",
            khk: "a sweater, gifts, prizes and the kol hatorah kulah experience or the regional trip"
        }
        const trackInfo = {
            yesod: [70, 50, 35],
            yediah: [180, 125, 100, 90],
            havonah: [370, 250, 200, 185],
            iyun: [370, 250, 200, 185],
            khk: [500, 400, 325, 250]
        }
        $.post('../ajax/getChildren.php', { admin: admin }, function (result) {
            let info = JSON.parse(result);
            if (info.error) {
                alert(info.error);
                return false
            }
            else if (info.success) {
                if (! info.children.length) {
                    alert("All children have been enrolled.");
                    return false;
                }
                let html = '';
                numChildren = info.children.length;
                for (let c in info.children) {
                    let child = info.children[c];
                    if (c == 0) {
                        child.track = 'yediah'
                        child.raised = 50
                        child.coupon = 0
                        child.grade = 7
                    } else if (c == 1) {
                        child.track = 'havonah'
                        child.raised = 304
                        child.coupon = 0
                        child.grade = 5
                    } else if (c == 2) {
                        child.track = 'havonah'
                        child.raised = 180
                        child.coupon = 0
                        child.grade = 8
                    }

                    // add rohr grant where applicable
                    const rohrGrant = checkForRohrGrant(child.raised)
                    if (rohrGrant) child.raised = 370

                    // figure out if child is in eligible for khk
                    const khk = checkKhkEligibility(child)

                    const trackWon = child.track[0].toUpperCase() + child.track.substr(1)

                    // if (parseInt(child.date_paid) > 0) continue;
                    children[child.user_id] = child;
                    children_ids.push(child.user_id);
                    const childPic = child.mobile_pic ? "mobile/reg/" + child.mobile_pic : "file_view.php?id=" + child.user_photo_id;
                    html += `
                            <div class="kid flex-kids">
                                <div>
                                    <img class="childImg" src="//mashpia.com/${childPic}" />
                                </div>
                                 <div style="margin-left: 20px;">
                                    <h5 class="formDetails">Dear ${child.first},</h5>
                                    <h5 class="formDetails">Mazal Tov! You have passed the <span class="track">${trackWon}</span>
                                    Track. You are eligible for ${khk ? trackText['khk']  : trackText[child.track]}.<h5>`
                    if (child.coupon > 0) {
                        html += `<h5 class="formDetails"><span class="title">Coupon Code</span><br />
                                You have a $${child.coupon} coupon which will be applied to your registration cost.</h5>`
                    }
                    if (child.raised > 0) {
                        html += `<h5 class="formDetails"><span class="title">Chidon Drive</span><br />
                                You raised $${child.raised} which gives you $${child.raised / 2} off your registration cost.</h5>`

                        // child.goal = 370
                        // const donation = child.raised
                        // const donationPercent = (donation / child.goal) * 100
                        // const subsidy = donation >= 270 ? 100 : 0
                        // const subsidyPercent = (subsidy / child.goal) * 100
                        // const balance = child.goal - (donation + subsidy)
                        // html += `
                        //     <div class="bar">
                        //         <div class="bar-circle">
                        //             <div class="bar-circle__inner">
                        //                 <span>$${child.goal}</span>
                        //             </div>
                        //         </div>
                        //         <div class="bar-bar">
                        //             <div class="bar-bar__wrapper">
                        //                 <div class="bar-bar__line donation" data-percent="${donationPercent}">
                        //                     <div class="bar-bar__circle"></div>
                        //                     <div class="bar-bar__marker">
                        //                         <img src="img/marker-blue.png" />
                        //                         <p>$${donation}</p>
                        //                     </div>
                        //                 </div>
                        //                 <div class="bar-bar__line subsidy" data-percent="${subsidyPercent}">
                        //                     <div class="bar-bar__circle"></div>
                        //                     <div class="bar-bar__marker">
                        //                         <img src="img/marker.png" />
                        //                         <p>${subsidy}</p>
                        //                     </div>
                        //                 </div>
                        //                 <div class="remaining">
                        //                     <div class="bar-bar__marker">
                        //                         <p>$${balance}<br>Remaining</p>
                        //                     </div>
                        //                 </div>
                        //             </div>
                        //         </div>
                        //     </div>`
                    }

                    let amounts, lowest, free
                    if (khk) {
                        let options = ['khk', 'havonah']
                        html += `<h5 class="formDetails"><span class="title">Registration Options</span><br />`
                        for (let option of options) {
                            amounts = trackInfo[option]
                            const khkOption = option === 'khk' ? true : false
                            lowest = getLowest(amounts, child.coupon, child.raised, khkOption)
                            if (lowest < amounts[amounts.length-1]) amounts.push(lowest)
                            if (lowest === 0) free = true
                            else free = false
                            html += `<h5 class="formDetails"><b>
                                    ${option === 'khk' ? 'KHK Trip Option' : 'Regional Trip Option (NOT KHK)'}
                                </b><br />`
                            html += `Full cost: $${amounts[0]}<br />`
                            html += `You can register for ${free ? 'FREE' : 'as little as $' + lowest}!.<br />`
                            html += `Please pay as much as you can.</h5>`
                        }

                        html += `<br />
                            <div class="flex">
                                <input class="inputCheckbox" type="radio" name="khk" class="khk_choice" value="1" onclick="togglePayment(1)" />
                                    <label class="checkboxLabel">YES, I would like to pay for the KHK Trip Option</label>
                            </div>
                            <div class="flex">
                                <input class="inputCheckbox" type="radio" name="khk" class="khk_choice" value="0" onclick="togglePayment(0)" />
                                    <label class="checkboxLabel">NO, I would only like to pay for the Regional Trip Option</label>
                            </div>`

                        for (let option of options) {
                            amounts = trackInfo[option]
                            html += `<h5 class="formDetails" id="${option + '_payment'}" style="display: none; margin-top: 10px;">I will pay:
                                   <select name="regAmount_${child.user_id}" class="regAmount" id="regAmount_${child.user_id}">`
                            let last = amounts.length - 1
                            for (; last >= 0; last--) {
                                html += `<option value='${amounts[last]}'>$${amounts[last]}</option>`
                            }
                            html += `</select></h5>`
                        }
                    } else {
                        amounts = trackInfo[child.track]
                        lowest = getLowest(amounts, child.coupon, child.raised, false)
                        if (lowest < amounts[amounts.length-1]) amounts.push(lowest)
                        if (lowest === 0) free = true
                        else free = false
                        html += `<h5 class="formDetails"><span class="title">Registration</span><br />`
                        html += `Full cost: $${amounts[0]}<br />`
                        html += `You can register for ${free ? 'FREE' : 'as little as $' + lowest}!.<br />`
                        html += `Please pay as much as you can.<br />`
                        html += `I would like to pay: <select name="regAmount_${child.user_id}" class="regAmount" id="regAmount_${child.user_id}">`
                        let last = amounts.length - 1
                        for (; last >= 0; last--) {
                            html += `<option value='${amounts[last]}'>$${amounts[last]}</option>`
                        }
                        html += `</select></h5>`
                    }

                    html += `</div>
                        </div>
                    </div>
                    <div style="clear: both"></div>
                    <br />`
                }
                $(".login").hide();
                $("#regForm").show();
                $("#purchasesForm").show();
                $("#logoffDiv").show();
                $(".children").empty();
                $(".children").append(html);
                $(".children").show();
                $("select").niceSelect()
            } else {
                alert(info.error);
            }
        });
    }

    function togglePayment(val) {
        if (val) {
            $("#khk_payment").show()
            $("#havanah_payment").hide()
        } else {
            $("#khk_payment").hide()
            $("#havanah_payment").show()
        }
    }

    function getSweaterInfo() {
        $.post('../ajax/sweaterInfo.php', function( result ) {
            const sweaters = JSON.parse( result )
            console.log( sweaters )
            for (let type in sweaters) {
                createSweaterSelect(type, sweaters[type])
                addSweaterImage(type, sweaters[type]['Adult XS']['img']);
            }
        })
    }

    function createSweaterSelect(type, options) {
        const sizes = {
            xs: 'Adult XS',
            small: 'Adult Small',
            medium: 'Adult Medium',
            large: 'Adult Large',
            xl: 'Adult XL'
        }
        let html = ''
        for (let size in sizes) {
            if (options[sizes[size]] && options[sizes[size]]['qty'] > 0)
                html += `<option value='${size}'>${sizes[size]} (${options[sizes[size]]['qty']} left)</option>`
            else
                html += `<option value=0>${sizes[size]} (<i>sold out</i>)</option>`
        }
        let elem = type.charAt(0).toUpperCase() + type.slice(1);
        elem = "#sweaterFor" + elem + "Size";
        $(elem).empty()
        $(elem).append(html)
        $(elem).niceSelect('update')
    }

    function addSweaterImage(type, img) {
        let elem = type.charAt(0).toUpperCase() + type.slice(1);
        elem = "#sweaterFor" + elem;
        $(elem).parent().find('label').after("<img src='http://mashpia.com" + img + "' style='max-height: 100px; margin: 10px;' />")
    }

    function getPrizesInfo(form, id) {
        $(form).hide()
        $.post('../ajax/chidon_prizes.php', function(result) {
            const amount = children[id].option == 'C' ? 200 : 100
            $(form).find(".maxPrizes").text(amount)
            if (amount == 200) {
                $(form).find('.listItemChidonTrips').remove()
            } else {
                if (children[id].option == 'A') $(form).find('.trips').show();
                else if (children[id].option == 'B') $(form).find('.vr').show()
            }
            const prizes_list = JSON.parse(result)
            let html = `<h5 class="formTitle chidon_prizes">Chidon Prizes</h5>`
            html += `
				<h5 class="formDetails" style="font-weight: bold;">
				You can choose up to $${amount} worth of prizes from this section
				</h5>
			`
            for (let prize_id in prizes_list) {
                let prize = prizes_list[prize_id]
                html += `
					<div class="field flex">
						<input class="inputCheckbox" type="checkbox" id="prize_${prize_id}" name="prize_${prize_id}" data-info="${prize_id}:${prize['price']}" />
						<label class="checkboxLabel" for="prize_${prize_id}">
					`
                html += `
					<span style='float: left'>
						<img src="http://mashpia.com${prize['prize_picture']}" style='max-height: 100px; padding-right: 10px;' /></span>
							${prize['prize_name']}
				`
                if (prize['size']) html += `<br />Size: ${prize['size']} `;
                if (prize['color']) html += `<br />Color: ${prize['color']} `
                html += ` - $${prize['price']} (${parseInt(prize['quantity']) - parseInt(prize['purchased'])} available)`
                if (prize['made_possible_by']) html += `<br />Made possible by: ${prize['made_possible_by']}`

                // for menorah add line
                if (prize['prize_name'].includes('Menorah')) {
                    html += "<br /><br />Please note: Menorahs will arrive in time for Chanukah 5782 (later than other prizes).";
                }

                // add box for name if it's tefillin or leather sefer
                if (prize['prize_name'].includes('Tefillin') || prize['prize_name'].includes('Leather')) {
                    html += `<br /><br />Please write your name in Hebrew as you would want it on your item. Once submitted,
						there will be <span style="font-weight: bold">no</span> name changes allowed.
						<input type="text" class="field he_name" name="he_${prize_id}" data-id="${prize_id}" style="width: 200px;" />`
                }

                html += `
						</label>
					</div>
				`
            }
            $(form).find('.prizes').empty()
            $(form).find('.prizes').append(html)
            $(form).show()

            // check off existing prizes in prize cart
            if (prizes[id]) {
                for (let prize of prizes[id]) {
                    let elem = "prize_" + prize.id
                    document.getElementById(elem.toString()).checked = true
                }
            }

            // find out which prizes user has already purhased
            $.post('../ajax/chidon_user_prizes.php', { user: id }, function(result) {
                const res = JSON.parse(result)
                for (let prize of res) {
                    let elem = "#prize_" + prize.prize_id
                    $(elem).trigger('click')
                    $(elem).parent().find('.he_name').val(prize.he_name)
                    $(elem).parent().find('.he_name').trigger('blur')
                }
            })
        })
    }

    function addToPrizes(prize_item) {
        if (!prizes[selectedChildId]) {
            prizes[selectedChildId] = []
        }

        // first check if child has enough points to get this prize
        const max = children[selectedChildId].option == 'C' ? 200 : 100
        let total = 0
        for (let prize of prizes[selectedChildId]) {
            total += parseFloat(prize.amount)
        }
        if ((total + parseFloat(prize_item.amount)) > max) {
            alert("Sorry, You do not have enough money to add this prize.")
            return false
        }

        // check if prize already exists in cart
        let add = true
        for (let prize of prizes[selectedChildId]) {
            if (prize_item.id == prize.id && prize_item.amount == prize.amount) {
                if (prize_item.he_name) prize.he_name = prize_item.he_name
                add = false
            }
        }
        if (add) prizes[selectedChildId].push(prize_item)
        return true
    }

    function removeFromPrizes(prize) {
        for (let p in prizes[selectedChildId]) {
            if (prizes[selectedChildId][p].id == prize.id) {
                prizes[selectedChildId].splice(p, 1)
            }
        }
    }

    function updateForm(form, id) {
        // find out if we need to check off anything
        const name = children[id].first + ' ' + children[id].last
        const elem = "." + form
        $(elem).find('.chayolName').text(name)
        if (cart[id]) {
            for (item of cart[id]) {
                if (item.desc == 'reg') {
                    if (form == 'formA') {
                        document.getElementById('registerForChidonA').checked = true
                    } else if (form == 'formB') {
                        document.getElementById('registerForChidonB').checked = false
                    } else if (form == 'formC' || form == 'formD') {
                        const amount = item.amount
                        if (amount == 25) {
                            if (form == 'formC') document.getElementById('registerForChidonGiftsA').checked = true
                            else if (form == 'formD') document.getElementById('registerForChidonGiftsB').checked = true
                        } else {
                            if (form == 'formC') {
                                document.getElementById('registerForChidonExperienceA').checked = true
                                document.getElementById('paymentAmountA').value = amount
                                $(".formC .prizes").show()
                            } else if (form == 'formD') {
                                document.getElementById('registerForChidonExperienceB').checked = true
                                document.getElementById('paymentAmountB').value = amount
                                $(".formD .prizes").show()
                            }
                            $('select').niceSelect('update');
                        }
                    }
                } else if (item.desc == 'yarmulka') {
                    switch (form) {
                        case 'formB':
                            $("#yarmulkaSizeB").val(item.size)
                            $("#boysOnlyYarmulkaSizesB select").niceSelect('update')
                            $("#boysOnlyYarmulkaSizesB").show()
                            break
                        case 'formC':
                            $("#yarmulkaSizeC").val(item.size)
                            $("#boysOnlyYarmulkaSizesC select").niceSelect('update')
                            $("#boysOnlyYarmulkaSizesC").show()
                            break
                        case 'formD':
                            $("#yarmulkaSizeD").val(item.size)
                            $("#boysOnlyYarmulkaSizesD select").niceSelect('update')
                            $("#boysOnlyYarmulkaSizesD").show()
                            break
                    }
                }
            }
        }
        // show trip options for myshliach / anash kinder kids
        const child = children[id]
        if (parseInt(child.school_id) == 61 || parseInt(child.school_id) == 269) {
            if (parseInt(child.school_id) == 61) {
                if (form == 'formC') $(".formC .tripOptionSchool").text("MyShliach")
                else if (form == 'formD') $(".formD .tripOptionSchool").text("MyShliach")
            } else if (parseInt(child.school_id) == 269) {
                if (form == 'formC') $(".formC .tripOptionSchool").text("Anash Kinder")
                else if (form == 'formD') $(".formD .tripOptionSchool").text("Anash Kinder")
            }
            if (form == 'formC') $(".formC .tripOptions").show()
            else if (form == 'formD') $(".formD .tripOptions").show()
        } else {
            $(".tripOptions").hide()
        }
    }

    function checkForPayment() {
        // check if admin already payed for registration
        $.post('../ajax/checkPayment.php', { admin: Cookies.get('chidon_admin') }, function(result) {
            const res = JSON.parse(result)
            if (res.success && res.payment.amount) {
                const item = {
                    id: 0,
                    desc: 'past_payment',
                    amount: parseFloat(res.payment.amount) * -1
                }
                addToCart(0, item)
                alert("We will now apply your previous payment to your cart!")
                calculateTotal()
            }
        })
    }

    $(document).ready(function (e) {
        // Select
        $('select').niceSelect();

        $("#loginForm").submit(function (evt) {
            evt.preventDefault();
            let user = $("#username").val();
            let pass = $("#password").val();
            $.post('../ajax/login.php', { username: user, password: pass }, function (result) {
                let info = JSON.parse(result);
                if (info.success) {
                    Cookies.set('chidon_admin', info.admin);
                    Cookies.set('from_enrollment', 1);
                    getChildren(info.admin);
                } else {
                    alert(info.error);
                }
            });
        });

        $(".tripOption").click( function() {
            const trip = $(this).val()
            const item = {
                desc: 'trip_option',
                option: trip,
                amount: 0
            }
            addToCart(selectedChildId, item)
        })

        $(document).on('click', '.continue', function( evt ) {
            evt.preventDefault()
            // check if there's any registrations
            if (cart_total == 0) {
                alert('You have not checked off any registrations!')
                return false
            }

            // check if we need to disable the celebration box or not and if we should show the hold option or not
            let numCelebrationBoxes = 0
            let first_name = ''
            for (let id in cart) {
                for (let item of cart[id]) {
                    if (item.desc == 'reg') {
                        let child = children[id]
                        if (child.shabbaton_expert || child.shabbaton_trophy) {
                            if (item.amount > 25) {
                                numCelebrationBoxes++
                                first_name += child.first + ', '
                            }
                        }
                    }
                }
            }
            $(".first_name").text(first_name)

            if (!numCelebrationBoxes) {
                $(".amountOfBoxes").text(0)
                $("#celebrationBox").attr('disabled', true)
                alert('You do not have any eligible children paying for the Chidon Experience in order to qualify for a free complimentary Celebration Box.')
            } else {
                $("#celebrationBox").attr('disabled', false)
                $(".amountOfBoxes").text(numCelebrationBoxes)
                let options = '';
                for (let i = 1; i <= numCelebrationBoxes; i++) {
                    options += `<option value=${i}>${i}</option>`;
                }
                $("#numberOfCelebrationBoxes").empty()
                $("#numberOfCelebrationBoxes").append(options)

                // check if cart already has celebration box info
                if (cart[0]) {
                    for (item of cart[0]) {
                        if (item.desc == 'celeb_box') {
                            document.getElementById('celebrationBox').checked = true
                            $("#numberOfCelebrationBoxes").val(item.qty)
                        }
                    }
                }

                $('.form2 select').niceSelect('update');

                $("#numberOfCelebrationBoxes").change( function() {
                    let elem = $(this).parent().find('input.inputCheckbox');
                    if (! $(elem).is(':checked')) $(elem).trigger('click')
                })
            }

            // add section detailing what parents already paid for / reserved
            if (cart[0]) {
                for (item of cart[0]) {
                    switch (item.desc) {
                        case 'celeb_box_add':
                            $("#additionalCelebrationBox").trigger('click')
                            break
                        case 'celeb_box_add_ship':
                            document.getElementById('shippedToAddress').checked = true
                            break
                        case 'celeb_box_add_addr':
                            $("#shippedToAddressInput").val(item.address)
                            break
                        case 'sweater_mother':
                            document.getElementById('sweaterForMother').checked = true
                            $("#sweaterForMotherSize").val(item.size)
                            $('#sweaterForMotherDetails select').niceSelect('update');
                            $("#sweaterForMotherDetails").show()
                            break;
                        case 'sweater_mother_ship':
                            document.getElementById("motherSweaterShippedToAddress").checked = true
                            break
                        case 'sweater_mother_ship_addr':
                            $("#motherSweaterShippedToAddressInput").val(item.address)
                            break
                        case 'sweater_father':
                            document.getElementById('sweaterForFather').checked = true
                            $("#sweaterForFatherSize").val(item.size)
                            $('#sweaterForFatherDetails select').niceSelect('update');
                            $("#sweaterForFatherDetails").show()
                            break;
                        case 'sweater_father_ship':
                            document.getElementById("fatherSweaterShippedToAddress").checked = true
                            break
                        case 'sweater_father_ship_addr':
                            $("#fatherSweaterShippedToAddressInput").val(item.address)
                            break
                        case 'sweater_bubby':
                            document.getElementById('sweaterForBubby').checked = true
                            $("#sweaterForBubbySize").val(item.size)
                            $('#sweaterForBubbyDetails select').niceSelect('update');
                            $("#sweaterForBubbyDetails").show()
                            break;
                        case 'sweater_bubby_ship':
                            document.getElementById("bubbySweaterShippedToAddress").checked = true
                            break
                        case 'sweater_bubby_ship_addr':
                            $("#bubbySweaterShippedToAddressInput").val(item.address)
                            break
                        case 'sweater_zaidy':
                            document.getElementById('sweaterForZaidy').checked = true
                            $("#sweaterForZaidySize").val(item.size)
                            $('#sweaterForZaidyDetails select').niceSelect('update');
                            $("#sweaterForZaidyDetails").show()
                            break;
                        case 'sweater_zaidy_ship':
                            document.getElementById("zaidySweaterShippedToAddress").checked = true
                            break
                        case 'sweater_zaidy_ship_addr':
                            $("#zaidySweaterShippedToAddressInput").val(item.address)
                            break
                    }
                }
            }

            $('.form2 select').niceSelect('update');
            $(".form2").show()
            $(window).scrollTop(400)
        })

        $(document).on('click', '.pay', function( evt ) {
            evt.preventDefault()
            if (! ($("#chargeCard").is(":checked") || $("#holdMoney").is(":checked")) ) {
                alert('You must indicate whether you would like to be charged now or later!')
                return false
            } else {
                $(".form2").hide()
                $("#paymentInfo").show()
                $(window).scrollTop(400)
                checkForVoucher()
                checkForPayment()
            }
        })

        $(document).on('click', '.prizes input[type=checkbox]', function( evt ) {
            // get id and amount
            const info = $(this).data('info').split(':')
            const prize = {
                id: info[0],
                amount: info[1]
            }
            if ($(this).is(":checked")) {
                if (!addToPrizes(prize)) {
                    $(this).trigger('click')
                }
            }
            else removeFromPrizes(prize)
        })

        $(document).on('blur', '.prizes .he_name', function( evt ) {
            // get id and amount
            const id = $(this).data('id')
            const he_name = {
                id: id,
                amount: 0,
                he_name: $(this).val()
            }
            addToPrizes(he_name)
            let elem = $(this).parent().parent().find('input.inputCheckBox')
            if (! $(elem).is(":checked") ) $(elem).trigger('click')
        })

        $("#code").blur( function() {
            const code = $(this).val();
            if ( code ) {
                $.post("../ajax/findCode.php", { code : code }, function( result ) {
                    const res = JSON.parse( result );
                    if (res.success) {
                        const item = {
                            id: parseInt(res.id),
                            desc: 'voucher',
                            amount: parseFloat(res.amount) * -1
                        }
                        addToCart(0, item)
                        alert('We have applied a coupon code to your cart!\nYour total is now $' + parseFloat(res.amount) + " less than before!")
                    } else {
                        alert(res.error)
                    }
                });
            }
        });

        $(document).on('click', '.processPayment', function( evt ) {
            evt.preventDefault()

            if (!cart_total && !cart) {
                alert('You have not checked off any registration!')
                return false
            }

            const name = $("#name").val()
            const email = $("#email").val()

            let cc = {}
            // if ($("#cc_on_file_yes").is(":checked")) {
            // 	cc.on_file = 1
            // } else {
            cc.on_file = 0
            cc.num = $("#cc_num").val();
            cc.exp = $("#exp_yy").val() + '-' + $("#exp_mm").val();
            cc.cvv = $("#cc_cvv").val();

            let billing = {}
            billing.apt = $("#bill_apt").val();
            billing.address = $("#bill_address").val();
            billing.city = $("#bill_city").val();
            billing.state = $("#bill_state").val();
            billing.zip = $("#bill_zip").val();
            billing.country = $("#bill_country").val();
            cc.billing = billing;

            if (cart_total && !(name && email && cc.num && cc.exp && cc.cvv && billing.address && billing.city && billing.state && billing.country)) {
                alert('You must fill in Name, Email, All Credit Card and Billing Info!');
                return false;
            }
            // }
            cc.skip = 0
            // cc.skip = 1

            let method = ''
            if ($("#chargeCard").is(":checked")) method = 'charge'
            else if ($("#holdMoney").is(":checked")) method = 'hold'

            if (method != '') {
                $(this).attr('disabled', true)
                $.post('../ajax/processPaymentNew.php', {
                    amount: cart_total,
                    details: JSON.stringify(cart),
                    method: method,
                    cc: cc,
                    name: name,
                    email: email,
                    prizes: JSON.stringify(prizes)
                }, function(result) {
                    const res = JSON.parse(result)
                    console.log(res)
                    alert(res.msg)
                    if (!res.success) {
                        $(".processPayment").attr('disabled', false)
                    } else {
                        alert('You will now be routed to the chidondrive setup page.')
                        location.href = 'intro.html'
                    }
                })
            } else {
                alert('You must indicate whether you want to be charged now or later!')
                return false
            }
        })

        $("#logoff").click(function () {
            Cookies.remove('chidon_admin');
            location.reload()
        });

        $(".sweater").change( function() {
            $(this).parent().parent().next('.addressInfo').empty()
            let num = parseInt($(this).val(), 10)
            if (num) {
                let template = `
                    <div class="field flex medium-select">
                        <label>Select Size</label>
                        <select>
                            <option value='xs'>Adult XS (50 left)</option>-->
                            <option value='small'>Adult Small (50 left)</option>-->
                            <option value='medium'>Adult Medium (100 left)</option>-->
                            <option value='large'>Adult Large (50 Left)</option>-->
                            <option value='xl'>Adult XL large (50 Left)</option>-->
                        </select>
                    </div>
                    <div class="listSection" style="background-color: #394190; margin-bottom: 1rem;">
                        <div class="flex">
                            <input class="inputCheckbox" type="radio">
                            <label class="checkboxLabel">
                                to be shipped to my school.
                            </label>
                        </div>
                        <div class="flex">
                            <input class="inputCheckbox" type="radio">
                            <label class="checkboxLabel">
                                +$10 to be shipped to an address in the USA
                            </label>
                        </div>
                        <div class="flex">
                            <input type="text" placeholder="address" />
                            <input type="text" placeholder="city" />
                            <input type="text" placeholder="state" />
                            <input type="text" placeholder="zip" />
                        </div>
                    </div>
                `
                let html
                for (let i = 0; i < num; i++) {
                    html += template
                }
                $(this).parent().parent().next('.addressInfo').append(html)
                $(".addressInfo select").niceSelect()
            }
        })
    });
</script>

<script>
    $(function () {
        var header = $("header");
        $(window).scroll(function () {
            var scroll = $(window).scrollTop();

            if (scroll >= 100) {
                header.addClass("header-alt");
                console.log('called');
            } else {
                header.removeClass('header-alt');
            }
        });
    });
</script>

</body>

</html>