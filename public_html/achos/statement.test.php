<? require('header.php'); ?>
<?
require_once('calendar.php');
require_once('file_save.php');
require_once('card_printer.php');

$range = gri('range', 0);

$user_row = mysql_fetch_assoc(mq("
SELECT user_id, first, last, first_he, last_he, username, gender, user_address1, user_address2,
       user_city, user_state, user_postal, user_country, user_phone,
       user_serial, user_photo_id, class_id, class_grade, class_sub, class_teacher, team_id, team_name, 
       school_name, school_number, school_city, school_state, school_makeup_id, school_logo_id, school_logo_kiosk_id, inst_logo_id, school_type_id, 
       rank_ord, rank_name, rank_image_id, rank_color
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

$user_row['class_average'] = ( is_null($user_row['class_id']) ) ? T_('N/A') : @number_format(mysql_result(mq(totalMarks("JOIN users USING (user_id) WHERE school_id = {$user['school_id']} AND class_id = {$user_row['class_id']} AND user_start_date IS NOT NULL")), 0) / mysql_result(mq("SELECT COUNT(*) FROM users WHERE school_id = {$user['school_id']} AND class_id = {$user_row['class_id']} AND user_start_date IS NOT NULL"), 0), 2);
$user_row['school_average'] = ( is_null($user_row['class_id']) ) ? T_('N/A') : @number_format(mysql_result(mq(totalMarks("JOIN users USING (user_id) WHERE school_id = {$user['school_id']} AND user_start_date IS NOT NULL")), 0) / mysql_result(mq("SELECT COUNT(*) FROM users WHERE school_id = {$user['school_id']} AND user_start_date IS NOT NULL"), 0), 2);
$user_row['total_miles'] = number_format( $user_miles = mysql_result(mq(totalMarks("WHERE user_id = {$user['user_id']}")), 0), 2 );

$today = cal_from_jd(unixtojd(), CAL_JEWISH);
$chay_elul = cal_to_jd(CAL_JEWISH, 13, 18, $today['year']-($today['month']==13 && $today['day']>=18 ? 0 : 1));

$withdraw_used_points = mysql_fetch_assoc(mq("SELECT SUM(points) points_total FROM user_withdraw WHERE user_id = {$user['user_id']}"));
$cur_points = floatval(mysql_result(mq(totalMarks("WHERE user_id = {$user['user_id']} AND mark_date >= $chay_elul")), 0));
$left_points = $cur_points - $withdraw_used_points['points_total'];
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="X-UA-Compatible" content="chrome=1">
<title>User Profile - Tzivos Hashem Management System</title>
<link rel="alternate" media="print" href="withdraw_print.php">
<link rel="stylesheet" type="text/css" href="kiosktest/scripts/shadowbox/shadowbox.css">
<link href="kiosktest/styles/reset.css" rel="stylesheet" type="text/css" />
<link href="kiosktest/styles/style.css" rel="stylesheet" type="text/css" />
<link href="kiosktest/styles/print.css" rel="stylesheet" type="text/css" media="print" />
<!--[if IE]>
<link href="kiosktest/styles/style_ie.css" rel="stylesheet" type="text/css" />
<![endif]-->
<style type="">
.rank {
  width: 100px;
  height: auto;
  border: none;
  position: absolute;
  margin-top: 100px;
}
</style>
<script src="kiosktest/scripts/jquery.core.js" type="text/javascript"></script>
<script src="kiosktest/scripts/jquery.ui.js" type="text/javascript"></script>
<!--Copyright Ariel Shkedi 2007-2010-->
</head>

<?if($user['registered']) include('code_processor.php'); ?>
<body class="green">
    <div id="wrapper">
        <div id="header">
          <div class="org">
            <div class="nav">
                <ul>
<!--                    <li class="icon_back"><a href="#" onclick="javascript:history.back(); return false">Back</a></li>-->
<!--                    <li class="icon_home"><a href="../statement.test.php">Home</a></li>-->
                    <li class="icon_logout"><a href="logout.php?n=statement.test.php">Logout</a></li>
                </ul>
            </div>
        	<div class="org_photo"><?=(!is_null($user_row['school_logo_id']) ? linkImgFile($user_row['school_logo_id'],100,100) : '');?></div>
            Base: #<?=$user_row['school_number']?><br>
        	<?=$user_row['school_name']?><br>
        	<?=$user_row['rank_name'].' '.$user_row['first'].' '.$user_row['last']?>
        </div>
      </div>
        <div id="main">
            <div id="page_title">Welcome</div>
            <div class="two_column">
            	<div class="padding_top padding_left member_info_box">
                	<div class="member_photo">
                        <div class="member_photo_img"><?=!is_null($user_row['user_photo_id']) ? linkImgFile($user_row['user_photo_id'], NULL, 150) : ''?></div>
                        <div class="member_photo_cover"></div>
                        <div class="member_badge">
                            <?=!is_null($user_row['rank_image_id']) ? linkImgFile($user_row['rank_image_id'], 100, 100) : ''?>
                        </div>
                    </div>
                    <div class="member_info">
                    	<ul>
                        	<li class="member_name"><?=$user['display']?></li>
                        	<li><label>Rank:</label> <span><?=$user_row['rank_name']?></span></li>
                        	<li><label>Serial #:</label> <span><?=$user_row['user_serial']?></span></li>
                        	<li><label>Platoon:</label> <span><?=$user_row['class_grade']?><?=$user_row['class_sub']?></span></li>
                        	<li><label>Teacher:</label> <span><?=$user_row['class_teacher']?></span></li>
                        	<li><label>Platoon Average:</label> <span><?=$user_row['class_average']?></span></li>
                        	<li><label>Base Average:</label> <span><?=$user_row['school_average']?></span></li>
                        	<li><label>Total Miles:</label> <span><?=$user_row['total_miles']?></span></li>
                        </ul>
                    </div>
                    <div class="clear"></div>
                </div>
            	<div class="padding_left">
                	<div class="scan_card">
                    	<div class="scan_card_inside">
							<script>
								function loadShadow(cardnum) {
									// open a welcome message as soon as the window loads
									Shadowbox.open({
										content:    'cardpop.php?card=' + cardnum.value,
										player:     "iframe",
										width:      770,
										height:     430
									});
									cardnum.value='';
									return false;
								};
                            </script>
                            <? if($user['registered']): ?>
                              
                                <FORM action="statement.php" method="post" id="scancard" accept-charset="UTF-8" autocomplete="off">
                                <DIV>
                                <INPUT type="hidden" name="range" value="<?=$range?>">
                                <?=T_('Scanning Station')?><BR>
                                <INPUT type="text" name="scan_code" size="25" maxlength="20" id="focus" title="<?=T_('Scan achievement or prize card')?>" autocomplete="off">
                                </DIV>
                                <SCRIPT type="text/javascript">document.getElementById('focus').focus();</SCRIPT>
                                </FORM>
                                
                                <? else: ?>
                                
                                <H1>
                                  <?=T_('You are not currently registered in Tzivos Hashem.')?><BR>
                                  <?=T_('Please see the program director at your school.')?>
                                </H1>
                                <? endif; ?>
                          <div class="scan_card_cover"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="one_column">
            <? $inbox = mysql_fetch_assoc(mq("SELECT COUNT(*) num FROM user_codes WHERE user_id = {$user_row['user_id']}")); ?>
              <ul class="buttons button_icons">
                  <li><a href="kiosktest/profile.php" class="icon_profile"><?=T_('Profile')?></a></li>
                  <li><a href="kiosktest/deposit.php" class="icon_deposit"><?=T_('Deposit')?>   </a><? if($inbox['num']){ ?><span class="badge"><?=$inbox['num']?></span><?}?></li>
                  <? if($user_row["school_type_id"]!=12 && $user_row["school_type_id"]!=13) {?>
                  <li><a href="kiosktest/withdraw.php" class="icon_withdraw"><?=T_('Withdraw')?></a><? if($left_points>50){?><span class="badge"><?=((int)($left_points/50))?></span><?}?></li>
                  <?}?>
                  <li><a href="http:kiosktest/campaigns.php" class="icon_campaigns"><?=T_('Campaigns')?></a></li>
                  <!-- LI><a href="logout.php?n=statement.php"><IMG src="kiosktest/images/icon_logout.png" alt="" width="48" height="48"><?=T_('Logout')?></A></li-->
                  <? if($user_row["school_makeup_id"]==4) {?>
                  <li><a href="kiosktest/todolist.php" class=""><?=T_('Todo List')?></a></li>
                  <?}?>
				</ul>
			</div>
	</div>
<div style="width:100%;text-align:center;padding:5px;bottom:0; position:absolute; left:120;">

<?if(!is_null($range)):?>

<?
$running_balance = $user_miles;
$result = userStatement($user['user_id'], $range);
?>
<STYLE type="text/css">
.transactions{
    margin-top:20px;
    margin-bottom:20px;
    margin-left:220px;
  	background-color: #ffffce;
  	-webkit-border-radius: .06in;
  	-moz-border-radius: .06in;
  	border-radius: .06in;
  	text-align: center;
  	color: black;
	font-size: 12px;
font-weight:normal;

}
p {
  text-align: center;
}
.transactions th {
  padding: 2px .5em;
  text-align: <?=$align_start?>;
  background-color: #e8efbd;
}

.transactions td {
  padding: 2px .5em;
}

.transactions tr.even td {
  background-color: #fff6aa;
}

a:link, a:visited, .link_button {
  color: rgb(142, 173, 62);
  text-decoration: none;
	
}
.noprint
  {
  font-family: Tahoma, Verdana, Arial, Helvetica, sans-serif;
  font-size: 14px;
  padding: 0px;
  margin-bottom: -5px;
color: rgb(255, 204, 0);
  }
a:hover, .link_button:hover {
  text-decoration: underline;


}
</style>
<TABLE class="transactions">
<? if(mysql_num_rows($result)): ?>
<TR>
  <TH><?=T_('Posting Date')?></TH>
  <TH><?=T_('Subject')?></TH>
  <TH><?=T_('Description')?></TH>
  <TH><?=T_('Points Earned')?></TH>
  <TH><?=T_('Balance')?></TH>
</TR>
<? $toggle = true; ?>
<? while($row = mysql_fetch_assoc($result)): ?>
<TR class="<?=($toggle = !$toggle) ? 'even' : 'odd'?>">
  <TD><?=dateToHebrew($row['mark_date'])?></TD>
  <TD><?=es($row['subject_name'])?><BR><?=es($row['name'])?></TD>
  <TD><?=es($row['description'])?></TD>
  <TD style="text-align: right;"><?=floatval($row['points']) ? number_format($row['points'], 2) : '-'?></TD>
  <TD style="text-align: right;"><?=number_format($running_balance, 2)?></TD>
</TR>
<? $running_balance -= $row['points']; ?>
<? endwhile; ?>
<? else: ?>
<TR><TD><?=T_('No transactions for the time period selected.')?></TD></TR>
<? endif; ?>
</TABLE>

<P class="noprint">
<?=T_('Show')?>:
<A HREF="statement.php?range=0"><?=T_('Today')?></A> &bull;
<A HREF="statement.php?range=1"><?=T_('This week')?></A> &bull;
<A HREF="statement.php?range=2"><?=T_('Two weeks')?></A> &bull;
<A HREF="statement.php?range=4"><?=T_('Four weeks')?></A>
</P>
<?endif;?>

<? if($user['registered']): ?>

<?if(mysql_result(mq("SELECT kiosk_print FROM schools WHERE school_id = {$user['school_id']}"), 0)):?>
<BR>
<? endif; ?>
<? endif; ?>
</div>
</div>
<script type="text/javascript" src="kiosktest/scripts/shadowbox/shadowbox.js"></script>
<script type="text/javascript">
setInterval ( "scanFocus()", 1000 );
var scancard = document.forms.scancard.scan_code;
function scanFocus() {
	scancard.focus();
}

Shadowbox.init({
    skipSetup: true,
	players:    ["iframe"],
	initialWidth:320,
	initialHeight:30,
	overlayOpacity:0.8
});

</script>
</BODY>
</HTML>
