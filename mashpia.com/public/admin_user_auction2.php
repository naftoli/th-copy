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

if(!empty($action)) switch($action) {
  case 'save':
    $user_prizes = gra('user_prizes');
    foreach($user_prizes['user_id'] as $user_id => $prizes) {
      foreach($prizes['prize_id'] as $prize_id => $quantity) {
        $user_id = intval($user_id);
        $prize_id = intval($prize_id);
        $quantity = max(0, min(intval($quantity), 65535));
        if($quantity) {
          mq("INSERT INTO auction_user_prizes (auction_id, user_id, prize_id, quantity) SELECT auction_id, user_id, prize_id, $quantity quantity FROM users JOIN prizes_auction JOIN auction_prizes USING (prize_id) JOIN auctions USING (auction_id) WHERE user_id = $user_id AND auction_id = $auction_id AND auctions.auction_ran = 0 AND prize_id = $prize_id AND (auctions.school_id = $school_id OR auctions.school_id IS NULL) AND (prizes_auction.school_id = $school_id OR prizes_auction.school_id IS NULL) AND users.school_id = $school_id ON DUPLICATE KEY UPDATE auction_user_prizes.quantity = $quantity");
        } else {
          mq("DELETE FROM auction_user_prizes USING users JOIN auction_user_prizes USING (user_id) JOIN auctions USING (auction_id) WHERE auction_id = $auction_id AND auctions.auction_ran = 0 AND user_id = $user_id AND prize_id = $prize_id AND users.school_id = $school_id");
        }
      }
    }
    $message = T_('Soldier Auction Prizes saved');
    $action = 'view';

  case 'view':
    //a list of users, with any prizes they already chose (if any)
    $auction = mysql_fetch_assoc(mq("SELECT auction_date, max_prize_points FROM auctions WHERE auction_id = $auction_id"));
    if(!$auction) break;
    $result = mq("
SELECT   users.user_id, users.username, users.first, users.last,
         prizes_auction.prize_id, prizes_auction.prize_points, IFNULL(auction_user_prizes.quantity, 0) quantity
FROM     users LEFT JOIN
          (auction_user_prizes
          JOIN (auction_prizes JOIN prizes_auction USING (prize_id))
          ON (auction_prizes.auction_id = $auction_id AND auction_prizes.auction_id = auction_user_prizes.auction_id AND auction_user_prizes.prize_id = auction_prizes.prize_id))
         USING (user_id)
WHERE    users.school_id = $school_id
         " . ($class_id >= 0 ? "AND users.class_id = $class_id" : '') . "
ORDER BY last, first, username
");
    $prizes = mq("
SELECT   prizes_auction.prize_id, prizes_auction.prize_name, prizes_auction.prize_points
FROM     prizes_auction JOIN auction_prizes USING (prize_id) JOIN auctions USING (auction_id)
WHERE    auction_id = $auction_id AND auctions.auction_ran = 0 AND (auctions.school_id = $school_id OR auctions.school_id IS NULL) AND (prizes_auction.school_id = $school_id OR prizes_auction.school_id IS NULL)" . (!is_null($auction['max_prize_points']) ? "AND prize_points <= {$auction['max_prize_points']}" : '') . "
ORDER BY prizes_auction.prize_points, prizes_auction.prize_name
");
    break;

  default:
    user_error('unknown action', E_USER_ERROR);
    break;
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
</HEAD>
<BODY <?=$action=='view' ? 'onLoad="init();"' : ''?>>
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
<INPUT type="hidden" name="action" value="view">

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

<? if($action != 'view'): ?>
<?=T_('Please select an auction.')?>
<? else: ?>
<? if(mysql_num_rows($result) && mysql_num_rows($prizes)):?>
<SCRIPT type="text/javascript">
var users = new Object();
var points = new Object();

<? while($prize = mysql_fetch_assoc($prizes)): ?>
  points[<?=($prize['prize_id'])?>] = <?=($prize['prize_points'])?>;
<? endwhile; ?>

function init() {
  var pattern = new RegExp("user_prizes\\[user_id\\]\\[(\\d+)\\]\\[prize_id\\]\\[(\\d+)\\]");
  for(var i=0; i<document.forms['user_prizes'].elements.length; i++) {
    if(matches=pattern.exec(document.forms['user_prizes'].elements[i].name)) {
      if(!users[matches[1]]) users[matches[1]] = new Object();
      users[matches[1]][matches[2]] = document.forms['user_prizes'].elements[i];
    }
  }
}

function totalPoints(user_id) {
  var total = 0;
  for(var prize_id in users[user_id]) {
    total += users[user_id][prize_id].value * points[prize_id];
  }
  document.getElementById('used_points_' +  user_id).innerHTML = total;
  document.getElementById('left_points_' +  user_id).innerHTML = document.getElementById('total_points_' +  user_id).innerHTML - total;
  document.getElementById('used_' +  user_id).style.color = (total > document.getElementById('total_points_' +  user_id).innerHTML) ? 'red' : '';
}
</SCRIPT>
<FORM action="admin_user_auction.php" method="post" accept-charset="UTF-8" name="user_prizes">
<DIV>
<INPUT type="hidden" name="school_id" value="<?=$school_id?>">
<INPUT type="hidden" name="class_id" value="<?=$class_id?>">
<INPUT type="hidden" name="auction_id" value="<?=$auction_id?>">
<INPUT type="hidden" name="action" value="save">

<TABLE CLASS="pretty_grid">
<THEAD>
<TR>
<TH style="text-align: <?=$align_end?>;"><?=T_('Points')?></TH>
<?
mysql_data_seek($prizes, 0);
$old_points_count = 0;
$row = $old_row = mysql_fetch_assoc($prizes);
?>
<?
  do {
    if($old_row['prize_points'] == $row['prize_points']) {
      $old_points_count++;
    } else {
      echo "<TH colspan='{$old_points_count}'>" . es($old_row['prize_points']) . "</TH>\n";
      $old_points_count = 1;
      $old_row = $row;
    }
    $row = mysql_fetch_assoc($prizes);
  } while($old_row);
?>
</TR>
<TR>
<TH style="text-align: <?=$align_end?>;"><?=T_('Name')?></TH>
<? mysql_data_seek($prizes, 0); ?>
<? while($prize = mysql_fetch_assoc($prizes)): ?>
  <TH><?=es($prize['prize_name'])?></TH>
<? endwhile; ?>
</TR>
</THEAD>
<?
$tabindex = 0;
$row = mysql_fetch_assoc($result);
$old_row = $row;
?>
<TBODY>
<? do { ?>
  <TR style="white-space: nowrap;">
    <?
    $user_prizes = array();
    $used_points = 0;
    do {
      if(!is_null($row['prize_id'])) $user_prizes[$row['prize_id']] = $row['quantity'];
      $used_points += $row['quantity']*$row['prize_points'];
    } while(($row = mysql_fetch_assoc($result)) && $old_row['user_id'] == $row['user_id']);
    mysql_data_seek($prizes, 0);
    $user_points = mysql_result(mq(totalMarks("WHERE user_id = {$old_row['user_id']} AND mark_date <= {$auction['auction_date']}")), 0);
    ?>
    <TH>
      <DIV><?=es($old_row['username'])?>: <?=es($old_row['last'])?>, <?=es($old_row['first'])?></DIV>
      <DIV><?=T_('Total Points')?>: <SPAN ID="total_points_<?=$old_row['user_id']?>"><?=$user_points?></SPAN></DIV>
      <DIV ID="used_<?=$old_row['user_id']?>" <?=$used_points>$user_points ? 'style="color: red;"' : ''?>>
        <?=T_('Points Used')?>: <SPAN ID="used_points_<?=$old_row['user_id']?>"><?=$used_points?></SPAN><BR>
        <?=T_('Points Left')?>: <SPAN ID="left_points_<?=$old_row['user_id']?>"><?=$user_points - $used_points?></SPAN>
      </DIV>
    </TH>
    <? while($prize = mysql_fetch_assoc($prizes)): ?>
      <TD style="white-space: nowrap;">
        <INPUT type="text" tabindex="<?=++$tabindex?>" size="3" maxlength="5" name="user_prizes[user_id][<?=$old_row['user_id']?>][prize_id][<?=$prize['prize_id']?>]" value="<?=isset($user_prizes[$prize['prize_id']]) ? $user_prizes[$prize['prize_id']] : '0' ?>" onChange="this.value = Math.max(0, Math.min(parseInt('0'+this.value, 10), 65535)); totalPoints(<?=$old_row['user_id']?>);">
        <A HREF="#" style="vertical-align: 30%; font-size: 150%;" onClick="el=document.forms['user_prizes'].elements['user_prizes[user_id][<?=$old_row['user_id']?>][prize_id][<?=$prize['prize_id']?>]']; el.value=parseInt('0'+el.value, 10)+1; el.onchange(); return false;">&uarr;</A>
        <A HREF="#" style="vertical-align: 30%; font-size: 150%;" onClick="el=document.forms['user_prizes'].elements['user_prizes[user_id][<?=$old_row['user_id']?>][prize_id][<?=$prize['prize_id']?>]']; el.value=parseInt('0'+el.value, 10)-1; el.onchange(); return false;">&darr;</A>
      </TD>
    <? endwhile; ?>
<? } while($old_row = $row); ?>
</TBODY>
</TABLE>
<INPUT class="submit" type="submit" value="<?=T_('Save')?>">
</DIV>
</FORM>
<? else: ?>
<?=T_('Either there are no users in this institution and platoon, or there are no prizes in this auction.')?>
<? endif; ?>
<? endif; ?>
<? endif; ?>
</DIV>
<? include('admin_footer.php'); ?>
</BODY>
</HTML>
