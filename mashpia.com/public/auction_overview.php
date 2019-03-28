<? require('header.php'); ?>
<?
require_once('file_save.php');

$user_row = mysql_fetch_assoc(mq("
SELECT user_id, first, last, first_he, last_he, username, gender, user_address1, user_address2,
       user_city, user_state, user_postal, user_country, user_phone,
       user_serial, user_photo_id, class_id, class_grade, class_sub, class_teacher, team_id,
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
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN"
   "http://www.w3.org/TR/html4/strict.dtd">
<HTML DIR="<?=$dir?>">
<HEAD>
<TITLE><?=T_('Auctions'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
<LINK href="styles_reset.css" rel="stylesheet" type="text/css">
<LINK href="styles_kiosk.css" rel="stylesheet" type="text/css">
<SCRIPT type="text/javascript" src="jquery.js"></SCRIPT>
<SCRIPT type="text/javascript" src="jquery-ui.js"></SCRIPT>
<SCRIPT type="text/javascript" src="modules/easySlider1.7.js"></SCRIPT>
<SCRIPT type="text/javascript" src="modules/jquery.scroll.js"></SCRIPT>
<LINK href="modules/jquery.scroll.css" rel="stylesheet" type="text/css">
<SCRIPT type="text/javascript">
$(function () {
  $("#slider").easySlider({
    numeric: true,
    controlsBefore: '<div class="page_dots">',
    controlsAfter:  '<\/div>'
  });

  $('.scroll-pane').jScrollPane({showArrows:true, scrollbarWidth: 42, arrowSize: 42});
});
</SCRIPT>
<style>
.overview::-webkit-scrollbar {
    width: 0px;  /* remove scrollbar space */
    background: transparent;  /* optional: just make scrollbar invisible */
}
</style>
</HEAD>
<body class="lgreen">
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

    <div id="main">
      <div id="page_title"><?=T_('Auction Overview')?></div>

      <div class="three_column padding_top">
        <div class="content">
          <div id="slider" style='padding: 15px 0px;'>
            <ul class="overview" style="height: 400px; overflow: auto; overflow-x: hidden; box-sizing: border-box;">

              <li>
                <div class="slider_title"><?=T_('About Miles')?></div>
                <div class="mainbox">
                  <div class="col2_image iconl_mileage"></div>
                  <div class="scroll-pane">
                    <div class="col2_text">
                      <p>
                        <span style="font-size: 150%;"><?=T_('Miles buy raffle tickets for a chance to win.')?></span><br>
                        <?=T_('You can buy raffle tickets with as few as 100 miles!')?>
                      </p>
                      <p>
                        <br><span style="font-size: 150%;"><?=T_('New Miles may be scanned until Rosh Chodesh Sivan.')?></span>
                      </p>
                      <p>
                        <br><span style="font-size: 150%;"><?=T_("Mitzvos don't expire and neither do miles.")?></span><br>
                        <?=T_('Earn at least 1200 miles during the year and reuse miles of all previous years.')?>
                      </p>
                    </div>
                  </div>
                </div>
              </li>

              <li>
                <div class="slider_title"><?=T_('Buying Tickets')?></div>
                <div class="mainbox">
                  <div class="col2_image iconl_cart"></div>
                  <div class="scroll-pane">
                    <div class="col2_text">
                      <p><?=T_('To buy tickets:')?></p>
                      <p>&bull; &nbsp; <?=T_('View the prizes in each auction.')?></p>
                      <p>&bull; &nbsp; <?=T_('Add or remove prizes from you cart until the auction date.')?></p>
                      <p><?=T_('You will be entered to win any items in your cart by the auction date.')?></p>
                    </div>
                  </div>
                </div>
              </li>

              <li>
                <div class="slider_title"><?=T_('Empty Carts')?></div>
                <div class="mainbox">
                  <div class="col2_image iconl_cart"></div>
                  <div class="scroll-pane">
                    <div class="col2_text">
                      <p><?=T_('If you have not chosen prizes, or you have not used up your miles by the auction date, the computer will randomly buy raffle tickets with your miles.')?></p>
                    </div>
                  </div>
                </div>
              </li>

              <li>
                <div class="slider_title"><?=T_('Prize Categories')?></div>
                <div class="mainbox">
                  <div class="col2_image iconl_ticket"></div>
                  <div class="scroll-pane">
                    <div class="col2_text">
                      <p><?=T_('When you visit the Auction page, you will see all the prizes you can win in your prize category. Each year you will unlock a higher category of prizes.')?></p>
                        <div class="button button_icons">
                        <div>
                          <a class="icon_back_to" href="auction_home.php"><?=T_('Back to Auctions')?></a>
                        </div>
                    </div>
                  </div>
                </div>
              </li>

            </ul>
          </div>
        </div>
      </div>
    </div>

    <div id="footer">
      <div class="footer_logo"></div>
      <div class="footer_logout"></div>
    </div>

  </div>
</body>
</html>
