<?
include 'inc/head.php';

if (!isset($_SESSION['cart'])) {
	header("Location: cart.php");
	exit;
}

$numCDs = 0;
$amount = 0;
require_once 'db.php';
$sql = "select id, price, discount_price from cds where id in (" . implode(',', $_SESSION['cart']) . ")";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	if (in_array($row['id'], array(12,13))) {
		$amount += $row['discount_price'];
	} else {
		$amount += $row['price'];
	}
	$numCDs++;
}
if ($amount && isset($_SESSION['coupon']) && $_SESSION['coupon'] == 'msth5775') {
	$amount -= $numCDs;
}

//if cart has promo add the promo
if (in_array(100, $_SESSION['cart'])) {
	if (isset($_SESSION['coupon']) && $_SESSION['coupon'] == 'msth5775') {
		$amount = 39.99;
	} else {
		$amount = 49.99;
	}
	$numCDs = 10;
}
?>

<link href="../css/flat-ui.css" rel="stylesheet">
<!--
<script src="http://code.jquery.com/jquery-1.10.2.min.js"></script>
<script src="http://netdna.bootstrapcdn.com/bootstrap/3.1.0/js/bootstrap.min.js"></script>
-->
<section id="tz-main"><!-- tz-main-->

    <section class="tz-main-body">

        <div class="container-fluid"><!--start container-fluid-->

            <div class="tz-inner"><!--start tz-inner-->

              <section class="tz-content-wrap row-fluid">

                   <div class="container">
<div class="stepwizard">
    <div class="stepwizard-row setup-panel">
        <div class="stepwizard-step">
            <a href="#step-1" type="button" class="btn btn-primary btn-circle">1</a>
            <p>Step 1</p>
        </div>
        <div class="stepwizard-step">
            <a href="#step-2" type="button" class="btn btn-default btn-circle" disabled="disabled">2</a>
            <p>Step 2</p>
        </div>
        <div class="stepwizard-step">
            <a href="#step-3" type="button" class="btn btn-default btn-circle" disabled="disabled">3</a>
            <p>Step 3</p>
        </div>
    </div>
</div>
<form role="form">
    <div class="row setup-content" id="step-1">
        <div class="col-xs-12">
            <div class="col-md-12">
                <h3> Step 1</h3>
                <div class="form-group">
                    <label class="control-label">First Name</label>
                    <input id="fname" maxlength="100" type="text" required="required" class="form-control" placeholder="Enter First Name"  />
                </div>
                <div class="form-group">
                    <label class="control-label">Last Name</label>
                    <input id="lname" maxlength="100" type="text" required="required" class="form-control" placeholder="Enter Last Name" />
                </div>
                <div class="form-group">
                    <label class="control-label">Email Address</label>
                    <input id="email" maxlength="200" type="text" required="required" class="form-control" placeholder="Enter Your Email Address"  />
                </div>
                <button class="btn btn-primary nextBtn btn-lg pull-right" type="button" >Next</button>
            </div>
        </div>
    </div>
    <div class="row setup-content" id="step-2">
        <div class="col-xs-12">
            <div class="col-md-12">
                <h3> Step 2</h3>
                <div class="form-group">
                    <label>Card Number</label>
                    <input id="ccnumber" type="text" class="form-control">
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Exp Month</label>
                            <input id="mm" type="text" class="form-control" placeholder="MM">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Exp Year</label>
                            <input id="yy" type="text" class="form-control" placeholder="YY">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>CVC</label>
                            <input id="cvv" type="text" class="form-control" placeholder="331">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>Cardholder's Billing Zip Code</label>
                    <input id="zip" type="text" class="form-control">
                </div>
                <button class="btn btn-primary nextBtn btn-lg pull-right" type="button" >Next</button>
            </div>
       </div>
    </div>
    <div class="row setup-content" id="step-3">
        <div class="col-xs-12">
            <div class="col-md-12">
                <h3> Step 3 - Summary</h3>
                <div class="form-group">
					<p>
						You will be charged $<?=$amount?> for your puchase of <?=$numCDs?> downloadable cd(s).
					</p>                    
                </div>
                <div class="form-group">
                    <input id="agree" type="checkbox" class="form-control"> 
                    I agree that the credit card provided will be charged $<?=$amount?>.
                </div>
                <br /><button class="btn btn-success btn-lg" type="submit" id="submit">Pay Now</button>
            </div>
        </div>
    </div>
</form>
<!--end tz-main-->
<br />

<? include 'inc/footer.html'; ?>

<script type="text/javascript">
$(document).ready(function () {

    var navListItems = $('div.setup-panel div a'),
            allWells = $('.setup-content'),
            allNextBtn = $('.nextBtn');

    allWells.hide();

    navListItems.click(function (e) {
        e.preventDefault();
        var $target = $($(this).attr('href')),
                $item = $(this);

        if (!$item.hasClass('disabled')) {
            navListItems.removeClass('btn-primary').addClass('btn-default');
            $item.addClass('btn-primary');
            allWells.hide();
            $target.show();
            $target.find('input:eq(0)').focus();
        }
    });

    allNextBtn.click(function(){
        var curStep = $(this).closest(".setup-content"),
            curStepBtn = curStep.attr("id"),
            nextStepWizard = $('div.setup-panel div a[href="#' + curStepBtn + '"]').parent().next().children("a"),
            curInputs = curStep.find("input[type='text'],input[type='url']"),
            isValid = true;

        $(".form-group").removeClass("has-error");
        for(var i=0; i<curInputs.length; i++){
            if (!curInputs[i].validity.valid){
                isValid = false;
                $(curInputs[i]).closest(".form-group").addClass("has-error");
            }
        }

        if (isValid)
            nextStepWizard.removeAttr('disabled').trigger('click');
    });

    $('div.setup-panel div a.btn-primary').trigger('click');
    
    $("#submit").click( function() {   
    	if (!$("#agree").is(":checked")) {
    		alert("You must agree to the charge.");
    		return false;
    	}
    	alert("Please Note: If you do not see any confirmation that your were either charged or denied, please do NOT click the PAY NOW button AGAIN. Please contact us."); 	
    	$.post('validate.php', {
    		fname : $("#fname").val().trim(), 
    		lname : $("#lname").val().trim(), 
    		email : $("#email").val().trim(), 
    		zip : $("#zip").val().trim(), 
    		ccnumber : $("#ccnumber").val().trim(), 
    		mm : $("#mm").val().trim(), 
    		yy : $("#yy").val().trim(), 
    		cvv : $("#cvv").val().trim(), 
    		amount : <?=$amount?>
    	}, function( msg ) {
    		var success = false;
    		msg = $.parseJSON( msg );
    		var len = msg.length;
    		if ( len > 1 ) {
	    		if ( msg[--len] == 'error' ) {
		    		var str = '';
		    		for (i = 0; i < len; i++) {
		    			str += msg[i] + "\n";
		    		}
		    		alert( str );
		    	} else {
		    		if ( msg[0] == 1 ) {
		            	success = true;
		            } else {
		            	alert( msg[3] );
		            }
		        }
		    } else {
		    	if ( msg == 'true' ) {
		    		success = true;
		    	} else if ( msg == 'false' ) {
		    		alert("There was an error emailing you your download link. Please contact us.");
		    	}
		    	//alert( msg );
		    }
		    if ( success ) {
		    	alert("You have successfuly purchased your cd(s).\nPlease check your email for further instructions.");
		        window.location.href = "index.php";
		    }
    	});
    	return false;
    });
});
</script>
