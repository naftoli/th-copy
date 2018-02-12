<? $admin_auth = array('school'); ?>
<? require('header.php'); ?>
<? require_once('calendar.php'); ?>
<?
$action = gr('action');
assure_id_school('school_id');
$school_id = gri('school_id', -1);
$class_id = gri('class_id', -1);
if($date = gri('date')) {
  $row = mysql_fetch_assoc(mq("SELECT auction_id FROM auctions WHERE auction_date = $date"));
  if($row) sgr('auction_id', $row['auction_id']);
}
$auction_id = gri('auction_id', -1);

$auction = mysql_fetch_assoc(mq("SELECT auction_date, auction_points_start_date, auction_points_trigger_date, max_prize_points FROM auctions WHERE auction_id = $auction_id AND auctions.auction_ran = 0" . ($admin_user['auth'] != 'super' ? ' AND (auction_run_date IS NULL OR auction_run_date > ' . unixtojd() . ')' : '') . " AND (school_id IS NULL OR school_id = $school_id)"));
if(!$auction) $auction_id = -1;

if(gr('save')) {
  $save_user_id = gri('save_user_id');
  foreach(gra('user_prizes') as $prize_id => $quantity) {
    $prize_id = intval($prize_id);
    $quantity = max(0, min(intval($quantity), 65535));
    if($quantity) {
      mq("INSERT INTO auction_user_prizes (auction_id, user_id, prize_id, quantity) SELECT auction_id, user_id, prize_id, $quantity quantity FROM users JOIN prizes_auction JOIN auction_prizes USING (prize_id) JOIN auctions USING (auction_id) LEFT JOIN classes ON (users.school_id = classes.school_id AND users.class_id = classes.class_id) WHERE user_id = $save_user_id AND auction_id = $auction_id AND auctions.auction_ran = 0 AND prize_id = $prize_id AND (auctions.school_id = $school_id OR auctions.school_id IS NULL) AND (prizes_auction.school_id = $school_id OR prizes_auction.school_id IS NULL) AND (max_prize_points IS NULL OR prize_points <= max_prize_points) AND (min_grade IS NULL OR min_grade+0 <= class_grade+0) AND (max_grade IS NULL OR max_grade+0 >= class_grade+0) AND users.school_id = $school_id ON DUPLICATE KEY UPDATE auction_user_prizes.quantity = $quantity");
    } else {
      mq("DELETE FROM auction_user_prizes USING users JOIN auction_user_prizes USING (user_id) JOIN auctions USING (auction_id) WHERE auction_id = $auction_id AND auctions.auction_ran = 0 AND user_id = $save_user_id AND prize_id = $prize_id AND users.school_id = $school_id");
    }
  }
  $message = T_('Soldier Auction Prizes saved');
  $save_user_points = auctionPoints($save_user_id, $auction);
  $save_user_points = $save_user_points['cur'];
  while(mysql_result(mq("SELECT IFNULL(SUM(prize_points*quantity), 0) FROM auction_user_prizes JOIN prizes_auction USING (prize_id) JOIN auction_prizes USING (auction_id, prize_id) JOIN users USING (user_id) WHERE user_id = $save_user_id AND users.school_id = $school_id AND auction_id = $auction_id"), 0) > $save_user_points) {
    mq("UPDATE auction_user_prizes SET quantity = quantity - 1 WHERE quantity > 0 AND auction_id = $auction_id AND user_id = $save_user_id ORDER BY rand() DESC LIMIT 1"); //the user_id is protected from other schools because the point counting query will return 0 if it's the wrong school, the auction_id is protected earlier
    $message = '<SPAN style="font-size: 150%; color: red;">' . T_("WARNING Error saving Soldier Auction Prizes, please check. The soldier's entries have been redisplayed.") . '</SPAN>';
    sgr('user_id', $save_user_id);
  }
}

if(is_null($user_id = gri('user_id'))) {
  $user = mysql_fetch_assoc(mq("SELECT user_id FROM users JOIN classes USING (school_id, class_id) WHERE school_id = $school_id" . ($class_id >= 0 ? " AND users.class_id = $class_id" : '') . ' ORDER BY class_grade, class_sub, last, first, username LIMIT 1'));
  $user_id = $user? $user['user_id'] : -1;
}
$user = mysql_fetch_assoc(mq("SELECT user_id, username, first, last, class_grade, class_sub, IFNULL(class_grade+0, -1) class_grade_ord FROM users LEFT JOIN classes USING (school_id, class_id) WHERE user_id = $user_id AND school_id = $school_id"));
if($user && $auction) {
  $user_prizes = mysql_fetch_column(mq("SELECT prize_id, quantity FROM auction_user_prizes WHERE auction_id = $auction_id AND user_id = $user_id"));
  $user_points = auctionPoints($user_id, $auction);
  $user_points = $user_points['cur'];
  $auction_points_max = is_null($auction['max_prize_points']) ? $user_points : min($user_points, $auction['max_prize_points']);
  $prizes = mq("SELECT prizes_auction.prize_id, prize_name, prize_number, prize_points FROM prizes_auction LEFT JOIN auction_prizes ON (prizes_auction.prize_id = auction_prizes.prize_id AND auction_id = $auction_id) WHERE (auction_prizes.prize_id IS NOT NULL AND (min_grade IS NULL OR min_grade <= {$user['class_grade_ord']}) AND (max_grade IS NULL OR max_grade <= {$user['class_grade_ord']}) AND prize_points <= $auction_points_max) OR EXISTS (SELECT * FROM auction_user_prizes WHERE auction_id = $auction_id AND user_id = $user_id AND auction_user_prizes.prize_id = prizes_auction.prize_id) ORDER BY prize_points, prize_number");
}

$class_result = mq("SELECT class_id, class_grade, class_sub FROM classes WHERE school_id = $school_id ORDER BY class_grade, class_sub");
$auction_result = mq("SELECT auction_id, auction_date, school_id FROM auctions WHERE (school_id = $school_id OR school_id IS NULL) AND auction_ran = 0 ORDER BY auction_date DESC");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN"
   "http://www.w3.org/TR/html4/strict.dtd">
<HTML DIR="<?=$dir?>">
<HEAD>
<TITLE><?=T_('Soldier Auction Prizes'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
<SCRIPT type="text/javascript">
function totalPoints(user_id) {
  var total = 0;
  for(var i = 0; i < document.forms['prizes'].elements.length; i++) {
    if(document.forms['prizes'].elements[i].type == 'text' && document.forms['prizes'].elements[i].name.substring(0, 12) == 'user_prizes[') {
      var tot = parseInt(document.forms['prizes'].elements[i].title, 10) * parseInt(document.forms['prizes'].elements[i].value, 10);
      total += tot;
      for(var j = tot.toString().length; j < 6; j++) tot = '&nbsp; ' + tot;
      document.getElementById('tot_' + document.forms['prizes'].elements[i].id).innerHTML = tot;
    }
  }
  document.getElementById('used_points').innerHTML = total;
  document.getElementById('left_points').innerHTML = (parseFloat(document.getElementById('total_points').innerHTML, 10) - total).toFixed(2);
  document.getElementById('used').style.color = (total > parseFloat(document.getElementById('total_points').innerHTML, 10)) ? 'red' : '';
}

function check_total() {
  return parseFloat(document.getElementById('used_points').innerHTML, 10) <= parseFloat(document.getElementById('total_points').innerHTML, 10);
}
</SCRIPT>
</HEAD>
<BODY>
<?include('admin_header.php');?>
<DIV CLASS="body">
<H1><?=T_('Soldier Auction Prizes')?></H1>
<?if(!empty($message)):?><H2><?=$message?></H2><?endif;?>
<?if($admin_user['auth'] == 'super' || count($admin_user['auths']['school']) != 1):?>
<? $school_result = mq('SELECT school_id, school_name FROM schools' . ($admin_user['auth'] != 'super' ? ' WHERE school_id IN (' . implode(',', $admin_user['auths']['school']) . ')' : '') . ' ORDER BY school_name'); ?>
<FORM action="admin_user_auction.php" method="get" accept-charset="UTF-8">
<P>
<INPUT type="hidden" name="auction_id" value="<?=$auction_id?>">
<LABEL><?=T_('Select Institution')?>: <SELECT name="school_id">
<?while($school_row = mysql_fetch_assoc($school_result)):?>
<OPTION value="<?=$school_row['school_id']?>" <?=$school_row['school_id'] == $school_id ? 'SELECTED' : ''?>><?=es($school_row['school_name'])?></OPTION>
<?endwhile;?>
</SELECT></LABEL>
<INPUT class="submit" type="submit" value="<?=T_('Go')?>">
</P>
</FORM>

<HR>
<?endif;?>
<?if($school_id == -1):?>
<?=T_('Please select an Institution.')?>
<?else:?>

<?if($admin_user['auth'] == 'super' || count($admin_user['auths']['school']) != 1):?><P><A HREF="admin_school.php"><?=T_('Back to Institution list')?></A></P><?endif;?>
<P><A HREF="admin_class.php?school_id=<?=$school_id?>"><?=T_('Back to Platoon list')?></A></P>

<FORM action="admin_user_auction.php" method="get" accept-charset="UTF-8">
<P>
<INPUT type="hidden" name="school_id" value="<?=$school_id?>">

<?=T_('Show Platoon')?>: <SELECT name="class_id">
<OPTION value="-1">&lt;<?=T_('All')?>&gt;
<? while($class_row = mysql_fetch_assoc($class_result)): ?>
<OPTION value="<?=$class_row['class_id']?>" <?=$class_row['class_id'] == $class_id ? 'SELECTED' : ''?>><?=es($class_row['class_grade'])?>-<?=es($class_row['class_sub'])?></OPTION>
<?endwhile;?>
</SELECT><BR>

<?=T_('Auction')?>: <SELECT name="auction_id" dir="rtl">
<? $multi = false; ?>
<? while($auction_row = mysql_fetch_assoc($auction_result)): ?>
<OPTION value="<?=$auction_row['auction_id']?>" <?=$auction_row['auction_id'] == $auction_id ? 'SELECTED' : ''?>><?if(is_null($auction_row['school_id'])) { $multi = true; echo '* '; }?><?=es(dateToHebrew($auction_row['auction_date']))?></OPTION>
<?endwhile;?>
</SELECT> <?= $multi ? '*' . T_('Multi schoool auction') : ''?><BR>
<INPUT class="submit" type="submit" value="<?=T_('Go')?>">
</P>
</FORM>

<? if(!isset($prizes)):?>
<?=T_('Please select an auction.')?>
<? else: ?>
<FORM action="admin_user_auction.php" method="post" accept-charset="UTF-8" name="prizes">
<DIV>
<INPUT type="hidden" name="school_id" value="<?=$school_id?>">
<INPUT type="hidden" name="save_user_id" value="<?=$user_id?>">
<INPUT type="hidden" name="class_id" value="<?=$class_id?>">
<INPUT type="hidden" name="auction_id" value="<?=$auction_id?>">
<INPUT type="hidden" name="action" value="save">

<H2><?=$user['class_grade'] != '' ? es($user['class_grade'] . '-' . $user['class_sub']) . ': ' : ''?><?=es($user['last'])?>, <?=es($user['first'])?> (<?=es($user['username'])?>)</H2>

<? if(!mysql_num_rows($prizes)): ?>
<?=T_('This soldier has no prizes available. Possible reassons are: not enough points for any prizes, the minimum grade necessary for the prizes, or the max prize points for this auction.')?>
<? else: ?>
<DIV id="user_prizes">
<? $used_points = 0; $old_points = -1; ?>
<? while($row = mysql_fetch_assoc($prizes)): ?>
  <? if($old_points != $row['prize_points']): ?>
    <DIV class='points'><?=$row['prize_points'], ' ', T_('Miles')?></DIV>
    <? $old_points = $row['prize_points']; ?>
  <? endif; ?>
  <DIV class="prize">
  <SPAN class="num">
    <INPUT type="text" size="3" maxlength="5" title="<?=$row['prize_points'], ' ', T_('Miles')?>" id="prize_<?=$row['prize_id']?>" name="user_prizes[<?=$row['prize_id']?>]" value="<?=isset($user_prizes[$row['prize_id']]) ? $user_prizes[$row['prize_id']] : '0' ?>" onChange="this.value = Math.max(0, Math.min(parseInt('0'+this.value, 10), 65535)); totalPoints();">
    <A HREF="#" style="font-size: 150%;" onClick="el=document.forms['prizes'].elements['user_prizes[<?=$row['prize_id']?>]']; el.value=parseInt('0'+el.value, 10)+1; el.onchange(); return false;">&uarr;</A>
    <A HREF="#" style="font-size: 150%;" onClick="el=document.forms['prizes'].elements['user_prizes[<?=$row['prize_id']?>]']; el.value=parseInt('0'+el.value, 10)-1; el.onchange(); return false;">&darr;</A>
  </SPAN> &nbsp;
  <SPAN class="tot" id="tot_prize_<?=$row['prize_id']?>"><?$tot = isset($user_prizes[$row['prize_id']]) ? $user_prizes[$row['prize_id']] * $row['prize_points'] : '0'?><?=str_repeat('&nbsp; ', 6-strlen($tot)), $tot?></SPAN>  &nbsp; &nbsp;
  <SPAN class="name"><?=$row['prize_number']?> &nbsp; <?=es($row['prize_name'])?></SPAN>
  </DIV>
  <? if(isset($user_prizes[$row['prize_id']])) $used_points += $user_prizes[$row['prize_id']] * $row['prize_points']; ?>
<? endwhile; ?>
</DIV>

<DIV ID="used" <?=$used_points > $user_points ? 'style="color: red;"' : ''?>>
  <?=T_('Points Avilable')?>: <SPAN ID="total_points"><?=number_format($user_points, 2, '.', '')?></SPAN><BR>
  <?=T_('Points Used')?>: <SPAN ID="used_points"><?=$used_points?></SPAN><BR>
  <?=T_('Points Left')?>: <SPAN ID="left_points"><?=number_format($user_points - $used_points, 2, '.', '')?></SPAN>
</DIV>

<INPUT class="submit" type="submit" name="save" value="<?=T_('Save and edit selected soldier')?>" onClick="if(check_total()) return true; else {alert('<?=T_('Unable to save, soldier has used too many points.')?>'); return false;}">

<? endif; ?>
</DIV>

<FIELDSET style="margin-top: 1em;">
<LEGEND style="font-size: 150%; font-weight: bold;"><?=T_('Next soldier')?>:</LEGEND>
<DIV style="-moz-column-width:20em;">
<? $next = false; ?>
<? $users = mq("SELECT users.user_id, first, last, username, class_grade, class_sub, IFNULL(SUM(prize_points*quantity), 0) auction_points, IFNULL(SUM(quantity), 0) auction_count FROM users LEFT JOIN (auction_user_prizes JOIN prizes_auction USING (prize_id)) ON (users.user_id = auction_user_prizes.user_id AND auction_id = $auction_id) LEFT JOIN classes USING (class_id) WHERE users.school_id = $school_id" . ($class_id >= 0 ? " AND users.class_id = $class_id" : '') . ' GROUP BY user_id, first, last, username, class_grade, class_sub ORDER BY class_grade, class_sub, last, first, username'); ?>
<? while($row = mysql_fetch_assoc($users)): ?>
  <DIV style="margin-bottom: 1em;"><LABEL>
    <? if($row['user_id'] == $user_id): ?>
      <? $next = true; ?>
      <B>&nbsp; &nbsp; &nbsp; &nbsp;
    <? else: ?>
      <INPUT type="radio" name="user_id" value="<?=$row['user_id']?>" <?=$next ? ' checked' : ''?> style="vertical-align: middle;">
      <? $next = false; ?>
    <? endif; ?>
   <?=$row['class_grade'] != '' ? es($row['class_grade'] . '-' . $row['class_sub']) . ': ' : ''?><?=es($row['last'])?>, <?=es($row['first'])?> (<?=es($row['username'])?>) <?=$row['auction_points'] || $row['auction_count'] ? '<B>[' . $row['auction_points'] . ' x' . $row['auction_count'] . ']</B>' : ''?>
    <? if($row['user_id'] == $user_id): ?></B><? endif; ?>
  </LABEL></DIV>
<? endwhile; ?>
</DIV>
</FIELDSET>
<P>
<INPUT class="submit" type="submit" name="no_save" value="<?=T_("Don't save, just edit selected soldier")?>">
</P>
</FORM>
<? $num_users = mysql_result(mq("SELECT COUNT(DISTINCT user_id) count FROM users JOIN auction_user_prizes USING (user_id) LEFT JOIN classes USING (class_id) WHERE users.school_id = $school_id" . ($class_id >= 0 ? " AND users.class_id = $class_id" : '') . " AND auction_id = $auction_id"), 0); ?>
<H2><?=T_('Total Soldiers')?>: <?=mysql_num_rows($users)?> &mdash; <?=T_('Total With Entries')?>: <?=$num_users?> <?= mysql_num_rows($users) ? '&mdash; (' . round(100*$num_users/mysql_num_rows($users), 2) . '%)' : ''?></H2>
<? endif; ?>
<? endif; ?>
</DIV>
<? include('admin_footer.php'); ?>
</BODY>
</HTML>
