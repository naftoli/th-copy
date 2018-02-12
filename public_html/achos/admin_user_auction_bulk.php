<? $admin_auth = array('school'); ?>
<? require('header.php'); ?>
<? require_once('calendar.php'); ?>
<?
$auction_id = gri('auction_id', -1);
assure_id_school('school_id');
$school_id = gri('school_id', -2);

$auction = mysql_fetch_assoc(mq("SELECT auction_points_start_date, auction_date, auction_points_trigger_date, max_prize_points FROM auctions WHERE auction_id = $auction_id AND auctions.auction_ran = 1 AND " . ($admin_user['auth'] == 'super' && $school_id == -1 ? ' auctions.school_id IS NULL' : " auctions.school_id = $school_id")));

if(!$auction) {
  $auction_id = -1;
} else {
  $auction_points_query = auctionPointsQuery($auction);
  $auction_points_query_exp = auctionPointsQueryExp();

  if(gr('save')) {
    $prize_id = gri('prize_id');

    for($reps = gri('reps', 1); $reps > 0; $reps--) {
      mq("INSERT INTO auction_user_prizes (auction_id, user_id, prize_id, quantity) 
	  SELECT auction_id, user_id, prize_id, 1 quantity 
	  FROM prizes_auction 
	  JOIN auction_prizes 
	  USING (prize_id) 
	  JOIN users 
	  JOIN classes 
	  USING (class_id) 
	  JOIN $auction_points_query 
	  USING (user_id) 
	  LEFT JOIN (
	  	SELECT SUM(prize_points*quantity) used_points, user_id 
	  	FROM auction_user_prizes 
	  	JOIN prizes_auction 
	  	USING (prize_id) 
	  	JOIN auction_prizes 
	  	USING (auction_id, prize_id) 
	  	WHERE auction_id = $auction_id GROUP BY user_id) used 
	  	USING (user_id) 
	  	WHERE $auction_points_query_exp - IFNULL(used_points,0) >= prize_points 
	  	AND user_start_date IS NOT NULL" . ($admin_user['auth'] == 'super' && $school_id == -1 ? '' : " 
	  	AND users.school_id = $school_id") . " 
	  	AND auction_id = $auction_id 
	  	AND prize_id = $prize_id 
	  	AND (min_grade IS NULL OR min_grade+0 <= class_grade+0) 
	  	AND (max_grade IS NULL OR max_grade+0 <= class_grade+0) 
	  	ON DUPLICATE KEY UPDATE quantity = quantity+1");
    }
    $message = T_('Soldier Auction Prizes saved');
  }

  $prizes = mq("SELECT prize_id, prize_name, prize_number, prize_points FROM prizes_auction JOIN auction_prizes USING (prize_id) WHERE auction_id = $auction_id" . (!is_null($auction['max_prize_points']) ? " AND prize_points <= {$auction['max_prize_points']}" : '') . ' ORDER BY prize_points, prize_number');

  $histogram = mq("SELECT FLOOR($auction_points_query_exp - IFNULL(used_points,0)) avail_points, COUNT(*) num FROM users JOIN classes USING (class_id) JOIN $auction_points_query USING (user_id) LEFT JOIN (SELECT SUM(prize_points*quantity) used_points, user_id FROM auction_user_prizes JOIN prizes_auction USING (prize_id) JOIN auction_prizes USING (auction_id, prize_id) WHERE auction_id = $auction_id GROUP BY user_id) used USING (user_id) WHERE (used_points IS NULL OR $auction_points_query_exp > used_points) AND user_start_date IS NOT NULL GROUP BY avail_points");
}

$auction_result = mq("SELECT auction_id, auction_name, auction_date, school_id FROM auctions WHERE auction_ran = 1 AND" . ($admin_user['auth'] == 'super' && $school_id == -1 ? ' auctions.school_id IS NULL' : " auctions.school_id = $school_id") . ' ORDER BY auction_date');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN"
   "http://www.w3.org/TR/html4/strict.dtd">
<HTML DIR="<?=$dir?>">
<HEAD>
<TITLE><?=T_('Soldier Auction Prizes'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
</HEAD>
<BODY>
<?include('admin_header.php');?>
<DIV CLASS="body">
<H1><?=T_('Soldier Auction Prizes')?></H1>
<?if(!empty($message)):?><H2><?=$message?></H2><?endif;?>
<?if($admin_user['auth'] == 'super' || count($admin_user['auths']['school']) != 1):?>
<? $school_result = mq('SELECT school_id, school_name FROM schools' . ($admin_user['auth'] != 'super' ? ' WHERE school_id IN (' . implode(',', $admin_user['auths']['school']) . ')' : '') . ' ORDER BY school_name'); ?>
<FORM action="admin_user_auction_bulk.php" method="get" accept-charset="UTF-8">
<P>
<INPUT type="hidden" name="auction_id" value="<?=$auction_id?>">
<LABEL><?=T_('Select Institution')?>: <SELECT name="school_id">
<?if($admin_user['auth'] == 'super'):?><OPTION value="-1">&lt;<?=T_('Multi institution auctions')?>&gt;<?endif;?>
<?while($school_row = mysql_fetch_assoc($school_result)):?>
<OPTION value="<?=$school_row['school_id']?>" <?=$school_row['school_id'] == $school_id ? 'SELECTED' : ''?>><?=es($school_row['school_name'])?></OPTION>
<?endwhile;?>
</SELECT></LABEL>
<INPUT class="submit" type="submit" value="<?=T_('Go')?>">
</P>
</FORM>

<HR>
<?endif;?>
<?if($school_id == -2):?>
<?=T_('Please select an Institution.')?>
<?else:?>

<FORM action="admin_user_auction_bulk.php" method="get" accept-charset="UTF-8">
<P>
<INPUT type="hidden" name="school_id" value="<?=$school_id?>">

<?=T_('Auction')?>: <SELECT name="auction_id" dir="rtl">
<? $multi = false; ?>
<? while($auction_row = mysql_fetch_assoc($auction_result)): ?>
<OPTION value="<?=$auction_row['auction_id']?>" <?=$auction_row['auction_id'] == $auction_id ? 'SELECTED' : ''?>><?if(is_null($auction_row['school_id'])) { $multi = true; echo '* '; }?><?=es($auction_row['auction_name'] . ' - ' . dateToHebrew($auction_row['auction_date']))?></OPTION>
<?endwhile;?>
</SELECT> <?= $multi ? '*' . T_('Multi schoool auction') : ''?><BR>
<INPUT class="submit" type="submit" value="<?=T_('Go')?>">
</P>
</FORM>

<HR>

<? if(!isset($prizes)):?>
<?=T_('Please select an auction.')?>
<? else: ?>

<H2><?=T_('Point Histogram')?></H2>
<? $width = mysql_num_rows($histogram)/100; ?>
<TABLE>
<CAPTION><?=T_('Height of bar is number of people with that many points left. Number under each bar is how many points they have left.')?></CAPTION>
<TR>
<? while($row = mysql_fetch_assoc($histogram)): ?>
<TD style="width: <?=$width?>%; padding: 1px; vertical-align: bottom;"><DIV style="background-color: black; height: <?=$row['num']*3?>px;"></DIV></TD>
<? endwhile; ?>
</TR>
<TR>
<? @mysql_data_seek($histogram, 0); ?>
<? while($row = mysql_fetch_assoc($histogram)): ?>
<TD style="text-align: center;">&nbsp;<?=$row['avail_points']?>&nbsp;</TD>
<? endwhile; ?>
</TR>
<TR><TD colspan="0" style="text-align: center;"><?=T_('Number of Points')?></TD></TR>
</TABLE>

<FORM action="admin_user_auction_bulk.php" method="post" accept-charset="UTF-8" name="prizes">
<DIV>
<INPUT type="hidden" name="school_id" value="<?=$school_id?>">
<INPUT type="hidden" name="auction_id" value="<?=$auction_id?>">

<? $old_points = -1; ?>
<? while($row = mysql_fetch_assoc($prizes)): ?>
  <? if($old_points != $row['prize_points']): ?>
    <H2><?=$row['prize_points'], ' ', T_('Miles')?></H2>
    <? $old_points = $row['prize_points']; ?>
  <? endif; ?>
  <LABEL><INPUT type="radio" name="prize_id" value="<?=$row['prize_id']?>"><?=$row['prize_number']?> &nbsp; <?=es($row['prize_name'])?></LABEL><BR>
<? endwhile; ?>
<BR>
<?=T_('Entries')?>: <INPUT type="text" name="reps" value="1"> (<?=T_('This will enter the prize X number times, if the solider has enough points left.')?><BR>
<INPUT class="submit" type="submit" name="save" value="<?=T_('Create an entry for each eligible soldier')?>">

</DIV>
</FORM>

<? endif; ?>
<? endif; ?>
</DIV>
<? include('admin_footer.php'); ?>
</BODY>
</HTML>
