<? 
	include_once ("../header.php");
	require_once('../file_save.php');
	require_once('../calendar.php');

$user_id = $user['user_id'];


// ***** CHECKOUT ***** //
$checkout = gr("checkout", "");
if ($checkout != "") {	
	$sql = "UPDATE store_purchases SET prize_shipped=1 WHERE user_id=" . $user_id . " AND prize_shipped=0";
	mq($sql);	
}
// ***** CHECKOUT ***** //

// ***** PRIZE ID ***** //
$prize_id = gri('prize_id', 0);
// ***** PRIZE ID ***** //

// ***** QUANTITY ***** //
$quantity = gri('quantity', 0);
// ***** QUANTITY ***** //

// ***** TOTAL PRICE ***** //
$prize_price = gri('prize_price', 0);
// ***** TOTAL PRICE ***** //

// ********** INSERT or UPDATE CART ITEMS ********** //
if ($prize_id > 0) {
	$sql = "SELECT store_purchase_id  FROM store_purchases WHERE user_id=" . $user_id . " AND prize_id=" . $prize_id . " AND prize_shipped=0";
	$query = mysql_query($sql);
	$row = mysql_fetch_assoc($query);
	
	if ($row['store_purchase_id'] > 0) {	
	
		if ($quantity > 0) {
			$sql = "UPDATE store_purchases SET prize_quantity=" . $quantity . " WHERE store_purchase_id=" . $row['store_purchase_id'];
			mq($sql);
		}
		else {
			$sql = "DELETE FROM store_purchases WHERE store_purchase_id=" . $row['store_purchase_id'];
			mq($sql);		
		}
	} 
	else {
		$sql = "INSERT INTO store_purchases (user_id, prize_id, prize_shipped, prize_points, prize_quantity) VALUES(";
		$sql = $sql . $user['user_id'] . ", " . $prize_id . ", 0, " . $prize_price . ", " . $quantity . ")";
		mq($sql);
	}
	
}
// ********** INSERT or UPDATE CART ITEMS ********** //

// ********** USER STORE MILES ********** //
$user_store_miles = floatval(mysql_result(mq(totalMarks("WHERE user_id = {$user['user_id']} AND mark_date >= " . chaiElul())), 0));
$total_spent = 0;
$sql = "SELECT prize_points, prize_quantity FROM store_purchases WHERE user_id=" . $user_id;
$query = mysql_query($sql);
while ($row = mysql_fetch_assoc($query)) {
	$total_spent = $total_spent+ ($row['prize_points'] * $row['prize_quantity']);
}	
$user_store_miles = $user_store_miles - $total_spent;
// ********** USER STORE MILES ********** //

// ********** CART ITEMS ********** //
$cart_items = array();
$sql = "SELECT cp.prize_id, ps.prize_name, ps.prize_image_id, cp.prize_quantity FROM store_purchases AS cp JOIN prizes_store AS ps USING (prize_id) WHERE user_id=" . $user_id . " AND prize_shipped=0";
$query = mysql_query($sql);
$num_purchases = mysql_num_rows($query);
while ($row = mysql_fetch_assoc($query)) {
	array_push($cart_items, array($row['prize_id'], $row['prize_name'], $row['prize_image_id'], $row['prize_quantity']));
}
// ********** CART ITEMS ********** //

// ********** USER ********** //
$user_row = mysql_fetch_assoc(mq("
SELECT user_id, first, last, first_he, last_he, username, gender, user_address1, user_address2,
       user_city, user_state, user_postal, user_country, user_phone,
       user_serial, user_photo_id, class_id, class_grade, class_sub, IFNULL(class_grade+0, -1) class_grade_ord, class_teacher, team_id,
       team_name, school_id, school_name, school_number, school_logo_id, school_logo_kiosk_id, inst_logo_id, school_type_id, rank_name, rank_image_id, rank_color, user_start_date, kiosk_edit
FROM users
     LEFT JOIN schools USING (school_id)
     LEFT JOIN institutions USING (inst_id)
     LEFT JOIN classes USING (school_id, class_id)
     LEFT JOIN teams USING (school_id, team_id)
     LEFT JOIN (SELECT user_id, MAX(rank_ord) rank_ord FROM rank_marks WHERE user_id = {$user['user_id']} GROUP BY user_id) rank USING (user_id)
     LEFT JOIN ranks USING (rank_ord)
WHERE user_id = {$user['user_id']}
ORDER BY class_grade, class_sub, last, first
"));
// ********** USER ********** //

// ***** SCHOOL ID ***** //
$school_id = $user_row['school_id'];
// ***** SCHOOL ID ***** //

$prize_points = 0;

function get_store_prize_points($school_id) {
	global $prize_points;
	
	$echo_string = "";
	
	//$sql = "SELECT prize_points FROM prizes_store WHERE (school_id=" . $school_id . " OR isnull(school_id)) AND prize_available > 0  GROUP BY prize_points ORDER BY prize_points";	
	$sql = "SELECT prize_points FROM prizes_store WHERE (school_id=" . $school_id . " OR isnull(school_id)) AND prize_current=1 GROUP BY prize_points ORDER BY prize_points";	
	//echo "<input type='hidden' name='SQL 1' value='" . $sql . "'>\n";		
	$query = mysql_query($sql);	
	
	$row_num = 0;
	while ($row = mysql_fetch_assoc($query)) {
		$row_num++;
		
		if ($row_num == 1) 
			$prize_points = $row['prize_points'];
		
		$echo_string = $echo_string . "\n<li class='price'>\n";
		$echo_string = $echo_string . "\t\t<a href='#' onClick=\"$('.prizes .pane_items>div').hide(); $('#prize_points_" .  $row['prize_points'] . "').show().parent().jScrollPane(); $('#overlay_parent input[name=prize_points]').val('" . $row['prize_points'] . "'); return false;\">\n\t\t\t";
		$echo_string = $echo_string . $row['prize_points'];
		$echo_string = $echo_string . "\n\t\t</a>\n";
		$echo_string = $echo_string . "</li>\n";
	}
	
	echo $echo_string;
}

function get_prizes_store($school_id, $prize_points) {	
	global $user_id;
	
	$sql = "SELECT ps.prize_id, ps.prize_points, ps.prize_name, ps.prize_image_id, sp.prize_quantity FROM prizes_store AS ps LEFT JOIN store_purchases AS sp ON user_id=" . $user_id ." AND ps.prize_id=sp.prize_id AND sp.prize_shipped=0 WHERE (school_id=" . $school_id . " OR isnull(school_id)) AND ps.prize_current=1 ORDER BY prize_points";		
	$query = mysql_query($sql);	
	$num_rows = mysql_num_rows($query);
	
	$row_num = 0;
	$prev_price1 = "";
	$prev_price2 = "";
	
	while ($row = mysql_fetch_assoc($query)) {
		$row_num++;
		
		$prev_price1 = $row['prize_points'];

		if ($prev_price1 != $prev_price2 && $prev_price2 != "")
			echo "\t</div>\n";
		
		if ($row['prize_points'] != $prev_price2) {
			if ($prev_price2 == "") 
				echo "\n\t<div id='prize_points_" . $row['prize_points'] . "'>\n";
			else
				echo "\n\t<div id='prize_points_" . $row['prize_points'] . "' style='display:none;'>\n";	

			echo "\t\t<div class='pane_title'>Prizes for " . $row['prize_points'] . " Dollars</div>\n";
		}
		
		echo "\t\t<div class='pane_item' onClick='show_overlay(" . $row['prize_id'] . ");'>\n";

		if ($row['prize_quantity'] > 0)
			echo "<div class='badge'>" . $row['prize_quantity'] . "</div>";
		
		echo "\t\t\t<div class='pane_item_image'><img SRC='/file_view.php?id=" . $row['prize_image_id'] . "' WIDTH='100' HEIGHT='100'  ALT=''></div>\n";
		echo "\t\t\t<div class='pane_item_title'>" . $row['prize_name'] . "</div>\n";
		echo "\t\t</div>\n";
		
		if ($row_num == $num_rows)
			echo "\t</div>\n";
		
		$prev_price2 = $row['prize_points'];
	}
	
}

$title = "Store";
include("includes/header.php");
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<HTML DIR="<?=$dir?>">
	<HEAD>
		<TITLE><?=T_('Store Prizes'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
		<LINK href="../styles_reset.css" rel="stylesheet" type="text/css">
		<LINK href="../styles_kiosk.css" rel="stylesheet" type="text/css">
		<SCRIPT type="text/javascript" src="jquery.js"></SCRIPT>
		<SCRIPT type="text/javascript" src="jquery-ui.js"></SCRIPT>
		<SCRIPT type="text/javascript" src="../modules/jquery.scroll.js"></SCRIPT>
		<LINK href="../modules/jquery.scroll.css" rel="stylesheet" type="text/css">
		<SCRIPT type="text/javascript" src="../modules/jquery.keypad.js"></SCRIPT>
		<LINK href="../modules/jquery.keypad.css" rel="stylesheet" type="text/css">

		<SCRIPT type="text/javascript">
			var prize_view = "";
			var old_quantity = 0;
			var num_purchases = <?=$num_purchases;?>;
			var user_store_miles = <?=$user_store_miles;?>;
			
			$(function() {
				$.extend($.fn.jScrollPane.defaults, {showArrows:true, scrollbarWidth: 42, arrowSize: 42});
				$('.scroll-pane').jScrollPane();
				$('.keypad').keypad({buttonImage: '../modules/images/keypad_btn.png'});
			});		
			
			function show_overlay(prize_id) {
				var form_name = "form" + prize_id;

				if (document.forms[form_name].elements["quantity"].value == "")
					old_quantity = 0;
				else
					old_quantity = document.forms[form_name].elements["quantity"].value;
			
				prize_view = "prize_view_" + prize_id;
				
				$('#wrapper').fadeTo('normal', 0.3);				
				document.getElementById(prize_view).style.display = "block";				
				$('#overlay_parent').css("top", Math.max(0, (($('body').height()-430)/2)) + 'px').fadeIn('normal');				
			}
			
			function close_window() {
				if (prize_view != "")
					document.getElementById(prize_view).style.display = "none";
				
				if (num_purchases > 0)
					document.getElementById("checkout_overlay").style.display = "none";
				
				document.getElementById("overlay_parent").style.display = "none";
				$('#wrapper').fadeTo('normal', 1.0);
			}
			
			function formatCurrency(num) {
				num = num.toString().replace(/\$|\,/g,'');
				
				if(isNaN(num))
					num = "0";
					
				sign = (num == (num = Math.abs(num)));
				num = Math.floor(num*100+0.50000000001);
				cents = num%100;
				num = Math.floor(num/100).toString();
				
				if(cents<10)
					cents = "0" + cents;
					
				for (var i = 0; i < Math.floor((num.length-(1+i))/3); i++)
					num = num.substring(0,num.length-(4*i+3))+','+num.substring(num.length-(4*i+3));
					
				return (((sign)?'':'-') + num + '.' + cents);
			}
			
			function update_points(quantity, price, hidden_field, id, prize_available) {
				var quantity_div = "quantity_" + id;
				var form_name = "form" + id;	
				
				var max_value = Math.floor(document.getElementById(hidden_field).value / price);
								
				if (quantity.value > max_value) {
					alert("You only have enough money to purchase " + max_value);
					quantity.value = max_value;
				}
					
				var total = quantity.value * price;
				user_store_miles_left = formatCurrency(user_store_miles - total);				
				
				$('.prize_price_total', quantity.parentNode.parentNode).find('span').text(total).end().fadeIn('normal');
				$('.prize_price_left', quantity.parentNode.parentNode).find('span').text(user_store_miles_left).end().fadeIn('normal');
				
				document.getElementById(quantity_div).value = quantity.value;
			}
			
			function save_quantity(prize_id) {
				prize_view = "prize_view_" + prize_id;
				
				var form_name = "form" + prize_id;
				var quantity = document.forms[form_name].elements["quantity"].value;
				
				if (quantity > 0 || (quantity == 0 && old_quantity > 0)) 
					document.forms[form_name].submit();
				else
					close_window();
			}
			
			function show_checkout() {
				$('#wrapper').fadeTo('normal', 0.3);
				document.getElementById("checkout_overlay").style.display = "block";
				$('#overlay_parent').css("top", Math.max(0, (($('body').height()-430)/2)) + 'px').fadeIn('normal');	
			}
			
			function buy_now() {
				document.forms["checkout"].submit();
			}
		</SCRIPT>
		
		<STYLE type="text/css">
			.auction {text-align:center;}
			#auction_container { background: url(images/auction_bg_boxes.png) no-repeat; margin:10px 20px 0 22px; width:982px; height:488px;}

			.prices, .categories { margin-bottom:10px; float:left; }
			.price, .category { width:200px; height:48px; /*background:#CCC;*/ margin-top:10px;}
			.pane.prizes { float:left; /*background:#CCC;*/ width:500px; height:445px; margin:-20px 0 10px 30px;}


			#tabs { float:left; width:748px; margin:7px; /*padding:5px; background:#999; margin:10px 30px;*/}

			.tabs {  margin:5px 0px 0px; width:200px; float:left;overflow:hidden; font-size:.75em;}

			.tabs_main { width:190px; margin-top:3px; margin-left:8px;}
			.tabs_main li{ float:left; font-size:.5em; /*margin-left:-5px;width:100px;*/ line-height:27px; }
			.tabs_main a { width:93px; height:27px; display:block; border-bottom:2px solid #b3d3c1;}
			.tabs_main a.current { background: url(images/auction_tabs.png) no-repeat; border:none; height:29px;}
			.tabs_main a.tab_cat { background-position:right;}

			.pane_items { height:445px; }
			.pane_item { float:left; width:100px; height:127px; margin:2px 0 0 2px; padding: 5px; font-size:.5em; font-weight:normal; /*background:#666;*/ position:relative; overflow: hidden;}
			.pane_item, .cart_item { cursor:pointer; }
			.pane_item .badge { position:absolute; top:-1px; right:-3px; font-size:.95em; background:url(images/cart_badge.png) no-repeat ; width:28px; height:28px; text-align:center; text-shadow:0 -1px 0 #5f801d; color:#709926; line-height:26px; padding-left:1px;}
			.pane_item_image { width:100px; height:108px; background:url(images/shadow_100.png) 0px 100px no-repeat; }
			.pane_item_image img, .cart_item_image img, .prize_image img { display: block; background-color: white; }
			.pane_item_title {}

			.jScrollPaneContainer { margin-top:0;}

			.overlay { 
			  position: relative;
			  width:640px;
			  min-height:300px;
			  padding: 10px;
			  background-color:#333;
			  color: white;
			  background-image:url(images/Camouflage-Background-Green.jpg);
			  background-position:50% 50%;
			  /*border:1px solid #666;*/

			  /* CSS3 styling for latest browsers*/
			  -moz-box-shadow:0 0 90px 5px #000;
			  -webkit-box-shadow: 0 0 90px #000;
			   display: block;
			}

			/* close button positioned on upper right corner */
			.overlay .close {
			  background-image:url(images/apple-close.png);
			  float: right;
			  margin:5px;
			  cursor:pointer;
			  height:28px;
			  width:28px;
			}

			.contentWrap { color:#EEEEEE; text-shadow:0 -1px 0 #000000;}

			.prize_overlay .pane_title { border-bottom:1px dotted #454545; }
			.prize_text, .prize_bottom { margin-left: 310px; width: 330px; }
			.prize_info { font-size:.8em; font-weight:normal; margin-top:5px; text-align:left;}
			.prize_price { text-align:left; float: left; /*margin-top:10px;*/}
			.prize_image { float:left;}
			.prize_image img { display: block; }
			.prize_bottom { position: absolute; bottom: 10px; font-size:.8em; border-top:1px solid #454545; padding-top:5px; }
			.prize_bottom .input {float:right;}
			.button_small {clear:right; text-align: right;}
			.prize_price_total, .prize_price_left { font-size:.75em;}


			.cart { float:left; width:200px; margin-left:9px; margin-top:20px; /*background:#CCC;*/ font-size:.8em;}
			.cart_title { position:relative; margin-bottom:8px;}
			.cart_items { margin: 0 5px; height:415px; font-size:.8em; font-weight:normal; text-align:left;}
			.cart_items .cart_empty {  text-align:center;}
			.cart_item { /*border-bottom:1px dotted #999;*/ height:53px; width:48px; margin-left:11px; margin-top:11px; float:left;background:url(images/shadow_48.png) 0px 48px no-repeat;}
			.cart_item_image {  position:relative;}
			.cart_item .badge { position:absolute; top:-8px; right:-8px; font-size:.7em; background:url(images/cart_badge.png) no-repeat ; width:28px; height:28px; text-align:center; text-shadow:0 -1px 0 #5f801d; color:#709926; line-height:26px; padding-left:1px;}
			.cart_item_text {}
			.cart_item_text div { margin-top:3px;}
			.cart_item_name {}
			.cart_item_price {font-size:.8em;}
			.cart_bottom {}
			.cart_total {}
			.cart_checkout {}

			.button_small div, .tabs li, .button_small input {background:url(images/buttons_small_bg.png) no-repeat top; width:142px; height:54px; font-size:.8em; float:left;}
			.button_small div, .button_small input { float:none;}
			.button_small div:hover, .tabs li:hover {background-position:bottom;}
			.button_small div a, .tabs li a { display:block; width:100%; height:100%; padding-top:17px;}
			.button_small input { border: none; color: white; cursor: pointer; }

			.tabs .prices { margin-left:23px; height:425px;}
			.tabs .prices li {background:url(images/buttons_tiny_bg.png) no-repeat top; width:79px; }
			.tabs .prices li:hover {background-position:bottom; }
			.tabs .categories .scroll-pane { margin-left:5px; width:190px; height:425px; }
			.tabs .categories .scroll-pane li {  }
			.tabs .categories li { float:none; margin:0 auto -10px; }
			.tabs li { margin-bottom:-10px; margin-top:0;}

			.bottom { margin-left:100px; margin-top:20px;}
			/*.ui-tabs .ui-tabs-hide {display:none !important;}*/
			.cart_balance { float:left; margin-top:12px; margin-left:10px;}
			.cart_pop_bottom { font-size:.8em; border-top:1px solid #454545; padding:0 5px; }

			.checkout_overlay { }
			.checkout_overlay .pane_title { border-bottom:1px dotted #454545; font-size:.85em; margin:10px; padding-bottom:5px;}
			.checkout_overlay .pane_text { font-size:.75em; padding:0 80px;}
		</STYLE>	
	</HEAD>
	
	<body class="lgreen">

		<input type="hidden" name="prize_id_number" id="prize_id_number">
		<input type="hidden" name="prize_quantity" id="prize_quantity">
		
		<div id="overlay_parent" style="position:fixed; z-index:50; width:100%; display:none; top:100px;">
		
			<div class="overlay" style="margin: auto;">
				<div class="close" id="close_overlay">
					<A href="#" onClick="close_window();">
						<img src="images/apple-close.png" alt="<?=T_('Close')?>">
					</A>
				</div> 
			
				
<!-- ****************************** PRIZE OVERLAY ****************************** -->
<?php
$row_num = 0;
$sql = "SELECT ps.*, cp.prize_quantity AS quantity FROM prizes_store AS ps LEFT JOIN store_purchases AS cp ON user_id=" . $user_id . " AND ps.prize_id=cp.prize_id AND cp.prize_shipped=0";
$query = mysql_query($sql);
while ($prizes = mysql_fetch_assoc($query)) {
	$hidden_name = "hidden" . $row_num;
?>
				<form action="store.php" method="post" name="form<?=$prizes['prize_id'];?>" id="form<?=$prizes['prize_id'];?>" accept-charset="UTF-8">
				
					<input type="hidden" name="prize_price" id="prize_price" value="<?=$prizes['prize_points'];?>">
				
					<div class="prize_overlay" id="prize_view_<?=$prizes['prize_id']?>" style="display: none;">
					
						<div class="prize_image">
							<img SRC="/file_view.php?id=<?=$prizes['prize_image_id'];?>" width="300" height="300"  alt="">
						</div>
						
						<div class="prize_text">
							<input type="hidden" name="prize_id" id="prize_id" value="<?=$prizes['prize_id'];?>">
							<div class="pane_title"><?=$prizes['prize_name'];?></div>
							<div class="prize_info"></div>
						</div>

						<div class="prize_bottom">
						
							<div class="prize_price">
								<div class="prize_price_miles">Miles Each: <span><?=$prizes['prize_points'];?></span></div>
								<div class="prize_price_total">Total: <span>0</span></div>
								<div class="prize_price_left">Miles Left: <span><?=number_format($user_store_miles, 2, '.', ',');?></span></div>
								<input type="hidden" name="<?=$hidden_name;?>" id="<?=$hidden_name;?>" value="<?=$user_store_miles;?>">
							</div>
							
							<div class="input">
								<input type="text" name="quantity" class="prize_qty keypad" onChange="update_points(this, <?=$prizes['prize_points'];?>, '<?=$hidden_name;?>', <?=$prizes['prize_id'];?>, <?=$prizes['prize_available'];?>);" value="<?=$prizes['quantity'];?>">
								<input type="hidden" name="quantity_<?=$prizes['prize_id'];?>" id="quantity_<?=$prizes['prize_id'];?>">								
							</div>
							
							<div class="button_small">
								<input type="button" value="Save" onClick="save_quantity(<?=$prizes['prize_id'];?>);">
							</div>
						</div>
						
					</div> <!-- prize_overlay -->	

				</form>
<?
}
?>
<!-- ****************************** PRIZE OVERLAY ****************************** -->				


<!-- ****************************** CHECKOUT OVERLAY ****************************** -->
<? if (count($cart_items) > 0) { 

	echo "<form action='store_withdraw.php' method='post' name='checkout' id='checkout' accept-charset='UTF-8'>\n";
	echo "<input type='hidden' name='checkout' id='checkout' value='true'>\n";
	echo "<div class='checkout_overlay' name='checkout_overlay' id='checkout_overlay' style='position:fixed; z-index:50; width:100%; display:none; top:100px;'>";
	
	for ($cntr = 0; $cntr < count($cart_items); $cntr++) {  ?>
	
						<div class="pane_item">
							<div class="pane_item_image">							
								<div class="badge">
									<?=$cart_items[$cntr][3];?>
								</div>							
								<img src="/file_view.php?id=<?=$cart_items[$cntr][2];?>" width="100" height="100" />
							</div>
							<div class="pane_item_title">
								<?=$cart_items[$cntr][1];?>
							</div>							
						</div>
	
<?	}	

	echo "<div class='clear'></div>\n";
	echo "<div class='button_small'>\n<div>\n";
	echo "<a class='cart_buynow' href='#' onClick='buy_now();'>Buy Now</a>\n";
	echo "</div>\n";
	echo "</div>\n";
	echo "</div>\n";
	echo "</form>\n";
	
} ?>	
<!-- ****************************** CHECKOUT OVERLAY ****************************** -->

			</div> <!-- overlay -->
			
			
		</div> <!-- overlay_parent -->
	
		<div id="overlay_checkout" style="position:fixed; z-index:50; width:100%; display:none; top:100px;">
		</div>

		<div id="wrapper">

			<div id="header">
				<?php include("includes/topbar.php"); ?>
			</div>		
		
			<div id="main" class="auction">
			
				<div id="page_title">
					Store
				</div>
				
				<div id="auction_container">
					
					<div id="tabs">
						
						<ul class="tabs_main">
							<li>
								<a class="tab_price" href="#"><?=T_('Prices')?></a>
							</li>
						</ul>
				
						<div class="clear">
						</div>
						
						<div class="tabs_pane">
						
							<div class="tabs">
					
								<ul class="prices tab">
                
									<? get_store_prize_points($school_id); ?>							
							
								</ul>
						
							</div> <!-- tabs -->
						
							<div class="pane prizes">
							
								<div class="pane_items scroll-pane">
								
									<? get_prizes_store($school_id, $prize_points); ?>
									
								</div> <!-- pane_items scroll-pane -->
								
							</div> <!-- pane prizes -->
							
						</div> <!-- tabs_pane --> 
						
					</div> <!-- tabs -->
					
					<div class="cart">
						<div class="cart_title">
							Your Cart
						</div>	

						<div class="cart_items scroll-pane">
						
<!-- ****************************** CART ITEMS ****************************** -->
<?php
					if (count($cart_items) > 0) {
						for ($cntr = 0; $cntr < count($cart_items); $cntr++) { 
?>
							<div class="cart_item" onClick="show_overlay(<?=$cart_items[$cntr][0];?>)">
								<div class="cart_item_image">

									<div class="badge">
										<?=$cart_items[$cntr][3];?>
									</div>

									<?=linkImgFile($cart_items[$cntr][2], 48, 48);?>
								</div>
							</div>
<?php							
						}
					}
					else { ?>
							<div class="cart_item">
								<div class="cart_item_image">
									Your cart is empty
								</div>
							</div>

<? } ?>						
<!-- ****************************** CART ITEMS ****************************** -->

							<div class="clear">
							</div>
						
							<div class="cart_bottom">
								<div class="cart_total">
								</div>
								
							<?php if (count($cart_items) > 0) { ?>
								<div class="cart_checkout button_small">
									<div>
										<a href="#" onClick="show_checkout();">Checkout</a>
									</div>
								</div>
							<? } ?>
							
								<div class="cart_checkout button_small">
									<div>
										<a href="store_withdraw.php"">Withdraw</a>
									</div>
								</div>
							
								
							</div>
						
						
					</div> <!-- cart -->
					
					<div class="clear">
					</div>
													
				</div> <!-- auction_container -->
				
			</div> <!-- main -->			
	
			<div id="footer">
				<div class="footer_logo"></div>
				<div class="footer_logout"></div>
			</div>
	
		</div> <!-- wrapper -->
		
	</body>
	
</html>
