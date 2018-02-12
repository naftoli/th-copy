<? $dual_auth = true; ?>
<? $admin_auth = array('school'); ?>
<? require('header.php'); ?>
<?
require_once('calendar.php');
require_once('file_save.php');

if(!empty($admin_user)) {
  assure_id_school('school_id');
  $school_id = gri('school_id', -1);
  $class_id = gri('class_id', -2);
  $user_id = gri('user_id', -2);
} else {
  $school_id = $user['school_id'];
  $class_id = $user['class_id'];
  $user_id = $user['user_id'];
}

if($date = gri('date')) {
  $row = mysql_fetch_assoc(mq("SELECT auction_id FROM auctions WHERE auction_date = $date"));
  if($row) sgr('auction_id', $row['auction_id']);
}
$auction_id = gri('auction_id', -1);
$hide_not_registered = gri('hide_not_registered', 0);
$hide_no_points = gri('hide_no_points', 0);

$hide_grade = mysql_result(mq("SELECT class_grade+0 class_grade_ord FROM classes WHERE class_grade = '2' LIMIT 1"), 0);

$auctions = mq('SELECT auction_id, auction_name, auction_date, school_id, school_name, inst_name FROM auctions LEFT JOIN schools USING (school_id) LEFT JOIN institutions USING (inst_id) WHERE auction_ran = 0 AND (auction_run_date IS NULL OR auction_run_date > ' . unixtojd() . ') AND auction_date < ' . unixtojd() . (empty($admin_user) || $admin_user['auth'] != 'super' ? " AND (school_id IS NULL OR school_id = $school_id)" : '') . ' ORDER BY auction_date, school_id');
$class_result = mq("SELECT class_id, class_grade, class_sub FROM classes WHERE school_id = $school_id AND class_grade > $hide_grade ORDER BY class_grade, class_sub");
$user_result = mq("SELECT class_grade, class_sub, user_id, username, first, last FROM users LEFT JOIN classes USING (school_id, class_id) WHERE school_id = $school_id" .  ($class_id >= 0 ? " AND class_id = $class_id" : '') . " AND (class_grade IS NULL OR class_grade > $hide_grade) ORDER BY class_grade, class_sub, last, first, username");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN"
   "http://www.w3.org/TR/html4/strict.dtd">
<HTML DIR="<?=$dir?>">
<HEAD>
<TITLE><?=T_('Auctions'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
<LINK href="admin_styles_wwtc.css" rel="stylesheet" type="text/css">
<STYLE type="text/css">
.page {
  page-break-after: always;
  margin: 10px;
}

.soldier .title, .base .title {
  width: 1in;
}

.soldier img, .base img {
  width: .8in;
}

.det {
  margin-left: 1.35in;
  margin-right: 1.35in;
  font-size: 10pt;
}

.soldier .title, .bars, .det, .base .title {
  height: 150px;
}

.instructions {
  width: 100%;
  margin: 10px 0px;
}

.instructions td, .instructions th {
  padding: 0px 10px;
  width: 33%;
  vertical-align: top;
}

.prizes {
  -moz-column-count: 3;
  -moz-column-gap: 20px;
  -webkit-column-count: 3;
  -webkit-column-gap: 20px;
  column-count: 3;
  column-gap: 20px;
}

.points {
  -moz-column-break-after: avoid ;
  -webkit-column-break-after: avoid;
  column-break-after: avoid;
  background-color: #757575;
  color: white;
  text-align: center;
  border: 1px solid #757575;
  -webkit-border-radius: 8px;
  -moz-border-radius: 8px;
  border-radius: 8px;
  font-weight: bold;
  font-size: 16px;
  padding: 6px 0px;
  margin: 4px 0px;
}

.prize {
  line-height: 25px;
  font-size: 14px;
  border-top: 2px solid #8c8c8c;
  padding: 4px 0px;
}

.first {
  border-top: none;
}

.num, .tot {
  border: 2px solid #757575;
  -webkit-border-radius: 8px;
  -moz-border-radius: 8px;
  border-radius: 8px;
  padding: 4px 2px;
  font-size: 10px;
}

.top .num, .top .tot, .top .name {
  border-color: 757575;
  color: #222222;
}

.sample .num, .sample .tot, .sample .name {
  border-color: #757575;
  color: #444444;
}

.off .num, .off .tot {
  border-color: transparent;
}
</STYLE>
</HEAD>
<BODY>
<DIV class="header">
<DIV class="noprint">

<H1>Auctions</H1>

<H2>Printing Instructions</H2>
<UL>
  <LI>Go to File<?=$next_arr?>Page Set up
  <OL>
    <LI>Portrait
    <LI>Scale 70
    <LI>Check the box "Print Background colors"
  </OL>
  <LI>Then, click on tab "Margins Header and footer"
  <OL>
    <LI>Margins: Top, Left, Right, Bottom (all): 0.0
    <LI>All headers and footers: Blank
  </OL>
</UL>
<HR>

<?if(!empty($admin_user) && ($admin_user['auth'] == 'super' || count($admin_user['auths']['school']) != 1)):?>
<? $school_result = mq('SELECT school_id, school_name, inst_name FROM schools JOIN institutions USING (inst_id)' . ($admin_user['auth'] != 'super' ? ' WHERE school_id IN (' . implode(',', $admin_user['auths']['school']) . ')' : '') . ' ORDER BY inst_name, school_name'); ?>
<FORM action="admin_report_auction.php" method="get" accept-charset="UTF-8">
<P>
<INPUT type="hidden" name="auction_id" value="<?=$auction_id?>">
<LABEL><?=T_('Select Institution')?>: <SELECT name="school_id">
<?while($school_row = mysql_fetch_assoc($school_result)):?>
<OPTION value="<?=$school_row['school_id']?>" <?=$school_row['school_id'] == $school_id ? 'SELECTED' : ''?>><?=es($school_row['inst_name'])?> - <?=es($school_row['school_name'])?></OPTION>
<?endwhile;?>
</SELECT></LABEL>
<INPUT class="submit" type="submit" value="<?=T_('Go')?>"><BR>
</P>
</FORM>

<HR>
<?endif;?>

<?if($school_id == -1):?>
<?=T_('Please select an Institution.')?>
<?else:?>
<FORM action="admin_report_auction.php" method="get" accept-charset="UTF-8">
<P>
<? if(!empty($admin_user)): ?>
<INPUT type="hidden" name="school_id" value="<?=$school_id?>">
<LABEL><?=T_('Show Platoon')?>: <SELECT name="class_id">
<OPTION value="-1">&lt;<?=T_('All')?>&gt;
<? while($class_row = mysql_fetch_assoc($class_result)): ?>
<OPTION value="<?=$class_row['class_id']?>" <?=$class_row['class_id'] == $class_id ? 'SELECTED' : ''?>><?=es($class_row['class_grade'])?>-<?=es($class_row['class_sub'])?></OPTION>
<?endwhile;?>
</SELECT></LABEL><BR>
<LABEL><?=T_('Show Soldier')?>: <SELECT name="user_id">
<OPTION value="-1">&lt;<?=T_('All')?>&gt;
<? while($user_row = mysql_fetch_assoc($user_result)): ?>
<OPTION value="<?=$user_row['user_id']?>" <?=$user_row['user_id'] == $user_id ? 'SELECTED' : ''?>><?=$class_id < 0 && $user_row['class_grade'] != '' ? es($user_row['class_grade'] . '-' . $user_row['class_sub']) . ': ' : ''?><?=es($user_row['last'])?>, <?=es($user_row['first'])?> (<?=es($user_row['username'])?>)</OPTION>
<?endwhile;?>
</SELECT></LABEL><BR>
<? endif; ?>
<LABEL><?=T_('Select Auction')?>: <SELECT name="auction_id">
<?while($row = mysql_fetch_assoc($auctions)):?>
<OPTION value="<?=$row['auction_id']?>" <?=$row['auction_id'] == $auction_id ? 'SELECTED' : ''?>><?=(!is_null($row['school_id']) ? es($row['inst_name']) . ' - ' . es($row['school_name']) . '; ' : '')?><?=es($row['auction_name'] . ' - ' . dateToHebrew($row['auction_date']))?></OPTION>
<?endwhile;?>
</SELECT></LABEL><BR>
<? if(!empty($admin_user)): ?>
<LABEL><?=T_('Hide non-registered soldiers?')?> <INPUT type="checkbox" name="hide_not_registered" value="1" <?=$hide_not_registered ? ' CHECKED' : ''?>></LABEL><BR>
<LABEL><?=T_('Hide soldiers with zero points?')?> <INPUT type="checkbox" name="hide_no_points" value="1" <?=$hide_no_points ? ' CHECKED' : ''?>></LABEL><BR>
<? endif; ?>
<INPUT class="submit" type="submit" value="<?=T_('Go')?>">
</P>
</FORM>
<?endif;?>
</DIV>
</DIV>
<?if($auction_id != -1): ?>
<P class="noprint" style="text-align: center;"><INPUT type="button" value="<?=T_('Print')?>" onClick="print();"></P>
<?
$auction = mysql_fetch_assoc(mq("SELECT auction_points_start_date, auction_date, auction_points_trigger_date, auction_run_date, auction_message, max_prize_points FROM auctions WHERE auction_id = $auction_id AND auction_ran = 0 AND auction_date < " . unixtojd() . (empty($admin_user) || $admin_user['auth'] != 'super' ? " AND (school_id IS NULL OR school_id = $school_id)" : '')));
$users = mq("
SELECT user_id, first, last, first_he, last_he, username, dob, dob_he_offset, class_id, class_teacher, class_grade, IFNULL(class_grade+0, -1) class_grade_ord, class_sub, user_serial, school_name, school_number, school_logo_id
FROM users JOIN schools USING (school_id) LEFT JOIN classes USING (school_id, class_id)
WHERE school_id = $school_id AND (class_grade IS NULL OR class_grade > $hide_grade)" .
      ($class_id != -1 ? " AND class_id = $class_id" : '') .
      ($user_id != -1 ? " AND user_id = $user_id" : '') .
      ($hide_not_registered ? " AND user_start_date IS NOT NULL" : '') .
' ORDER BY class_grade, class_sub, last, first, user_id
');
?>
<? while($user_row = mysql_fetch_assoc($users)):?>
<?
$auction_points = auctionPoints($user_row['user_id'], $auction);
if($hide_no_points && !$auction_points['cur']) continue;
$auction_points_max = is_null($auction['max_prize_points']) ? $auction_points['cur'] : min($auction_points['cur'], $auction['max_prize_points']);
?>
<DIV class="page">
<HR class="noprint">
<TABLE class="top_banner"><TR>
<TD style="width: 33%;"><IMG src="images_auction/Header.png" style="width: 100%;" alt="CTH Global Chinese Auction Order Form"></TD>
<TD colspan="2" style="width: 66%; padding-<?=$align_start?>: 20px;">
  <DIV class="soldier soldier_<?=$align_start?>">
    <DIV class="box_dark title">
      <IMG src="images_wwtc/Rank_Private.png" alt=""><BR><IMG src="images_wwtc/Soldier.png" alt="<?=T_('Soldier')?>"><BR><IMG src="images_wwtc/Profile.png" alt="<?=T_('Profile')?>">
    </DIV>
    <DIV class="bars">
      <DIV class="bars_off"></DIV>
      <DIV class="bars_off"></DIV>
      <DIV class="bars_on"></DIV>
      <DIV class="bars_off"></DIV>
      <DIV class="bars_off"></DIV>
      <DIV class="bars_off"></DIV>
      <DIV class="bars_on"></DIV>
      <DIV class="bars_off"></DIV>
    </DIV>
  </DIV>
  <DIV class="base base_<?=$align_end?>">
    <DIV class="box_dark title">
      <?=!is_null($user_row['school_logo_id']) ? linkImgFile($user_row['school_logo_id']) : ''?><BR><SPAN><IMG src="images_wwtc/Base.png" alt="<?=T_('Base')?>"></SPAN><BR><IMG src="images_wwtc/Profile.png" alt="<?=T_('Profile')?>">
    </DIV>
    <DIV class="bars">
      <DIV class="bars_off"></DIV>
      <DIV class="bars_off"></DIV>
      <DIV class="bars_on"></DIV>
      <DIV class="bars_off"></DIV>
      <DIV class="bars_off"></DIV>
      <DIV class="bars_off"></DIV>
      <DIV class="bars_on"></DIV>
      <DIV class="bars_off"></DIV>
    </DIV>
  </DIV>
    <DIV class="box det">
      <DIV class="name"><?=es(firstInitial($user_row['first']))?> <?=es($user_row['last'])?> <!--(<?=es($user_row['username'])?>)--> <?=es(firstInitial($user_row['first_he']))?> <?=es($user_row['last_he'])?></DIV>

      <EM><?=T_('Platoon')?>:</EM> <?=$user_row['class_grade'], $user_row['class_grade']!=='' && $user_row['class_sub']!=='' ? '-' : '', $user_row['class_sub']?> &nbsp; &nbsp;
      <EM><?=T_('Teacher')?>:</EM> <?=$user_row['class_teacher']?><BR>

      <EM><?=T_('Serial')?> #:</EM> <?=$user_row['user_serial']?> &nbsp; &nbsp;
      <EM><?=T_('Age')?>:</EM> <?=calcAge(dateToJD($user_row['dob'])+$user_row['dob_he_offset'])?> &nbsp; &nbsp;
      <EM><?=T_('Total Miles')?>:</EM> <?=number_format(mysql_result(mq(totalMarks("WHERE user_id = {$user_row['user_id']}")), 0), 2)?><BR>

      <EM><?=T_('Rank')?>:</EM> Private &nbsp; &nbsp;
      <EM><?=T_('Platoon Average')?>:</EM> <?=is_null($user_row['class_id']) ? T_('N/A') : @number_format(mysql_result(mq(totalMarks("JOIN users USING (user_id) WHERE school_id = $school_id AND class_id = {$user_row['class_id']} AND user_start_date IS NOT NULL")), 0) / mysql_result(mq("SELECT COUNT(*) base_count FROM users WHERE school_id = $school_id AND class_id = {$user_row['class_id']} AND user_start_date IS NOT NULL"), 0), 2)?><BR>
      <BR>
      <EM><?=T_('TH Base')?> #:</EM> <?=$user_row['school_number']?> &mdash; <?=es($user_row['school_name'])?><BR>
      <EM><?=T_('Base Mileage')?>:</EM> <?=@number_format(mysql_result(mq(totalMarks("JOIN users USING (user_id) WHERE school_id = $school_id AND user_start_date IS NOT NULL"))), 2)?><BR>
      <BR>
    </DIV>
</TD>
</TR></TABLE>
<TABLE class="instructions box"><TR>
<TD>
<DIV style="float: <?=$align_start?>; text-align: center; font-weight: bold; width: 80px; margin-<?=$align_end?>: 10px;"><IMG src="images_wwtc/note_edit.png" alt="" width="48" height="48"><BR><IMG src="images_auction/Instructions.png" alt="<?=T_('Instructions')?>" width="80"></DIV>
<DIV style="margin-<?=$align_start?>: 80px;">
1. <?=T_('Fill in the amount of tickets per prize in the "# tix" box and the total miles needed in the "Total mi." box.')?>
</DIV>
</TD>
<TD>2. <?=T_('If you accidentally put in more tickets than your mileage allows, your base will decide which prizes to remove.')?></TD>
<TD>3. <?=sprintf(T_('Your total includes miles earned through %s. Mileage earned after %1$s will be applied to the next auction.'), dateToHebrewNoYear($auction['auction_date']))?></TD>
</TR></TABLE>
<DIV class="prizes">
<DIV class="box_dark" style="border-color: #757575; text-align: center;">
<SPAN style="font-size: 18px;">
<IMG src="images_wwtc/calendar.png" alt="" style="vertical-align: middle;"> <IMG src="images_auction/Auction_Date.png" alt="Auction Date:" height="18" style="vertical-align: baseline;"> <?=dateToHebrew($auction['auction_run_date'])?></SPAN><BR>
<?=$auction['auction_message']?>
</DIV>
<DIV class='prize first top'><SPAN class='num'><?=T_('# tix')?></SPAN> <SPAN class='tot'><?=T_('Total mi.')?></SPAN> &nbsp; &nbsp; <SPAN class='name'><?=T_('Prize#')?> &nbsp; <?=T_('Prize description')?></SPAN></DIV>
<?
$prizes = mq("SELECT prize_name, prize_number, prize_points FROM prizes_auction JOIN auction_prizes USING (prize_id) WHERE auction_id = $auction_id AND (min_grade IS NULL OR min_grade <= {$user_row['class_grade_ord']}) AND (max_grade IS NULL OR max_grade >= {$user_row['class_grade_ord']}) ORDER BY prize_points, prize_number");
$old_points = -1;
$first = true;
while($row = mysql_fetch_assoc($prizes)) {
  if($old_points != $row['prize_points']) {
    $first = true;
    echo "<DIV class='points'>{$row['prize_points']} " . T_('Miles') . "</DIV>\n";
    if($old_points == -1) {
      echo "<DIV class='prize" . ($first ? ' first' : '') ." sample'><SPAN class='num'>&nbsp; 5 &nbsp;</SPAN> <SPAN class='tot'>&nbsp; &nbsp; &nbsp;25&nbsp; &nbsp; &nbsp;</SPAN> &nbsp; &nbsp; <SPAN class='name'>500 &nbsp; " . T_('Sample prize') . "</SPAN></DIV>\n";
      $first = false;
    }
    $old_points = $row['prize_points'];
  }
  echo "<DIV class='prize" . ($first ? ' first' : '') . ($row['prize_points'] > $auction_points_max ? ' off' : '') . "'><SPAN class='num'>&nbsp;&nbsp; &nbsp; &nbsp;</SPAN> <SPAN class='tot'>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;</SPAN> &nbsp; &nbsp; <SPAN class='name'>{$row['prize_number']} &nbsp; ". es($row['prize_name']) . "</SPAN></DIV>\n";
  $first = false;
}
?>
<BR>
<DIV class="box_dark" style="border-color: #757575; line-height: 16px; position: relative; padding-<?=$align_start?>: 30px;">
<IMG src="images_wwtc/currency_dollar.png" alt="" style="position: absolute; <?=$align_start?>: -20px; margin-top: 4px;">
<DIV style="overflow: hidden; white-space: nowrap; font-weight: bold; margin-bottom: 4px;"><?=T_('Total miles used')?>: ___________________________________</DIV>
<DIV style="float: <?=$align_end?>; line-height: 32px; font-size: 32px;"><?=number_format($auction_points['cur'], 0)?></DIV>
<?=T_('Mileage available<BR>for this auction')?>:
</DIV>
</DIV>
</DIV>
<?endwhile;?>
<?endif;?>
</BODY>
</HTML>
