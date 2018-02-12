<? 
require('header.php');
require('calendar.php');
require('file_save.php');

$user_row = mysql_fetch_assoc(mq("
SELECT user_id, first, last, first_he, last_he, username, gender, user_address1, user_address2,
       user_city, user_state, user_postal, user_country, user_phone, user_code, 
       user_serial, user_photo_id, class_id, class_grade, class_sub, IFNULL(class_grade+0, -1) class_grade_ord, class_teacher, team_id,
       team_name, school_name, school_number, school_logo_id, school_logo_kiosk_id, inst_logo_id, school_type_id, rank_name, rank_image_id, rank_color, user_start_date, kiosk_edit
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

if (isset($_COOKIE['naftoli'])) {
	echo "<input type='hidden' name='user' value='" . print_r($user_row) . "'>";
}

$prize_points = gri('prize_points', 0); //start on this pane

$auction_id = gri('auction_id', -1);
$auction = mysql_fetch_assoc(mq("SELECT auction_name, auction_points_start_date, auction_date, auction_points_trigger_date, auction_run_date FROM auctions WHERE auction_id = $auction_id and kiosk_auction=1 AND auctions.auction_ran = 0 AND (auction_run_date IS NULL OR auction_run_date >= " . unixtojd() . ") AND (school_id IS NULL OR school_id = {$user['school_id']})"));

if (!$auction) {
	$auction_id = -1;
	$auction_points['cur'] = $user_prizes_total = 0;
} 
else {
	//$auction_points = auctionPoints($user['user_id'], $auction);
	//print_r($auction_points);
	//echo gri('prize_id');
	$sqlDate = "select auction_points_start_date from auctions where auction_id = " . $auction_id;
	$resDate = mysql_query($sqlDate);
    $rowDate = mysql_fetch_assoc($resDate);
    $date = $rowDate['auction_points_start_date'];
	
	/*
	$usercode = $user_row['user_code'];
    $mashpiaPoints = floor(mysql_result(mq(totalMarks("WHERE user_id = {$user['user_id']} and mark_date >= $date")), 0));
	$points = header_auction_points(array(
        "user_code" => $usercode,
        "auction_date" => $date
    ));
    
    if (floor($points[$usercode] + $mashpiaPoints) >= 1200) {
        $mashpiaPoints = floor(mysql_result(mq(totalMarks("WHERE user_id = " . $user['user_id'])), 0));
        $points = header_total_points(array("user_code" => $usercode));
    }
	$auction_points['cur'] = floor($mashpiaPoints + $points[$usercode]);
	*/
	
	require 'class.points.php';
	$p = new Points( $user['user_id'] );
	$auction_points['cur'] = $p->getAuctionPoints( $date );

	if($prize_id = gri('prize_id')) {

    $check_prize = mysql_fetch_assoc(mq("SELECT prize_points FROM auctions JOIN auction_prizes USING (auction_id) JOIN prizes_auction USING (prize_id) WHERE auctions.auction_id = $auction_id AND prizes_auction.prize_id = $prize_id AND (min_grade IS NULL OR min_grade <= {$user_row['class_grade_ord']}) AND (max_grade IS NULL OR max_grade >= {$user_row['class_grade_ord']}) AND (max_prize_points IS NULL OR prize_points <= max_prize_points)"));

    if(!$check_prize || gr('remove')) {
      $quantity = 0;
    } else {
      $check_prizes_total = mysql_result(mq("SELECT IFNULL(SUM(prize_points * quantity), 0) total FROM auction_user_prizes JOIN prizes_auction USING (prize_id) WHERE auction_id = $auction_id AND user_id = {$user['user_id']} AND prize_id != $prize_id"), 0);

      $quantity = max(0,
                      min(gri('quantity', 0),
                          floor(($auction_points['cur'] - $check_prizes_total) /
                                $check_prize['prize_points']),
                          65535
                      )
                  );
    }

    if($quantity) {
      mq("INSERT INTO auction_user_prizes (auction_id, user_id, prize_id, quantity) SELECT auction_id, user_id, prize_id, $quantity quantity FROM users JOIN prizes_auction JOIN auction_prizes USING (prize_id) WHERE user_id = {$user['user_id']} AND auction_id = $auction_id AND prize_id = $prize_id ON DUPLICATE KEY UPDATE quantity = VALUES(quantity)");
    } else {
      mq("DELETE FROM auction_user_prizes WHERE auction_id = $auction_id AND user_id = {$user['user_id']} AND prize_id = $prize_id");
    }
    $message = T_('Prize choices saved.');
  }

	$user_prizes_total_sql = "SELECT IFNULL(SUM(prize_points * quantity), 0) total FROM auction_user_prizes JOIN prizes_auction USING (prize_id) WHERE auction_id = $auction_id AND user_id = {$user['user_id']}";
	echo "<input type='hidden' name='user_prizes_total_sql' value='" . $user_prizes_total_sql . "'>\n";
	
	$user_prizes_total = mysql_result(mq("SELECT IFNULL(SUM(prize_points * quantity), 0) total FROM auction_user_prizes JOIN prizes_auction USING (prize_id) WHERE auction_id = $auction_id AND user_id = {$user['user_id']}"), 0);

  $prizes = mq("SELECT prizes_auction.prize_id, prize_name, prize_description, prize_number, prize_points, prize_image_id, quantity FROM auctions JOIN auction_prizes USING (auction_id) JOIN prizes_auction USING (prize_id) LEFT JOIN auction_user_prizes ON (auction_user_prizes.auction_id = auctions.auction_id AND user_id = {$user['user_id']} AND auction_user_prizes.prize_id = prizes_auction.prize_id) WHERE auctions.auction_id = $auction_id and prizes_auction.archived = 0 AND (min_grade IS NULL OR min_grade <= {$user_row['class_grade_ord']}) AND (max_grade IS NULL OR max_grade >= {$user_row['class_grade_ord']}) AND (max_prize_points IS NULL OR prize_points <= max_prize_points) ORDER BY prize_points, prize_number");
  $prize_point_list = mq("SELECT DISTINCT prize_points FROM auctions JOIN auction_prizes USING (auction_id) JOIN prizes_auction USING (prize_id) WHERE auctions.auction_id = $auction_id AND (min_grade IS NULL OR min_grade <= {$user_row['class_grade_ord']}) AND (max_grade IS NULL OR max_grade >= {$user_row['class_grade_ord']}) AND (max_prize_points IS NULL OR prize_points <= max_prize_points) ORDER BY prize_points");
  $user_prizes = mq("SELECT prize_id, prize_name, prize_description, prize_number, prize_points, prize_image_id, quantity FROM auction_user_prizes JOIN prizes_auction USING (prize_id) WHERE auction_id = $auction_id AND user_id = {$user['user_id']} ORDER BY prize_points, prize_number");

  if (isset($_COOKIE['naftoli'])) {
	  //echo prizes query
	  echo "<input type='hidden' name='prize_sql' value='
	  SELECT prizes_auction.prize_id, prize_name, prize_description, prize_number, prize_points, prize_image_id, quantity 
	  FROM auctions JOIN auction_prizes USING (auction_id) 
	  JOIN prizes_auction USING (prize_id) 
	  LEFT JOIN auction_user_prizes 
	  ON (auction_user_prizes.auction_id = auctions.auction_id 
		AND user_id = {$user['user_id']} 
		AND auction_user_prizes.prize_id = prizes_auction.prize_id) 
	  WHERE auctions.auction_id = $auction_id 
	  AND (min_grade IS NULL OR min_grade <= {$user_row['class_grade_ord']}) 
	  AND (max_grade IS NULL OR max_grade >= {$user_row['class_grade_ord']}) 
	  AND (max_prize_points IS NULL OR prize_points <= max_prize_points) 
	  ORDER BY prize_points, prize_number'>";
	  
	  echo "<input type='hidden' name='prize_point_list' value='
	  SELECT DISTINCT prize_points FROM auctions 
	  JOIN auction_prizes USING (auction_id) 
	  JOIN prizes_auction USING (prize_id) 
	  WHERE auctions.auction_id = $auction_id 
	  AND (min_grade IS NULL OR min_grade <= {$user_row['class_grade_ord']}) 
	  AND (max_grade IS NULL OR max_grade >= {$user_row['class_grade_ord']}) 
	  AND (max_prize_points IS NULL OR prize_points <= max_prize_points) 
	  ORDER BY prize_points
	  '>";
  }
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">

<HTML DIR="<?=$dir?>">
<HEAD>
<TITLE><?=T_('Chinese Auction Prizes'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
<LINK href="styles_reset.css" rel="stylesheet" type="text/css">
<LINK href="styles_kiosk.css" rel="stylesheet" type="text/css">
<SCRIPT type="text/javascript" src="jquery.js"></SCRIPT>
<SCRIPT type="text/javascript" src="jquery-ui.js"></SCRIPT>
<SCRIPT type="text/javascript" src="modules/jquery.scroll.js"></SCRIPT>
<LINK href="modules/jquery.scroll.css" rel="stylesheet" type="text/css">
<SCRIPT type="text/javascript" src="modules/jquery.keypad.js"></SCRIPT>
<LINK href="modules/jquery.keypad.css" rel="stylesheet" type="text/css">

<SCRIPT type="text/javascript">
$(function() {
  $.extend($.fn.jScrollPane.defaults, {showArrows:true, scrollbarWidth: 42, arrowSize: 42});
  $('.scroll-pane').jScrollPane();
  $('.keypad').keypad({buttonImage: 'modules/images/keypad_btn.png'});
});

function showOverlay(id) {
  $('#overlay_parent .prize_overlay').hide();
  $('#prize_view_' + id).show();
  $('#wrapper').fadeTo('normal', 0.3);
  $('#overlay_parent').css("top", Math.max(0, (($('body').height()-430)/2)) + 'px').fadeIn('normal');
  $(document).bind("keyup.overlay", function (e) {
    if(e.keyCode==27) {
      $('#close_overlay a').click();
      $(document).unbind("keyup.overlay");
    }
  });
};

function updatePoints(price, el, origQuantity) {
  $('.prize_price_total', el.parentNode.parentNode).find('span').text(el.value*price).end().fadeIn('normal');
  $('.prize_price_left span', el.parentNode.parentNode).text((<?=floatval($auction_points['cur'] - $user_prizes_total)?>+price*origQuantity-el.value*price).toFixed(2));
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
.pane_item { float:left; width:100px; height:133px; margin:2px 0 0 2px; padding: 5px; font-size:.5em; font-weight:normal; /*background:#666;*/ position:relative; overflow: hidden;}
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

  <div id="overlay_parent" style="position: fixed; z-index: 50; width: 100%; display: none;">
    <div class="overlay" style="margin: auto;">
      <div class="close" id="close_overlay">
          <A HREF="#" onClick="$('#overlay_parent').fadeOut('normal'); $('#wrapper').fadeTo('normal', 1); if(document.getElementById('focus')) document.getElementById('focus').focus(); return false;"><img src="images/apple-close.png" alt="<?=T_('Close')?>"></A>
      </div>
      <?if($auction_id != -1) while($row = mysql_fetch_assoc($prizes)):?>
        <FORM action="auction.php" method="post" accept-charset="UTF-8">
          <div class="prize_overlay" id="prize_view_<?=$row['prize_id']?>" style="display: none;">
            <div class="prize_image"><?=!is_null($row['prize_image_id']) ? linkImgFile($row['prize_image_id'], 300, 300) : ''?></div>
            <div class="prize_text">
              <div class="pane_title"><?=es($row['prize_name'])?></div>
              <div class="prize_info"><?=$row['prize_description']?></div>
            </div>
            <div class="prize_bottom">
              <div class="prize_price">
                <div class="prize_price_miles"><?=T_('Miles Each')?>: <span><?=$row['prize_points']?></span></div>
                <div class="prize_price_total" <?if(!$row['quantity']):?>style="display: none;"<?endif;?>><?=T_('Total')?>: <span><?=$row['prize_points']*$row['quantity']?></span></div>
                <div class="prize_price_left"><?=T_('Miles Left')?>: <span><?=floatval($auction_points['cur'] - $user_prizes_total)?></span></div>
              </div>
              <?if($auction_points['cur'] >= $row['prize_points'] || $row['quantity']):?>
              <div class="input">
                <input type="text" name="quantity" class="prize_qty keypad" value="<?=$row['quantity']?>" onChange="this.value = Math.max(0, Math.min(parseInt('0'+this.value, 10), <?=intval(($auction_points['cur'] - $user_prizes_total+$row['prize_points']*$row['quantity'])/$row['prize_points'])?>)); updatePoints(<?=$row['prize_points']?>, this, <?=intval($row['quantity'])?>);">
                <input type="hidden" name="auction_id" value="<?=$auction_id?>">
                <input type="hidden" name="prize_id" value="<?=$row['prize_id']?>">
                <input type="hidden" name="prize_points" value="">
              </div>
              <div class="button_small" <?if($row['quantity']):?>style="clear: both;"<?endif;?>>
                <?if($row['quantity']):?>
                  <INPUT type="submit" name="remove" value="<?=T_('Remove')?>"> &nbsp;
                <?endif;?>
                <INPUT type="submit" value="<?=T_('Save')?>">
              </div>
              <?else:?>
              <?endif;?>
            </div>
          </div>
        </FORM>
      <?endwhile;?>
    </div>
  </div>
<?@mysql_data_seek($prizes, 0);?>
  <div id="wrapper">
    <div id="header">
      <div class="org">
        <div class="nav">
          <ul>
            <li class="icon_back"><a href="auction_home.php"><?=T_('Back')?></a>
            <li class="icon_home"><a href="kiosk.php"><?=T_('Home')?></a></li>
            <li class="icon_logout"><a href="logout.php?n=kiosk.php"><?=T_('Logout')?></a></li>
          </ul>
        </div>
        <div class="org_photo">
          <?=!is_null($user_row['school_logo_kiosk_id']) ? linkImgFile($user_row['school_logo_kiosk_id'], null, 100) : (!is_null($user_row['school_logo_id']) ? linkImgFile($user_row['school_logo_id'], null, 100) : '')?>
        </div>
        <?=T_('Base')?>: #<?=$user_row['school_number']?><BR>
        <?=es($user_row['school_name'])?><BR>
        <?=es($user_row['rank_name'])?> <?=es($user_row['first'])?> <?=es($user_row['last'])?> <!--(<?=es($user_row['username'])?>)--> <?=es($user_row['first_he'])?> <?=es($user_row['last_he'])?>
      </div>
      <noscript><p class="js_alert">Notice: You have javascript disabled.<br>Some parts of the site will not function without javascript.</p></noscript>
    </div>

    <div id="main" class="auction">
<?if($auction_id == -1):?>
    <?=T_("Error. Can't find auction.")?>
    <div class="button button_icons">
      <div>
        <a class="icon_back_to" href="auction_home.php"><?=T_('Back to Auctions')?></a>
      </div>
    </div>
<?else:?>
      <div id="page_title"><?=es(sprintf(T_('%s'), $auction['auction_name']))?></div>
      <div id="auction_container">
        <div id="tabs">
          <ul class="tabs_main">
            <li><a class="tab_price" href="#"><?=T_('Prices')?></a></li>
            <li><a class="tab_cat" href="#"><!--<?=T_('Categories')?>--></a></li>
          </ul>
          <div class="clear"></div>
          <div class="tabs_pane">
            <div class="tabs">
              <ul class="prices tab">
                <?while($row = mysql_fetch_assoc($prize_point_list)):?>
                  <li class="price"><a href="#" onClick="$('.prizes .pane_items>div').hide(); $('#prize_points_<?=$row['prize_points']?>').show().parent().jScrollPane(); $('#overlay_parent input[name=prize_points]').val('<?=$row['prize_points']?>'); return false;"><?=$row['prize_points']?></a></li>
                <?endwhile;?>
              </ul>
            </div>
            <div class="pane prizes">
              <div class="pane_items scroll-pane">
                <?if(mysql_num_rows($prizes)):?>
                <?$old_prize_points = -1;?>
                <?while($row = mysql_fetch_assoc($prizes)):?>
                  <?if($row['prize_points'] != $old_prize_points):?>
                    <?if($old_prize_points != -1):?></div><?endif;?>
                    <div id="prize_points_<?=$row['prize_points']?>" <?if(!(($prize_points && $row['prize_points'] == $prize_points) || (!$prize_points && $old_prize_points == -1))):?>style="display: none;"<?endif;?>>
                      <div class="pane_title"><?=sprintf(T_('Prizes for %s Miles'), $row['prize_points'])?></div>
                    <?$old_prize_points = $row['prize_points'];?>
                  <?endif;?>
                  <div class="pane_item" onClick="showOverlay(<?=$row['prize_id']?>);">
                    <?if($row['quantity']):?><div class="badge"><?=$row['quantity']?></div><?endif;?>
                    <div class="pane_item_image"><?=!is_null($row['prize_image_id']) ? linkImgFile($row['prize_image_id'], 100, 100) : ''?></div>
                    <div class="pane_item_title"><?=es($row['prize_name'])?></div>
                  </div>
                <?endwhile;?>
                </div>
                <?else:?>
                  <?=T_("This auction has no prizes available. Perhaps you don't have enough points.")?>
                <?endif;?>
              </div>
            </div>
          </div>
          <div class="clear"></div>
        </div>
        <div class="cart">
          <div class="cart_title">Your Cart</div>
          <div class="cart_items scroll-pane">
            <?if(mysql_num_rows($user_prizes)):?>
              <?while($row = mysql_fetch_assoc($user_prizes)):?>
                <div class="cart_item" onClick="showOverlay(<?=$row['prize_id']?>);">
                  <div class="cart_item_image">
                    <div class="badge"><?=$row['quantity']?></div>
                    <?=!is_null($row['prize_image_id']) ? linkImgFile($row['prize_image_id'], 48, 48) : ''?>
                  </div>
                </div>
              <?endwhile;?>
            <?else:?>
              <div class="cart_empty">
                <?=T_('Your cart is empty.')?>
              </div>
            <?endif;?>
          </div>
          <div class="cart_bottom">
            <div class="cart_total"></div>
          </div>
        </div>
        <div class="clear"></div>
		
        <div class="bottom">
			<? if (floor($auction_points['cur']) < 1200) : ?>
			<span style='font-size: 16px;'>Please Note: Only points from this year are included - not from previous years<br /></span>
			<? endif; ?>
			<span style="white-space: nowrap;">
				<?=T_('Miles available')?>: <?=floor($auction_points['cur'])?>
			</span> 
			&bull;
			<span style="white-space: nowrap;">
				<?=T_('Miles used')?>: <?=$user_prizes_total?>
			</span> 
			&bull;
			<span style="white-space: nowrap;">
				<?=T_('Miles left')?>: <?=floor($auction_points['cur'] - $user_prizes_total)?>
			</span>
        </div>
		
      </div>
<?endif;?>
    </div>
    <div id="footer">
      <div class="footer_logo"></div>
      <div class="footer_logout"></div>
    </div>

  </div>
</body>
</html>
