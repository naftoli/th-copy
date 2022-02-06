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

        #payment
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
        <h2 class="title">Chidon Checkout</h2>

        <div class="form" id="checkout">
            <form class="personalInfo">

            </form>
        </div>

        <div class="payment-box" id="newCreditCard" style="display: none">
            <div class="form">
                <form id="paymentForm">
                    <div id="paymentFormDetails">
                        <h5 class="formDetails"><span class="title">New Credit Card</span></h5>
                        <div class="field">
                            <label>Name on Credit Card:</label>
                            <input type="text" id="name" placeholder="Full Name" required />
                        </div>
                        <div class="field">
                            <label>Email (for receipt):</label>
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
                                    <option>2022</option><option>2023</option><option>2024</option>
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
                </form>
            </div>
        </div>

        <div class="form">
            <form id="terms">
                <h5 class="formDetails"><span class="title">Terms & Conditions</span></h5>
                <div class="flex">
                    <input type="checkbox" name="terms1" class="inputCheckbox" id="terms1" />
                    <label for="terms1">We are doing our best to ensure all items will be received at your school
                        or at the US addresses specified before the Chidon Event on Sunday, 2 Nissan 5782, April 3rd 2022.
                        We do not take responsibility in case of unforeseen circumstances.</label>
                </div>
                <div class="flex">
                    <input type="checkbox" name="terms2" class="inputCheckbox" id="terms2" />
                    <label for="terms2">All payments are final. There are no refunds.</label>
                </div>
                <div align="center">
                    <br />
                    <input type="submit" class="button processPayment" value="Pay & Register" style="cursor: pointer; font-size: 20px;" />
                </div>
            </form>
        </div>

        <!-- END OF FORMS -->

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

    if (! Cookies.get('chidon_admin')) {
        location.href = 'enroll_5782.php'
    }

    $('#menu-opener').click(function (e) {
        $('header nav').toggleClass('open');
    });

    $("select").niceSelect()

    /**** GLOBAL VARS ****/
    var children = []; // global variable for children info
    var children_ids = []; // global variable to keep track of order of children and ids
    var numChildren; // global variable to track number of children
    var cart = []
    var cart_total = 0
    var addresses = {} // all addresses entered
    var admin

    $.post('../ajax/getAdminInfo.php', function(result) {
        admin = JSON.parse(result)
        let cards = []
        if (admin['cards']) {
            for (let i in admin['cards']) {
                let cardInfo = admin['cards'][i]
                let info = cardInfo.payment.creditCard.cardType + ' - ' + cardInfo.payment.creditCard.cardNumber.substr(4)
                let card = {
                    type: info,
                    number: cardInfo.payment.creditCard.cardNumber
                }
                cards.push(card)
            }
        }
        // console.log(cards)
        let html = ''
        html += `<select name="payment-card" class="payment-card">`
        for (let card of cards) {
            html += `<option value=${card.number}>${card.type}</option>`
        }
        html += '</select>'
        $("#creditCards").html(html)
        $("select.payment-card").niceSelect()
    })

    if (window.localStorage) {
        cart = JSON.parse(window.localStorage.getItem('cart'))
        addresses = JSON.parse(window.localStorage.getItem('addresses'))
    } else {
        cart = JSON.parse(Cookies.get('cart'))
        addresses = JSON.parse(Cookies.get('addresses'))
    }

    console.log(cart)
    console.log(addresses)

    // parse cart info
    let reg = []
    let shipping = 0
    let celeb_boxes = 0
    let celeb_box_shipping = 0
    let sweaters = []
    let sweater_shipping = 0

    const celeb_box_price = 20
    const sweater_price = 25

    for (let item of cart) {
        if (item.desc.includes('reg')) {
            const reg_info = item.desc.split('_')
            const reg_item = {
                id: reg_info[1],
                fee: parseInt(item.value)
            }
            reg.push(reg_item)
        } else if (item.desc === 'ship_usa' || item.desc === 'ship_intl') {
            shipping = parseInt(item.value)
        } else if (item.desc === 'num_celeb_boxes') {
            celeb_boxes = parseInt(item.value)
        } else if (item.desc === 'celeb_box_ship') {
            celeb_box_shipping = parseInt(item.value)
        } else {
            const fields = ['mother_sweater', 'father_sweater', 'bubby_sweater', 'zaidy_sweater']
            for (let field of fields) {
                let num = 0
                if (item.desc === field) {
                    if (parseInt(item.value) > 0) {
                        let sweater = {
                            type: item.desc,
                            amount: parseInt(item.value)
                        }
                        sweaters.push(sweater)
                    }
                }
                // check shipping for this sweater
                let sweater = sweaters.filter(sweater => sweater.type === field)
                if (sweater.length) {
                    let num = sweater.amount
                    for (let i = 1; i <= num; i++) {
                        let newField = field + '_' + i + '_ship'
                        if (item.desc === newField) {
                            let cost = parseInt(item.value)
                            if (cost > 0) {
                                sweater_shipping += cost
                            }
                        }
                    }
                }
            }
        }
    }

    let html = ''
    let reg_fee = 0
    if (reg.length) {
        html += `<h5 class="formDetails"><span class="title">Registration Summary</span></h5>`
        for (let info of reg) {
            html += `<h5 class="formDetails">Registering for ${info.id} : $${info.fee}</h5>`
            reg_fee += info.fee
        }
    }
    if (shipping) {
        html += `<h5 class="formDetails">Shipping Fee: $${shipping}</h5>`
    }
    if (reg.length) {
        html += `<h5 class="formDetails">Total Registration Fee: $${reg_fee + shipping}</h5>`
        cart_total += reg_fee + shipping
    }
    if (celeb_boxes) {
        html += `<h5 class="formDetails"><span class="title">Celebration Boxes</span></h5>`
        html += `<h5 class="formDetails">Number of Celebration Boxes: ${celeb_boxes}</h5>`
        html += `<h5 class="formDetails">Cost per Celebration Box: $${celeb_box_price}</h5>`
        html += `<h5 class="formDetails">Additional Shipping Fee: $${celeb_box_shipping}</h5>`
        html += `<h5 class="formDetails">Total Cost: $${celeb_boxes * celeb_box_price + celeb_box_shipping}`
        cart_total += celeb_boxes * celeb_box_price + celeb_box_shipping
    }
    if (sweaters.length) {
        let sweater_cost = 0
        html += `<h5 class="formDetails"><span class="title">Sweaters</span></h5>`
        html += `<h5 class="formDetails">Cost per Sweater: $${sweater_price}</h5>`
        for (let sweater of sweaters) {
            let type = sweater.type
            let typeInfo = type.split('_')
            for (let i = 0; i < typeInfo.length; i++) {
                typeInfo[i] = typeInfo[i][0].toUpperCase() + typeInfo[i].substr(1)
            }
            let typeName = typeInfo.join(' ')
            html += `<h5 class="formDetails">Number of ${typeName}s: ${sweater.amount}</h5>`
            sweater_cost += sweater.amount * sweater_price
        }
        html += `<h5 class="formDetails">Additional Sweater Shipping: $${sweater_shipping}</h5>`
        sweater_cost += sweater_shipping
        html += `<h5 class="formDetails">Total Sweater Cost: $${sweater_cost}</h5>`
        cart_total += sweater_cost
    }
    html += `<h5 class="formDetails"><span class="title">Grand Total</span></h5>`
    html += `<h5 class="formDetails">Total Charge Today: $${cart_total}</h5>`

    // payment
    html += `<h5 class="formDetails"><span class="title">Payment</span></h5>`
    html += `
        <div class="flex">
            <input type="radio" name="payment" class="inputCheckbox payment" value="0" />
            <label for="payment" style="display: inline">Please use my credit card on file ending in</label>
            <span id="creditCards"></span>
        </div>
        <div class="flex">
            <input type="radio" name="payment" class="inputCheckbox payment" value="1" />
            <label for="payment" style="display: inline">I will enter a new card</label>
        </div>
    `
    $(".personalInfo").append(html)

    if (reg.length && cart_total === 0) $(".processPayment").val('Register')
    else if (reg.length && cart_total > 0) $(".processPayment").val('Pay & Register')
    else if (cart_total > 0) $(".payment").val('processPayment')

    $(".payment").click( function() {
        if (parseInt($(this).val())) {
            $("#newCreditCard").show()
        } else {
            $("#newCreditCard").hide()
        }
    })

    $(document).on('click', '.processPayment', function( evt ) {
        evt.preventDefault()

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